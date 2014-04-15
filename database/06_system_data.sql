
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
    (6, 'терминал', 2, 'airport_terminal')
;

SELECT setval('place_types_id_seq',  (SELECT MAX(id) FROM place_types LIMIT 1));

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
    (16, NULL, '1', 6),
    (17, NULL, '2', 6),
    (18, NULL, 'новый', 6)
;

SELECT setval('places_id_seq',  (SELECT MAX(id) FROM places LIMIT 1));

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
    ('Морской', 5)
;

UPDATE places p
SET full_title = p.title || ' ' || pt.title
FROM place_types pt
WHERE pt.id = p.type_id;
