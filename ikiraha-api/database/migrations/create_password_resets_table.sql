-- Create password_resets table for forgot password functionality
-- Run this SQL in your MySQL database

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `used` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token` (`token`),
    KEY `user_id` (`user_id`),
    KEY `email` (`email`),
    KEY `expires_at` (`expires_at`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `password_resets_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for efficient cleanup of expired tokens
CREATE INDEX `idx_password_resets_cleanup` ON `password_resets` (`expires_at`, `used`);

-- Create index for rate limiting queries
CREATE INDEX `idx_password_resets_rate_limit` ON `password_resets` (`email`, `created_at`);

-- Insert sample data for testing (optional - remove in production)
-- INSERT INTO `password_resets` (`user_id`, `email`, `token`, `expires_at`, `created_at`, `used`) VALUES
-- (1, 'test@example.com', 'sample_hashed_token_for_testing', DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW(), 0);

-- Create a stored procedure to clean up expired tokens (optional)
DELIMITER //
CREATE PROCEDURE CleanupExpiredPasswordResets()
BEGIN
    DELETE FROM password_resets 
    WHERE expires_at < NOW() OR used = 1;
    
    SELECT ROW_COUNT() as deleted_count;
END //
DELIMITER ;

-- Create an event to automatically clean up expired tokens every hour (optional)
-- Uncomment the following lines if you want automatic cleanup
/*
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS cleanup_password_resets
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
  CALL CleanupExpiredPasswordResets();
*/
