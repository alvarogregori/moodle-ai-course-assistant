<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class mistral_client extends base_http_client implements llm_client_interface {
    public function chat(array $messages): string {
        $apikey = get_config('block_aicourseassistant', 'apikey');
        if (empty($apikey)) {
            throw new \moodle_exception('missingapikey', 'block_aicourseassistant');
        }

        $payload = [
            'model' => get_config('block_aicourseassistant', 'model') ?: 'mistral-small-latest',
            'messages' => $messages,
            'max_tokens' => (int)(get_config('block_aicourseassistant', 'maxtokens') ?: 800),
            'temperature' => (float)(get_config('block_aicourseassistant', 'temperature') ?: 0.3),
        ];

        $baseurl = $this->clean_base_url(get_config('block_aicourseassistant', 'apibaseurl') ?: 'https://api.mistral.ai/v1');
        $data = $this->request_json($baseurl . '/chat/completions', ['Authorization: Bearer ' . $apikey], $payload);
        return trim($data['choices'][0]['message']['content'] ?? '');
    }
}
