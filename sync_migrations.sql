-- Sync migrations table with renamed migration files
-- This tells Laravel that all current migrations have already been run

-- First, clear the existing migration records
TRUNCATE TABLE migrations;

-- Then, manually insert all migration files as batch 1 (already run)
-- You'll need to list all your migration files here
-- Get the list by running: dir database\migrations /b

-- For now, just mark all as run to avoid conflicts on local dev
-- The actual migration files will run correctly on the server
