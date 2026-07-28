<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_aicourseassistant_send_message' => [
        'classname' => 'block_aicourseassistant\external\chat',
        'methodname' => 'send_message',
        'classpath' => '',
        'description' => 'Send a student question to the AI Course Assistant.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'block/aicourseassistant:use',
    ],
];
