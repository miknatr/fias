-- говно в интервалах
WITH RECURSIVE
    ids as ( select DISTINCT i.address_id
                        from intervals as i
                        left join afh
                            on afh.address_id = i.address_id
                        where afh.address_id is null
    ),
    required_address(base, height, parent_id, title) AS (
        SELECT DISTINCT a.address_id AS base, 0, parent_id, prefix || '. ' || title AS title
        FROM address_objects_xml_importer AS a
         INNER JOIN ids
            ON ids.address_id = a.address_id
        UNION ALL
        SELECT base, ra.height + 1, ar.parent_id, ar.prefix || '. ' || ar.title AS title
           FROM address_objects_xml_importer AS ar
           INNER JOIN required_address AS ra
               ON ra.parent_id =
                  ar.address_id),
    complete_addresses AS (
        SELECT base,array_to_string(array_agg(title),', ') AS title
             FROM
                 (SELECT * FROM required_address ORDER BY 1, 2 DESC) AS tmp
             GROUP BY 1
    )
SELECT DISTINCT
    i.start,
    i.end,
    i.type,
    COALESCE(i.postal_code || ' ') || ca.title,
    ca.base
FROM complete_addresses AS ca
    INNER JOIN intervals AS i
        ON i.address_id = ca.base
           AND (ca.title ILIKE ANY(ARRAY['%обл. Московская%', '%обл. Ленинградская%']))
    INNER JOIN ids ON ids.address_id = i.address_id
WHERE i.end NOT IN (99, 100, 101, 200, 201, 300, 301, 400, 401, 500, 501,600, 601, 700, 701, 800, 801, 900, 991, 990,  999, 1000, 10001, 9999, 199)
ORDER BY 2;


WITH RECURSIVE
        ids as ( select DISTINCT i.address_id
                 from intervals as i
                     left join afh
                         on afh.address_id = i.address_id
                 where afh.address_id is null
    ),
        required_address(base, height, parent_id, title) AS (
        SELECT DISTINCT a.address_id AS base, 0, parent_id, prefix || '. ' || title AS title
        FROM address_objects_xml_importer AS a
            INNER JOIN ids
                ON ids.address_id = a.address_id
        UNION ALL
        SELECT base, ra.height + 1, ar.parent_id, ar.prefix || '. ' || ar.title AS title
        FROM address_objects_xml_importer AS ar
            INNER JOIN required_address AS ra
                ON ra.parent_id =
                   ar.address_id),
        complete_addresses AS (
        SELECT base,array_to_string(array_agg(title),', ') AS title
        FROM
            (SELECT * FROM required_address ORDER BY 1, 2 DESC) AS tmp
        GROUP BY 1
    )
SELECT DISTINCT

    ca.base
FROM complete_addresses AS ca
    INNER JOIN intervals AS i
        ON i.address_id = ca.base
           AND (ca.title ILIKE ANY(ARRAY['%обл. Московская%', '%ленингадская%']))
    INNER JOIN ids ON ids.address_id = i.address_id
;

UPDATE address_objects AS ao SET
    level      = tmp.level,
    full_title = tmp.title
FROM (
WITH RECURSIVE required_addresses(level, address_id, title) AS (
        SELECT DISTINCT 0, address_id, "prefix" || ' ' || title
        FROM address_objects_xml_importer
        WHERE parent_id IS NULL
    UNION ALL
        SELECT ra.level + 1, ar.address_id, ra.title || ', ' || "prefix" || ' ' || ar.title
        FROM address_objects_xml_importer AS ar
        INNER JOIN required_addresses AS ra
        ON ra.address_id = ar.parent_id
)
   SELECT * FROM required_addresses
) AS tmp
WHERE tmp.address_id = ao.address_id;

