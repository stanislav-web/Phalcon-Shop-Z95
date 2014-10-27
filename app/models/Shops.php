<?php
class Shops extends \Phalcon\Mvc\Model
{
	/**
	 * Декларация полей
	 * @var
	 */
	public 		$id,
				$name,
				$title,
				$code,
				$host,
				$currency,
				$country_code,
				$price_id,
				$date_create,
				$date_update;
}