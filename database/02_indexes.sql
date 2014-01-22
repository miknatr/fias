START TRANSACTION;

CREATE UNIQUE INDEX "address_objects_address_id_uq_idx"
    ON "address_objects"
    USING BTREE ("address_id")
;

CREATE INDEX "address_objects_parent_id_fkey_idx"
    ON "address_objects"
    USING BTREE ("parent_id")
;

CREATE INDEX "address_objects_full_title_lower_idx"
    ON "address_objects"
    USING BTREE (lower("full_title"))
;

CREATE INDEX "houses_parent_id_fkey_idx"
    ON "houses"
    USING BTREE ("address_id")
;

COMMIT;
