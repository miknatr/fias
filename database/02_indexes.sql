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

CREATE INDEX "houses_address_id_fkey_idx"
    ON "houses"
    USING BTREE ("address_id")
;

CREATE INDEX "houses_full_number_idx"
    ON "houses"
    USING BTREE ("full_number")
;

CREATE INDEX "tmp_houses_number_id_fkey_idx"
    ON "houses"
    USING BTREE ("number")
;

CREATE INDEX "tmp_houses_building_id_fkey_idx"
    ON "houses"
    USING BTREE ("building")
;

CREATE INDEX "tmp_houses_structure_fkey_idx"
    ON "houses"
    USING BTREE ("structure")
;
-- Что бы если заглючит оптимизатор,он планы составил исходя из индексов все равно в момент массовой правки
ANALYZE;

COMMIT;
