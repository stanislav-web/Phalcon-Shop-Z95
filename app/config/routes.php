<?php
	/**
	 * Конфигурация роутера Phalcon
	 */

	// Смена языка
	$router->add("/language/{language:[a-z]+}", [
		'controller'    => 'index',
		'action'        => 'language'
	]);

	// Роутер каталога

	$router->addGet("/catalogue/:params", [
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->addGet("/tags/:params", [
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->addGet("/brands/:params", [
		'controller'    => 'catalogue',
		'action'        => 'index'
	]);

	$router->add("/cart/", [
		'controller'    => 'cart',
		'action'        => 'index',
	]);

	$router->addGet("/catalogue/[0-9]+", [
		'controller'    => 'catalogue',
		'action'        => 'item'
	]);

	$router->add("/about", [
		'controller'    => 'index',
		'action'        => 'about',
	]);

	// Удаление косых в конце
	$router->removeExtraSlashes(true);