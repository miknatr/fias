START TRANSACTION;
-- STOPPER целостность оптимизация, фильтрация говнятины, точка входа
    DROP TABLE IF EXISTS address_objects;
    CREATE TABLE address_objects(
        id UUID PRIMARY KEY NOT NULL,
        parent_id UUID DEFAULT NULL,
        title VARCHAR,
        official_title VARCHAR,
        postal_code INTEGER,
        prefix VARCHAR
    );

    COMMENT ON TABLE address_objects IS 'Данные по адресным объектам(округам, улицам, городам)';
    COMMENT ON COLUMN address_objects.title IS 'Наименование объекта';
    COMMENT ON COLUMN address_objects.official_title IS 'Официальное наименование объекта(для документов)';
    COMMENT ON COLUMN address_objects.postal_code IS 'Индекс';
    COMMENT ON COLUMN address_objects.prefix IS 'ул.(улица) пр.(проспект) и так далее';

COMMIT;
