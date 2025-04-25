DROP TABLE IF EXISTS Laptops;
DROP TABLE IF EXISTS Devices;
DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
  user_id        INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(100)   NOT NULL UNIQUE,
  password_hash  VARCHAR(255)   NOT NULL,
  role           ENUM('Technician','Manager') NOT NULL
);

CREATE TABLE Employees (
  emp_id        INT AUTO_INCREMENT PRIMARY KEY,
  emp_code      VARCHAR(50)    NOT NULL UNIQUE,  -- 4-digit HR code
  login_id      VARCHAR(100)   NOT NULL,         -- to join on assets
  first_name    VARCHAR(100)   NOT NULL,
  last_name     VARCHAR(100)   NOT NULL,
  phone_number  VARCHAR(50)
);

CREATE TABLE Devices (
  device_id    INT AUTO_INCREMENT PRIMARY KEY,
  asset_tag    VARCHAR(100) NOT NULL UNIQUE,
  status       ENUM(
                  'Active',
                  'Lost',
                  'Shelf',
                  'Pending Return',
                  'Decommissioned',
                  'Open'
                ) NOT NULL,
  assigned_to  VARCHAR(50),                                   -- emp_code
  FOREIGN KEY (assigned_to) REFERENCES Employees(emp_code)
);

CREATE TABLE Laptops (
  device_id       INT PRIMARY KEY,     -- 1:1 with Devices
  internet_policy VARCHAR(100),
  cpu             VARCHAR(100) NOT NULL,
  ram             INT          NOT NULL,
  os              VARCHAR(50)  NOT NULL,
  FOREIGN KEY (device_id) REFERENCES Devices(device_id)
);
