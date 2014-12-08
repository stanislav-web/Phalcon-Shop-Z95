<?php
$config = new Phalcon\Config([

	'sync'	=>	[
		'token'	=>	'435c4d614fcdcf443f433f2469920f35',
		'url'	=>	'http://b.maggadda.dev95.ru/api/jsonrpc/',
		'delay'	=>	9000,
		'limit'	=>	1000,
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