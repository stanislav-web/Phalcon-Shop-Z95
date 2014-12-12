<?php
$config = new Phalcon\Config([

	'sync'	=>	[
		'token'	=>	'435c4d614fcdcf443f433f2469920f35',
		'url'	=>	'http://b.stanislavw.dev95.ru/api/jsonrpc/',
		'delay'	=>	60,
		'limit'	=>	500,
		'decode'=>	1,
		'adapter'	=>	'serialize', // json_encode, serialize
	],

	// Коннект к Backend
	'database'  => [
		'adapter'     => 'Mysql',
		'host'        => 'localhost',
		'username'    => 'root',
		'password'    => 'd9eb77mms',
		'dbname'      => 'Shop',
		'persistent'  => false
	]
]);

	$service = new Phalcon\DI\Service('request', 'Phalcon\Http\Request');
