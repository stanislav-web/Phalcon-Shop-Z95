####Phalcon Z95.ru (Z95.com.ua, Z95.kz) Shop
[![Codacy Badge](https://www.codacy.com/project/badge/0de281474c6049aab9df132a36d9da1f)](https://www.codacy.com/public/stanisov/Phalcon)
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
