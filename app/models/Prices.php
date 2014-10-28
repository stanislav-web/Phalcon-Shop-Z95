<?php
namespace Models;

class Prices extends \Phalcon\Mvc\Model
{
	const TABLE = 'prices';

	/**
	 * Декларация полей
	 * @var
	 */
	public 		$id,
				$product_id,
				$price,
				$discount,
				$percent,
				$date_create,
				$date_update;
}