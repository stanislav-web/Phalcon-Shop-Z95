<?php
	// Настройка компонентов-зависимостей

	// Компонент Config. Все настройки проекта

	$di->set('config', function() use ($config) {
		return new \Phalcon\Config($config);
	});

	// Компонент Navigation. Управление навигацией на сайте

	$di->set('navigation', function() use ($di) {
		return new \Navigation\Navigation($di->get('config'));
	}, true);

	// Компонент Router. Регистрирую конфигурацию роутинга из внешнего файла

	$di->set('router', function() {
		$router = new \Phalcon\Mvc\Router();
		require getcwd().'/../app/config/routes.php';
		return $router;
	});

	// Компонент URL используется для генерации всех видов адресов в приложении

	$di->set('url', function() use ($config) {
		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($config['application']['baseUri']);
		return $url;
	});

	// Компонент Views для вывода шаблонов

	$di->set('view', function() {
		$view = new \Phalcon\Mvc\View();
		return $view;
	});

	// Компонент frontendCache для кэширования Frontend (шаблоны, стили, скрипты)

	$di->set('backendCache', function() use ($config) {

		// Кэширование данных (запросы, конструкции, json итп)
		$backCache = new Phalcon\Cache\Frontend\Data([
			"lifetime" => $config['cache']['cache_backend_lifetime']
		]);

		// Выбор системы хранения

		switch($config['cache']['cache_backend_adapter'])
		{
			case 'File':
				$cache = new Phalcon\Cache\Backend\File($backCache, [
					"cacheDir"  =>  $config['application']['cacheDir'].'/backend/',
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
					"host" 		=>  $config['cache']['memcache_host'],
        			"port" 		=>  $config['cache']['memcache_port'],
					"persistent"=>	true,
				]);
			break;
		}
		return $cache;
	});

	// Компонент frontendCache для кэширования Frontend (шаблоны, стили, скрипты)

	$di->set('viewCache', function() use ($config) {

		// Кэширование Frontend (шаблоны, стили, скрипты)
		$frontCache = new Phalcon\Cache\Frontend\Output([
			"lifetime" => $config['cache']['cache_frontend_lifetime']
		]);

		// Настройки файлов кэша
		$cache = new Phalcon\Cache\Backend\File($frontCache, [
			"cacheDir"  => $config['application']['cacheDir'].'/frontend/',
			"prefix"    => $config['cache']['cache_frontend_prefix']
		]);
		return $cache;
	});

	// Компонент DB. Регистрирую коннект к MySQL

	$di->set('db', function() use ($config) {

		return new \Phalcon\Db\Adapter\Pdo\Mysql([
			"host"      => 	$config['database']['host'],
			"username"  => 	$config['database']['username'],
			"password"  => 	$config['database']['password'],
			"dbname"    => 	$config['database']['dbname'],
			"persistent"    => 	$config['database']['persistent'],
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_CASE 		=> PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
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

	// Компонент FlashMessenger. Классы для вывода окон

	$di->set('flash', function() {
		$flash = new Phalcon\Flash\Direct([
			'error'     => 'alert alert-error',
			'success'   => 'alert alert-success',
			'notice'    => 'alert alert-info',
		]);
		return $flash;
	});

	// Обработчик ошибок 404

	$di->set('dispatcher', function() use ($di) {

			$evManager = $di->getShared('eventsManager');

			$evManager->attach(
				"dispatch:beforeException",
				function($event, $dispatcher, $exception)
				{
					switch ($exception->getCode()) {
						case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
						case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
							$dispatcher->forward(
								array(
									'controller' => 'error',
									'action'     => 'show404',
								)
							);
							return false;
					}
				}
			);
			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setEventsManager($evManager);
			return $dispatcher;
	}, true);