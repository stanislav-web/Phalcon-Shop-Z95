<?php
	/**
	 * Конфигурация роутера Phalcon
	 */

	$router->add("/language/{language:[a-z]+}", [
		'controller'    => 'index',
		'action'        => 'language'
	]);

//	$router->add("/catalog/", [
//		'controller'    => 'catalog',
//		'action'        => 'index'
//	]);

	$router->add("/cart/", [
		'controller'    => 'cart',
		'action'        => 'index',
	]);

	$router->add("/catalogue/{params:[0-9]+}", [
		'controller'    => 'catalogue',
		'action'        => 'item',
	]);