-- Add profile fields to users table
-- Migration: add_profile_fields_to_users
-- Date: 2025-09-06

-- Add new profile fields to users table
ALTER TABLE users 
ADD COLUMN address TEXT NULL AFTER profile_image,
ADD COLUMN date_of_birth DATE NULL AFTER address,
ADD COLUMN gender ENUM('male', 'female', 'other') NULL AFTER date_of_birth,
ADD COLUMN bio TEXT NULL AFTER gender;

-- Add indexes for better performance
CREATE INDEX idx_users_gender ON users(gender);
CREATE INDEX idx_users_date_of_birth ON users(date_of_birth);

-- Update existing users with default values if needed
-- (Optional - can be removed if not needed)
-- UPDATE users SET gender = NULL WHERE gender = '';
-- UPDATE users SET date_of_birth = NULL WHERE date_of_birth = '0000-00-00';
