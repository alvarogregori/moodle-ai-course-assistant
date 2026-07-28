<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class openai_client extends base_http_client implements llm_client_interface {
    public function chat(array $messages): string {
        $apikey = get_config('block_aicourseassistant', 'openai_apikey');
        if (empty($apikey)) {
            throw new \moodle_exception('missingapikey', 'block_aicourseassistant');
        }

        $payload = [
            'model' => get_config('block_aicourseassistant', 'openai_model') ?: 'gpt-5-mini',
            'input' => $messages,
            'max_output_tokens' => (int)(get_config('block_aicourseassistant', 'maxtokens') ?: 800),
        ];

        $baseurl = $this->clean_base_url(get_config('block_aicourseassistant', 'openai_apibaseurl') ?: 'https://api.openai.com/v1');
        $data = $this->request_json($baseurl . '/responses', ['Authorization: Bearer ' . $apikey], $payload);
        if (!empty($data['output_text'])) {
            return trim($data['output_text']);
        }
        $text = '';
        foreach (($data['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text') {
                    $text .= $content['text'] ?? '';
                }
            }
        }
        return trim($text);
    }
}
