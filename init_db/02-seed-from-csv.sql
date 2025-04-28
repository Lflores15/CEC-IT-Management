-- 02-seed-from-csv.sql
-- 0) Dummy “Unassigned” employee
INSERT INTO Employees (emp_code, username, first_name, last_name, phone_number)
VALUES ('0000','system','Unassigned','Unassigned','')
ON DUPLICATE KEY UPDATE emp_code = emp_code;
-- 1) Load Employees
LOAD DATA INFILE '/docker-entrypoint-initdb.d/SparkList_Inventory.csv'
IGNORE
INTO TABLE Employees
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(@status,@internet_policy,@asset_tag,
 @username,@first_name,@last_name,@emp_code,@phone_number,
 @cpu,@ram,@os)
SET
  emp_code     = @emp_code,
  username     = @username,
  first_name   = @first_name,
  last_name    = @last_name,
  phone_number = @phone_number;
-- 2) Load Devices
LOAD DATA INFILE '/docker-entrypoint-initdb.d/SparkList_Inventory.csv'
IGNORE
INTO TABLE Devices
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(@status,@internet_policy,@asset_tag,
 @username,@first_name,@last_name,@emp_code,@phone_number,
 @cpu,@ram,@os)
SET
  status      = @status,
  asset_tag   = @asset_tag,
  assigned_to = COALESCE(NULLIF(@emp_code, ''), '0000');
-- 3) Load Laptops
LOAD DATA INFILE '/docker-entrypoint-initdb.d/SparkList_Inventory.csv'
IGNORE
INTO TABLE Laptops
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(@status,@internet_policy,@asset_tag,
 @username,@first_name,@last_name,@emp_code,@phone_number,
 @cpu,@ram,@os)
SET
  device_id       = (
    SELECT d.device_id
      FROM Devices AS d
     WHERE d.asset_tag = @asset_tag
     LIMIT 1
  ),
  internet_policy = @internet_policy,
  cpu             = @cpu,
  ram             = @ram,
  os              = @os;