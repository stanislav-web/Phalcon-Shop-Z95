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

	$router->addGet("/categories/:params", [
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

	// Удаление косых в конце
	$router->removeExtraSlashes(true);