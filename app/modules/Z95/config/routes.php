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
	])->setName("sale");

	$router->add("/brands", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'brands',
	])->setName("brands");

	$router->add("/catalogue/favorites", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'favorites',
	])->setName("favorites");

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
	])->setName("cart");

	$router->add("/catalogue/([a-z_-]+/[0-9]+)|(/[0-9]+)", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'item'
	]);

	$router->add("/catalogue/brands/([0-9]{1,3})", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/about", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'about',
	])->setName("about");

	$router->add("/about/discounts", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'discounts',
	])->setName("about-discounts");

	$router->add("/about/delivery", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'delivery',
	])->setName("about-delivery");

	$router->add("/about/return", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'return',
	])->setName("about-return");

	$router->add("/about/opt", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'opt',
	])->setName("about-opt");

	$router->add("/about/useful", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'index',
		'action'        => 'static',
		'param'        	=> 'useful',
	])->setName("about-useful");

	$router->add("/order", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'order',
		'action'        => 'index',
	])->setName("order");

	$router->add("/customer/cart", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'basket',
		'action'        => 'index',
	]);

	$router->add("/error/show404", [
		'module'    	=>  $module,
		'namespace' 	=> 'Modules\\'.$module.'\Controllers\\',
		'controller'    => 'error',
		'action'        => 'show404',
	])->setName("show404");

	// Удаление косых в конце
	$router->removeExtraSlashes(true);
