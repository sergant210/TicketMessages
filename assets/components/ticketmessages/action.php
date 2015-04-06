<?php

if (empty($_REQUEST['action'])) {
	die('Access denied');
}
else {
	$action = $_REQUEST['action'];
}

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/index.php';

$modx->getService('error','error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

// Get properties
$properties = array();
/* @var TicketThread $thread */
if (!empty($_REQUEST['thread']) && $thread = $modx->getObject('TicketThread', array('name' => filter_input(INPUT_POST,'thread',FILTER_SANITIZE_SPECIAL_CHARS)))) {
	if ($thread->get('closed') && $action == 'comment/getlist') die('{"success":false,"message":"Ветка закрыта."}');
	$properties = $thread->get('properties');
} else {
	$properties['thread'] = filter_input(INPUT_POST,'thread',FILTER_SANITIZE_SPECIAL_CHARS);
	$properties['resource'] = filter_input(INPUT_POST,'resource',FILTER_SANITIZE_NUMBER_INT);
}

// Switch context
if (isset($properties['context'])) {$context = $properties['context'];} else {$context = 'web';}

if (!empty($thread) && $thread->resource && $resource = $thread->getOne('Resource')) {
	$context = $resource->get('context_key');
}
elseif (!empty($_REQUEST['ctx']) && $modx->getCount('modContext', $_REQUEST['ctx'])) {
	$context = filter_input(INPUT_POST,'ctx',FILTER_SANITIZE_SPECIAL_CHARS);
}
if ($context != 'web') {
	$modx->switchContext($context);
}
if ($action == 'message/save' && isset($properties['tplCommentAuthor'])) $properties['tplCommentAuth'] = $properties['tplCommentAuthor'];

/* @var Tickets $Tickets */
define('MODX_ACTION_MODE', true);
if ($action !== 'message/getlist') {
	$Tickets = $modx->getService('tickets', 'Tickets', $modx->getOption('tickets.core_path', NULL, $modx->getOption('core_path') . 'components/tickets/') . 'model/tickets/', $properties);
	if ($modx->error->hasError() || !($Tickets instanceof Tickets)) {
		die('Error');
	}
}
switch ($action) {
	case 'message/getlist':	$response = $modx->runSnippet('getMessages',$properties);	break;
	case 'message/save':
	case 'comment/save': $response = $Tickets->saveComment($_POST); break;
	case 'comment/getlist': $response = $Tickets->getNewComments($_POST['thread']); break;
	default:
		$message = $_REQUEST['action'] != $action ? 'tickets_err_register_globals' : 'tickets_err_unknown';
		$response = $modx->toJSON(array('success' => false, 'message' => $modx->lexicon($message)));
}

if (is_array($response)) {
	$response = $modx->toJSON($response);
}

@session_write_close();
exit($response);
