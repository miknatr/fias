START TRANSACTION;

-- STOPPER целостность оптимизация, фильтрация говнятины, точка входа, убрать тесты, фильтрация по регионам
DROP TABLE IF EXISTS address_objects;
CREATE TABLE address_objects (
    id             UUID PRIMARY KEY NOT NULL,
    address_id     UUID NOT NULL,
    parent_id      UUID DEFAULT NULL,
    title          VARCHAR,
    postal_code    INTEGER,
    prefix         VARCHAR
);

COMMENT ON TABLE address_objects IS 'Данные по адресным объектам(округам, улицам, городам)';
COMMENT ON COLUMN address_objects.id IS 'идентификационный код записи';
COMMENT ON COLUMN address_objects.guid IS 'идентификационный код адресного объекта';
COMMENT ON COLUMN address_objects.title IS 'Наименование объекта';
COMMENT ON COLUMN address_objects.postal_code IS 'Индекс';
COMMENT ON COLUMN address_objects.prefix IS 'ул.(улица) пр.(проспект) и так далее';

DROP TABLE IF EXISTS "pure_address_objects";
CREATE TABLE "pure_address_objects" (
    "AOGUID"     VARCHAR,
    "FORMALNAME" VARCHAR,
    "REGIONCODE" VARCHAR,
    "AUTOCODE"   VARCHAR,
    "AREACODE"   VARCHAR,
    "CITYCODE"   VARCHAR,
    "CTARCODE"   VARCHAR,
    "PLACECODE"  VARCHAR,
    "STREETCODE" VARCHAR,
    "EXTRCODE"   VARCHAR,
    "SEXTCODE"   VARCHAR,
    "OFFNAME"    VARCHAR,
    "POSTALCODE" VARCHAR,
    "IFNSFL"     VARCHAR,
    "TERRIFNSUL" VARCHAR,
    "OKATO"      VARCHAR,
    "OKTMO"      VARCHAR,
    "UPDATEDATE" VARCHAR,
    "SHORTNAME"  VARCHAR,
    "AOLEVEL"    VARCHAR,
    "PARENTGUID" VARCHAR,
    "AOID"       VARCHAR,
    "PREVID"     VARCHAR,
    "NEXTID"     VARCHAR,
    "CODE"       VARCHAR,
    "PLAINCODE"  VARCHAR,
    "ACTSTATUS"  VARCHAR,
    "CENTSTATUS" VARCHAR,
    "OPERSTATUS" VARCHAR,
    "CURRSTATUS" VARCHAR,
    "STARTDATE"  VARCHAR,
    "ENDDATE"    VARCHAR,
    "NORMDOC"    VARCHAR
);

COMMIT;
