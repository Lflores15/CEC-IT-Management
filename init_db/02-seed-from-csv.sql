-- 1) Import Employees (ignore duplicates on emp_code)
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Employees
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(
  @status_dummy,
  @internet_policy_dummy,
  @asset_tag_dummy,
  @login_id,
  @first_name,
  @last_name,
  @emp_code,
  @phone_number,
  @cpu_dummy,
  @ram_dummy,
  @os_dummy,
  @d1,
  @d2
)
SET
  login_id     = @login_id,
  first_name   = @first_name,
  last_name    = @last_name,
  emp_code     = @emp_code,
  phone_number = @phone_number;

-- 2a) populate Devices only
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Devices
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(
  @status,
  @internet_policy,  -- just read it here, but…
  @asset_tag,
  @login_id,         -- ignore these
  @first_name,
  @last_name,
  @emp_code,
  @phone_number,
  @cpu,
  @ram,
  @os,
  @d1,
  @d2
)
SET
  status      = @status,
  asset_tag   = @asset_tag,
  assigned_to = NULLIF(@emp_code,'');  -- blanks → NULL so FK doesn’t choke


-- 2b) then populate Laptops with the extra columns
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Laptops
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 LINES
(
  @status,
  @internet_policy,
  @asset_tag,
  @login_id,
  @first_name,
  @last_name,
  @emp_code,
  @phone_number,
  @cpu,
  @ram,
  @os,
  @d1,
  @d2
)
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
