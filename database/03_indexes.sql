CREATE UNIQUE INDEX address_objects_address_id_uq_idx
    ON address_objects
    USING BTREE (address_id)
;

CREATE INDEX address_objects_parent_id_fkey_idx
    ON address_objects
    USING BTREE (parent_id)
;

CREATE INDEX address_objects_level_full_title_lower_idx
    ON address_objects
    USING BTREE (level, lower(full_title))
;

CREATE INDEX address_objects_title_lower_idx
    ON address_objects
    USING BTREE (lower(title))
;

CREATE INDEX houses_address_id_fkey_idx
    ON houses
    USING BTREE (address_id)
;

CREATE INDEX houses_full_number_idx
    ON houses
    USING BTREE (full_number)
;

CREATE INDEX tmp_houses_number_id_fkey_idx
    ON houses
    USING BTREE (number)
;

CREATE INDEX tmp_houses_building_id_fkey_idx
    ON houses
    USING BTREE (building)
;

CREATE INDEX tmp_houses_structure_fkey_idx
    ON houses
    USING BTREE (structure)
;

CREATE UNIQUE INDEX update_log_version_id_idx
    ON update_log
    USING BTREE (version_id)
;

CREATE INDEX place_types_parent_id_fkey_idx
    ON place_types
    USING BTREE (parent_id)
;

CREATE INDEX places_type_id_fkey_idx
    ON places
    USING BTREE (type_id)
;

CREATE UNIQUE INDEX places_title_type_id_parent_id_uq_idx
    ON places
    USING BTREE (title, type_id, parent_id)
;

CREATE INDEX places_title_idx
    ON places
    USING BTREE (title)
;

-- Что бы если заглючит оптимизатор,он планы составил исходя из индексов все равно в момент массовой правки
ANALYZE;
