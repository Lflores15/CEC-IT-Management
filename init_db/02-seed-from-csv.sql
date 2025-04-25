-- 2a) Seed Employees
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Employees
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(@status, @internet_policy, @asset_tag,
 @login_id, @first_name, @last_name,
 @emp_code, @phone_number,
 @cpu, @ram, @os, @d1, @d2)
SET
  login_id     = @login_id,
  first_name   = @first_name,
  last_name    = @last_name,
  emp_code     = NULLIF(@emp_code,''),   -- turn blanks into NULL
  phone_number = @phone_number;


-- 2b) Seed Devices
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Devices
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(@status, @internet_policy, @asset_tag,
 @login_id, @first_name, @last_name,
 @emp_code, @phone_number,
 @cpu, @ram, @os, @d1, @d2)
SET
  status      = @status,
  asset_tag   = @asset_tag,
  assigned_to = NULLIF(@emp_code,'');   -- blanks → NULL so FK is happy


-- 2c) Seed Laptops
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
  internet_policy = @internet_policy,
  cpu             = @cpu,
  ram             = @ram,
  os              = @os;
