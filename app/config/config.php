<?php
	/**
	 * Конфигурация Phalcon
	 */
	$config =  [
		'database' => [
			'adapter'     => 'Mysql',
			'host'        => 'localhost',
			'username'    => 'root',
			'password'    => 'd9eb77mms',
			'dbname'      => 'Shop',
			'profiler'	=> true,
		],
		'application' => [
			'controllersDir' => __DIR__ . '/../../app/controllers/',
			'helpersDir'     => __DIR__ . '/../../app/helpers/',
			'libraryDir'	 => __DIR__ . '/../../app/library/',
			'messagesDir'    => __DIR__ . '/../../app/messages/',
			'modelsDir'      => __DIR__ . '/../../app/models/',
			'viewsDir'       => __DIR__ . '/../../app/views/',
			'pluginsDir'     => __DIR__ . '/../../app/plugins/',
			'cacheDir'       => __DIR__ . '/../../app/cache/',
			'baseUri'        => __DIR__ .'/',
		],
		'models' => [
			'metadata' => [
				'adapter' => 'Apc',
				'lifetime' => 86400
			]
		]
	];
