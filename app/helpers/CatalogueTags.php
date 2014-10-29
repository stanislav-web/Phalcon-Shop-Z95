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
}