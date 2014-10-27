<?php
	/**
	 * Конфигурация роутера Phalcon
	 */
	$router = new Phalcon\Mvc\Router();

	$router->add("/language/{language:[a-z]+}", [
		'controller'    => 'index',
		'action'        => 'language'
	]);

	$router->add("/catalog/", [
		'controller'    => 'catalog',
		'action'        => 'index'
	]);

	$router->add("/cart/", [
		'controller'    => 'cart',
		'action'        => 'index',
	]);