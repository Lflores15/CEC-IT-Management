-- ==============================================
-- 1. Insert Sample Employees (For device assignments)
-- ==============================================
INSERT INTO Employees (employee_id, first_name, last_name, login_id, email, phone_number) VALUES
    ('EMP1001', 'John', 'Doe', 'jdoe', 'john.doe@example.com', '555-1234'),
    ('EMP1002', 'Jane', 'Smith', 'jsmith', 'jane.smith@example.com', '555-5678'),
    ('EMP1003', 'Alice', 'Johnson', 'ajohnson', 'alice.johnson@example.com', '555-7890');

-- ==============================================
-- 2. Insert Sample Devices (IT asset tracking)
-- ==============================================
INSERT INTO Devices (device_name, asset_tag, serial_number, category, brand, model, os, cpu, ram, storage, status, assigned_to, location, purchase_date, warranty_expiry, notes) VALUES
    -- Active Laptops
    ('Dell XPS 15', 'CEC-LT001', 'SN123456', 'laptop', 'Dell', 'XPS 15', 'Windows 10', 'Intel i7-9750H', 16, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), 'Head Office', '2022-01-15', '2025-01-15', 'Used by IT department'),
    ('HP EliteBook', 'CEC-LT002', 'SN654321', 'laptop', 'HP', 'EliteBook 850', 'Windows 11', 'Intel i5-1135G7', 8, 256, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), 'Branch Office', '2023-03-10', '2026-03-10', 'Issued to HR department'),
    ('Lenovo ThinkPad X1', 'CEC-LT003', 'SN789012', 'laptop', 'Lenovo', 'ThinkPad X1 Carbon', 'Windows 11', 'Intel i7-1185G7', 16, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1003'), 'Sales Department', '2023-06-20', '2026-06-20', 'Used for fieldwork'),
    ('Dell Latitude 5420', 'CEC-LT004', 'SN432198', 'laptop', 'Dell', 'Latitude 5420', 'Windows 11', 'Intel i5-1145G7', 16, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1004'), 'Operations', '2023-07-01', '2026-07-01', 'For onsite support'),

    -- Pending Return Laptops
    ('HP ProBook 450', 'CEC-LT005', 'SN223344', 'laptop', 'HP', 'ProBook 450', 'Windows 10', 'Intel i5-10210U', 8, 256, 'Pending Return', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1005'), 'Finance', '2022-09-12', '2025-09-12', 'To be returned to IT inventory'),
    ('Lenovo ThinkPad T14', 'CEC-LT006', 'SN334455', 'laptop', 'Lenovo', 'ThinkPad T14', 'Windows 11', 'Intel i7-1165G7', 16, 512, 'Pending Return', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1006'), 'Marketing', '2023-08-01', '2026-08-01', 'Awaiting return from employee'),

    -- Shelf Laptops (Unassigned)
    ('Acer Aspire 5', 'CEC-LT007', 'SN556677', 'laptop', 'Acer', 'Aspire 5', 'Windows 10', 'Intel i5-1035G1', 8, 512, 'Shelf', 
        NULL, 'Storage Room', '2022-10-01', '2025-10-01', 'Backup inventory for temporary use'),
    ('Dell Vostro 3500', 'CEC-LT008', 'SN889900', 'laptop', 'Dell', 'Vostro 3500', 'Windows 11', 'Intel i5-1135G7', 8, 512, 'Shelf', 
        NULL, 'IT Storage', '2023-04-15', '2026-04-15', 'Reserved for new employees'),

    -- Lost Laptops
    ('HP ZBook Firefly', 'CEC-LT009', 'SN991122', 'laptop', 'HP', 'ZBook Firefly', 'Windows 11', 'Intel i7-11850H', 32, 1024, 'Lost', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1007'), 'Unknown', '2023-05-05', '2026-05-05', 'Reported lost by employee'),
    ('Lenovo Legion 5', 'CEC-LT010', 'SN112233', 'laptop', 'Lenovo', 'Legion 5', 'Windows 10', 'AMD Ryzen 7 5800H', 16, 1024, 'Lost', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1008'), 'Field Work', '2022-11-25', '2025-11-25', 'Last seen at remote site'),

    -- Active Desktops
    ('Dell OptiPlex 7090', 'CEC-DT001', 'SN445566', 'desktop', 'Dell', 'OptiPlex 7090', 'Windows 11', 'Intel i7-11700', 16, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1009'), 'Head Office', '2023-02-14', '2026-02-14', 'Assigned to IT department'),
    ('HP EliteDesk 800', 'CEC-DT002', 'SN778899', 'desktop', 'HP', 'EliteDesk 800', 'Windows 11', 'Intel i5-10500', 8, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1010'), 'Accounting', '2023-06-15', '2026-06-15', 'Issued to finance team'),

    -- Shelf Desktops
    ('Lenovo ThinkCentre M720', 'CEC-DT003', 'SN223311', 'desktop', 'Lenovo', 'ThinkCentre M720', 'Windows 10', 'Intel i3-9100', 8, 256, 'Shelf', 
        NULL, 'IT Storage', '2022-07-10', '2025-07-10', 'Backup unit in case of emergency'),
    ('Dell Precision 5820', 'CEC-DT004', 'SN334477', 'desktop', 'Dell', 'Precision 5820', 'Windows 11', 'Intel Xeon W-2223', 32, 1024, 'Shelf', 
        NULL, 'Storage Room', '2023-01-22', '2026-01-22', 'Spare high-performance workstation'),

    -- Lost Desktops
    ('HP ProDesk 600', 'CEC-DT005', 'SN556688', 'desktop', 'HP', 'ProDesk 600', 'Windows 10', 'Intel i5-8500', 8, 256, 'Lost', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1011'), 'Unknown', '2022-12-05', '2025-12-05', 'Reported stolen from remote office'),
    
    -- Active Tablets
    ('Microsoft Surface Pro 7', 'CEC-TB001', 'SN112244', 'tablet', 'Microsoft', 'Surface Pro 7', 'Windows 11', 'Intel Core i5', 8, 256, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1012'), 'Field Work', '2023-09-20', '2026-09-20', 'Assigned to field team'),
    ('Lenovo Yoga Tablet', 'CEC-TB002', 'SN334476', 'tablet', 'Lenovo', 'Yoga Tablet 10', 'Windows 10', 'Intel Atom', 4, 128, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1013'), 'Operations', '2023-07-14', '2026-07-14', 'Used for mobile data collection'),

    -- Shelf Tablets
    ('Dell Latitude 7220 Rugged', 'CEC-TB003', 'SN667788', 'tablet', 'Dell', 'Latitude 7220 Rugged', 'Windows 10', 'Intel i5-8350U', 8, 256, 'Shelf', 
        NULL, 'IT Storage', '2022-08-30', '2025-08-30', 'Reserved for emergency use'),

    -- Active iPhones
    ('iPhone 13', 'CEC-PH001', 'SN13SN789', 'iPhone', 'Apple', 'iPhone 13', 'iOS 16', NULL, NULL, 128, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), 'Field Work', '2023-03-10', '2025-03-10', 'Work phone');

-- ==============================================
-- 3. Insert Sample Laptops (For laptop-specific details)
-- ==============================================
INSERT INTO Laptops (device_id, backup_type, internet_policy, backup_removed, sinton_backup, midland_backup, c2_backup, actions_needed) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT001'), 'Full Backup', 'Restricted', TRUE, FALSE, TRUE, FALSE, 'Needs OS update'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT002'), 'Incremental Backup', 'Unrestricted', FALSE, FALSE, FALSE, TRUE, 'Battery replacement required');

-- ==============================================
-- 4. Insert Sample Decommissioned Laptops (For tracking decommissioned laptops)
-- ==============================================
INSERT INTO Decommissioned_Laptops (laptop_id, broken, duplicate, decommission_status, additional_notes) VALUES
    ((SELECT id FROM Laptops WHERE device_id = (SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT001')), TRUE, FALSE, 'Destroyed', 'Dropped and motherboard failure');

-- ==============================================
-- 5. Insert Sample iPhones (For tracking iPhone-specific details)
-- ==============================================
INSERT INTO iPhones (device_id, responsible_party, carrier, phone_number, previous_owner, notes) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-PH001'), 'IT Department', 'AT&T', '555-6789', 'John Doe', 'Company-provided mobile device');

-- ==============================================
-- 6. Insert Sample Tablets (For tracking tablet-specific details)
-- ==============================================
INSERT INTO Tablets (device_id, responsible_party, type, carrier, phone_number, imei, notes) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-TB001'), 'Operations', 'Work', 'T-Mobile', '555-9876', '356938035643809', 'Assigned to warehouse team');

-- ==============================================
-- 7. Insert Sample Assignments (Track which employee has which device)
-- ==============================================
INSERT INTO Assignments (device_id, emp_id, assigned_at, status) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT002'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-PH001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-TB001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), NOW(), 'Active');
    
-- ==============================================
-- Insert Generic Sample Users
-- ==============================================
INSERT INTO Users (username, email, password_hash, role, created_at) VALUES
    ('admin1', 'admin@example.com', '$2y$12$K5NfL5q9P08pYiMiF2mGcOb/5E5y9xz/7U5QF2N3YP8...', 'admin', '2025-02-13 19:40:38'),
    ('user1', 'johndoe@example.com', '$2a$12$FTd/ITBj27P3FSoW4zrAReCd98LhA7SnVYme4iZQRqm...', 'user', '2025-02-13 19:40:38'),
    ('adminUser', 'adminUser@example.com', 'hashedpassword123', 'admin', '2025-03-20 01:16:26'),
    ('user2', 'user2@example.com', 'hashedpassword456', 'user', '2025-03-20 01:16:26');

-- ==============================================
-- Plain Text Passwords (For Reference Only - Do Not Store in DB)
-- ==============================================
-- admin1   -> Password: AdminPass123
-- user1    -> Password: UserPass456
-- adminUser -> Password: SuperAdmin789
-- user2    -> Password: UserTest999