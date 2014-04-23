DROP INDEX tmp_houses_number_id_fkey_idx;
DROP INDEX tmp_houses_building_id_fkey_idx;
DROP INDEX tmp_houses_structure_fkey_idx;

CLUSTER address_objects USING  address_objects_level_full_title_lower_idx;
CLUSTER houses USING houses_full_number_idx;
ANALYZE;
