<?php
namespace Helpers;
use \Phalcon\Http\Request;

class CatalogueSizes extends \Phalcon\Tag
{

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
			CatalogueTags::arrayUnshiftAssoc($values[$k], reset($sizeRange), $f.' - '.$l);
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