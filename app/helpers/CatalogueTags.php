<?php
namespace Helpers;
use \Phalcon\Http\Request;

/**
 * Class CatalogueTags Помощник для работы с каталогом
 * @example // Разбивка роутинга на деревья
 *
 * 		CatalogueTags::catalogueNavTree($this->request->getURI(), [
 *			'categories', 'brands', 'tags'
 *		]);
 *
 * @example // Поиск категории в дереве массива
 * 		CatalogueTags::findInTree($array, 'parent_id, '150)
 */

class CatalogueTags extends \Phalcon\Tag
{

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
	 * arrayToAssoc($array, $field) Сортировка массива по указаному $field
	 *
	 * @param array | object $array исходный массив
	 * @param $field поле
	 * @access static
	 * @return array
	 */
	public static function arrayToAssoc($array, $field)
	{
		$result = array();

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
	 * findInTree($array, $key, $value) Поиск массивов в дереве по ключ=>значение
	 * @param array object $array исходный массив
	 * @param string $key ключ
	 * @param string $value значение
	 * @access static
	 * @return array
	 */
	public static function findInTree($array, $key, $value)
	{
		$results = array();

		$arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		foreach ($arrIt as $sub) {
			$subArray = $arrIt->getSubIterator();
			if ($subArray[$key] === $value) {
				$results[] = iterator_to_array($subArray);
			}
		}
		return $results;
	}

	/**
	 * findInTree($array, $key, $value) Исключение из массивов по ключ=>значение
	 * @param array object $array исходный массив
	 * @param string $key ключ
	 * @param string $value значение
	 * @access static
	 * @return array
	 */
	public static function excludeFromTree($array, $key, $value)
	{
		$results = array();

		$arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		foreach($arrIt as $sub) {
			$subArray = $arrIt->getSubIterator();
			if($subArray[$key] === $value)
				unset($subArray);
			else $results[] = iterator_to_array($subArray);
		}
		return $results;
	}


	public static function categoriesToTree($array, $parent_id = 0) {
		$tree = array();

		if( !empty($array)){
			foreach( $array as $id=> $element ){

				$element = (array) $element;
				if( !isset($element['parent_id']) ) continue;
				if( $element['parent_id'] == $parent_id ){
					$tree[$id] = $element;
					unset($array[$element['id']]);
					$tree[$id]['childs'] = self::categoriesToTree($array, $element['id']);

				}
			}
			return $tree;
		}

	}

	/**
	 * Функция перегрупировки массива по значению
	 *
	 * @param array $array
	 * @param       $field
	 * @return array
	 */
	public static function groupArray(array $array, $field)
	{
		$output = array();
		foreach ($array as $key=>$val) $output[$val[$field]][] = $val;
		return $output;
	}
}