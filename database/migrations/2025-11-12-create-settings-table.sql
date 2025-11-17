
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(191) PRIMARY KEY,
    `value` TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO settings (`key`, `value`) SELECT 'cod_delivery_fee', '50.00' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE `key`='cod_delivery_fee');

