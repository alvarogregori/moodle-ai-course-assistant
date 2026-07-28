<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class anthropic_client extends base_http_client implements llm_client_interface {
    public function chat(array $messages): string {
        $apikey = get_config('block_aicourseassistant', 'anthropic_apikey');
        if (empty($apikey)) {
            throw new \moodle_exception('missingapikey', 'block_aicourseassistant');
        }

        $system = '';
        $conversation = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'system') {
                $system .= "\n\n" . ($message['content'] ?? '');
                continue;
            }
            $conversation[] = [
                'role' => ($message['role'] ?? '') === 'assistant' ? 'assistant' : 'user',
                'content' => $message['content'] ?? '',
            ];
        }

        $payload = [
            'model' => get_config('block_aicourseassistant', 'anthropic_model') ?: 'claude-sonnet-4-5',
            'system' => trim($system),
            'messages' => $conversation,
            'max_tokens' => (int)(get_config('block_aicourseassistant', 'maxtokens') ?: 800),
            'temperature' => (float)(get_config('block_aicourseassistant', 'temperature') ?: 0.3),
        ];

        $baseurl = $this->clean_base_url(get_config('block_aicourseassistant', 'anthropic_apibaseurl') ?: 'https://api.anthropic.com/v1');
        $data = $this->request_json($baseurl . '/messages', [
            'x-api-key: ' . $apikey,
            'anthropic-version: 2023-06-01',
        ], $payload);

        $text = '';
        foreach (($data['content'] ?? []) as $content) {
            if (($content['type'] ?? '') === 'text') {
                $text .= $content['text'] ?? '';
            }
        }
        return trim($text);
    }
}
