<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class llm_provider_factory {
    public static function create(): llm_client_interface {
        $provider = get_config('block_aicourseassistant', 'llmprovider') ?: 'mistral';
        return match ($provider) {
            'openai' => new openai_client(),
            'anthropic' => new anthropic_client(),
            default => new mistral_client(),
        };
    }
}
