<?php
	/**
	 * Конфигурация Phalcon
	 */
return new \Phalcon\Config([
    'database' => [
		'adapter'     => 'Mysql',
        'host'        => '192.168.12.145',
        'username'    => 'root',
        'password'    => 'd9eb77mms',
        'dbname'      => 'Shop',
    ],
    'application' => [
        'controllersDir' => __DIR__ . '/../../app/controllers/',
        'modelsDir'      => __DIR__ . '/../../app/models/',
        'viewsDir'       => __DIR__ . '/../../app/views/',
        'pluginsDir'     => __DIR__ . '/../../app/plugins/',
        'libraryDir'     => __DIR__ . '/../../app/library/',
        'cacheDir'       => __DIR__ . '/../../app/cache/',
        'baseUri'        => '/',
    ],
    'models' => [
	    'metadata' => [
		    'adapter' => 'Apc',
		    'lifetime' => 86400
	    ]
    ]
]);
