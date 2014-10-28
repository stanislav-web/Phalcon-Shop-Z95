<?php
class Products extends \Phalcon\Mvc\Model
{
	const TABLE = 'products';

	/**
	 * Декларация полей
	 * @var
	 */
	public 		$id,
				$articul,
				$name,
				$description,
				$images,
				$alias,
				$brand_id,
				$sex,
				$rating,
				$published,
				$date_create,
				$date_update;
}