-- user used for running migrations
GRANT SUPER ON *.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON portal.* TO 'admin'@'%';

-- portal
GRANT SELECT, INSERT, UPDATE, DELETE ON portal.* TO 'portal'@'%';

FLUSH PRIVILEGES;
