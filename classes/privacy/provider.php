<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_aicourseassistant\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * No personal data is stored in Moodle (rate-limit counters are short-lived cache entries),
 * but student questions and course documents are sent to the configured AI provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {
        $fields = [
            'message' => 'privacy:metadata:message',
            'history' => 'privacy:metadata:history',
            'documents' => 'privacy:metadata:documents',
        ];
        foreach (['mistral', 'openai', 'anthropic'] as $provider) {
            $collection->add_external_location_link($provider, $fields, 'privacy:metadata:' . $provider);
        }
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    public static function get_users_in_context(userlist $userlist): void {
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
    }
}
