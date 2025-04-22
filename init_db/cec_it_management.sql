CREATE DATABASE IF NOT EXISTS cec_it_management;
USE cec_it_management;

--  Create Users table (Used for website authentication)
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM ('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--  Create Employees table (For device assignments)
CREATE TABLE IF NOT EXISTS Employees (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    login_id VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone_number VARCHAR(20)
);

--  Create Devices table (For IT asset tracking)
CREATE TABLE IF NOT EXISTS Devices (
    device_id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(255) UNIQUE NOT NULL,
    asset_tag VARCHAR(100) UNIQUE NOT NULL,
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    category ENUM ('laptop', 'desktop', 'iPhone', 'tablet') NOT NULL,
    brand VARCHAR(255),
    model VARCHAR(255),
    os VARCHAR(255),
    status ENUM ('Active', 'Pending Return', 'Shelf', 'Lost') DEFAULT 'Shelf',
    assigned_to INT NULL,  
    location VARCHAR(255),
    purchase_date DATE,
    warranty_expiry DATE,
    notes TEXT,
    FOREIGN KEY (assigned_to) REFERENCES Employees(emp_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Laptops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT UNIQUE, -- Links to Devices table
    cpu VARCHAR(255),
    ram INT,
    storage INT,
    backup_type VARCHAR(255) NOT NULL,
    internet_policy ENUM('admin', 'default', 'office'),
    backup_removed BOOLEAN DEFAULT FALSE,
    sinton_backup BOOLEAN DEFAULT FALSE,
    midland_backup BOOLEAN DEFAULT FALSE,
    c2_backup BOOLEAN DEFAULT FALSE,
    actions_needed TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES Devices(device_id) ON DELETE CASCADE
);

--  Create Decommissioned Laptops table (Tracks decommissioned laptops)
CREATE TABLE IF NOT EXISTS Decommissioned_Laptops (
    decommission_id INT AUTO_INCREMENT PRIMARY KEY,
    laptop_id INT UNIQUE, 
    broken BOOLEAN DEFAULT FALSE,
    duplicate BOOLEAN DEFAULT FALSE,
    decommission_status VARCHAR(255) DEFAULT 'Decommissioned',
    additional_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laptop_id) REFERENCES Laptops(id) ON DELETE CASCADE
);

-- Create iPhones table (For tracking iPhone-specific details)
CREATE TABLE IF NOT EXISTS iPhones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT UNIQUE, -- Links to Devices table
    responsible_party VARCHAR(255) NOT NULL,
    carrier VARCHAR(100),
    phone_number VARCHAR(20) UNIQUE,
    previous_owner VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES Devices(device_id) ON DELETE CASCADE
);

-- Create Tablets table (For tracking tablet-specific details)
CREATE TABLE IF NOT EXISTS Tablets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT UNIQUE, -- Links to Devices table
    responsible_party VARCHAR(255) NOT NULL,
    type ENUM('Personal', 'Work', 'Loaner'),
    carrier VARCHAR(100),
    phone_number VARCHAR(20) UNIQUE,
    imei VARCHAR(50) UNIQUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES Devices(device_id) ON DELETE CASCADE
);

--  Create Assignments table (Tracks which employee has which device)
CREATE TABLE IF NOT EXISTS Assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    emp_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returned_at TIMESTAMP NULL,
    status ENUM ('Active', 'Returned', 'Lost') DEFAULT 'Active',
    FOREIGN KEY (device_id) REFERENCES Devices(device_id) ON DELETE CASCADE,
    FOREIGN KEY (emp_id) REFERENCES Employees(emp_id) ON DELETE CASCADE
);