<?php
class Categories extends \Phalcon\Mvc\Model
{
	const TABLE = 'categories';

	/**
	 * Декларация полей
	 * @var
	 */
	public 		$id,
				$name,
				$parent_id,
				$alias,
				$date_create,
				$date_update;
}