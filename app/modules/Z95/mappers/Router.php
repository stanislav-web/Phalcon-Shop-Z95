<?php
namespace Mappers;
use Helpers\Catalogue,
	Models;

/**
 * Class Router  Маршрутизатор выдачи товаров
 *
 * @package Shop
 * @subpackage Mappers
 * @author Stanislav WEB
 * @example <code>
 *          (new Router())	->setRules($action)
 *          	->setCollection($collection)
 *				->setExclude(['top' => 'ТОП 200', 'favorites' => 'Понравилось', 'new' => 'Новинки', 'sale' => 'Распродажа'])
 *				->setNav($this->_breadcrumbs)
 *				->render();
 *          </code>
 */
class Router extends \Phalcon\Mvc\Controller
{
	private

			/**
			 * Модель для работы
			 * @see рекомендую передавать модель чтобы использовать
			 * 		уже готовое соединение
		 	 * @var object
		 	 */
			$_model	= false,

			/**
			 * Параметры магазина
		 	 * @var object
		 	 */
			$_shop	= false,

			/**
			 * Входящий набор для маршрутизации
		 	 * @var object
		 	 */
			$_rules	= false,

			/**
			 * Категории: набор исключений (для виртуальных категорий)
		 	 * @var array
		 	 */
			$_exclude	=	[],

			/**
			 * Коллекция всех категорий
		 	 * @var array
		 	 */
			$_collection	=	[],

			/**
			 * Навигационная цепочка
		 	 * @var \Breadcrumbs\Breadcrumbs
		 	 */
			$_breadcrumbs	=	false,

			/**
		 	 * Заголовок: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_title		=	false,

			/**
			 * Шаблон выдачи: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_template	=	false,

			/**
		 	 * Шаблон выдачи: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_items	=	false,

			/**
			 * Вывод товаров на страницу
		 	 * @var int
		 	 */
			$_pages	=	100,

			/**
			 * Пагинация: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_pagination	=	false;

	/**
	 * Установка модели
	 * @param \Models\Products $model
	 * @return \Mappers\Router
	 */
	public function setModel(\Models\Products $model)
	{
		$this->_model	=	$model;
		return $this;
	}

	/**
	 * Установка правил маршрута
	 * @param stdClass $rules
	 * @return \Mappers\Router
	 */
	public function setRules($rules)
	{
		$this->_rules	=	$rules;
		return $this;
	}

	/**
	 * Установка правил магазина
	 * @param array $shop
	 * @return \Mappers\Router
	 */
	public function setShop(array $shop)
	{
		$this->_shop	=	$shop;
		return $this;
	}

	/**
	 * Установка коллекции всех категорий с параметрами
	 * @param array $collection
	 * @return \Mappers\Router
	 */
	public function setCollection(array $collection)
	{
		$this->_collection	=	$collection;
		return $this;
	}

	/**
	 * Установка заголовка
	 * @param string $title
	 * @access protected
	 * @return \Mappers\Router
	 */
	protected function setTitle($title)
	{
		$this->_title	=	$title;
		$this->tag->prependTitle($title.' - ');
		return $this;
	}

	/**
	 * Установка навигационной цепочки
	 * @param \Breadcrumbs\Breadcrumbs $breadcrumbs
	 * @return \Mappers\Router
	 */
	public function setNav(\Breadcrumbs\Breadcrumbs $breadcrumbs)
	{
		$this->_breadcrumbs	=	$breadcrumbs;
		return $this;
	}

	/**
	 * Установка шаблона для вывода
	 * @param string $template
	 * @return \Mappers\Router
	 */
	public function setTemplate($template)
	{
		$this->_template	=	$template;
		return $this;
	}

	/**
	 * Установка шаблона для вывода
	 * @param array $items
	 * @return \Mappers\Router
	 */
	public function setItems(array $items)
	{
		$this->_items	=	$items;
		return $this;
	}

	/**
	 * Установка исключений
	 * @param array $items
	 * @return \Mappers\Router
	 */
	public function setExclude(array $exclude)
	{
		$this->_exclude	=	$exclude;
		return $this;
	}

	/*
	 * Вывод результатов
	 * @param object 	$rules 		правила для маппера
	 * @param array  	$collection коллекция всех категорий
	 * @param array 	$exclude 	исключения: вирт. категории
	 * @return null
	 */
	public function render($model = false, array $shop = [], $rules = false, array $collection = [], array $exclude = [])
	{

		// устанавливаю параметры магазина

		if(!$this->_model)
			$this->setModel($model);

		// устанавливаю параметры магазина

		if(!$this->_shop)
			$this->setShop($shop);

		// устанавливаю правила, если их не задали

		if(!$this->_rules)
			$this->setRules($rules);

		// устанавливаю все категории с их описанием

		if(!$this->_collection)
			$this->setCollection($collection);

		// устанавливаю исключения (предусмотрена возможность если их и не будет)

		if(!$this->_exclude && !empty($exclude))
			$this->setExclude($exclude);

		// определяю категорию отображения
		// сначала проверяю ее в виртуальных, так как их меньше
		$intersect = array_intersect($this->_rules->catalogue, array_values(array_flip($this->_exclude)));

		if(isset($this->_exclude[$this->_rules->current])
			|| !empty($intersect))
		{
			// Обработка виртуалок

			$category	= [
				'name'	=>	$this->_exclude[$intersect[0]],
				'alias'	=>	$intersect[0]
			];
		}
		else
		{
			// Обычная выдача категорий

			// получаю параметры текущей категории
			$category = $this->_collection[$this->_rules->current];

			// ищем родителя категории, (уже сброшены, поэтому родитель всегда 0)
			$parent = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[0]);

			// добавляю в навигацию
			$this->_breadcrumbs->add($parent[0]['name'], 'catalogue/'.$parent[0]['alias']);

			//@todo Будет проверка на фильтры


			$items	=	$this->_model->getProducts(
								$this->_shop['price_id'],
								['rel.category_id'	=>	$category['id']]
								, 0, $this->_pages,
								true);

			$count = array_pop($items);


			// задаю шаблон для вывода результата выдачи
			$this->setItems($items)->setTemplate('itemsline');
		}


		// Устанавливаю мета данные
		$this->setTitle($category['name']);
		$this->_breadcrumbs->add($category['name'], 'catalogue/'.$category['alias']);

		// передача переменных в шаблон
		$this->view->setVars([
			'title'			=>	$this->_title,
			'template'  	=> 	$this->_template,
			'items'			=>	$this->_items,
			'pagination'	=>	$this->_pagination,
		]);

		// рендеринг шаблона
		$this->view->pick("catalogue/index");
	}
}
