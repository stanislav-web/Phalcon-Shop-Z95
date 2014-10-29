<?php
namespace Helpers;

/**
 * Class CatalogueTags Помощник для работы с каталогом
 * @example // Разбивка роутинга на деревья
 *
 * 		CatalogueTags::catalogueNavTree($this->request->getURI(), [
 *			'categories', 'brands', 'tags'
 *		]);
 *
 * @example // Создание дерева параметров из таблиц БД
 * 		CatalogueTags::catalogueNavTree($_SERVER['REUEST_URI']) Разбивка роутинга на деревья
 */
class CatalogueTags extends \Phalcon\Tag
{
	/**
	 * catalogueCollectData(array $data, array $params) Создание дерева параметров из таблиц БД
	 *
	 * @param array $data REQUEST_URI
	 * @param array $params массив с параметрами на которые надо делить
	 * @access static
	 * @return array
	 */
	static public function catalogueCollectData(array $data, array $params)
	{

	}


	/**
	 * catalogueRouteTree($request, array $keys) Создание дерева навигации
	 *
	 * @example <code>
	 *          $tree = Helpers\CatalogueTags::catalogueRouteTree($this->request->getURI(), [
	 *				'categories', 'brands', 'tags'
	 *			]);
	 *          </code>
	 *
	 * @param string $request REQUEST_URI
	 * @param array $keys массив с разделами
	 * @access static
	 * @return array
	 */
	static public function catalogueRouteTree($request, array $keys)
	{
		// будущее дерево из url параметров
		$treeData = [];

		// Разбиваю URL на массив
		$urlData = array_filter(explode( '/', $request));

		foreach($urlData as $key => $value)
		{
			if(in_array($value, $keys))
			{
				$treeData[$value]	=	array();
				$cursor = $value;
			}
			else
			{
				$treeData[$cursor][] = $value;
			}
		}
		return $treeData;
	}

	/**
	 * arrayToAssoc(array $array, $field) Сортировка массива по указаному $field
	 *
	 * @param array $array исходный массив
	 * @param $field поле
	 * @access static
	 * @return array
	 */
	public static function arrayToAssoc(array $array, $field)
	{
		$result = array();
		$array = self::objectToArray($array);

		foreach($array as $v)
		{
			if(isset($v[$field]))
				$result[$v[$field]] = $v;
		}
		return $result;
	}

	/**
	 * objectToArray($obj) из объекта в массив
	 *
	 * @param stdObject $obj
	 * @access static
	 * @return array
	 */
	public static function objectToArray($obj)
	{
		if(is_object($obj)) $obj = (array)$obj;
		if(is_array($obj))
		{
			$new = array();
			foreach($obj as $key => $val)
			{
				$new[$key] = self::objectToArray($val);
			}
		}
		else $new = $obj;
		return $new;
	}

	/**
	 * findInTree($array, $key, $value) Поиск массива в дереве по ключ=>значение
	 * @param array object $array исходный массив
	 * @param string $key ключ
	 * @param string $value значение
	 * @access static
	 * @return array
	 */
	public static function findInTree($array, $key, $value)
	{
		$results = array();
		$array = self::objectToArray($array);

		$arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		foreach($arrIt as $sub)
		{
			$subArray = $arrIt->getSubIterator();
			if($subArray[$key] === $value)
			{
				$results[] = iterator_to_array($subArray);
			}
		}
		return $results;
	}
}