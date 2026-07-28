<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

class block_aicourseassistant_edit_form extends block_edit_form {
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('advcheckbox', 'config_enabled', get_string('config:enabled', 'block_aicourseassistant'));
        $mform->setDefault('config_enabled', 1);

        $mform->addElement('text', 'config_assistantname', get_string('config:assistantname', 'block_aicourseassistant'));
        $mform->setType('config_assistantname', PARAM_TEXT);
        $mform->setDefault('config_assistantname', get_string('defaultassistantname', 'block_aicourseassistant'));

        $mform->addElement('textarea', 'config_intro', get_string('config:intro', 'block_aicourseassistant'), ['rows' => 4, 'cols' => 60]);
        $mform->setType('config_intro', PARAM_RAW);
        $mform->setDefault('config_intro', get_string('defaultintro', 'block_aicourseassistant'));

        $mform->addElement('textarea', 'config_systemprompt', get_string('config:systemprompt', 'block_aicourseassistant'), ['rows' => 8, 'cols' => 60]);
        $mform->setType('config_systemprompt', PARAM_RAW);

        $mform->addElement('filemanager', 'config_documents', get_string('config:documents', 'block_aicourseassistant'), null, $this->file_options());
        $mform->addHelpButton('config_documents', 'config:documents', 'block_aicourseassistant');
    }

    public function set_data($defaults): void {
        if (!empty($this->block->context) && !empty($this->block->instance->id)) {
            $draftitemid = file_get_submitted_draft_itemid('config_documents');
            file_prepare_draft_area(
                $draftitemid,
                $this->block->context->id,
                'block_aicourseassistant',
                'documents',
                $this->block->instance->id,
                $this->file_options()
            );
            $defaults->config_documents = $draftitemid;
        }
        parent::set_data($defaults);
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data && !empty($this->block->context) && !empty($this->block->instance->id)) {
            file_save_draft_area_files(
                $data->config_documents,
                $this->block->context->id,
                'block_aicourseassistant',
                'documents',
                $this->block->instance->id,
                $this->file_options()
            );
        }
        return $data;
    }

    private function file_options(): array {
        return [
            'subdirs' => 0,
            'maxfiles' => 20,
            'accepted_types' => ['.txt', '.md', '.csv', '.xml', '.json', '.html', '.htm', '.pdf'],
        ];
    }
}
