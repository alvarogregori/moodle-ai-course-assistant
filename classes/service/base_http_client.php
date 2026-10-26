<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

abstract class base_http_client {
    protected function request_json(string $url, array $headers, array $payload): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $headers[] = 'Content-Type: application/json';
        $response = $curl->post($url, json_encode($payload), [
            'CURLOPT_HTTPHEADER' => $headers,
            'CURLOPT_TIMEOUT' => 60,
            'CURLOPT_CONNECTTIMEOUT' => 15,
        ]);

        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);
        $decoded = json_decode((string)$response, true);

        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            throw new \moodle_exception('llmrequestfailed', 'block_aicourseassistant', '', $status ?: 'unknown',
                $this->error_detail($decoded, (string)$response, (string)($curl->error ?? '')));
        }

        return $decoded;
    }

    /**
     * Extract the provider's own error explanation (kept as debuginfo, shown to site administrators only).
     */
    private function error_detail($decoded, string $raw, string $curlerror): string {
        if (is_array($decoded)) {
            // OpenAI and Anthropic: {"error": {"message": ...}}; Mistral: {"message": ...} or {"detail": ...}.
            $message = $decoded['error']['message'] ?? $decoded['message'] ?? $decoded['detail'] ?? null;
            if (is_string($message) && $message !== '') {
                return $message;
            }
        }
        if ($curlerror !== '') {
            return $curlerror;
        }
        return \core_text::substr(trim($raw), 0, 300);
    }

    protected function clean_base_url(string $url): string {
        return rtrim($url, '/');
    }
}
