<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var TicketMessages $TicketMessages */
$TicketMessages = $modx->getService('ticketmessages', 'TicketMessages', $modx->getOption('ticketmessages_core_path', null, $modx->getOption('core_path') . 'components/ticketmessages/') . 'model/ticketmessages/');
$modx->lexicon->load('ticketmessages:default');

// handle request
$corePath = $modx->getOption('ticketmessages_core_path', null, $modx->getOption('core_path') . 'components/ticketmessages/');
$path = $modx->getOption('processorsPath', $TicketMessages->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));