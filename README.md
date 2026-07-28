# Moodle AI Course Assistant

Moodle block plugin component: `block_aicourseassistant`.

Install this directory as:

```text
blocks/aicourseassistant
```

## What It Does

AI Course Assistant adds a course block where students can ask questions about the course. The assistant builds its answers from:

- The current conversation history.
- The course documentation attached to the block instance.
- The global and instance-level system prompts.

The plugin supports Mistral, OpenAI and Anthropic through a shared provider interface.

## Supported Documentation Files

The block instance filemanager accepts:

- `.txt`
- `.md`
- `.csv`
- `.xml`
- `.json`
- `.html`
- `.htm`
- `.pdf`

PDF extraction is intentionally basic because Moodle does not include a full PDF text extractor by default. For best results, attach text, Markdown, CSV, XML, JSON or HTML course documents.

## Setup

1. Copy or upload the `aicourseassistant` directory to `blocks/aicourseassistant`.
2. Run the Moodle upgrade.
3. Go to Site administration > Plugins > Blocks > AI Course Assistant.
4. Select the AI provider and configure its API key, model and base URL.
5. Add the block to a course.
6. Configure the block instance and attach the course documentation.

## Main Capabilities

- `block/aicourseassistant:addinstance`
- `block/aicourseassistant:myaddinstance`
- `block/aicourseassistant:use`

## AJAX Service

- Function: `block_aicourseassistant_send_message`
- Class: `block_aicourseassistant\external\chat`
- Parameters: `blockinstanceid`, `message`, `history`
