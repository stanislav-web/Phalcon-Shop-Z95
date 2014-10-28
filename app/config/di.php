<?php
	// Настройка компонентов-зависимостей

	// Компонент Config. Все настройки проекта

	$di->set('config', function() use ($config) {
		return new \Phalcon\Config($config);
	});

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

	$di->set('view', function() use ($config) {
		$view = new \Phalcon\Mvc\View();
		//$view->setViewsDir($config['application']['viewsDir']);
		return $view;
	});

	// Компонент viewCache для кэширования фронтэнда

	$di->set('viewCache', function() use ($config) {
		// время жизни кэша
		$frontCache = new Phalcon\Cache\Frontend\Output([
			"lifetime" => 1
		]);

		// Настройки файлов кэша
		$cache = new Phalcon\Cache\Backend\File($frontCache, [
			"cacheDir"  => $config['application']['cacheDir'],
			"prefix"    => "page-"
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
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_CASE 		=> PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
			)
		]);
	});

	// Компонент Session. Стартую сессию
	$di->set('session', function() {
		$session = new Phalcon\Session\Adapter\Files();
		$session->start();
		return $session;
	});

	// Компонент Cookies. Стартую сессию
	$di->set('cookies', function() {
		$cookies = new Phalcon\Http\Response\Cookies();
		$cookies->useEncryption(false);
		return $cookies;
	});

	// Компонент FlashMessenger. Ориентируюсь на Twitter Bootstrap классы для вывода окон

	$di->set('flash', function() {
		$flash = new Phalcon\Flash\Direct([
			'error'     => 'alert alert-error',
			'success'   => 'alert alert-success',
			'notice'    => 'alert alert-info',
		]);
		return $flash;
	});

	// Обработчик ошибок

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