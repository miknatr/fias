START TRANSACTION;

DROP TABLE IF EXISTS "correctTable";
CREATE TABLE "correctTable" (id SERIAL, title VARCHAR);

INSERT INTO "correctTable" (id, title)
    VALUES (1, 'test1'), (2, 'test2');

COMMIT;
