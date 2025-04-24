-- Bulk‑load Employees & Devices from your import.csv in one shot
LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Employees
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(@status,       @internet_policy, @asset_tag, @login_id,
 @first_name,   @last_name,       @employee_code, @phone_number,
 @cpu,          @ram,             @os,            @dummy1, @dummy2)
SET
  employee_id    = @employee_code,
  first_name     = @first_name,
  last_name      = @last_name,
  login_id       = @login_id,
  phone_number   = @phone_number;

LOAD DATA INFILE '/docker-entrypoint-initdb.d/import.csv'
IGNORE
INTO TABLE Devices
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(@status,       @internet_policy, @asset_tag, @login_id,
 @first_name,   @last_name,       @employee_code, @phone_number,
 @cpu,          @ram,             @os,            @dummy1, @dummy2)
SET
  asset_tag        = @asset_tag,
  status           = @status,
  internet_policy  = @internet_policy,
  cpu              = @cpu,
  ram              = @ram,
  os               = @os,
  assigned_to      = (
    SELECT emp_id FROM Employees WHERE login_id = @login_id
  );

-- Now every device we just inserted is a laptop; link them.
INSERT INTO Laptops (device_id)
SELECT device_id FROM Devices;
