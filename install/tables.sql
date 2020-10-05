CREATE TABLE "case"
(
    id VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE pairing
(
    id SERIAL NOT NULL,
    case_id VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    expires_at TIMESTAMP,
    is_paired INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT fk_pairing_c FOREIGN KEY (case_id) REFERENCES "case" (id) ON DELETE CASCADE
);
