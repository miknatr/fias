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

delete from houses_xml_importer as h where not exists(select address_id from address_objects as ao where ao.address_id = h.address_id)
QUERY PLAN
----------------------------------------------------------------------------------------------
Delete on houses_xml_importer h  (cost=6917.81..967663.59 rows=12526196 width=12)
->  Hash Anti Join  (cost=6917.81..967663.59 rows=12526196 width=12)
Hash Cond: (h.address_id = ao.address_id)
->  Seq Scan on houses_xml_importer h  (cost=0.00..444313.82 rows=21876782 width=22)
->  Hash  (cost=5088.36..5088.36 rows=99636 width=22)
->  Seq Scan on address_objects ao  (cost=0.00..5088.36 rows=99636 width=22)
(6 rows)
Time: 288465.809 ms


DELETE FROM houses_xml_importer as h USING (SELECT h.id FROM houses_xml_importer as h left join address_objects as ao on ao.address_id = h.address_id where ao.id is null) AS toDel WHERE toDel.id = h.id;
QUERY PLAN
--------------------------------------------------------------------------------------------------------------------
Delete on houses_xml_importer h  (cost=7112.81..1106316.72 rows=1 width=18)
->  Nested Loop  (cost=7112.81..1106316.72 rows=1 width=18)
->  Hash Left Join  (cost=7112.81..1106305.31 rows=1 width=28)
Hash Cond: (h.address_id = ao.address_id)
Filter: (ao.id IS NULL)
->  Seq Scan on houses_xml_importer h  (cost=0.00..444313.82 rows=21876782 width=38)
->  Hash  (cost=5088.36..5088.36 rows=99636 width=38)
->  Seq Scan on address_objects ao  (cost=0.00..5088.36 rows=99636 width=38)
->  Index Scan using houses_xml_importer_pkey on houses_xml_importer h  (cost=0.00..11.39 rows=1 width=22)
Index Cond: (id = h.id)
(10 rows)
Time: 444060.063 ms

DELETE FROM houses_xml_importer WHERE address_id NOT IN (select address_id from address_objects);
QUERY PLAN
-------------------------------------------------------------------------------------------
Delete on houses_xml_importer  (cost=0.00..69159898094.11 rows=10938391 width=6)
->  Seq Scan on houses_xml_importer  (cost=0.00..69159898094.11 rows=10938391 width=6)
Filter: (NOT (SubPlan 1))
SubPlan 1
->  Materialize  (cost=0.00..6073.54 rows=99636 width=16)
->  Seq Scan on address_objects  (cost=0.00..5088.36 rows=99636 width=16)
(6 rows)
Time > 1 hour.


select h.*, ao.full_title from houses_xml_importer h
    inner join address_objects ao on ao.address_id = h.address_id
WHERE number ~ '[^0-9]+' AND ( (structure ~ '[^0-9]+' AND number = structure) OR (building ~ '[^0-9]+' AND number = building) );

UPDATE houses_xml_importer SET
building = NULL,
structure = NULL
WHERE number ~ '[^0-9]+'
AND (
(structure ~ '[^0-9]+' AND number = structure)
OR
(building ~ '[^0-9]+' AND number = building)
);


select * from houses_xml_importer where number ~ '[^0-9]+'

select * from houses_xml_importer where number ~ '[^0-9]+'
