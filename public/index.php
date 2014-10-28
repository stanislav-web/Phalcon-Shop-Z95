<?php

	try {

		// Читаю файл конфигураций

		require __DIR__.'/../app/config/config.php';

		// Регистрирую автозагрузчик модулей

		$loader = new \Phalcon\Loader();

		// Создаю контейнер зафисимости классов

		$di = new Phalcon\DI\FactoryDefault();

		require __DIR__.'/../app/config/di.php';

		// Регистрирую  ДИРЕКТОРИИ

		$loader->registerDirs([
				$config['application']['controllersDir'],
				$config['application']['libraryDir'],
				$config['application']['helpersDir'],
				$config['application']['modelsDir'],
		])

		// Регистрация пространств имен
		->registerNamespaces([
			"PDW"         	=> $config['application']['libraryDir'].'PDW',
		])
		->register();

		if($config['profiler'])
		{
			$debugWidget = new \PDW\DebugWidget($di);
		}

		// Рендеринг контента приложения

		$application = new \Phalcon\Mvc\Application();
		$application->setDI($di);

		echo $application->handle()->getContent();
	}
	catch(\Phalcon\Exception $e)
	{
		echo "PhalconException: ", $e->getMessage();
	}
