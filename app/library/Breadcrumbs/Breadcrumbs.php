<?php
namespace Breadcrumbs;


class Breadcrumbs {

	public $tree;

	/**
	 * Build all navigation with configuration
	 *
	 * @param type $config
	 */
	public function __construct($config) {
		$this->tree	= \Helpers\CatalogueTags::arrayToAssoc($config, 'url');
	}


	/**
	 * catalogueBreadcrumbs($separator, $classLink, $home) Хлебные крошки каталога
	 * @param string $separator разделитель
	 * @param string $classLink класс сылок
	 * @param string $home Домашняя директория по умолчанию
	 * @access static
	 * @return array
	 */
	public  function get($separator, $home = 'Главная')
	{
		$request = (new \Phalcon\Http\Request())->get()['_url'];
		// This gets the REQUEST_URI (/path/to/file.php), splits the string (using '/') into an array, and then filters out any empty values
		$path = array_filter(explode('/', $request));
		// This will build our "base URL" ... Also accounts for HTTPS :)
		$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

		// Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
		$breadcrumbs = Array("<li><a href=\"$base\">$home</a></li>");

		// Find out the index for the last value in our path array
		$last = end(array_keys($path));
		// Build the rest of the breadcrumbs
		foreach ($path AS $x => $crumb) {
			// Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
			$title = ucwords(str_replace(Array('.php', '_'), Array('', ' '), $crumb));

			// If we are not on the last index, then display an <a> tag
			if($x != $last)
				$breadcrumbs[] = "<li><a href=\"$base$crumb\">{$this->tree[$request]['name']}</a></li>";
			// Otherwise, just display the title (minus)
			else
				$breadcrumbs[] = $title;
		}
		// Build our temporary array (pieces of bread) into one big string :)
		return implode($separator, $breadcrumbs);
	}


	public function getTree()
	{
		return $this->tree;
	}
}