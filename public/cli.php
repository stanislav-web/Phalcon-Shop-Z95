<?php

	use Phalcon\DI\FactoryDefault\CLI as CliDI,
		Phalcon\CLI\Console as ConsoleApp;

	define('VERSION', '1.0.0');

	//Используем стандартный для CLI контейнер зависимостей

	$di = new CliDI();

	// Определяем путь к каталогу приложений
	defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(__FILE__));
	defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) . '/../app'));

	// Регистрируем автозагрузчик, и скажем ему, чтобы зарегистрировал каталог задач

	$loader = new \Phalcon\Loader();
	$loader->registerDirs([
		APP_PATH . '/tasks'
	]);
	$loader->register();

	// Загрузка конфигуратора коммандной строки

	if(is_readable(APP_PATH.'/config/cli.php')) {
		$config = include APP_PATH . '/config/cli.php';
		$di->set('config', $config);
	}

	//Создаем консольное приложение

	$console = new ConsoleApp();
	$console->setDI($di);

	// Определяем консольные аргументы

	$arguments = array();
	$params = array();

	foreach($argv as $k => $arg) {
		if($k == 1)
			$arguments['task'] = $arg;
		elseif($k == 2)
			$arguments['action'] = $arg;
		elseif($k >= 3)
			$params[] = $arg;
	}
	if(count($params) > 0)
		$arguments['params'] = $params;

	// определяем глобальные константы для текущей задачи и действия
	define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
	define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

	try {
		// обрабатываем входящие аргументы
		$console->handle($arguments);
	}
	catch (\Phalcon\Exception $e) {
		echo $e->getMessage();
		exit(255);
	}