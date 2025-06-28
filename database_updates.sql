-- Database updates to fix orphanages table and ensure all functionality works

-- Fix orphanages table to have AUTO_INCREMENT id
ALTER TABLE `orphanages` MODIFY `id` int NOT NULL AUTO_INCREMENT;

-- Ensure proper indexes exist
ALTER TABLE `orphanages` ADD INDEX `idx_orphanages_status` (`status`);
ALTER TABLE `orphanages` ADD INDEX `idx_orphanages_created` (`created_at`);

-- Update any existing orphanages to have proper IDs if needed
-- This will set the AUTO_INCREMENT value to start after the highest existing ID
SET @max_id = (SELECT COALESCE(MAX(id), 0) FROM orphanages);
SET @sql = CONCAT('ALTER TABLE orphanages AUTO_INCREMENT = ', @max_id + 1);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure campaigns table has proper foreign key constraints
-- (This should already exist but let's make sure)
ALTER TABLE `campaigns` 
ADD CONSTRAINT `fk_campaigns_orphanage` 
FOREIGN KEY (`orphanage_id`) REFERENCES `orphanages` (`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Ensure donations table has proper foreign key constraints for campaigns
-- (This should already exist but let's make sure)
ALTER TABLE `donations` 
ADD CONSTRAINT `fk_donations_campaign` 
FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Add any missing indexes for better performance
ALTER TABLE `donations` ADD INDEX `idx_donations_orphanage_status` (`orphanage_id`, `payment_status`);
ALTER TABLE `campaigns` ADD INDEX `idx_campaigns_status_priority` (`status`, `priority`);
