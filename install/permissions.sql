GRANT SELECT, INSERT, UPDATE ON "case" TO private_api;
GRANT SELECT, INSERT, UPDATE, DELETE ON pairing TO private_api;
GRANT USAGE ON SEQUENCE pairing_id_seq TO private_api;

GRANT SELECT ON "case" TO public_api;
GRANT SELECT, UPDATE, DELETE ON pairing TO public_api;

-- TODO: remove
GRANT INSERT, DELETE ON "case" TO public_api;
GRANT INSERT, DELETE ON pairing TO public_api;
GRANT USAGE ON SEQUENCE pairing_id_seq TO public_api;
