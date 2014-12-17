####Phalcon Z95.ru (Z95.com.ua, Z95.kz) Shop
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/stanislav-web/Phalcon/badges/quality-score.png?b=Test)](https://scrutinizer-ci.com/g/stanislav-web/Phalcon/?branch=Test) [![Build Status](https://scrutinizer-ci.com/g/stanislav-web/Phalcon/badges/build.png?b=Test)](https://scrutinizer-ci.com/g/stanislav-web/Phalcon/build-status/Test)
=======

Рефакторинг корзины закончен.
Идет разработка синхронизации с Backend  интерфейсом и обновлением позиций

Совместимо с PHP 5.4 > 
Протестировано на MySQL 5.5.4 
Код требует отладки на продакшн версии. Используемая `Shop`. И тестирования на большую нагрузку.

##### Конфигурация
- настроить БД
- добавить (отредактировать) хост в Shop.shops
- добавить этот хост /app/config/modules.php
По умолчанию там стоит уже хост для Казахстана с кодом ZKZ.
Чтобы добавить еще один хост необходимо создать для него модуль или скопировать прежний и автозаменой заменить код текущего магазина "ZKZ" на свой желаемый.
