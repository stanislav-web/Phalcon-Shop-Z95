<?php
	/**
	 * Конфигурация Z95 Модуля
	 */
	$config =  [

		'profiler'	        => false,		// Включение отладки
		'cache'	  =>    [   // Кэширование
			'frontend'                  =>  false,
			'cache_frontend_lifetime'   =>  300,        // Время сохранения в сек.
			'cache_frontend_prefix'     => 'page-',	    // Префикс страниц в кэше

		    'backend'                   =>  false,
		    'cache_backend_lifetime'    =>  300,        // Время сохранения в сек.
		    'cache_backend_adapter'     =>  'File',     // Адаптер File, Apc, XCache, Memcahce

			'memcache_host'				=>	'localhost',// Хост для Memcache
			'memcache_port'				=>	'11211',	// Порт для Memcache
		],

		// Коннект к базе

		'database'  => [
			'adapter'     => 'Mysql',
			'host'        => 'localhost',
			'username'    => 'root',
			'password'    => 'd9eb77mms',
			'dbname'      => 'Shop',
			'persistent'  => false
		],

		// Настройка директорий

		'application' => [
			'messagesDir'    => APP_PATH.'/modules/'.self::MODULE.'/messages',
			'viewsDir'       => APP_PATH.'/modules/'.self::MODULE.'/views',
			'cacheDir'       => APP_PATH.'/modules/'.self::MODULE.'/cache',
			'baseUri'        => '/',
		],
	];
