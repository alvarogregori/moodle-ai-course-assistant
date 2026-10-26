<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Turns the documents attached to a block instance into plain text for the system prompt.
 *
 * Every document keeps its title and boundaries ("=== Document: ... ===" / "=== End of document ... ===")
 * and PDF pages are marked "--- Page N ---", so the model can cite where an answer comes from.
 */
class course_document_parser {
    /** Total characters of documentation placed in the prompt, shared by all documents. */
    private const MAX_CHARS = 60000;

    public static function instance_documents(\context_block $context, int $instanceid): string {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_aicourseassistant', 'documents', $instanceid, 'filename', false);

        $documents = [];
        foreach ($files as $file) {
            if (!$file->is_directory()) {
                $documents[] = self::read_document($file);
            }
        }
        if (!$documents) {
            return '';
        }

        $budgets = self::allocate_budgets($documents);
        $rendered = [];
        foreach ($documents as $i => $document) {
            $rendered[] = self::render($document, $budgets[$i]);
        }
        return implode("\n\n", $rendered);
    }

    /**
     * @return array ['title' => string, 'filename' => string, 'sections' => [['label' => ?string, 'text' => string]]]
     */
    private static function read_document(\stored_file $file): array {
        $filename = $file->get_filename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $title = pathinfo($filename, PATHINFO_FILENAME);

        if ($extension === 'pdf') {
            $pdf = self::pdf_pages($file);
            $sections = [];
            foreach ($pdf['pages'] as $number => $text) {
                if ($text !== '') {
                    $sections[] = ['label' => 'Page ' . ($number + 1), 'text' => $text];
                }
            }
            return ['title' => $pdf['title'] !== '' ? $pdf['title'] : $title, 'filename' => $filename, 'sections' => $sections];
        }

        $content = $file->get_content();
        if (in_array($extension, ['html', 'htm'], true)) {
            $content = \html_to_text($content, 0, false);
        } else if ($extension === 'xml') {
            $content = self::normalise_xml($content);
        } else if (!in_array($extension, ['txt', 'md', 'csv', 'json'], true)) {
            $content = '';
        }
        $content = self::clean_text($content);
        $sections = $content !== '' ? [['label' => null, 'text' => $content]] : [];
        return ['title' => $title, 'filename' => $filename, 'sections' => $sections];
    }

    /**
     * Extract PDF text per page. Parsing is slow for large files, so results are cached by content hash.
     *
     * @return array ['title' => string, 'pages' => string[]]
     */
    private static function pdf_pages(\stored_file $file): array {
        $cache = \cache::make('block_aicourseassistant', 'pdftext');
        $key = $file->get_contenthash();
        $cached = $cache->get($key);
        if ($cached !== false) {
            return json_decode($cached, true);
        }

        self::register_pdfparser_autoloader();
        \raise_memory_limit(MEMORY_EXTRA);
        \core_php_time_limit::raise(120);

        $result = ['title' => '', 'pages' => []];
        try {
            $config = new \Smalot\PdfParser\Config();
            $config->setRetainImageContent(false);
            $pdf = (new \Smalot\PdfParser\Parser([], $config))->parseContent($file->get_content());
            foreach ($pdf->getPages() as $page) {
                $result['pages'][] = self::clean_text($page->getText());
            }
            $title = $pdf->getDetails()['Title'] ?? '';
            $result['title'] = is_string($title) ? self::clean_text($title) : '';
        } catch (\Throwable $e) {
            debugging('block_aicourseassistant: PDF text extraction failed for ' . $file->get_filename() . ': '
                . $e->getMessage(), DEBUG_DEVELOPER);
        }

        // Failures are cached too, so a broken PDF is not re-parsed on every message.
        $cache->set($key, json_encode($result));
        return $result;
    }

    private static function register_pdfparser_autoloader(): void {
        static $registered = false;
        if ($registered) {
            return;
        }
        spl_autoload_register(function (string $class): void {
            if (strpos($class, 'Smalot\\PdfParser\\') !== 0) {
                return;
            }
            $path = __DIR__ . '/../../thirdparty/pdfparser/src/' . str_replace('\\', '/', $class) . '.php';
            if (is_readable($path)) {
                require_once($path);
            }
        });
        $registered = true;
    }

    /**
     * Share the character budget so small documents are kept whole and large ones split what is left,
     * instead of the first large document using up the whole limit.
     *
     * @return int[] Budget per document, in the same order as $documents.
     */
    private static function allocate_budgets(array $documents): array {
        $needs = [];
        foreach ($documents as $i => $document) {
            $needs[$i] = \core_text::strlen(self::render($document, PHP_INT_MAX));
        }
        asort($needs);

        $budgets = [];
        $remaining = self::MAX_CHARS;
        $left = count($needs);
        foreach ($needs as $i => $need) {
            $budgets[$i] = min($need, intdiv($remaining, $left));
            $remaining -= $budgets[$i];
            $left--;
        }
        ksort($budgets);
        return $budgets;
    }

    private static function render(array $document, int $budget): string {
        $header = '=== Document: ' . $document['title'] . " ===\nFile: " . $document['filename'];
        $footer = '=== End of document: ' . $document['title'] . ' ===';

        if (!$document['sections']) {
            return $header . "\nNo readable text could be extracted from this file.\n" . $footer;
        }

        $chunks = [];
        foreach ($document['sections'] as $section) {
            $chunks[] = "\n" . ($section['label'] !== null ? '--- ' . $section['label'] . " ---\n" : '') . $section['text'] . "\n";
        }
        $full = $header . implode('', $chunks) . $footer;
        if (\core_text::strlen($full) <= $budget) {
            return $full;
        }

        $body = '';
        $complete = 0;
        $partial = false;
        // Room for the header, footer and the truncation note.
        $room = $budget - \core_text::strlen($header . $footer) - 200;
        foreach ($chunks as $chunk) {
            $length = \core_text::strlen($chunk);
            if ($length <= $room) {
                $body .= $chunk;
                $room -= $length;
                $complete++;
                continue;
            }
            if ($room > 500) {
                // Keep the start of the section, cut at a word boundary.
                $part = \core_text::substr($chunk, 0, $room);
                $body .= preg_replace('/\s+\S*$/u', '', $part) . " […]\n";
                $partial = true;
            }
            break;
        }

        $total = count($chunks);
        $missing = $total - $complete - ($partial ? 1 : 0);
        $body .= '[Document truncated to fit the documentation limit: ' . ($partial ? 'one section cut short, ' : '')
            . "{$missing} of {$total} section(s) not included.]\n";
        return $header . $body . $footer;
    }

    private static function normalise_xml(string $content): string {
        $text = preg_replace('/<[^>]+>/', ' ', $content);
        return html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function clean_text(string $content): string {
        $content = \core_text::convert($content, 'UTF-8');
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content) ?? '';
        $content = preg_replace('/[ \t]+/', ' ', $content);
        $content = preg_replace('/\R{3,}/', "\n\n", $content);
        return trim($content ?? '');
    }
}
