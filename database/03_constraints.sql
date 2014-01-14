START TRANSACTION;

ALTER TABLE address_objects
    ADD CONSTRAINT "address_objects_parent_id_fkey"
    FOREIGN KEY("parent_id") REFERENCES address_objects("address_id")
    ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE
;

ALTER TABLE houses
ADD CONSTRAINT "houses_parent_id_fkey"
FOREIGN KEY("parent_id") REFERENCES address_objects("address_id")
ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE
;

COMMIT;
