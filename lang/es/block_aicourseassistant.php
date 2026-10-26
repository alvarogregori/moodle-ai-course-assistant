<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AI Course Assistant';
$string['aicourseassistant:addinstance'] = 'Añadir un nuevo bloque AI Course Assistant';
$string['aicourseassistant:myaddinstance'] = 'Añadir un nuevo bloque AI Course Assistant al Área personal';
$string['aicourseassistant:use'] = 'Usar AI Course Assistant';
$string['defaultassistantname'] = 'AI Course Assistant';
$string['defaultintro'] = 'Haz preguntas sobre este curso. El asistente responde usando la documentación del curso adjunta por el equipo docente.';
$string['defaultsystemprompt'] = 'Eres AI Course Assistant, un asistente útil para estudiantes dentro de un curso Moodle. Responde usando solo la documentación del curso adjunta y el contexto de la conversación. Si la documentación no contiene información suficiente, dilo claramente y sugiere dónde debería consultar el estudiante. No inventes políticas, fechas, calificaciones, normas de evaluación ni instrucciones del profesorado.';
$string['inputplaceholder'] = 'Pregunta algo sobre el curso...';
$string['send'] = 'Enviar';
$string['clear'] = 'Limpiar';
$string['loading'] = 'Pensando...';
$string['privacyprovider'] = 'Las preguntas se envían a {$a} con el contexto relevante de la conversación y la documentación adjunta del curso.';
$string['disabledinstance'] = 'Este asistente está desactivado.';
$string['erroremptymessage'] = 'Escribe una pregunta antes de enviar.';
$string['emptyllmanswer'] = 'El asistente devolvió una respuesta vacía.';
$string['llmrequestfailed'] = 'La petición al proveedor de IA falló. Estado HTTP: {$a}.';
$string['missingapikey'] = 'Falta la clave API del proveedor de IA seleccionado.';
$string['ratelimited'] = 'Has enviado demasiados mensajes recientemente. Inténtalo de nuevo en un minuto.';
$string['providererroradmin'] = '{$a->message} Detalle del proveedor (solo visible para administradores): {$a->detail}';
$string['cachedef_ratelimit'] = 'Contadores de mensajes por usuario para la limitación de uso';
$string['cachedef_pdftext'] = 'Texto extraído de los documentos PDF adjuntos';
$string['privacy:metadata:mistral'] = 'Cuando Mistral es el proveedor configurado, las preguntas se envían a Mistral AI para generar respuestas.';
$string['privacy:metadata:openai'] = 'Cuando OpenAI es el proveedor configurado, las preguntas se envían a OpenAI para generar respuestas.';
$string['privacy:metadata:anthropic'] = 'Cuando Anthropic Claude es el proveedor configurado, las preguntas se envían a Anthropic para generar respuestas.';
$string['privacy:metadata:message'] = 'La pregunta que el usuario escribe en el asistente.';
$string['privacy:metadata:history'] = 'Preguntas y respuestas anteriores de la conversación actual.';
$string['privacy:metadata:documents'] = 'Texto extraído de la documentación del curso adjunta al bloque.';
$string['config:enabled'] = 'Activar este asistente';
$string['config:assistantname'] = 'Nombre del asistente';
$string['config:intro'] = 'Texto introductorio';
$string['config:systemprompt'] = 'Prompt de sistema adicional';
$string['config:documents'] = 'Documentación del curso';
$string['config:documents_help'] = 'Adjunta la documentación del curso que el asistente debe usar como contexto. Se soportan texto, Markdown, CSV, XML, JSON, HTML y PDF con texto (los PDF escaneados no se pueden leer).';
$string['settings:llmprovider'] = 'Proveedor de IA';
$string['settings:llmprovider_desc'] = 'Proveedor usado para las respuestas del asistente.';
$string['settings:apikey'] = 'Clave API';
$string['settings:model'] = 'Modelo';
$string['settings:apibaseurl'] = 'URL base de API';
$string['settings:generation'] = 'Generación';
$string['settings:maxtokens'] = 'Tokens máximos';
$string['settings:temperature'] = 'Temperatura';
$string['settings:defaultsystemprompt'] = 'Prompt de sistema por defecto';
$string['settings:ratelimit_user'] = 'Mensajes por usuario y minuto';
