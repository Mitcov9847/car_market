CREATE DATABASE car_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE car_market;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    brand VARCHAR(50),
    model VARCHAR(50),
    year YEAR,
    mileage INT,
    price DECIMAL(10,2),
    status ENUM('available','sold','deleted') DEFAULT 'available',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(seller_id) REFERENCES users(id)
);