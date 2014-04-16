
INSERT INTO place_types(id, title)
VALUES
    (1, 'транспортный объект')
;

INSERT INTO place_types(id, title, parent_id, system_name)
VALUES
    (2, 'аэропорт', 1, 'airport'),
    (3, 'вокзал', 1, 'railway_station'),
    (4, 'автовокзал', 1, 'bus_terminal'),
    (5, 'порт', 1, 'port'),
    (6, 'терминал', 2, 'airport_terminal'),
    (7, 'речной вокзал', 1, 'riverside_station')
;

SELECT setval('place_types_id_seq', (SELECT MAX(id) FROM place_types LIMIT 1));

INSERT INTO places(id, parent_id, title, type_id)
VALUES
    (1, NULL, 'Внуково', 2),
    (2, 1, 'A', 6),
    (3, 1, 'B', 6),
    (4, 1, 'D', 6),
    (5, NULL, 'Домодедово', 2),
    (6, NULL, 'Шереметьево', 2),
    (7, 6, 'B', 6),
    (8, 6, 'C', 6),
    (9, 6, 'D', 6),
    (10, 6, 'E', 6),
    (11, 6, 'F', 6),
    (12, NULL, 'Остафьево', 2),
    (13, NULL, 'Раменское', 2),
    (14, NULL, 'Чкаловский', 2),
    (15, NULL, 'Пулково', 2),
    (16, 15, '1', 6),
    (17, 15, '2', 6),
    (18, 15, 'новый', 6)
;

SELECT setval('places_id_seq', (SELECT MAX(id) FROM places LIMIT 1));

INSERT INTO places(title, type_id)
VALUES
    ('Балтийский', 3),
    ('Витебский', 3),
    ('Ладожский', 3),
    ('Московский', 3),
    ('Финляндский', 3),
    ('Белорусский', 3),
    ('Казанский', 3),
    ('Киевский', 3),
    ('Курский', 3),
    ('Ленинградский', 3),
    ('Павелецкий', 3),
    ('Рижский', 3),
    ('Савеловский', 3),
    ('Ярославский', 3),
    ('Щелковский', 4),
    ('Павелецкий', 4),
    ('Морской', 5),
    ('Пролетарский', 7),
    ('Уткина заводь', 7)
;

UPDATE places p
SET full_title = p.title || ' ' || pt.title
FROM place_types pt
WHERE pt.id = p.type_id;

-- Формируем полный заголовок
UPDATE places p
SET full_title = tmp.title
FROM (
    WITH RECURSIVE required_places(id, title) AS (
        SELECT DISTINCT pw.id, pw.title || ' ' || pwt.title
        FROM places pw
        INNER JOIN place_types pwt
            ON pwt.id = pw.type_id
        WHERE pw.parent_id IS NULL
        UNION ALL
        SELECT pwr.id, pw.title || ', ' || pwr.title || ' ' || pwt.title
        FROM places pwr
        INNER JOIN required_places pw
            ON pw.id = pwr.parent_id
        INNER JOIN place_types pwt
            ON pwt.id = pwr.type_id
    )
    SELECT * FROM required_places
) tmp
WHERE tmp.id = p.id;

-- Определяем наличие дочерних записей
UPDATE places
SET have_children = TRUE
WHERE id IN (SELECT DISTINCT parent_id FROM places)


