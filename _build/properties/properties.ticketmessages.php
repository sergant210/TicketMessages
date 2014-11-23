<?php

$properties = array();

$tmp = array(
	'fastMode' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	'gravatarIcon' => array(
		'type' => 'textfield',
		'value' => 'mm',
	),
	'gravatarSize' => array(
		'type' => 'numberfield',
		'value' => '24',
	),
	'gravatarUrl' => array(
		'type' => 'textfield',
		'value' => 'https://www.gravatar.com/avatar/',
	),
	'tplCommentForm' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.message.form',
	),
	'tplCommentAuth' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.message.one.auth',
	),
	'tplCommentAuthor' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.message.one.author',
	),
	'tplCommentDeleted' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.comment.one.deleted',
	),
	'tplComments' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.message.wrapper',
	),
	'tplCommentEmailSubscription' => array(
		'type' => 'textfield',
		'value' => 'tpl.Tickets.message.email.subscription',
	),
	'autoPublish' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
);

foreach ($tmp as $k => $v) {
	$properties[$k] = array_merge(array(
		'name' => $k,
		'desc' => 'ticketmessages_prop_'.$k,
		'lexicon' => 'ticketmessages:properties',
	), $v);
}

return $properties;
