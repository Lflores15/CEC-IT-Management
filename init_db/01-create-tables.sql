-- DROP any existing, so a fresh volume really starts clean
DROP TABLE IF EXISTS Laptops;
DROP TABLE IF EXISTS Devices;
DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS Users;

-- 1) Who can log in
CREATE TABLE Users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(100) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('Technician','Manager') NOT NULL
);

-- 2) The actual employees in HR
CREATE TABLE Employees (
  emp_id        INT AUTO_INCREMENT PRIMARY KEY,
  emp_code      VARCHAR(50)  NOT NULL UNIQUE,   -- the 4-digit HR code
  login_id      VARCHAR(100) NOT NULL,           -- how we link them to assets
  first_name    VARCHAR(100) NOT NULL,
  last_name     VARCHAR(100) NOT NULL,
  phone_number  VARCHAR(50)
);

-- 3) All assets go here
CREATE TABLE Devices (
  device_id    INT AUTO_INCREMENT PRIMARY KEY,
  asset_tag    VARCHAR(100)   NOT NULL UNIQUE,
  status       ENUM(
     'Active'
    ,'Lost'
    ,'Shelf'
    ,'Pending Return'
    ,'Decommissioned'
    ,'Open'                -- ← now supports your “Open” rows
  ) NOT NULL,
  assigned_to  VARCHAR(50),  -- emp_code of whoever it’s assigned to
  FOREIGN KEY (assigned_to) REFERENCES Employees(emp_code)
);

-- 4) Laptop-specific details; 1:1 with Devices entries that are laptops
CREATE TABLE Laptops (
  device_id       INT PRIMARY KEY,              -- same PK as in Devices
  internet_policy VARCHAR(100),
  cpu             VARCHAR(100) NOT NULL,
  ram             INT          NOT NULL,
  os              VARCHAR(50)  NOT NULL,
  FOREIGN KEY (device_id) REFERENCES Devices(device_id)
);
