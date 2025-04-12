-- Drop existing database (for full rebuild; use with caution in production)
DROP DATABASE IF EXISTS cec_it_management;
CREATE DATABASE cec_it_management;
USE cec_it_management;

-- Employees table:
CREATE TABLE Employees (
  emp_id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(10) UNIQUE,         -- four-digit code or similar identifier
  login_id VARCHAR(100),
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  phone_number VARCHAR(50),
  active TINYINT(1) DEFAULT 1,              -- 1 = active, 0 = inactive
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Devices table:
CREATE TABLE Devices (
  device_id INT AUTO_INCREMENT PRIMARY KEY,
  status VARCHAR(50),
  internet_policy VARCHAR(50),
  asset_tag VARCHAR(100) UNIQUE,
  os VARCHAR(10),                           -- Store as "10" or "11"
  category VARCHAR(50),                     -- e.g., 'laptop' or 'phone'
  assigned_to INT,                          -- Foreign key reference to Employees
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES Employees(emp_id)
);

-- Laptops table:
CREATE TABLE Laptops (
  laptop_id INT AUTO_INCREMENT PRIMARY KEY,
  device_id INT UNIQUE,                     -- one-to-one relationship with Devices
  cpu VARCHAR(50),
  ram VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (device_id) REFERENCES Devices(device_id)
);

-- AssetHistory table:
CREATE TABLE AssetHistory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  asset_id INT,                             -- Reference to Devices(device_id)
  event_date DATE NOT NULL,
  event_time TIME NOT NULL,
  event_type VARCHAR(50) NOT NULL,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (asset_id) REFERENCES Devices(device_id)
);
