<?php
$siteInfo = require 'config.inc.php';
$constInfo = require 'config.const.php';
$config = array(
		'URL_MODEL' => 0,
		'DB_TYPE' => 'mysql',
		'DB_HOST' => '219.138.135.11',
		'DB_PORT' => '11711',
		'DB_NAME' => 'xmwcom',
		'DB_USER' => 'jwkj',
		'DB_PWD' => 'K*34S6D^fero&',
		'DB_PREFIX' => 'xmw_',
		'DEFAULT_FILTER' => 'htmlspecialchars',
);

return array_merge($siteInfo, $constInfo, $config);