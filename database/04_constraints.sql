ALTER TABLE address_objects
    ADD CONSTRAINT address_objects_parent_id_fkey
    FOREIGN KEY(parent_id) REFERENCES address_objects(address_id)
    ON UPDATE CASCADE ON DELETE CASCADE
    DEFERRABLE INITIALLY IMMEDIATE
;

ALTER TABLE address_objects
    ADD CONSTRAINT address_objects_address_level_fkey
    FOREIGN KEY(address_level) REFERENCES address_object_levels(id)
    ON UPDATE CASCADE ON DELETE CASCADE
    DEFERRABLE INITIALLY IMMEDIATE
;

ALTER TABLE houses
    ADD CONSTRAINT houses_parent_id_fkey
    FOREIGN KEY(address_id) REFERENCES address_objects(address_id)
    ON UPDATE CASCADE ON DELETE CASCADE
    DEFERRABLE INITIALLY IMMEDIATE
;

ALTER TABLE place_types
    ADD CONSTRAINT place_types_parent_id_fkey
    FOREIGN KEY(parent_id) REFERENCES place_types(id)
    ON UPDATE CASCADE ON DELETE CASCADE
;

ALTER TABLE places
    ADD CONSTRAINT places_type_id_fkey
    FOREIGN KEY (type_id) REFERENCES place_types(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
;
