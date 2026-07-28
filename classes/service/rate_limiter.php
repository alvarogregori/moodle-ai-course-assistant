<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\service;

defined('MOODLE_INTERNAL') || die();

class rate_limiter {
    public static function check(int $userid): void {
        $limit = (int)(get_config('block_aicourseassistant', 'ratelimit_user') ?: 30);
        if ($limit <= 0) {
            return;
        }

        $cache = \cache::make('block_aicourseassistant', 'ratelimit');
        $key = 'u' . $userid . '_' . gmdate('YmdHi');
        $count = (int)$cache->get($key);
        if ($count >= $limit) {
            throw new \moodle_exception('ratelimited', 'block_aicourseassistant');
        }
        $cache->set($key, $count + 1);
    }
}
