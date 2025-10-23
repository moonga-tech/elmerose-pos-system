-- Add delivery fields to orders table
ALTER TABLE orders 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'pickup',
ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0.00;