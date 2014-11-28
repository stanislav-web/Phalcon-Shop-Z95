<?php

	// Настройка компонентов зависимостей для модуля ZKZ

	// Диспетчер контроллеров

	$di->set('dispatcher', function() use ($di) {

		$evManager = $di->getShared('eventsManager');
		$evManager->attach(
			"dispatch:beforeException",
			function($event, $dispatcher, $exception)
			{
				switch ($exception->getCode()) {
					case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
					case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:

						// на 404 если не нашли

						$dispatcher->forward([
							'module'    	=>  self::MODULE,
							'namespace' 	=> 'Modules\\'.self::MODULE.'\Controllers\\',
							'controller' 	=> 'error',
							'action'     	=> 'show404',
						]);
						return false;
				}
			}
		);

		$dispatcher = new \Phalcon\Mvc\Dispatcher();
		$dispatcher->setEventsManager($evManager);
		$dispatcher->setDefaultNamespace("Modules\\".self::MODULE."\Controllers\\");
		return $dispatcher;

	});

	// Компонент Config. Все настройки модуля

	$di->set('config', function() {

		return new \Phalcon\Config($this->_config);

	});

	// Компонент Breadcrumbs. Управление навигацией на сайте

	$di->set('breadcrumbs', function() {
			return new Breadcrumbs\Breadcrumbs();
	}, true);


	// Компонент Navigation. Управление навигацией на сайте

	$di->set('navigation', function() use ($di) {

		require_once APP_PATH.'/modules/'.self::MODULE.'/config/navigation.php';
		if(isset($navigation))
			return new \Navigation\Navigation(new \Phalcon\Config($navigation));
	}, true);

	// Компонент URL используется для генерации всех видов адресов в приложении

	$di->set('url', function() {

		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($this->_config['application']['baseUri']);
		return $url;

	});

	// Component Logger. $this->di->get('logger')->log('.....',Logger::ERROR);
	$di->set('logger', function() {
		$formatter = new \Phalcon\Logger\Formatter\Line('[%date%][%type%] %message%');
		$logger = new \Phalcon\Logger\Adapter\File(APP_PATH.'/logs/loader.log');
		$logger->setFormatter($formatter);
		return $logger;
	});

	// Компонент frontendCache для кэширования Frontend (шаблоны, стили, скрипты)

	$di->set('backendCache', function() {

		// Кэширование данных (запросы, конструкции, json итп)
		$backCache = new Phalcon\Cache\Frontend\Data([
			"lifetime" => $this->_config['cache']['cache_backend_lifetime']
		]);

		// Выбор системы хранения

		switch($this->_config['cache']['cache_backend_adapter'])
		{
			case 'File':
				$cache = new Phalcon\Cache\Backend\File($backCache, [
					"cacheDir"  =>  $this->_config['application']['cacheDir'].'/backend/',
					"prefix"    =>  'file-'
				]);
				break;

			case 'Apc':
				$cache = new Phalcon\Cache\Backend\Apc($backCache, [
					"prefix" => 'apc-'
				]);
				break;

			case 'XCache':
				$cache = new Phalcon\Cache\Backend\Xcache($backCache, [
					"prefix"    =>  'xcache-',
				]);
				break;

			case 'Memcache':
				$cache = new Phalcon\Cache\Backend\Memcache($backCache, [
					"prefix"    =>  'memcache-',
					"host" 		=>  $this->_config['cache']['memcache_host'],
					"port" 		=>  $this->_config['cache']['memcache_port'],
					"persistent"=>	true,
				]);
				break;
		}
		return $cache;
	});

	// Компонент DB. Регистрирую коннект к MySQL

	$di->set('db', function() {

		return new \Phalcon\Db\Adapter\Pdo\Mysql([
			"host"      => 	$this->_config['database']['host'],
			"username"  => 	$this->_config['database']['username'],
			"password"  => 	$this->_config['database']['password'],
			"dbname"    => 	$this->_config['database']['dbname'],
			"persistent"    => 	$this->_config['database']['persistent'],
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_CASE 		=> PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
			)
		]);
	});

	// Компонент Session. Стартую сессию
	$di->setShared('session', function() {

		$session = new Phalcon\Session\Adapter\Files();
		$session->start();
		return $session;

	});

	// Компонент Cookies. Стартую куки
	$di->set('cookies', function() {

		$cookies = new Phalcon\Http\Response\Cookies();
		$cookies->useEncryption(false);
		return $cookies;

	});