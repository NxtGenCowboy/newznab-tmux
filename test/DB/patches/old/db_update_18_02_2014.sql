ALTER TABLE `releases`
    DROP INDEX ix_releases_status,
    ADD COLUMN `nzbstatus` BOOL NOT NULL DEFAULT 0,
    ADD COLUMN `iscategorized` BOOL NOT NULL DEFAULT 0,
    ADD COLUMN `isrenamed` BOOL NOT NULL DEFAULT 0,
    ADD COLUMN `ishashed` BOOL NOT NULL DEFAULT 0,
    ADD COLUMN `isrequestid` BOOL NOT NULL DEFAULT 0,
    ADD INDEX ix_releases_status (nzbstatus, iscategorized, isrenamed, nfostatus, ishashed, isrequestid, passwordstatus, dehashstatus, reqidstatus, musicinfoID, consoleinfoID, bookinfoID, haspreview, categoryID, imdbID, rageID);
UPDATE releases SET nzbstatus = 1 WHERE (bitwise & 256) = 256;
UPDATE releases SET iscategorized = 1 WHERE (bitwise & 1) = 1;
UPDATE releases SET isrenamed = 1 WHERE (bitwise & 4) = 4;
UPDATE releases SET ishashed = 1 WHERE (bitwise & 512) = 512;
UPDATE releases SET isrequestid = 1 WHERE (bitwise & 1024) = 1024;

DROP TRIGGER IF EXISTS check_insert;
DROP TRIGGER IF EXISTS check_update;

DELIMITER $$
CREATE TRIGGER check_insert BEFORE INSERT ON releases FOR EACH ROW BEGIN IF NEW.searchname REGEXP '[a-fA-F0-9]{32}' OR NEW.name REGEXP '[a-fA-F0-9]{32}' THEN SET NEW.ishashed = 1;ELSEIF NEW.name REGEXP '^\\[[[:digit:]]+\\]' THEN SET NEW.isrequestid = 1; END IF; END;$$
CREATE TRIGGER check_update BEFORE UPDATE ON releases FOR EACH ROW BEGIN IF NEW.searchname REGEXP '[a-fA-F0-9]{32}' OR NEW.name REGEXP '[a-fA-F0-9]{32}' THEN SET NEW.ishashed = 1;ELSEIF NEW.name REGEXP '^\\[[[:digit:]]+\\]' THEN SET NEW.isrequestid = 1; END IF; END;$$
DELIMITER ;
