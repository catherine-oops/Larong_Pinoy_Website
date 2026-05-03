-- ============================================================
-- Larong Pinoy — schema cleanup (existing database)
-- Run in phpMyAdmin or: mysql -u root -p larong_pinoy_db < migration_2026_schema_cleanup.sql
--
-- Removes: points (User_Points_Log, total_points), achievements (User_Achievement),
--          comment moderation enums (comments are always "approved").
-- Recreates: vw_User_Profile, vw_User_Dashboard, sp_UpdateUserStats, comment triggers.
-- ============================================================

USE larong_pinoy_db;

-- Dependent objects first
DROP VIEW IF EXISTS vw_User_Dashboard;
DROP VIEW IF EXISTS vw_User_Profile;

DROP TRIGGER IF EXISTS trg_AfterPlayLogInsert;
DROP TRIGGER IF EXISTS trg_AfterCommentUpdate;
DROP TRIGGER IF EXISTS trg_AfterCommentInsert;
DROP TRIGGER IF EXISTS trg_AfterCommentDelete;

DROP PROCEDURE IF EXISTS sp_UpdateUserStats;

DROP TABLE IF EXISTS User_Points_Log;
DROP TABLE IF EXISTS User_Achievement;

-- Drop points column (ignore error if already removed)
SET @dbname = DATABASE();
SET @tablename = 'User_Account';
SET @columnname = 'total_points';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'ALTER TABLE User_Account DROP COLUMN total_points',
  'SELECT 1'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Normalize comments before narrowing ENUM
UPDATE Comment SET comment_status = 'approved' WHERE comment_status IN ('pending', 'rejected');

ALTER TABLE Comment
  MODIFY COLUMN comment_status ENUM('approved') NOT NULL DEFAULT 'approved';

DELIMITER //

CREATE PROCEDURE sp_UpdateUserStats(IN user_id_param INT)
BEGIN
    UPDATE User_Account u
    SET
        games_played_count = (SELECT COUNT(DISTINCT game_id) FROM Play_Log WHERE user_id = user_id_param),
        comments_count = (SELECT COUNT(*) FROM Comment WHERE user_id = user_id_param)
    WHERE u.user_id = user_id_param;
END //

CREATE TRIGGER trg_AfterPlayLogInsert
AFTER INSERT ON Play_Log
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(NEW.user_id);
END //

CREATE TRIGGER trg_AfterCommentInsert
AFTER INSERT ON Comment
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(NEW.user_id);
END //

CREATE TRIGGER trg_AfterCommentDelete
AFTER DELETE ON Comment
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(OLD.user_id);
END //

DELIMITER ;

CREATE VIEW vw_User_Profile AS
SELECT
    u.user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.bio,
    u.profile_picture,
    u.location,
    u.birthdate,
    u.games_played_count,
    u.comments_count,
    u.role,
    u.created_at,
    u.last_login,
    (SELECT COUNT(*) FROM User_Favorite WHERE user_id = u.user_id) AS favorites_count,
    (SELECT COUNT(*) FROM Play_Log WHERE user_id = u.user_id) AS total_sessions
FROM User_Account u;

CREATE VIEW vw_User_Dashboard AS
SELECT
    u.user_id,
    u.username,
    u.profile_picture,
    COUNT(DISTINCT pl.game_id) AS unique_games_played,
    COUNT(DISTINCT uf.game_id) AS favorite_games_count,
    COUNT(DISTINCT c.comment_id) AS total_comments,
    MAX(pl.played_date) AS last_played_date
FROM User_Account u
LEFT JOIN Play_Log pl ON u.user_id = pl.user_id
LEFT JOIN User_Favorite uf ON u.user_id = uf.user_id
LEFT JOIN Comment c ON u.user_id = c.user_id
GROUP BY u.user_id, u.username, u.profile_picture;

-- Refresh aggregates for all users
UPDATE User_Account u
SET
    games_played_count = (SELECT COUNT(DISTINCT pl.game_id) FROM Play_Log pl WHERE pl.user_id = u.user_id),
    comments_count = (SELECT COUNT(*) FROM Comment c WHERE c.user_id = u.user_id);

SELECT 'Migration complete: points and achievements removed; comments simplified.' AS status;
