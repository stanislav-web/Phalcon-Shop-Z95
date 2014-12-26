<?php
$config = new Phalcon\Config([

	'sync'	=>	[
		'token'	=>	'',
		'url'	=>	'http://b.stanislavw.dev95.ru/api/jsonrpc/',
		'delay'	=>	60,
		'decode'=>	0,	// base64
		'adapter'	=>	'serialize', // json_encode, serialize
		'checkonly'	=>	0, // just check
	],

	// Коннект к Backend
	'database'  => [
		'adapter'     => 'Mysql',
		'host'        => 'localhost',
		'username'    => 'stanislavw',
		'password'    => '',
		'dbname'      => 'Shop',
		'persistent'  => false
	]
]);
