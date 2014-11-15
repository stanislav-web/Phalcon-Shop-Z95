<?php
namespace Helpers;
use \Phalcon\Http\Request;

/**
 * Class Catalogue Помощник для работы с каталогом
 * @example // Разбивка роутинга на деревья
 *
 * 		Catalogue::catalogueNavTree($this->request->getURI(), [
 *			'categories', 'brands', 'tags'
 *		]);
 *
 * @example // Поиск категории в дереве массива
 * 		Catalogue::findInTree($array, 'parent_id, '150)
 */

class Catalogue
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

	public static function findInTree2($array, $key, $value, $key2, $value2)
	{
		$results = array();

		$arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		foreach ($arrIt as $sub) {
			$subArray = $arrIt->getSubIterator();
			if ($subArray[$key2] == $value2 && $subArray[$key] == $value) {
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

	/**
	 * categoriesToTree  Построение дерева категорий (с сортировкой)
	 * @param      $array входящий массив с категориями
	 * @param int  $parent_id внутри какого parent сортировать?
	 * @param bool $sort Сортировать?
	 * @return array
	 */
	public static function categoriesToTree(array $array, $parent_id = 0, $sort = false) {
		$tree = array();

		if( !empty($array)){
			$array = self::arrayToAssoc($array, 'id');

			foreach( $array as $id => $element ){

				$element = (array) $element;
				if( !isset($element['parent_id']) ) continue;
				if( $element['parent_id'] == $parent_id ){
					$tree[$id] = $element;
					unset($array[$element['id']]);
					$tree[$id]['childs'] = self::categoriesToTree($array, $element['id']);

				}
			}
			if($sort) $tree = self::sortCategories($tree);
			return $tree;
		}
	}

	public static function declOfNum($number, $titles) {
		$cases = array (2, 0, 1, 1, 1, 2);
		return $number . ' ' . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ] . ' ';
	}

	/**
	 * basketMini(array $basket) подсчет в мини корзине
	 *
	 * @param array $basket['items']
	 * @return array
	 */
	public static function basketMini(array $basket)
	{
		$cart = [
			'total' =>	0,
			'sum' 	=>	0
		];

		foreach($basket as $item => $val)
		{
			$countSizes = (isset($basket[$item]['sizes'])) ? sizeof($basket[$item]['sizes']) : 0;
			$cart['total']	+= (isset($val['sizes']) && !empty($val['sizes']))  ? sizeof($val['sizes']) : 0;
			$cart['sum']	+= ((isset($val['discount']))  ? $val['discount'] : 0) * $countSizes;
		}
		return $cart;
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
				if(!empty($v)) {
					// collect usable data
					$values[$k][$size] = $v;
				}
			}
		}

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
	 * @see app/modules/Z95/views/partials/catalogue/measured_sizes.phtml
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
		return $categories[$category_id];
	}

	/**
	 * dimensionsImages($category_id) также учавствует в формировании изображения для примерки
	 * @param string $image
	 * @see app/modules/Z95/views/partials/catalogue/measured_sizes.phtml
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