<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AI Course Assistant';
$string['aicourseassistant:addinstance'] = 'Add a new AI Course Assistant block';
$string['aicourseassistant:myaddinstance'] = 'Add a new AI Course Assistant block to Dashboard';
$string['aicourseassistant:use'] = 'Use the AI Course Assistant';
$string['defaultassistantname'] = 'AI Course Assistant';
$string['defaultintro'] = 'Ask questions about this course. The assistant answers using the course documentation attached by the teaching team.';
$string['defaultsystemprompt'] = 'You are AI Course Assistant, a helpful assistant for students inside a Moodle course. Answer using only the attached course documentation and the conversation context. If the documentation does not contain enough information, say so clearly and suggest where the student should check next. Do not invent policies, dates, grades, assessment rules, or teacher instructions.';
$string['inputplaceholder'] = 'Ask a question about the course...';
$string['send'] = 'Send';
$string['clear'] = 'Clear';
$string['loading'] = 'Thinking...';
$string['privacyprovider'] = 'Questions are sent to {$a} with the relevant conversation context and attached course documentation.';
$string['disabledinstance'] = 'This assistant is disabled.';
$string['erroremptymessage'] = 'Write a question before sending.';
$string['emptyllmanswer'] = 'The assistant returned an empty answer.';
$string['llmrequestfailed'] = 'The AI provider request failed. HTTP status: {$a}.';
$string['missingapikey'] = 'The selected AI provider API key is missing.';
$string['ratelimited'] = 'You have sent too many messages recently. Please try again in a minute.';
$string['config:enabled'] = 'Enable this assistant';
$string['config:assistantname'] = 'Assistant name';
$string['config:intro'] = 'Introductory text';
$string['config:systemprompt'] = 'Additional system prompt';
$string['config:documents'] = 'Course documentation';
$string['config:documents_help'] = 'Attach the course documentation that the assistant should use as context. Text, Markdown, CSV, XML, JSON, HTML and basic PDF text extraction are supported.';
$string['settings:llmprovider'] = 'AI provider';
$string['settings:llmprovider_desc'] = 'Provider used for assistant responses.';
$string['settings:apikey'] = 'API key';
$string['settings:model'] = 'Model';
$string['settings:apibaseurl'] = 'API base URL';
$string['settings:generation'] = 'Generation';
$string['settings:maxtokens'] = 'Maximum tokens';
$string['settings:temperature'] = 'Temperature';
$string['settings:defaultsystemprompt'] = 'Default system prompt';
$string['settings:ratelimit_user'] = 'Messages per user per minute';
