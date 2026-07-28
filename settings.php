<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configselect(
        'block_aicourseassistant/llmprovider',
        get_string('settings:llmprovider', 'block_aicourseassistant'),
        get_string('settings:llmprovider_desc', 'block_aicourseassistant'),
        'mistral',
        [
            'mistral' => 'Mistral',
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic Claude',
        ]
    ));

    $settings->add(new admin_setting_heading('block_aicourseassistant/mistralheading', 'Mistral', ''));
    $settings->add(new admin_setting_configpasswordunmask('block_aicourseassistant/apikey', get_string('settings:apikey', 'block_aicourseassistant'), '', ''));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/model', get_string('settings:model', 'block_aicourseassistant'), '', 'mistral-small-latest'));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/apibaseurl', get_string('settings:apibaseurl', 'block_aicourseassistant'), '', 'https://api.mistral.ai/v1'));

    $settings->add(new admin_setting_heading('block_aicourseassistant/openaiheading', 'OpenAI', ''));
    $settings->add(new admin_setting_configpasswordunmask('block_aicourseassistant/openai_apikey', get_string('settings:apikey', 'block_aicourseassistant'), '', ''));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/openai_model', get_string('settings:model', 'block_aicourseassistant'), '', 'gpt-5-mini'));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/openai_apibaseurl', get_string('settings:apibaseurl', 'block_aicourseassistant'), '', 'https://api.openai.com/v1'));

    $settings->add(new admin_setting_heading('block_aicourseassistant/anthropicheading', 'Anthropic', ''));
    $settings->add(new admin_setting_configpasswordunmask('block_aicourseassistant/anthropic_apikey', get_string('settings:apikey', 'block_aicourseassistant'), '', ''));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/anthropic_model', get_string('settings:model', 'block_aicourseassistant'), '', 'claude-sonnet-4-5'));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/anthropic_apibaseurl', get_string('settings:apibaseurl', 'block_aicourseassistant'), '', 'https://api.anthropic.com/v1'));

    $settings->add(new admin_setting_heading('block_aicourseassistant/generationheading', get_string('settings:generation', 'block_aicourseassistant'), ''));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/maxtokens', get_string('settings:maxtokens', 'block_aicourseassistant'), '', 800, PARAM_INT));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/temperature', get_string('settings:temperature', 'block_aicourseassistant'), '', '0.3', PARAM_FLOAT));
    $settings->add(new admin_setting_configtextarea(
        'block_aicourseassistant/defaultsystemprompt',
        get_string('settings:defaultsystemprompt', 'block_aicourseassistant'),
        '',
        get_string('defaultsystemprompt', 'block_aicourseassistant')
    ));
    $settings->add(new admin_setting_configtext('block_aicourseassistant/ratelimit_user', get_string('settings:ratelimit_user', 'block_aicourseassistant'), '', 30, PARAM_INT));
}
