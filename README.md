# Elmerose POS System

A Point of Sale system built with PHP and MySQL.

## Docker Deployment

### Prerequisites
- Docker
- Docker Compose

### Quick Start

1. Clone the repository:
```bash
git clone <repository-url>
cd elmerose-pos
```

2. Build and run with Docker Compose:
```bash
docker-compose up -d
```

3. Access the application:
- Web Application: http://localhost:8080
- Admin Panel: http://localhost:8080/admin
- Customer Portal: http://localhost:8080/customer

### Default Credentials

**Admin Login:**
- Email: admin@example.com
- Password: admin123

**Customer Registration:**
- Register at: http://localhost:8080/customer-register.php

### Features

- Product Management
- Inventory Tracking
- Order Management
- Customer Management
- Cart Functionality
- Payment Processing (COD)
- Delivery Fee Management
- Product Variants
- Expiry Date Tracking

### Development

To stop the containers:
```bash
docker-compose down
```

To rebuild after code changes:
```bash
docker-compose up --build
```