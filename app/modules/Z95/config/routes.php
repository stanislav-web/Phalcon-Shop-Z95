<?php
	/**
	 * Конфигурация роутера Phalcon
	 */

	// Роутер каталога

	$router->add("/catalogue/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/catalogue/sale", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'sale'
	]);

	$router->add("/brands", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'brands',
	]);

	$router->add("/catalogue/favorites", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'favorites',
	]);

	$router->add("/catalogue/top/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/catalogue/new/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/catalogue", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'categories'
	]);

	$router->add("/cart", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'cart',
		'action'        => 'index',
	]);

	$router->add("/catalogue/([a-z_-]+/[0-9]+)|(/[0-9]+)", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'item'
	]);

	$router->add("/about", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'about',
	]);

	$router->add("/discounts", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'discounts',
	]);

	$router->add("/delivery", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'delivery',
	]);

	$router->add("/return", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'return',
	]);

	$router->add("/opt", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'opt',
	]);
	$router->add("/order", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'order',
		'action'        => 'index',
	]);

	$router->add("/customer/cart/:params", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'basket',
		'action'        => 'index',
	]);

	// Удаление косых в конце
	$router->removeExtraSlashes(true);
