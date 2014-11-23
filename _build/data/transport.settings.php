<?php

$settings = array();

$tmp = array(
	'msg_refresh_interval' => array(
		'xtype' => 'textfield',
		'value' => 20,
		'area' => 'ticketmessages_main',
	),

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'tm.' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
