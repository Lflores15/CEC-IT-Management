-- 2a) Employees: turn empty codes into NULL
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
INTO TABLE Employees
FIELDS TERMINATED BY ',' ENCLOSED BY '"' 
IGNORE 1 LINES
(@status, @internet_policy, @asset_tag,
 @login_id, @first_name, @last_name,
 @raw_emp_code, @phone_number,
 @cpu, @ram, @os)
SET
  emp_code      = NULLIF(@raw_emp_code, ''),   -- blank → NULL
  login_id      = @login_id,
  first_name    = @first_name,
  last_name     = @last_name,
  phone_number  = @phone_number;

USE cec_it_management;
-- 2a) Seed Devices *and* assign them:
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
  IGNORE
  INTO TABLE Devices
  FIELDS TERMINATED BY ',' ENCLOSED BY '"'
  IGNORE 1 LINES
  (
    @status,
    @internet_policy,
    @asset_tag,
    @login_id,
    @first_name,
    @last_name,
    @emp_code,         -- ← the HR code column
    @phone_number,
    @cpu,
    @ram,
    @os,
    @junk1,
    @junk2
  )
SET
  status          = @status,
  internet_policy = @internet_policy,
  asset_tag       = @asset_tag,
  assigned_to     = NULLIF(@emp_code, '');

-- 2b) Seed Laptops (1:1 child of Devices)
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Laptops
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
  (@status, @internet_policy, @asset_tag,
   @login_id, @first_name, @last_name,
   @emp_code, @phone_number,
   @cpu, @ram, @os, @d1, @d2)
SET
  device_id       = (
    SELECT device_id
      FROM Devices
     WHERE asset_tag = @asset_tag
  ),
  internet_policy = NULLIF(@internet_policy, ''),
  cpu             = @cpu,
  ram             = @ram,
  os              = @os;
