-- 1) USERS: just technicians who can log in
CREATE TABLE IF NOT EXISTS Users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(100) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('Technician','Manager') NOT NULL DEFAULT 'Technician'
);

-- 2) EMPLOYEES: the “real” employees each of whom may have one or more devices
CREATE TABLE IF NOT EXISTS Employees (
  emp_id         INT AUTO_INCREMENT PRIMARY KEY,
  employee_id    VARCHAR(50)    NOT NULL UNIQUE,  -- the 4‑digit HR code
  first_name     VARCHAR(100)   NOT NULL,
  last_name      VARCHAR(100)   NOT NULL,
  login_id       VARCHAR(50)    NOT NULL UNIQUE,  -- what you use to look them up from CSV
  phone_number   VARCHAR(20)
);

-- 3) DEVICES: generic device table (for future, can expand beyond laptops)
CREATE TABLE IF NOT EXISTS Devices (
  device_id        INT AUTO_INCREMENT PRIMARY KEY,
  asset_tag        VARCHAR(100)   NOT NULL UNIQUE,
  status           ENUM('Active','Lost','Shelf','Pending Return','Decommissioned') NOT NULL,
  cpu              VARCHAR(100),
  ram              INT,
  os               VARCHAR(50),
  internet_policy  VARCHAR(100),
  assigned_to      INT,
  FOREIGN KEY (assigned_to) REFERENCES Employees(emp_id)
);

-- 4) LAPTOPS: a 1:1 “sub‑table” of Devices for laptop‑specific query speed
CREATE TABLE IF NOT EXISTS Laptops (
  laptop_id   INT AUTO_INCREMENT PRIMARY KEY,
  device_id   INT        NOT NULL UNIQUE,
  FOREIGN KEY (device_id) REFERENCES Devices(device_id)
);
