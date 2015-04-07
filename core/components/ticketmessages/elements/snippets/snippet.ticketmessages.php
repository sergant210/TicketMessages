<?php
/** @var array $scriptProperties */
$scriptProperties['resource'] = $modx->resource->id;
$scriptProperties['snippetPrepareComment'] = $modx->getOption('tickets.snippet_prepare_comment');
$scriptProperties['tickets.action_url'] = $modx->getOption('assets_url').'components/ticketmessages/action.php';

// Отключаем настройки Tickets.
//Временное решение. В следующих версиях Tickets Василий обещал сделать возможность подключать собственные стили и скрипты вместо дефолтных.
$tickets_js = $modx->getOption('tickets.frontend_js');
$tickets_css = $modx->getOption('tickets.frontend_css');
$modx->setOption('tickets.frontend_js','');
$modx->setOption('tickets.frontend_css','');

/** @var Tickets $Tickets */
$Tickets = $modx->getService('tickets','Tickets',$modx->getOption('tickets.core_path',null,$modx->getOption('core_path').'components/tickets/').'model/tickets/',$scriptProperties);
$Tickets->initialize($modx->context->key, $scriptProperties);
//Возвращаем настройки обратно
$modx->setOption('tickets.frontend_js',$tickets_js);
$modx->setOption('tickets.frontend_css',$tickets_css);

/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
if (!$pdoClass = $modx->loadClass($fqn, '', false, true)) {return false;}
$pdoFetch = new $pdoClass($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

$commentsThread = $pdoFetch->getChunk($tplComments);
$form = $pdoFetch->getChunk($tplCommentForm);

$output = $commentsThread . $form;

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="CommentsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

$msg_refresh_interval = $modx->getOption('tm.msg_refresh_interval', null, '20')*1000;
$scriptProperties['resource'] = $modx->resource->id;
$_SESSION['ticketmessages']['sp'] = $scriptProperties;
//Регистрируем стили
//Если Bootstrap уже грузится, то отключите загрузку
$modx->regClientCSS($modx->getOption('assets_url').'components/ticketmessages/css/lib/bootstrap.min.css');
$modx->regClientCSS($modx->getOption('assets_url').'components/ticketmessages/css/messages.css');
//Регистрируем скрипты
$modx->regClientStartupScript("<script type=\"text/javascript\">\n\tvar msg_refresh_interval=".$msg_refresh_interval.";\n</script>", true);
$modx->regClientScript($modx->getOption('assets_url').'components/ticketmessages/js/messages.js');

// Return output
return $output;