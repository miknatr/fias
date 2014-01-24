START TRANSACTION;

DROP TABLE IF EXISTS "correctTable";
CREATE TABLE "correctTable"(id SERIAL, title varchar);

INSERT INTO "correctTable"(id, title)
    VALUES (1, 'test1'), (2, 'test2');

COMMIT;
