<?php
	/**
	 * Конфигурация роутера Phalcon
	 */

	// Смена языка
	$router->add("/language/{language:[a-z]+}", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'language'
	]);

	// Роутер каталога




	$router->addGet("/catalogue/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->addGet("/catalogue", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'subcategories'
	]);

	$router->addGet("/tags/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->addGet("/brands/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/cart/", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'cart',
		'action'        => 'index',
	]);

	$router->addGet("/catalogue/[0-9]+", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'item'
	]);

	$router->addGet("/catalogue/sale", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'sale'
	]);

	$router->add("/about", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'about',
	]);

	$router->add("/basket/get", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'basket',
		'action'        => 'get',
	]);
