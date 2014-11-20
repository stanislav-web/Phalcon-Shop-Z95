<?php
namespace Breadcrumbs;

use Phalcon\Http\Request;

class Breadcrumbs {

	/**
	 * Набор для хранения элементов
	 * @var
	 */
	private $_elements,


		/**
		 * Набор для хранения элементов
		 * @var
		 */
		$_request;

	/**
	 * Принимаю по умолчанию параметры главной страницы
	 */
	public function __construct()
	{
		$this->_request	=	new Request();
		// Ставлю началом в цепочке главную страницу

		$this->_elements[] = [
			'active' => false,
			'link'   => '/'.$this->_request->getHttpHost(),
			'text'   => 'Главная',
		];
		if($this->_request->getURI() != '/catalogue')
			$this->_elements[] = [
				'active' => false,
				'link'   => '/'.$this->_request->getHttpHost().'/catalogue',
				'text'   => 'Каталог',
			];
	}

	/**
	 * Добавление элементов в цепочку
	 *
	 * @param string $caption заголовок
	 * @param string $link ссылка
	 * @access public
	 * @return this
	 */
	public function add($caption, $link)
	{
		$this->_elements[] = [
			'active' => false,
			'link'   => '/'.$this->_request->getHttpHost().'/'.$link,
			'text'   => $caption,
		];

		return $this;
	}

	/**
	 * Сброс цепочки
	 * @access public
	 * @return null
	 */
	public function reset()
	{
		$this->_elements = [];
	}

	/**
	 * Удаление последнего элемента из навигации
	 * @access public
	 * @return null
	 */
	public function unsetLast()
	{
		array_pop($this->_elements);
	}

	/**
	 * Генерация JSON из элементов навигации
	 *
	 * @return string
	 */
	public function generate()
	{
		$lastKey = key(array_slice($this->_elements, -1, 1, true));
		$this->_elements[$lastKey]['active'] = true;
		return $this->_elements;
	}
}