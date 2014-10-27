<?php

	try {

		// Читаю файл конфигураций

		require __DIR__.'/../app/config/config.php';

		// Регистрирую автозагрузчик модулей

		$loader = new \Phalcon\Loader();

		// Регистрирую модули, отвечающие за работу приложения

		$loader->registerDirs(
			[
				__DIR__.$config->phalcon->controllersDir,
				__DIR__.$config->phalcon->librariesDir,
				__DIR__.$config->phalcon->helpersDir,
				__DIR__.$config->phalcon->modelsDir
			]
		)->register();

		// Создаю контейнер зафисимости классов

		$di = new Phalcon\DI\FactoryDefault();

		// Компонент Router. Регистрирую конфигурацию роутинга из внешнего файла

		$di->set('router', function() {
			require __DIR__.'/../app/config/routes.php';
			return $router;
		});

		// Компонент URL используется для генерации всех видов адресов в приложении

		$di->set('url', function() use ($config) {
			$url = new \Phalcon\Mvc\Url();
			$url->setBaseUri($config->phalcon->baseUri);
			return $url;
		});

		// Компонент Views для вывода шаблонов

		$di->set('view', function() use ($config) {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir(__DIR__.$config->phalcon->viewsDir);
			return $view;
		});

		// Компонент viewCache для кэширования фронтэнда

		$di->set('viewCache', function() use ($config) {
			// время жизни кэша
			$frontCache = new Phalcon\Cache\Frontend\Output([
				"lifetime" => 2592000
			]);

			// Настройки файлов кэша
			$cache = new Phalcon\Cache\Backend\File($frontCache, [
				"cacheDir"  => __DIR__.$config->phalcon->cacheDir,
				"prefix"    => "page-"
			]);

			return $cache;
		});

		// Компонент DB. Регистрирую коннект к MySQL

		$di->set('db', function() use ($config) {
			return new \Phalcon\Db\Adapter\Pdo\Mysql([
				"host"      => $config->database->host,
				"username"  => $config->database->username,
				"password"  => $config->database->password,
				"dbname"    => $config->database->dbname
			]);
		});

		// Компонент Session. Стартую сессию
		$di->set('session', function() {
			$session = new Phalcon\Session\Adapter\Files();
			$session->start();
			return $session;
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


		// Рендеринг контента приложения

		$application = new \Phalcon\Mvc\Application();
		$application->setDI($di);
		echo $application->handle()->getContent();
	}
	catch(\Phalcon\Exception $e)
	{
		echo "PhalconException: ", $e->getMessage();
	}