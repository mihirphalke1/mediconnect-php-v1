-- Add new columns to doctors table
ALTER TABLE doctors
ADD COLUMN IF NOT EXISTS clinic_address TEXT AFTER address,
ADD COLUMN IF NOT EXISTS consultation_fee DECIMAL(10, 2) AFTER experience,
ADD COLUMN IF NOT EXISTS consultation_duration INT DEFAULT 30 AFTER consultation_fee,
ADD COLUMN IF NOT EXISTS clinic_name VARCHAR(255) AFTER consultation_duration,
ADD COLUMN IF NOT EXISTS clinic_phone VARCHAR(20) AFTER clinic_name,
ADD COLUMN IF NOT EXISTS clinic_email VARCHAR(100) AFTER clinic_phone,
ADD COLUMN IF NOT EXISTS working_hours TEXT AFTER clinic_email,
ADD COLUMN IF NOT EXISTS languages_spoken VARCHAR(255) AFTER working_hours,
ADD COLUMN IF NOT EXISTS education TEXT AFTER languages_spoken,
ADD COLUMN IF NOT EXISTS certifications TEXT AFTER education,
ADD COLUMN IF NOT EXISTS awards TEXT AFTER certifications,
ADD COLUMN IF NOT EXISTS bio TEXT AFTER awards,
ADD COLUMN IF NOT EXISTS is_available BOOLEAN DEFAULT TRUE AFTER bio,
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) AFTER is_available,
ADD COLUMN IF NOT EXISTS qualifications TEXT AFTER profile_image; 