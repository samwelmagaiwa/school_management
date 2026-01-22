-- Fix for renamed migration files on local development environment
-- Run this in phpMyAdmin for database: school_2_sms

-- Insert the renamed migrations as already run (batch 1)
INSERT INTO migrations (migration, batch) VALUES
('2025_01_01_000001_create_user_types_table', 1),
('2025_01_01_000002_create_blood_groups_table', 1),
('2025_01_01_000003_create_settings_table', 1),
('2025_01_01_000004_create_nationalities_table', 1)
ON DUPLICATE KEY UPDATE batch=batch;

-- Note: Only run this on your LOCAL database
-- These migrations were renamed from 2026 dates to 2025 dates to fix execution order
-- The server will run them correctly during deployment
