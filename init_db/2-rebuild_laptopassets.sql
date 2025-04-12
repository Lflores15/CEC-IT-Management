USE cec_it_management;

DROP TABLE IF EXISTS LaptopAssets;

CREATE TABLE LaptopAssets (
   id INT AUTO_INCREMENT PRIMARY KEY,
   status VARCHAR(50),
   internet_policy VARCHAR(50),
   asset_tag VARCHAR(100) UNIQUE,
   login_id VARCHAR(100),
   first_name VARCHAR(100),
   last_name VARCHAR(100),
   employee_id VARCHAR(10),       -- new column for the four-digit employee id
   phone_number VARCHAR(50),
   cpu VARCHAR(50),
   ram VARCHAR(50),
   os VARCHAR(10)
);
