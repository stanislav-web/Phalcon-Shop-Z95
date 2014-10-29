<?php
namespace Helpers;

/**
 * Class LocationUrls Пощник для работы с построением ссылок
 *
 * @example // Генерация ссылки
 * 		LocationUrls::location($params) Разбивка роутинга на деревья
 */
class LocationUrls extends \Phalcon\Tag
{
	/**
	 * location($request, array $keys) Cоздание URI для href
	 *
	 * @param string $params
	 * @access static
	 * @return string
	 */
	static public function location($params)
	{
		return $params;
	}
}