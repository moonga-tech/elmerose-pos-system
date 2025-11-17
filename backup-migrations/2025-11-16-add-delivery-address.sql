-- Migration: Add delivery_address column to orders
-- Run this migration with appropriate DB tools or manually via mysql client

ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `delivery_address` TEXT NULL AFTER `delivery_fee`;
