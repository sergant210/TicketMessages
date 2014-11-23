<?php

$chunks = array();

$tmp = array(
	'tpl.Tickets.message.one.author' => array(
		'file' => 'tickets.message.one.author',
		'description' => 'The author message chunk',
	),
	'tpl.Tickets.message.form' => array(
		'file' => 'tickets.message.form',
		'description' => '',
	),
	'tpl.Tickets.message.one.auth' => array(
		'file' => 'tickets.message.one.auth',
		'description' => 'The user message chunk',
	),
	'tpl.Tickets.message.wrapper' => array(
		'file' => 'tickets.message.wrapper',
		'description' => 'The base chunk for messages',
	),
	'tpl.Tickets.message.email.subscription' => array(
		'file' => 'tickets.message.email.subscription',
		'description' => '',
	),
	'TicketMessages' => array(
		'file' => 'tickets.messages',
		'description' => '',
	),
);

// Save chunks for setup options
$BUILD_CHUNKS = array();

foreach ($tmp as $k => $v) {
	/* @avr modChunk $chunk */
	$chunk = $modx->newObject('modChunk');
	$chunk->fromArray(array(
		'id' => 0,
		'name' => $k,
		'description' => @$v['description'],
		'snippet' => file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v['file'] . '.tpl'),
		'static' => BUILD_CHUNK_STATIC,
		'source' => 1,
		'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/chunk.' . $v['file'] . '.tpl',
	), '', true, true);

	$chunks[] = $chunk;

	$BUILD_CHUNKS[$k] = file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v['file'] . '.tpl');
}

unset($tmp);
return $chunks;