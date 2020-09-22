CREATE TABLE dbco_case
(
    id SERIAL NOT NULL,
    case_id VARCHAR(10) NOT NULL,
    pairing_code VARCHAR(10),
    pairing_code_expires_at timestamp NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (pairing_code)
);