<?php
	/**
	 * Конфигурация Z95 Модуля
	 */
	$config =  [

		'profiler'	        => true,		// Включение отладки
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


		//  Навигация

		'navigation' => [
			'top' => [
				'class'  => 'nav navbar-nav megamenu',
				'childs' => [
					[
						'name'       	=> 'HOME',
						'url'     		=> '/',
						'target' 		=> '_self',
						'classLink'		=> 'has-category',
					],
					[
						'name'       	=> 'CATEGORIES',
						'url'     		=> '/catalogue',
						'controller'	=>	'catalogue',
						'action'		=>	'index',
						'target' 		=> '_self',
						'classLink'		=> 'dropdown-toggle has-category',
						'class'			=> 'parent dropdown aligned-fullwidth',
					],
					[
						'name'       	=> 'SHOP',
						'url'     		=> '/catalog',
						'controller'	=>	'catalog',
						'action'		=>	'index',
						'target' 		=> '_self',
						'classLink'		=> 'dropdown-toggle has-category',
						'class'			=> 'parent dropdown aligned-fullwidth',
					],
					[
						'name'   		=> 'COMMUNITY',
						'url'     		=> '/community',
						'controller'	=>	'index',
						'action'		=>	'community',
						'target' 		=> '_self',
						'classLink'  	=> 'has-category',
					],
					[
						'name'   		=> 'DELIVERY',
						'url'     		=> '/delivery',
						'controller'	=>	'index',
						'action'		=>	'delivery',
						'target' 		=> '_self',
						'classLink'  	=> 'has-category',
					],
					[
						'name'   		=> 'ABOUT US',
						'url'     		=> '/about',
						'controller'	=>	'index',
						'action'		=>	'about',
						'target' 		=> '_self',
						'classLink'  	=> 'has-category',
					],
				]
			]
		],
	];
