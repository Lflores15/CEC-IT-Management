CREATE DATABASE IF NOT EXISTS cec_it_management;
USE cec_it_management;

-- 01-create-tables.sql

-- drop any old versions
DROP TABLE IF EXISTS Laptops;
DROP TABLE IF EXISTS Devices;
DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS Users;

-- 1) Who can log in
CREATE TABLE Users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  login          VARCHAR(100) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('Technician','Manager') NOT NULL
);

-- 2) The actual employees in HR
CREATE TABLE Employees (
  emp_id        INT AUTO_INCREMENT PRIMARY KEY,
  emp_code      VARCHAR(50)  NOT NULL UNIQUE,   -- the 4-digit HR code (cannot be blank)
  username      VARCHAR(100) NOT NULL,
  first_name    VARCHAR(100) NOT NULL,
  last_name     VARCHAR(100) NOT NULL,
  phone_number  VARCHAR(50)
);

-- 3) All assets go here
CREATE TABLE Devices (
  device_id    INT AUTO_INCREMENT PRIMARY KEY,
  asset_tag    VARCHAR(100) NOT NULL UNIQUE,
  status       ENUM(
                  'active',
                  'lost',
                  'shelf-cc',
                  'shelf-md',
                  'shelf-hx',
                  'pending Return',
                  'decommissioned',
                  'open'
                ) NOT NULL,
  assigned_to  VARCHAR(50) NOT NULL,  
  FOREIGN KEY (assigned_to) REFERENCES Employees(emp_code)
);

-- 4) Laptop-specific details; 1:1 with Devices entries that are laptops
CREATE TABLE Laptops (
  device_id       INT PRIMARY KEY,
  internet_policy VARCHAR(100) NOT NULL,
  cpu             VARCHAR(100)   NOT NULL,
  ram             INT            NOT NULL,
  os              VARCHAR(50)    NOT NULL,
  FOREIGN KEY (device_id) REFERENCES Devices(device_id)
);
