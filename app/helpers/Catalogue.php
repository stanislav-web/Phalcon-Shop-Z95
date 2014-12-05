<?php
namespace Helpers;
use \Phalcon\Http\Request,
	\Phalcon\Mvc\Router,
	Phalcon\Tag;

/**
 * Class Catalogue Помощник для работы с каталогом
 *  @package Phalcon
 * @subpackage Helpers
 */
class Catalogue extends  Tag
{

	/**
	 * catalogueRouteTree($request) Создание правил маршрута для маппера
	 *
	 * @example <code>
	 *          $tree = Catalogue::catalogueRouteRules($this->request->getURI());
	 *          </code>
	 *
	 * @param string $request REQUEST_URI
	 * @access static
	 * @return array
	 */
	static public function catalogueRouteRules($request)
	{
		$path = parse_url($request, PHP_URL_PATH);

		// Разбиваю URL на массив
		$urlData = array_filter(explode( '/', $path));

		// извлекаю из пути catalogue
		array_shift($urlData);

		// получаю параметры query_string
		parse_str(parse_url($request, PHP_URL_QUERY), $query);

		// возвращаю уже готовый для обработки маппером объект
		return (object)[
				'path'		=>	$path,
				'query'		=>	(!empty($query)) ? $query : false,
				'catalogue'	=>	$urlData,
				'current'	=>	end($urlData)
			];
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
	public static function findInTree($array, $key, $value, $key2 = null, $value2 = null)
	{
		$results = array();

		$arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		foreach ($arrIt as $sub) {
			$subArray = $arrIt->getSubIterator();
			if(!is_null($key2) && !is_null($value2))
			{
				if ($subArray[$key2] == $value2 && $subArray[$key] == $value) {
					$results[] = iterator_to_array($subArray);
				}
			}
			else
			{
				if ($subArray[$key] === $value) {
					$results[] = iterator_to_array($subArray);
				}
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

	/**
	 * categoriesToTree  Построение дерева категорий (с сортировкой)
	 * @param      $array входящий массив с категориями
	 * @param int  $parent_id внутри какого parent сортировать?
	 * @param bool $sort Сортировать?
	 * @param bool $sumFiled суммировать поля?
	 * @return array
	 */
	public static function categoriesToTree(array $array, $parent_id = 0, $sort = false, $sumField = '') {
		$tree = array();

		if( !empty($array)){

			foreach( $array as $id => $element ){

				$element = (array) $element;
				if( !isset($element['parent_id']) ) continue;
				if( $element['parent_id'] == $parent_id ) {
					$tree[$id] = $element;
					unset($array[$element['id']]);
					$tree[$id]['childs'] = self::categoriesToTree($array, $element['id']);
				}
			}
			if($sort) $tree = self::sortCategories($tree);

			if(!empty($sumField))
			{
				// суммирую количество в дочерних категориях по [$sumField] и приписываю к родителю

				$tree = self::arraySort(self::getChildrenSum($tree, $sumField), $sumField, true);
			}
			$tree = self::arrayToAssoc($tree, 'id');

			return $tree;
		}
	}

	/**
	 *  arraySort($array, $key) сортировка обычного массива
	 * @param $array исх. массив
	 * @param $key ключ по которому сортировать
	 * @param $desc обратный порядок?
	 * @param boolean $keepkey сохранить ключи
	 * @return mixed
	 */
	public static function arraySort($array, $key, $desc = false, $keepkey = false)
	{

		$ascending = function($a, $b) use ($key) {
			if(isset($a[$key]) && isset($b[$key]))
			{
				if ($a[$key] == $b[$key]) {
					return 0;
				}
				return ($a[$key] < $b[$key]) ? -1 : 1;
			}
		};
		if(!$keepkey) usort($array, $ascending);
		else uasort($array, $ascending);
		if($desc) $array = array_reverse($array, true);
		return $array;
	}

	/**
	 * getChildrenSum($array, $field) Подсчет суммы всех сумм в дочерних элементах
	 * @param $array
	 * @param $field
	 * @return mixed
	 */
	public static function getChildrenSum($array, $field)
	{
		foreach($array as $k => $item)
		{
			if(!empty($item['childs']))
			{
				$array[$k][$field] = array_sum(array_map(
					function($element){
						return $element['count_products'];
					},
					$item['childs']));
			}
		}
		return $array;
	}

	/**
	 * Склонение числительных Catalogue::declOfNum(5, ['вещь', 'вещей', 'вещи'])
	 * @param $number
	 * @param array $titles
	 * @return string
	 */
	public static function declOfNum($number, array $titles, $hidenum = false) {

		$cases = array (2, 0, 1, 1, 1, 2);
		if($hidenum === false)
			return ' ' . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ] . ' ';
		else
			return $number . ' ' . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ] . ' ';
	}

	public static function wordEnding($number, $word, $group_id = false)
	{
		$letters = array('р'=>2, 'ь'=>3, 'я'=>1, 'з'=>4, 'л'=>5, 'н'=>'6', 'е'=>'7', 'у'=>'8');
		$group	= array(
			1 => array('я', 'и', 'й'),
			2 => array('р', 'ра', 'ров'),
			3 => array('ь', 'и', 'ей'),
			4 => array('з', 'за', 'зов'),
			5 => array('л', 'ло', 'ло'),
			6 => array('н', 'но', 'но'),
			7 => array('е', 'я', 'й'),
			8 => array('у', 'и', ''),

			9 => array('л', 'ла', 'лов'),
		);

		$ending = mb_substr($word, -1, 1);
		if ($group_id === false) {
			$letter_group = ( isset($letters[$ending]) ) ? $letters[$ending] : 0;
			$group_id = ( isset($group[$letter_group]) ) ? $group[$letter_group] : 0;
		} else {
			$group_id = ( isset($group[$group_id]) ) ? $group[$group_id] : 0;
		}
		if ($group_id != 0) {
			$word = mb_substr($word, 0, (mb_strlen($word) -1) );
			$two_last_digits = substr($number, -2, 2);
			if ($two_last_digits > 10 and $two_last_digits < 20) {
				$ending = $group_id[2];
			} else {

				$one_last_digit = substr($number, -1, 1);
				if( $one_last_digit == 1) {
					$ending = $group_id[0];
				} else if ($one_last_digit > 1 and $one_last_digit < 5) {
					$ending = $group_id[1];
				} else {
					$ending = $group_id[2];
				}
			}
			$word .= $ending;
		}
		return $word;
	}

	/**
	 * orderFilterItems(array $items) Фильтрация купленных вещей на отправку заказа
	 * @param array $items массив с купленными вещами
	 * @return array
	 */
	public static function orderFilterItems(array $items)
	{
		$filter = [];
		foreach($items as $item)
		{
			if(!empty($item['size']))
			{
				foreach($item['size'] as $size => $count)
				{
					$filter[]	=	[
						'cat_id'	=>	$item['product_id'],
						'size'		=>	$size,
						'count'		=>	$count,
					];
				}
			}
		}
		return $filter;
	}

	/**
	 * tagsToTree(array $elements) Теги с размерами превращию в дерево
	 * @param array $elements
	 * @return array
	 */
	public static function tagsToTree(array $elements)
	{
		$tags	=	[];
		$sizes 	= 	[];

		// вычисляю размеры

		foreach($elements as $element)
			if($element['parent_id'] == '')
				$sizes[]	=	$element;
		$sizes = self::arrayToAssoc($sizes, 'id');

		// вычисляю теги

		$elements = self::arrayToAssoc($elements, 'id');
		foreach($elements as $k => $val)
			if(!isset($sizes[$k]))
				$tags[]	=	$val;


		// создаю дерево тегов
		$tree	=	self::categoriesToTree($tags, 0);
		return [
			'sizes'	=>	$sizes,
			'tags'	=>	$tree
		];
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

	public static function wordDeclension($word, $case = 4)
	{
		$replace = array(
			4 => array(
				'/^(.+)(а)$/' => '$1у',
				'/^(.+)(я|ю)$/' => '$1ю',
				'/^(.+)(ая)$/' => '$1ую',
			)
		);

		if(isset($replace[$case]))
		{
			foreach ($replace[$case] as $pattern => $replacement)
			{
				if(preg_match($pattern, $word))
				{
					$word = preg_replace($pattern, $replacement, $word);
					break;
				}
			}
		}
		return $word;
	}

	/**
	 * Поиск и замена в массиве
	 * @param array $find
	 * @param array $replace
	 * @param string $string
	 * @access static
	 * @return string
	 */
	public static function replaceInArray(array $find, array $replace, $string)
	{
		foreach($find as $v)
		{
			if(isset($replace[$v]))
				return str_replace($v, $replace[$v], $string);
		}
		return $string;
	}

	/**
	 * Convert multidimensional array to key => pair array
	 * @param $obj
	 * @access static
	 * @return array
	 */
	public static function arrayToPair(array $array)
	{
		$result = [];
		if(!empty($array))
		{
			foreach($array as $values)
			{
				$values = array_values($values);
				$result[$values[0]]	=	$values[1];
			}
		}
		return $result;
	}

	/**
	 * Парсинг QUERY_STRING в массив
	 *
	 * @param string $string QUERY_STRING
	 * @return array
	 */
	public static function queryToArray($string)
	{
		$array = [];
		parse_str($string, $array);
		return $array;
	}

	/**
	 * Функция сортировки категорий на главной и каталоге
	 *
	 * @param $categories
	 * @return mixed
	 */
	public static function sortCategories(array $categories)
	{
		foreach($categories as $id	=>	$category)
		{
			$new_id = $category['sort'].$id;
			$new_categories[$new_id] = $category;
		}

		ksort($new_categories);
		unset($categories);
		return self::arrayToAssoc($new_categories, 'id');
	}

	/**
	 * arrayUnshiftAssoc(&$arr, $key, $val) Add element of assoc array to first position
	 * @param array $arr
	 * @param string $key
	 * @param string $val
	 * @return int
	 */
	public static function arrayUnshiftAssoc(&$arr, $key, $val)
	{
		$arr = array_reverse($arr, true);
		$arr[$key] = $val;
		$arr = array_reverse($arr, true);
		return count($arr);
	}

	/**
	 * itemMeasures($itemDimensions) get using item dimensions
	 * @param array $itemDimensions
	 * @return array filtered values like
	 * Usable Values Array
	 *      (
	 *          [back_lenght] => Array
	 *          (
	 *              [S] => 56
	 *              [M] => 58
	 *              [L] => 60
	 *              [XS] => 54
	 *              [XL] => 62
	 *              [XXL] => 64
	 *          )
	 *
	 *          [sleeve_lenght] => Array
	 *          (
	 *              [S] => 35
	 *              [M] => 37
	 *              [L] => 39
	 *              [XS] => 33
	 *              [XL] => 41
	 *              [XXL] => 43
	 *          )
	 *      )
	 */
	static public function itemMeasures($itemDimensions)
	{
		$values                 =   array();

		// sort items by callback compare function
		uksort($itemDimensions, 'self::itemCompareSize');

		// Search parameters for measuring this product
		foreach($itemDimensions as $size => $measure)
		{
			// Removing unusable measures and collect usable
			foreach($measure as $k => $v) {

				if(!empty($v) || $v !='0') {

					// collect usable data
					$values[$k][$size] = $v;
				}
			}
		}

		if(!empty($values))
		{
			$sizeRange  = array_keys(reset($values));
			// add range between first and last elements

			$f = reset($sizeRange);
			$l = end($sizeRange);

			if(isset($f) && isset($l))
				array_unshift($sizeRange, $f.' - '.$l);

			// overwritten with the value range in size
			foreach($values as $k => $v) {
				$f = reset($v);
				$l = end($v);
				self::arrayUnshiftAssoc($values[$k], reset($sizeRange), $f.' - '.$l);
			}

			return array(
				'values'        =>  array_reverse($values),
				'sizes'         =>  $sizeRange,
			);
		}
		else return [];

	}

	/**
	 * arraySum(array $array, $property) Суммирует значения в массиве по определенному полю
	 * @param array $array instance of array
	 * @param  string $property sum field
	 * @access static
	 * @return int
	 */
	public static function arraySum(array $array, $property)
	{
		$total = 0;
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$total += self::arraySum($value, $property);
			}
			else if ($key == $property) {
				$total += $value;
			}
		}
		return $total;
	}

	/**
	 * itemCompareSize($a,$b) helps callback to compare item size
	 * @var string $itemSizeField
	 * return array
	 */
	public static function itemCompareSize($a,$b)
	{
		$a = trim($a);
		$b = trim($b);
		$s = array(
			"XXS"		=> 0,
			"XS"        => 1,
			"S"         => 2,
			"M"         => 3,
			"L"         => 4,
			"XL"        => 5,
			"XXL"       => 6,
			"XXXL"      => 7,
			"XXXXL"     => 8,
			"XXXXXL"    => 9,
			"XXXXXXL"   => 10,
		);
		if($a == $b) return 0;
		if(isset($s[$a]) && isset($s[$b])) return ($s[$a] > $s[$b] ? 1 : -1);
		if(isset($s[$a])) return 1;
		if(isset($s[$b])) return -1;
		$a_is_num = preg_match('/^[0-9\.,]+$/', $a);
		$b_is_num = preg_match('/^[0-9\.,]+$/', $b);
		if ($a_is_num && !$b_is_num) return -1;
		if ($b_is_num && !$a_is_num) return 1;
		if ($a_is_num && $b_is_num) return floatval($a) > floatval($b) ? 1 : -1;
		return strnatcmp($a,$b);
	}

	/**
	 * catalogueDimensionsImages($category_id)  Возвращает изображение с примеркой по категории
	 * @param int $category_id
	 * @see app/modules/ZKZ/views/partials/catalogue/measured_sizes.phtml
	 * @access static
	 * @return array
	 */
	public static function catalogueDimensionsImages($category_id)
	{
		$categories = [
			'11' => array('shirt.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght' */ // 'Куртки и пуховики'
			'13' => array('jeans.png'), /* 'item_lenght','internal_lenght','planting_depth','waist' */ // 'Джинсы и штаны'
			'14' => array('shirt.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght' */ // 'Свитера и кофты'
			'16' => array('shirt.png'), /* 'back_lenght','collar_volume','shoulders_width','chest_width','sleeve_lenght','wrist_volume' */ // 'Рубашки'
			'17' => array('jeans.png'), /* 'item_lenght','internal_lenght0','planting_depth','waist' */ // 'Шорты'
			'18' => array('t-shirt.png'), /* 'back_lenght','shoulders_width','chest_volume','sleeve_lenght0' */ // 'Футболки и поло'
			'19' => array('shirt.png'), /* 'back_lenght','shoulders_width','chest_volume','sleeve_lenght' */ // 'Кофты и блузки'
			'20' => array('jeans.png'), /* 'item_lenght','internal_lenght0','planting_depth','waist','hips','shin_volume' */ // 'Джинсы и брюки'
			'21' => array('jacket.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght' */ // 'Куртки и плащи'
			'22' => array('dress.png'), /* 'item_lenght','chest_volume','waist','hips','sleeve_lenght0','wrist_volume','cut_depth','back_cut_depth' */ // 'Платья'
			'23' => array('shirt.png','jeans.png','skirt.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght','pants_lenght','internal_lenght','planting_depth0','waist' */ // 'Костюмчики'
			'24' => array('t-shirt.png'), /* 'back_lenght','shoulders_width','chest_volume','sleeve_lenght0' */ // 'Футболки и топики'
			'25' => array('jeans.png','skirt.png'), /* 'item_lenght','internal_lenght0','planting_depth','waist' */ // 'Шорты и юбки'
			'105' => array('belt.png'), /* 'min_waist','max_waist' */ // 'Ремни'
			'1054' => array('jacket.png'), /* 'back_lenght','shoulders_width','chest_volume0','sleeve_lenght' */ // 'Пиджаки'
			'1772' => array('belt.png'), /* 'min_waist','max_waist' */ // 'Ремни'
			'1773' => array('belt.png'), /* 'min_waist','max_waist' */ // 'Мужские ремни'
			'1774' => array('belt.png'), /* 'min_waist','max_waist' */ // 'Женские ремни'
			'3373' => array('shirt.png', 'skirt.png', 'jeans.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght','pants_lenght','internal_lenght','waist' */ // Детская одежда
			'18325' => array('shirt.png', 'jeans.png'), /* 'back_lenght','shoulders_width','chest_width','sleeve_lenght','pants_lenght','internal_lenght','planting_depth0','waist' */ // 'Спортивные костюмы'
			'24552' => array('shirt.png', 'jeans.png'), /* 'back_lenght','shoulders_width','chest_width0','sleeve_lenght','pants_lenght','internal_lenght','planting_depth0','waist' */ // 'Костюмы'
		];
		return isset($categories[$category_id]) ? $categories[$category_id] : false;
	}

	/**
	 * dimensionsImages($category_id) также учавствует в формировании изображения для примерки
	 * @param string $image
	 * @see app/modules/ZKZ/views/partials/catalogue/measured_sizes.phtml
	 * @access static
	 * @return mixed
	 */
	public static function dimensionsImages($image)
	{
		$images = [
			'belt.png' 		=> array('description' => ''),
			'dress.png' 	=> array('description' => ''),
			'jacket.png' 	=> array('description' => ''),
			'jeans.png' 	=> array('description' => ''),
			'shirt.png' 	=> array('description' => '', 'dimensions' => array('back_lenght','collar_volume','shoulders_width','chest_width','sleeve_lenght','wrist_volume')),
			'skirt.png' 	=> array('description' => ''),
			't-shirt.png' 	=> array('description' => ''),
		];
		return $images[$image];
	}
}