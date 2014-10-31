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

		foreach ($arrIt as $sub) {
			$subArray = $arrIt->getSubIterator();
			if ($subArray[$key] === $value) {
				$results[] = iterator_to_array($subArray);
			}
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
	 * catalogueBreadcrumbs($separator, $classLink, $home) Хлебные крошки каталога
	 * @param string $separator разделитель
	 * @param string $classLink класс сылок
	 * @param string $home Домашняя директория по умолчанию
	 * @access static
	 * @return array
	 */
	public static function catalogueBreadcrumbs($separator, $classLink, $home = 'Home')
	{
		// This gets the REQUEST_URI (/path/to/file.php), splits the string (using '/') into an array, and then filters out any empty values
		$path = array_filter(explode('/', (new Request)->getURI()));

		// This will build our "base URL" ... Also accounts for HTTPS :)
		$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/catalogue/';

		// Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
		$breadcrumbs = Array("<a class=\"$classLink\" href=\"$base\">$home</a>");

		// Find out the index for the last value in our path array
		$last = end(array_keys($path));

		// Build the rest of the breadcrumbs
		foreach ($path AS $x => $crumb) {
			// Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
			$title = ucwords(str_replace(Array('.php', '_'), Array('', ' '), $crumb));

			// If we are not on the last index, then display an <a> tag
			if($x != $last)
				$breadcrumbs[] = "<a href=\"$base$crumb\">$title</a>";
			// Otherwise, just display the title (minus)
			else
				$breadcrumbs[] = $title;
		}
		// Build our temporary array (pieces of bread) into one big string :)
		return implode($separator, $breadcrumbs);
	}
}