-- Create paint colors reference table first
CREATE TABLE IF NOT EXISTS paint_colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    hex_code VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create product variants table
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    size VARCHAR(50) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    volume_liters DECIMAL(10,3) DEFAULT NULL,
    weight_kg DECIMAL(10,3) DEFAULT NULL,
    price_adjustment DECIMAL(10,2) DEFAULT 0.00,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id)
);



-- Insert common paint colors
INSERT INTO paint_colors (name, hex_code) VALUES
('Tulle White', '#F8F8F5'),
('Vanilla Ice', '#FBEBD7'),
('Castle Gray', '#D3D3D3'),
('Pure White', '#FFFFFF'),
('Antique White', '#FAEBD7'),
('Light Gray', '#D3D3D3'),
('Medium Gray', '#808080'),
('Dark Gray', '#404040'),
('Black', '#000000'),
('Red', '#FF0000'),
('Blue', '#0000FF'),
('Green', '#008000'),
('Yellow', '#FFFF00');

-- Add color_id to products table
ALTER TABLE products ADD COLUMN color_id INT NULL;