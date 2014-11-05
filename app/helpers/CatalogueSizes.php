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
		uksort($itemDimensions, 'itemCompareSize');

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
			array_unshift_assoc($values[$k], reset($sizeRange), $f.' - '.$l);
		}

		return array(
			'values'        =>  array_reverse($values),
			'sizes'         =>  $sizeRange,
		);
	}


}