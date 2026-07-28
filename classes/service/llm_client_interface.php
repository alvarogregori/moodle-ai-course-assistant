<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

interface llm_client_interface {
    public function chat(array $messages): string;
}
