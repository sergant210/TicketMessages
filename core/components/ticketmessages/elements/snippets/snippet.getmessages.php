<?php
/** @var array $scriptProperties */
if (empty($thread)) {$scriptProperties['thread'] = $modx->getOption('thread', $scriptProperties, 'resource-'.$modx->resource->id, true);}

if (empty($message_user)) $message_user = 0;
if (!isset($modx->resource) && isset($_SESSION['TicketComments']['scriptProperties']) ) {
	$modx->resource = $modx->getObject('modResource',$_SESSION['TicketComments']['scriptProperties']['resource']);
}

$scriptProperties['resource'] = $modx->resource->id;
$scriptProperties['snippetPrepareComment'] = $modx->getOption('tickets.snippet_prepare_comment');

if (empty($tplComments)) {$tplComments = 'tpl.Tickets.message.wrapper';}
if (empty($tplCommentForm)) {$tplCommentForm = 'tpl.Tickets.message.form';}
if (empty($tplCommentAuth)) {$tplCommentAuth = 'tpl.Tickets.message.one.auth';}
if (empty($tplCommentAuthor)) {$tplCommentAuthor = 'tpl.Tickets.message.one.author';}
if (empty($tplCommentDeleted)) {$tplCommentDeleted = 'tpl.Tickets.comment.one.deleted';}
if (empty($outputSeparator)) {$outputSeparator = "\n";}

preg_match_all('/\d+/', $scriptProperties['thread'], $matches);
$subscribers = array_map(intval,$matches[0]);

/** @var Tickets $Tickets */
$Tickets = $modx->getService('tickets','Tickets',$modx->getOption('tickets.core_path',null,$modx->getOption('core_path').'components/tickets/').'model/tickets/',$scriptProperties);
$Tickets->initialize($modx->context->key, $scriptProperties);

/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
if (!$pdoClass = $modx->loadClass($fqn, '', false, true)) {return false;}
$pdoFetch = new $pdoClass($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

// Prepare Ticket Thread
/** @var TicketThread $thread */
if (!$thread = $modx->getObject('TicketThread', array('name' => $scriptProperties['thread']))) {
	$thread = $modx->newObject('TicketThread');
	$thread->fromArray(array(
		'name' => $scriptProperties['thread'],
		'resource' => $modx->resource->id,
		'createdby' => $modx->user->id,
		'createdon' => date('Y-m-d H:i:s'),
		'subscribers' => $subscribers,
	));
}
elseif ($thread->get('deleted')) {
	die('{"success":false,"message":"'.$modx->lexicon('ticket_thread_err_deleted').'"}');
}

$thread->set('properties', $scriptProperties);
$thread->save();

// Prepare query to db
$class = 'TicketComment';
$where = array();
if (empty($showUnpublished)) {$where['published'] = 1;}

// Joining tables
$innerJoin = array(
	'Thread' => array(
		'class' => 'TicketThread',
		'on' => '`Thread`.`id` = `TicketComment`.`thread` AND `Thread`.`name` = "'.$thread->get('name').'"'
	)
);
$leftJoin = array(
	'User' => array('class' => 'modUser', 'on' => '`User`.`id` = `TicketComment`.`createdby`'),
	'Profile' => array('class' => 'modUserProfile', 'on' => '`Profile`.`internalKey` = `TicketComment`.`createdby`'),
);
// Fields to select
$select = array(
	'TicketComment' => $modx->getSelectColumns('TicketComment', 'TicketComment', '', array('raw'), true) . ', `parent` as `new_parent`, `rating` as `rating_total`',
	'Thread' => '`Thread`.`resource`',
	'User' => '`User`.`username`',
	'Profile' => $modx->getSelectColumns('modUserProfile', 'Profile', '', array('id','email'), true) . ',`Profile`.`email` as `user_email`',
);

// Add custom parameters
foreach (array('where','select','leftJoin','innerJoin') as $v) {
	if (!empty($scriptProperties[$v])) {
		$tmp = $modx->fromJSON($scriptProperties[$v]);
		if (is_array($tmp)) {
			$$v = array_merge($$v, $tmp);
		}
	}
	unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = array(
	'class' => $class,
	'where' => $modx->toJSON($where),
	'innerJoin' => $modx->toJSON($innerJoin),
	'leftJoin' => $modx->toJSON($leftJoin),
	'select' => $modx->toJSON($select),
	'sortby' => $class.'.id',
	'sortdir' => 'ASC',
	'groupby' => $class.'.id',
	'limit' => 0,
	'fastMode' => true,
	'return' => 'data',
	'nestedChunkPrefix' => 'tickets_',
);

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$pdoFetch->addTime('Query parameters prepared.');
$rows = $pdoFetch->run();

// Processing rows
$output = $commentsThread = null;
if (!empty($rows) && is_array($rows)) {
	$tmp = array();
	$i = 1;
	foreach ($rows as $row)  {
		$row['idx'] = $i ++;
		$tmp[$row['id']] = $row;
	}
	$rows = $thread->buildTree($tmp, $depth);
	unset($tmp, $i);

	if (!empty($formBefore)) {
		$rows = array_reverse($rows);
	}
	foreach ($rows as $row) {
		$tpl = $row['createdby'] == $modx->user->id
			? $tplCommentAuthor
			: $tplCommentAuth;

		$output[] = $Tickets->templateNode($row, $tpl);
	}

	$pdoFetch->addTime('Returning processed chunks');
	$output = implode($outputSeparator, $output);
}
if ($thread->get('closed')) {
	$output .= '<li class="alert alert-danger thread-alert">Тема закрыта!</li>';
}
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="CommentsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

die($modx->toJSON(array('success'=>true,'data'=>$output)));