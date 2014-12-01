<?php
	/**
	 * Конфигурация ZKZ Модуля
	 */
	$config =  [

		// Конфигурация отправки почты

		'mailer' => [
			'driver' 	 => 'mail',
			//'sendmail' 	 => '/usr/sbin/sendmail -t -i -f stanislavw@gmail.com',
			'from'		 => [
				'email' => 'shopFrom@z95.com',
				'name'	=> 'ZKZ Shop'
			],
			'to'	=>	[
				'email' => 'shopTo@z95.com',
				'name'	=> 'ZKZ Shop'
			]
		],

		'profiler'	        => true,		// Включение отладки
		'cache'	  =>    [   // Кэширование
			'frontend'                  =>  false,
			'cache_frontend_lifetime'   =>  300,        // Время сохранения в сек.
			'cache_frontend_prefix'     => 'page-',	    // Префикс страниц в кэше
			'backend'                   =>  false,
			'cache_backend_lifetime'    =>  300,        // Время сохранения в сек.
			'cache_backend_adapter'     =>  'File',     // Адаптер File, Apc, XCache, Memcache
			'memcache_host'				=>	'localhost',// Хост для Memcache
			'memcache_port'				=>	11211,		// Порт для Memcache
		],
		// Коннект к базе
		'database'  => [
			'adapter'     => 'Mysql',
			'host'        => 'localhost',
			'username'    => 'root',
			'password'    => '',
			'dbname'      => 'Shop',
			'persistent'  => false
		],
		// Настройка директорий
		'application' => [
			'controllersDir' => APP_PATH.'/modules/'.self::MODULE.'/controllers',
			'modelsDir' 	 => APP_PATH.'/models',
			'helpersDir' 	 => APP_PATH.'/helpers',
			'messagesDir'    => APP_PATH.'/modules/'.self::MODULE.'/messages',
			'viewsDir'       => APP_PATH.'/modules/'.self::MODULE.'/views',
			'cacheDir'       => APP_PATH.'/modules/'.self::MODULE.'/cache',
			'mappersDir'     => APP_PATH.'/modules/'.self::MODULE.'/mappers',
			'baseUri'        => '/',
		],
	];
