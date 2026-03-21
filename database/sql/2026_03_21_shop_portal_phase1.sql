CREATE TABLE IF NOT EXISTS `shops` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `contact_name` VARCHAR(255) NULL,
    `contact_phone` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `expires_on` DATE NULL,
    `owner_user_id` BIGINT(20) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `shops_code_unique` (`code`),
    KEY `shops_owner_user_id_index` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `shops` (`code`, `name`, `contact_name`, `contact_phone`, `notes`, `is_active`, `expires_on`, `owner_user_id`)
SELECT 'main-shop', 'ร้านหลัก', '', '', 'สร้างอัตโนมัติจาก phase 1', 1, NULL, NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `shops`
);

ALTER TABLE `shops`
    ADD COLUMN IF NOT EXISTS `owner_user_id` BIGINT(20) NULL AFTER `expires_on`;

ALTER TABLE `shops`
    ADD INDEX `shops_owner_user_id_index` (`owner_user_id`);

ALTER TABLE `branches`
    ADD COLUMN IF NOT EXISTS `shop_id` INT UNSIGNED NULL AFTER `id`;

UPDATE `branches`
SET `shop_id` = (
    SELECT `id`
    FROM `shops`
    ORDER BY `id`
    LIMIT 1
)
WHERE `shop_id` IS NULL;

ALTER TABLE `branches`
    MODIFY COLUMN `shop_id` INT UNSIGNED NOT NULL;

ALTER TABLE `branches`
    ADD INDEX `branches_shop_id_index` (`shop_id`);

ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('super_admin', 'shop_owner', 'branch_manager', 'cashier', 'masseuse')
    NOT NULL DEFAULT 'cashier';

ALTER TABLE `users`
    MODIFY COLUMN `staff_id` BIGINT(20) NULL;
