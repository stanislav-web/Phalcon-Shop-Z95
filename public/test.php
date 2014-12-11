<?php
	class Beer {
		public $o;
		const NAME = 'Beer!';
		public function getName() {
			return self::NAME;
		}
	}
	class Ale extends Beer {

		public $o = '3343';
		const NAME = 'Ale!';
	}

	$beerDrink = new Beer;
	$aleDrink = new Ale;

	var_dump($beerDrink);var_dump($aleDrink);