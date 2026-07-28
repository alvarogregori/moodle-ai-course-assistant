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
        $decoded = json_decode($response, true);

        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            throw new \moodle_exception('llmrequestfailed', 'block_aicourseassistant', '', $status ?: 'unknown');
        }

        return $decoded;
    }

    protected function clean_base_url(string $url): string {
        return rtrim($url, '/');
    }
}
