    -- 1. АКТУАЛЬНЫЕ ОБНОВЛЕНИЯ БРЕНДОВ

        DELETE FROM brands;

        INSERT INTO
            `Shop`.`brands` (`id`, `name`, `description`, `alias`, `date_create`, `date_update`)
        SELECT
            catalogue_brands.`id` AS id,
            catalogue_brands.`brand` AS name,
            catalogue_brands.`text` AS description,
            transliterate(catalogue_brands.`brand`)  AS alias,
            NOW(),
            catalogue_brands.`last_update` AS date_update

            FROM `frontend`.`catalogue_brands` catalogue_brands;


    -- 2. АКТУАЛЬНОЕ ОБНОВЛЕНИЕ ТОВАРОВ

        DELETE FROM products;

        INSERT INTO
            `Shop`.`products` (`id`, `articul`, `name`, `description`, `preview`, `images`, `brand_id`, `is_new`, `sex`,
            `rating`, `published`, `tags`,  `filter_tags`, `filter_size`, `date_create`, `date_update`)

        SELECT
            catalogue.`cat_id` AS id,
            catalogue.`articul` AS articul,
            catalogue.`cat_title` AS name,
            catalogue.`cat_text` AS description,
            catalogue.`preview` AS preview,
            catalogue.`photos` AS images,
            catalogue.`brand_id` AS brand_id,
            catalogue.`is_new` AS is_new,
            catalogue_categories.`sex` AS sex,
            catalogue.`rating` AS rating,
            catalogue.`cat_status` AS published,
            catalogue.`tags` AS tags,
            GROUP_CONCAT(catalogue_tags_items.tag_id SEPARATOR ',') AS filter_tags,
            GROUP_CONCAT(DISTINCT catalogue_dimensions.size ORDER BY size ASC SEPARATOR ',') AS filter_size,
            catalogue.`cat_create_dt` AS date_create,
            catalogue.`cat_update_dt` AS date_update

            FROM `frontend`.`catalogue` catalogue

            INNER JOIN frontend.catalogue_categories_items catalogue_categories_items ON (
    	       catalogue_categories_items.item_id = catalogue.`cat_id`
            )
            INNER JOIN frontend.catalogue_categories catalogue_categories ON (
    	       catalogue_categories.`id` = catalogue_categories_items.`cat_id`
            )
            LEFT JOIN frontend.catalogue_tags_items catalogue_tags_items ON (
    	       catalogue_tags_items.`cat_id` = catalogue.`cat_id`
            )
            LEFT JOIN frontend.catalogue_dimensions catalogue_dimensions ON (
    	       catalogue_dimensions.cat_id = catalogue.`cat_id`
            )

            WHERE 	catalogue.`cat_status` = 1
                && EXISTS  (
                        SELECT id
                        FROM `Shop`.`brands`
                        WHERE id = `catalogue`.`brand_id`
            )

            GROUP BY catalogue.`cat_id`;

    -- 3. АКТУАЛЬНОЕ ОБНОВЛЕНИЕ ЦЕН

        DELETE FROM prices;

        INSERT INTO
            `Shop`.`prices` (`id`, `product_id`, `price`, `discount`, `percent`, `date_create`)
        SELECT
            catalogue_prices.`price_id` AS id,
            catalogue_prices.`cat_id` AS product_id,
            catalogue_prices.`price` AS price,
            catalogue_prices.`discount_price` AS discount,
            catalogue_prices.`discount` AS percent,
            catalogue_prices.`last_update` AS date_create

            FROM `frontend`.`catalogue_prices` catalogue_prices

            WHERE EXISTS (
                        SELECT id
                        FROM `Shop`.`products`
                        WHERE id = catalogue_prices.`cat_id`
            );

    -- 4  АКТУАЛЬНОЕ ОБНОВЛЕНИЕ МАГАЗИНОВ
        -- Добавлять вручную, по мере создания нового модуля в Phalcon

    -- 5. АКТУАЛЬНОЕ ОБНОВЛЕНИЕ КАТЕГОРИЙ

        DELETE FROM categories;

        INSERT INTO
            `Shop`.`categories` (`id`, `name`, `parent_id`, `alias`, `date_create`, `date_update`)
        SELECT
            catalogue_categories.`id` AS id,
            catalogue_categories.`title` AS name,
            catalogue_categories_join_shop.`parent` AS parent_id,
            catalogue_categories.`url`  AS alias,
            catalogue_categories.`create_date`  AS date_create,
            catalogue_categories.`last_update`  AS date_update

            FROM `frontend`.`catalogue_categories` catalogue_categories

            INNER JOIN `frontend`.`catalogue_categories_join_shop` catalogue_categories_join_shop ON (
                catalogue_categories_join_shop.category = catalogue_categories.id
            )

            WHERE catalogue_categories.status = 1
            GROUP BY catalogue_categories.`id`;

    -- 6 АКТУАЛЬНЫЕ ОБНОВЛЕНИЯ СВЯЗЕЙ КАТЕГОРИЙ - МАГАЗИН

        DELETE FROM category_shop_relationship;

        INSERT INTO
            `Shop`.`category_shop_relationship` (`category_id`, `category_parent_id`, `shop_id`, `sort`)
        SELECT
            catalogue_categories_join_shop.`category` AS category_id,
            catalogue_categories_join_shop.`parent` AS category_parent_id,
            catalogue_categories_join_shop.`shop` AS shop_id,
            catalogue_categories_join_shop.`sort` AS sort

            FROM `frontend`.`catalogue_categories_join_shop` catalogue_categories_join_shop

            WHERE EXISTS    (
                        SELECT id
                        FROM `Shop`.`categories`
                        WHERE id = catalogue_categories_join_shop.`category` || id = catalogue_categories_join_shop.`parent`
                    )
                    AND EXISTS (
                        SELECT id
                        FROM `Shop`.`shops`
                        WHERE id = catalogue_categories_join_shop.`shop`
            );

    -- 7. АКТУАЛЬНОЕ ОБНОВЛЕНИЕ ТЕГОВ

    DELETE FROM tags;
    ALTER TABLE Shop.tags AUTO_INCREMENT = 0;
    SET @i=131; -- установить инкремент последнего тега, так как далее идут размеры

    INSERT INTO
        `Shop`.`tags` (`id`,`name`, `parent_id`, `alias`, `date_create`, `date_update`)

    SELECT
        catalogue_tags.`id` AS id,
        catalogue_tags.`title` AS name,
        catalogue_tags.`parent_id` AS parent_id,
        IFNULL(catalogue_tags.`alias`, transliterate(catalogue_tags.`title`)) AS alias,
        NOW() AS date_create,
        catalogue_tags.last_update AS date_update

        FROM `frontend`.`catalogue_tags` catalogue_tags

        UNION SELECT
            @i:= @i+1 AS id,
            TRIM(`catalogue_dimensions`.size) AS name,
            null AS parent_id,
            TRIM(`catalogue_dimensions`.size) AS alias,
            NOW() AS date_create,
            catalogue_dimensions.last_update AS date_update

            FROM `frontend`.`catalogue_dimensions` catalogue_dimensions

            GROUP BY TRIM(`catalogue_dimensions`.`size`);

    -- 8. АКТУАЛЬНОЕ ОБНОВЛЕНИЕ СВЯЗЕЙ ТЕГОВ ТОВАРОВ КАТЕГОРИй

        DELETE FROM products_relationship;

        INSERT INTO
            `Shop`.`products_relationship` (`product_id`, `category_id`, `tag_id`)
        SELECT
            catalogue_categories_items.`item_id` AS product_id,
            catalogue_categories_items.`cat_id` AS category_id,
            NULL AS tag_id
            FROM `frontend`.`catalogue_categories_items` catalogue_categories_items
            WHERE  (
                        SELECT id
                        FROM `Shop`.`products`
                        WHERE id = catalogue_categories_items.`item_id`
                    )

            UNION SELECT
                catalogue_tags_items.`cat_id` AS product_id,
                null AS category_id,
                catalogue_tags_items.`tag_id` AS tag_id

                FROM `frontend`.`catalogue_tags_items` catalogue_tags_items

                WHERE EXISTS (
                        SELECT id
                        FROM `Shop`.`products`
                        WHERE id = catalogue_tags_items.`cat_id`
                    );