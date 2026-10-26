<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

class block_aicourseassistant extends block_base {
    public function init(): void {
        $this->title = get_string('pluginname', 'block_aicourseassistant');
    }

    public function specialization(): void {
        if (!empty($this->config->assistantname)) {
            $this->title = format_string($this->config->assistantname);
        }
    }

    public function get_content() {
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (empty($this->instance) || empty($this->context)) {
            $this->content->text = '';
            return $this->content;
        }

        if (!has_capability('block/aicourseassistant:use', $this->context, $USER)) {
            $this->content->text = '';
            return $this->content;
        }

        if (isset($this->config->enabled) && empty($this->config->enabled)) {
            $this->content->text = get_string('disabledinstance', 'block_aicourseassistant');
            return $this->content;
        }

        $assistantname = !empty($this->config->assistantname)
            ? format_string($this->config->assistantname)
            : get_string('defaultassistantname', 'block_aicourseassistant');
        $intro = !empty($this->config->intro)
            ? format_text($this->config->intro, FORMAT_HTML)
            : get_string('defaultintro', 'block_aicourseassistant');

        $templatedata = [
            'blockinstanceid' => $this->instance->id,
            'assistantname' => $assistantname,
            'intro' => $intro,
            'placeholder' => get_string('inputplaceholder', 'block_aicourseassistant'),
            'sendlabel' => get_string('send', 'block_aicourseassistant'),
            'clearlabel' => get_string('clear', 'block_aicourseassistant'),
            'privacylabel' => get_string('privacyprovider', 'block_aicourseassistant', $this->provider_name()),
            'loadinglabel' => get_string('loading', 'block_aicourseassistant'),
        ];

        $this->content->text = $OUTPUT->render_from_template('block_aicourseassistant/chat', $templatedata);
        $PAGE = $this->page;
        $PAGE->requires->js_call_amd('block_aicourseassistant/chat', 'init', [$this->instance->id]);

        return $this->content;
    }

    public function has_config(): bool {
        return true;
    }

    public function instance_allow_multiple(): bool {
        return true;
    }

    public function applicable_formats(): array {
        // Not on the Dashboard: block/aicourseassistant:use is granted through course roles only.
        return ['all' => true, 'my' => false, 'mod' => false, 'tag' => false];
    }

    private function provider_name(): string {
        $provider = get_config('block_aicourseassistant', 'llmprovider') ?: 'mistral';
        $choices = [
            'mistral' => 'Mistral',
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
        ];
        return $choices[$provider] ?? $provider;
    }
}
