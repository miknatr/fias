START TRANSACTION;

DROP INDEX "houses_full_number_id_fkey_idx";
DROP INDEX "tmp_houses_number_id_fkey_idx";
DROP INDEX "tmp_houses_building_id_fkey_idx";
DROP INDEX "tmp_houses_structure_fkey_idx";

CLUSTER;
VACUUM FULL;
ANALYZE;

COMMIT;
