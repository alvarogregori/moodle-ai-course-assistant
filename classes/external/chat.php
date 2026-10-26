<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\external;

use block_aicourseassistant\service\course_document_parser;
use block_aicourseassistant\service\llm_provider_factory;
use block_aicourseassistant\service\rate_limiter;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class chat extends \external_api {
    public static function send_message_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            'blockinstanceid' => new \external_value(PARAM_INT, 'Block instance id'),
            'message' => new \external_value(PARAM_TEXT, 'Student message'),
            'history' => new \external_value(PARAM_RAW, 'Conversation history as JSON', VALUE_DEFAULT, '[]'),
        ]);
    }

    public static function send_message(int $blockinstanceid, string $message, string $history = '[]'): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::send_message_parameters(), [
            'blockinstanceid' => $blockinstanceid,
            'message' => $message,
            'history' => $history,
        ]);

        $message = trim($params['message']);
        if ($message === '') {
            return ['success' => false, 'answer' => '', 'error' => get_string('erroremptymessage', 'block_aicourseassistant')];
        }

        $instance = $DB->get_record('block_instances', ['id' => $params['blockinstanceid'], 'blockname' => 'aicourseassistant'], '*', MUST_EXIST);
        $context = \context_block::instance($instance->id);
        self::validate_context($context);
        require_capability('block/aicourseassistant:use', $context);
        rate_limiter::check((int)$USER->id);

        $configdata = !empty($instance->configdata) ? base64_decode($instance->configdata) : '';
        $config = $configdata !== '' ? unserialize_object($configdata) : new \stdClass();
        if (isset($config->enabled) && empty($config->enabled)) {
            throw new \moodle_exception('disabledinstance', 'block_aicourseassistant');
        }

        $history = self::decode_history($params['history']);
        $messages = self::build_messages($context, $instance->id, $config, $history, $message);
        try {
            $answer = llm_provider_factory::create()->chat($messages);
        } catch (\moodle_exception $e) {
            // Site administrators also see the provider's explanation so configuration problems are diagnosable.
            if (!empty($e->debuginfo) && has_capability('moodle/site:config', \context_system::instance())) {
                throw new \moodle_exception('providererroradmin', 'block_aicourseassistant', '', (object)[
                    'message' => $e->getMessage(),
                    'detail' => clean_param(\core_text::substr($e->debuginfo, 0, 500), PARAM_NOTAGS),
                ]);
            }
            throw $e;
        }

        if ($answer === '') {
            throw new \moodle_exception('emptyllmanswer', 'block_aicourseassistant');
        }

        return ['success' => true, 'answer' => $answer, 'error' => ''];
    }

    public static function send_message_returns(): \external_single_structure {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL, 'Whether the request succeeded'),
            'answer' => new \external_value(PARAM_RAW, 'Assistant answer'),
            'error' => new \external_value(PARAM_TEXT, 'Error message'),
        ]);
    }

    private static function decode_history(string $history): array {
        $decoded = json_decode($history, true);
        if (!is_array($decoded)) {
            return [];
        }

        $safe = [];
        foreach (array_slice($decoded, -12) as $item) {
            $role = $item['role'] ?? '';
            $content = trim((string)($item['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $safe[] = [
                'role' => $role,
                'content' => \core_text::substr($content, 0, 4000),
            ];
        }
        return $safe;
    }

    private static function build_messages(\context_block $context, int $instanceid, \stdClass $config, array $history, string $message): array {
        $defaultprompt = get_config('block_aicourseassistant', 'defaultsystemprompt')
            ?: get_string('defaultsystemprompt', 'block_aicourseassistant');
        $instanceprompt = trim((string)($config->systemprompt ?? ''));
        $documents = course_document_parser::instance_documents($context, $instanceid);

        $system = trim($defaultprompt . "\n\n" . $instanceprompt);
        if ($documents !== '') {
            $system .= "\n\nCourse documentation available to answer student questions. Each document starts with a"
                . " '=== Document: ... ===' header and PDF pages are marked '--- Page N ---'; cite the document title"
                . " and page when you use them:\n\n" . $documents;
        } else {
            $system .= "\n\nNo course documentation has been attached to this block instance. Say that you do not have enough course documentation when the answer depends on missing material.";
        }

        $messages = [[
            'role' => 'system',
            'content' => $system,
        ]];

        foreach ($history as $item) {
            $messages[] = $item;
        }

        $messages[] = [
            'role' => 'user',
            'content' => \core_text::substr($message, 0, 4000),
        ];

        return $messages;
    }
}
