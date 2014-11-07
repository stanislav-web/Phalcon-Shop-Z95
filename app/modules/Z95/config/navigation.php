<?php

	//  Навигация для Z95

	$navigation = [
		'navigation' => [

			'top' 		=> [
				'wrapper' => 'div',    // ul, div
				'class'   	=> 'header-menu',
				'id'   		=> 'header-menu',
				'childs'  => [
					[
						'name'       => 'Женщинам',
						'url'        => '/catalogue/woman',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'open'       => '<div class="block left">',
								'name'       => 'ТОП',
								'url'        => '/catalogue/women/top',
								'classLink'  => 'top',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кофты и блузки',
								'url'        => '/catalogue/woman/sweaters-blouses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Джинсы и брюки',
								'url'        => '/catalogue/woman/jeans',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Пиджаки',
								'url'        => '/catalogue/woman/coats',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Куртки и плащи',
								'url'        => '/catalogue/woman/jackets',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Платья',
								'url'        => '/catalogue/woman/dresses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Костюмы',
								'url'        => '/catalogue/woman/suits',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Футболки и топики',
								'url'        => '/catalogue/woman/tops-and-shirts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шорты и юбки',
								'url'        => '/catalogue/woman/skirts-and-shorts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Нижнее белье',
								'url'        => '/catalogue/woman/underwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Купальники',
								'url'        => '/catalogue/woman/swimwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шарфы, платки, перчатки',
								'url'        => '/catalogue/woman/swimwear',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>'
							],
							[
								'open'       => '<div class="block left">',
								'name'       => 'Аксессуары',
								'url'        => '#',
								'controller' => 'catalogue',
								'action'     => 'index',
								'click'    => 'return false;',
								'classLink'  => 'top',
							],
							[
								'name'       => 'Сумки',
								'url'        => '/catalogue/bags/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Женские ремни',
								'url'        => '/catalogue/straps/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Обувь',
								'url'        => '/catalogue/shoes/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кепки',
								'url'        => '/catalogue/accessories/baseball-hats',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>'
							],
							[
								'open'       => '<div class="block left">',
								'name'       => 'Очки',
								'url'        => '#',
								'controller' => 'catalogue',
								'action'     => 'index',
								'click'    => 'return false;',
								'classLink'  => 'top',
							],
							[
								'name'       => 'Женские очки',
								'url'        => '/catalogue/glasses/glasses-women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Медицинские очки',
								'url'        => '/catalogue/glasses/medicine',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>'
							],
						]
					],
					[
						'name'       => 'Мужчинам',
						'url'        => '/catalogue/man',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [
							[
								'open'       => '<div class="block left">',
								'name'       => 'ТОП',
								'url'        => '/catalogue/man/top',
								'classLink'  => 'top',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Куртки и пуховики',
								'url'        => '/catalogue/man/winter-fall',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Джинсы и штаны',
								'url'        => '/catalogue/man/jeans',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Свитера и кофты',
								'url'        => '/catalogue/man/sweaters',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Спортивные костюмы',
								'url'        => '/catalogue/man/sportsuits',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Рубашки',
								'url'        => '/catalogue/man/shirts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шорты',
								'url'        => '/catalogue/man/shorts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Плавки',
								'url'        => '/catalogue/man/swimming trunks',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шарфы, шапки, перчатки',
								'url'        => '/catalogue/man/scarves-hats-gloves',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Нижнее белье',
								'url'        => '/catalogue/man/underwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Футболки и поло',
								'url'        => '/catalogue/man/pollos',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Галстуки',
								'url'        => '/catalogue/man/ties',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Костюмы',
								'url'        => '/catalogue/man/suits',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>',
							],
							[
								'open'       => '<div class="block center">',
								'name'       => 'Аксессуары',
								'url'        => '#',
								'controller' => 'catalogue',
								'action'     => 'index',
								'click'    => 'return false;',
								'classLink'  => 'top',
							],
							[
								'name'       => 'Сумки',
								'url'        => '/catalogue/bags/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужские ремни',
								'url'        => '/catalogue/staraps/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Обувь',
								'url'        => '/catalogue/shoes/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кепки',
								'url'        => '/catalogue/accessories/baseball-hats',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Запонки',
								'url'        => '/catalogue/accessories/cufflinks',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>',
							],
							[
								'open'       => '<div class="block right">',
								'name'       => 'Очки',
								'url'        => '#',
								'controller' => 'catalogue',
								'action'     => 'index',
								'click'    => 'return false;',
								'classLink'  => 'top',
							],
							[
								'name'       => 'Мужские очки',
								'url'        => '/catalogue/man/winter-fall',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>',
							],
						]
					],
					[
						'name'       => 'Обувь',
						'url'        => '/catalogue/shoes',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Женская обувь',
								'url'        => '/catalogue/shoes/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужская обувь',
								'url'        => '/catalogue/shoes/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],

						]
					],
					[
						'name'       => 'Детям',
						'url'        => '/catalogue/kids',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Детская одежда',
								'url'        => '/catalogue/kids/kids-clothes',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Детская обувь',
								'url'        => '/catalogue/kids/kids-shoes',
								'controller' => 'catalogue',
								'action'     => 'index',
							],

						]
					],
					[
						'name'       => 'Аксессуары',
						'url'        => '/catalogue/accessories',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'open'       => '<div class="block left">',
								'name'       => 'Браслеты',
								'url'        => '/catalogue/accessories/bracelet',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Ручки для письма',
								'url'        => '/catalogue/accessories/pen',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Постельное белье',
								'url'        => '/catalogue/accessories/bed',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Запонки',
								'url'        => '/catalogue/accessories/cufflinks',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Брелки и подвески',
								'url'        => '/catalogue/accessories/',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кошельки',
								'url'        => '/catalogue/accessories/purses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кепки',
								'url'        => '/catalogue/accessories/baseball-hats',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Пляжные',
								'url'        => '/catalogue/accessories/beach',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Подарки',
								'url'        => '/catalogue/accessories/gifts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Зонты',
								'url'        => '/catalogue/accessories/umbrellas',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>',

							],
							[
								'open'       => '<div class="block left">',
								'name'       => 'Мужские сумки',
								'url'        => '/catalogue/bags/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Женские сумки',
								'url'        => '/catalogue/bags/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Чемоданы',
								'url'        => '/catalogue/bags/luggage',
								'controller' => 'catalogue',
								'action'     => 'index',
								'close'      => '</div>',

							],
							[
								'open'       => '<div class="block left">',
								'name'       => 'Мужские ремни',
								'url'        => '/catalogue/straps/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Женские ремни',
								'url'        => '/catalogue/straps/women',
								'controller' => 'catalogue',
								'action'     => 'index',
								'open'       => '</div>',
							],
						]
					],
					[
						'name'       => 'Очки',
						'url'        => '/catalogue/glasses',
						'class'      => 'header-menu-item category',
						'classLink'  => 'dropdown double',
						'click'    => "event.stopPropagation(); if ($(this).hasClass('s')) { return true; } else { $('.header-menu-item .submenu').hide(); $('.header-menu-item a').removeClass('s'); $('.submenu', $(this).parent()).show(); $(this).addClass('s'); return false; }",
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Женские очки',
								'url'        => '/catalogue/glasses/glasses-woman',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужские очки',
								'url'        => '/catalogue/glasses/glasses-man',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Медицинские очки',
								'url'        => '/catalogue/glasses/medicine',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],
					[
						'name'       => 'iPhone/iPad',
						'url'        => '/catalogue/apple',
						'class'      => 'header-menu-item category',
						'controller' => 'catalogue',
						'action'     => 'index',
					],

					[
						'name'       => 'ТОП 200',
						'url'        => '/catalogue/top',
						'class'      => 'header-menu-item category m-hide',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Женская одежда',
								'url'        => '/catalogue/woman/top',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужская одежда',
								'url'        => '/catalogue/man/top',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],

					[
						'name'       => 'Понравилось <span class="favorites-count"><span id="FavoritesCount">0</span></span>',
						'url'        => '/catalogue/favorites',
						'class'      => 'header-menu-item category',
						'classLink'  => 'favorites',
						'controller' => 'catalogue',
						'action'     => 'index',
					],
				]
			],

			'bottom' 	=> [
				'wrapper' => 'ul',    // ul, div
				'class'   => 'catalogue-structure',
				'childs'  => [
					[
						'name'       => 'Женщинам',
						'url'        => '/catalogue/woman',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Кофты и блузки',
								'url'        => '/catalogue/woman/sweaters-blouses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Джинсы и брюки',
								'url'        => '/catalogue/woman/jeans',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Пиджаки',
								'url'        => '/catalogue/woman/coats',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Куртки и плащи',
								'url'        => '/catalogue/woman/jackets',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Платья',
								'url'        => '/catalogue/woman/dresses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Костюмы',
								'url'        => '/catalogue/woman/suits',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Футболки и топики',
								'url'        => '/catalogue/woman/tops-and-shirts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шорты и юбки',
								'url'        => '/catalogue/woman/skirts-and-shorts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Нижнее белье',
								'url'        => '/catalogue/woman/underwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Купальники',
								'url'        => '/catalogue/woman/swimwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шарфы, платки, перчатки',
								'url'        => '/catalogue/woman/swimwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],
					[
						'name'       => 'Мужчинам',
						'url'        => '/catalogue/man',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [
							[
								'name'       => 'Куртки и пуховики',
								'url'        => '/catalogue/man/winter-fall',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Джинсы и штаны',
								'url'        => '/catalogue/man/jeans',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Свитера и кофты',
								'url'        => '/catalogue/man/sweaters',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Спортивные костюмы',
								'url'        => '/catalogue/man/sportsuits',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Рубашки',
								'url'        => '/catalogue/man/shirts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шорты',
								'url'        => '/catalogue/man/shorts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Плавки',
								'url'        => '/catalogue/man/swimming trunks',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Шарфы, шапки, перчатки',
								'url'        => '/catalogue/man/scarves-hats-gloves',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Нижнее белье',
								'url'        => '/catalogue/man/underwear',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Футболки и поло',
								'url'        => '/catalogue/man/pollos',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Галстуки',
								'url'        => '/catalogue/man/ties',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Костюмы',
								'url'        => '/catalogue/man/suits',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],
					[
						'name'       => 'Обувь',
						'url'        => '/catalogue/shoes',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Женская обувь',
								'url'        => '/catalogue/shoes/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужская обувь',
								'url'        => '/catalogue/shoes/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],

						]
					],
					[
						'name'       => 'Детям',
						'url'        => '/catalogue/kids',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Детская одежда',
								'url'        => '/catalogue/kids/kids-clothes',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Детская обувь',
								'url'        => '/catalogue/kids/kids-shoes',
								'controller' => 'catalogue',
								'action'     => 'index',
							],

						]
					],

					[
						'name'       => 'Ремни',
						'url'        => '/catalogue/straps',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Мужские ремни',
								'url'        => '/catalogue/straps/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Женские ремни',
								'url'        => '/catalogue/straps/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],

						]
					],

					[
						'name'       => 'Сумки',
						'url'        => '/catalogue/bags',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Мужские сумки',
								'url'        => '/catalogue/bags/men',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Женские сумки',
								'url'        => '/catalogue/bags/women',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Чемоданы',
								'url'        => '/catalogue/bags/luggage',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],

					[
						'name'       => 'Аксессуары',
						'url'        => '/catalogue/accessories',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Браслеты',
								'url'        => '/catalogue/accessories/bracelet',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Ручки для письма',
								'url'        => '/catalogue/accessories/pen',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Постельное белье',
								'url'        => '/catalogue/accessories/bed',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Запонки',
								'url'        => '/catalogue/accessories/cufflinks',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Брелки и подвески',
								'url'        => '/catalogue/accessories/',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кошельки',
								'url'        => '/catalogue/accessories/purses',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Кепки',
								'url'        => '/catalogue/accessories/baseball-hats',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Пляжные',
								'url'        => '/catalogue/accessories/beach',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Подарки',
								'url'        => '/catalogue/accessories/gifts',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Зонты',
								'url'        => '/catalogue/accessories/umbrellas',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],
					[
						'name'       => 'Очки',
						'url'        => '/catalogue/glasses',
						'controller' => 'catalogue',
						'action'     => 'index',
						'childs'     => [

							[
								'name'       => 'Женские очки',
								'url'        => '/catalogue/glasses/glasses-woman',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Мужские очки',
								'url'        => '/catalogue/glasses/glasses-man',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
							[
								'name'       => 'Медицинские очки',
								'url'        => '/catalogue/glasses/medicine',
								'controller' => 'catalogue',
								'action'     => 'index',
							],
						]
					],
					[
						'name'       => 'iPhone/iPad',
						'url'        => '/catalogue/apple',
						'controller' => 'catalogue',
						'action'     => 'index',
					],
				]
			],
		]
	];