START TRANSACTION;

DROP TABLE IF EXISTS houses;
CREATE TABLE "houses" (
    id          UUID PRIMARY KEY NOT NULL,
    house_id    UUID             NOT NULL,
    address_id  UUID DEFAULT NULL,
    number      VARCHAR,
    full_number VARCHAR,
    building    VARCHAR,
    structure   VARCHAR,
    postal_code INTEGER
);
COMMENT ON TABLE  houses             IS 'данные по домам';
COMMENT ON COLUMN houses.id          IS 'идентификационный код записи';
COMMENT ON COLUMN houses.house_id    IS 'идентификационный код дома';
COMMENT ON COLUMN houses.address_id  IS 'идентификационный код адресного объекта';
COMMENT ON COLUMN houses.number      IS 'номер дома';
COMMENT ON COLUMN houses.building    IS 'корпус';
COMMENT ON COLUMN houses.structure   IS 'строение';
COMMENT ON COLUMN houses.postal_code IS 'индекс';

DROP TABLE IF EXISTS address_objects;
CREATE TABLE address_objects (
    id          UUID PRIMARY KEY NOT NULL,
    address_id  UUID             NOT NULL,
    parent_id   UUID             DEFAULT NULL,
    level       INTEGER,
    house_count INTEGER,
    title       VARCHAR,
    full_title  VARCHAR,
    postal_code INTEGER,
    prefix      VARCHAR
);
COMMENT ON TABLE address_objects              IS 'данные по адресным объектам(округам, улицам, городам)';
COMMENT ON COLUMN address_objects.id          IS 'идентификационный код записи';
COMMENT ON COLUMN address_objects.address_id  IS 'идентификационный код адресного объекта';
COMMENT ON COLUMN address_objects.parent_id   IS 'идентификационный код родительского адресного объекта';
COMMENT ON COLUMN address_objects.title       IS 'наименование объекта';
COMMENT ON COLUMN address_objects.full_title  IS 'полное наименование объекта';
COMMENT ON COLUMN address_objects.postal_code IS 'индекс';
COMMENT ON COLUMN address_objects.prefix      IS 'ул., пр. и так далее';
COMMENT ON COLUMN address_objects.house_count IS 'количество домов';

COMMIT;
