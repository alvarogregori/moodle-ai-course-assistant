<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class course_document_parser {
    private const MAX_CHARS = 60000;

    public static function instance_documents(\context_block $context, int $instanceid): string {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_aicourseassistant', 'documents', $instanceid, 'filename', false);
        $parts = [];

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $parts[] = self::extract_file($file);
            if (\core_text::strlen(implode("\n\n", $parts)) > self::MAX_CHARS) {
                break;
            }
        }

        $text = trim(implode("\n\n", array_filter($parts)));
        if (\core_text::strlen($text) > self::MAX_CHARS) {
            $text = \core_text::substr($text, 0, self::MAX_CHARS);
        }
        return $text;
    }

    private static function extract_file(\stored_file $file): string {
        $filename = $file->get_filename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $content = $file->get_content();

        if ($extension === 'pdf') {
            $content = self::extract_pdf_text($content);
        } else if (in_array($extension, ['html', 'htm'], true)) {
            $content = \html_to_text($content, 0, false);
        } else if ($extension === 'xml') {
            $content = self::normalise_xml($content);
        } else if (!in_array($extension, ['txt', 'md', 'csv', 'json'], true)) {
            $content = '';
        }

        $content = self::clean_text($content);
        if ($content === '') {
            return "Document: {$filename}\nNo readable text could be extracted from this file.";
        }
        return "Document: {$filename}\n{$content}";
    }

    private static function normalise_xml(string $content): string {
        $text = preg_replace('/<[^>]+>/', ' ', $content);
        return html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function extract_pdf_text(string $content): string {
        preg_match_all('/\(([^()]*)\)\s*Tj/s', $content, $matches);
        $text = implode(' ', $matches[1] ?? []);
        $text = str_replace(['\\(', '\\)', '\\n', '\\r'], ['(', ')', "\n", "\n"], $text);
        return $text;
    }

    private static function clean_text(string $content): string {
        $content = \core_text::convert($content, 'UTF-8');
        $content = preg_replace('/[ \t]+/', ' ', $content);
        $content = preg_replace('/\R{3,}/', "\n\n", $content);
        return trim($content ?? '');
    }
}
