<?php

/**
 * Мультимодульная структура загрузчика
 */
defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(__FILE__));
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) . '/../app'));

use Phalcon\Mvc\Application,
	Phalcon\Http\Request,
	Phalcon\Mvc\Router,
	Phalcon\DI\FactoryDefault;

// Подключение конфигурации модулей

require APP_PATH.'/config/modules.php';

// Чтение HTTP заголовков

$request = new Request();

// Определение модуля для автозагрузки

	if(isset($modules[$request->getHttpHost()]))
		$module = $modules[$request->getHttpHost()];
		else $module = $modules['dafault'];

try {

	// Инициализация Dependency Injections
	$di = new FactoryDefault();

	// Регистрация роутинга модуля

	$di->set('router', function() use ($module) {

		$router = (new Router)->setDefaultModule($module);
		require APP_PATH.'/modules/'.$module.'/config/routes.php';
		return $router;
	});


	// Компонент представлений для вывода шаблонов

	$di->set('view', function() use ($module) {
		return (new Phalcon\Mvc\View())->setViewsDir(APP_PATH.'/modules/'.$module.'/views/');
	});

	// Создание приложения

	$application = new Application($di);

	$application->registerModules([
		$module	=>	[
			'className' => 'Modules\Module\\'.$module,
			'path'      => APP_PATH.'/modules/'.$module.'/Module.php',
		],
	]);

	// Обработка запроса
	echo $application->handle()->getContent();
}
catch(\Exception $e)
{
	echo $e->getMessage();
}

