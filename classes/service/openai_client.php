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

        $model = get_config('block_aicourseassistant', 'openai_model') ?: 'gpt-5-mini';
        $payload = [
            'model' => $model,
            'input' => $messages,
            'max_output_tokens' => (int)(get_config('block_aicourseassistant', 'maxtokens') ?: 800),
        ];
        // Reasoning tokens count against max_output_tokens; keep effort low so an answer still fits.
        if (self::is_reasoning_model($model)) {
            $payload['reasoning'] = ['effort' => 'low'];
        }

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
        $text = trim($text);
        if ($text === '' && ($data['status'] ?? '') === 'incomplete') {
            throw new \moodle_exception('emptyllmanswer', 'block_aicourseassistant', '', null,
                'Response incomplete: ' . ($data['incomplete_details']['reason'] ?? 'unknown reason'));
        }
        return $text;
    }

    private static function is_reasoning_model(string $model): bool {
        $model = strtolower($model);
        if (strpos($model, '-chat') !== false) {
            return false;
        }
        return (bool)preg_match('/^(gpt-5|o\d)/', $model);
    }
}
