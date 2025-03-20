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
    ('Dell XPS 15', 'CEC-LT001', 'SN123456', 'laptop', 'Dell', 'XPS 15', 'Windows 10', 'Intel i7-9750H', 16, 512, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), 'Head Office', '2022-01-15', '2025-01-15', 'Used by IT department'),
    
    ('Dell XPS 16', 'CEC-LT1002', 'SN654321', 'laptop', 'Dell', 'XPS 16', 'Windows 11', 'Intel i7-9750H', 32, 1024, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), 'Remote Worker', '2023-05-20', '2026-05-20', 'For remote work'),

    ('iPhone 13', 'CEC-PH2001', 'IP13SN789', 'iPhone', 'Apple', 'iPhone 13', 'iOS 16', NULL, NULL, 128, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), 'Field Work', '2023-03-10', '2025-03-10', 'Work phone'),

    ('Samsung Galaxy Tab', 'CEC-TAB3001', 'TB13SN789', 'tablet', 'Samsung', 'Galaxy Tab S8', 'Android 13', NULL, NULL, 256, 'Active', 
        (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), 'Warehouse', '2022-07-25', '2024-07-25', 'Tablet for inventory tracking');

-- ==============================================
-- 3. Insert Sample Laptops (For laptop-specific details)
-- ==============================================
INSERT INTO Laptops (device_id, backup_type, internet_policy, backup_removed, sinton_backup, midland_backup, c2_backup, actions_needed) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-001'), 'Full Backup', 'Restricted', TRUE, FALSE, TRUE, FALSE, 'Needs OS update'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-1002'), 'Incremental Backup', 'Unrestricted', FALSE, FALSE, FALSE, TRUE, 'Battery replacement required');

-- ==============================================
-- 4. Insert Sample Decommissioned Laptops (For tracking decommissioned laptops)
-- ==============================================
INSERT INTO Decommissioned_Laptops (laptop_id, broken, duplicate, decommission_status, additional_notes) VALUES
    ((SELECT id FROM Laptops WHERE device_id = (SELECT device_id FROM Devices WHERE asset_tag = 'CEC-001')), TRUE, FALSE, 'Destroyed', 'Dropped and motherboard failure');

-- ==============================================
-- 5. Insert Sample iPhones (For tracking iPhone-specific details)
-- ==============================================
INSERT INTO iPhones (device_id, responsible_party, carrier, phone_number, previous_owner, notes) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-2001'), 'IT Department', 'AT&T', '555-6789', 'John Doe', 'Company-provided mobile device');

-- ==============================================
-- 6. Insert Sample Tablets (For tracking tablet-specific details)
-- ==============================================
INSERT INTO Tablets (device_id, responsible_party, type, carrier, phone_number, imei, notes) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-3001'), 'Operations', 'Work', 'T-Mobile', '555-9876', '356938035643809', 'Assigned to warehouse team');

-- ==============================================
-- 7. Insert Sample Assignments (Track which employee has which device)
-- ==============================================
INSERT INTO Assignments (device_id, emp_id, assigned_at, status) VALUES
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-LT1002'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-PH2001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1001'), NOW(), 'Active'),
    ((SELECT device_id FROM Devices WHERE asset_tag = 'CEC-TAB3001'), (SELECT emp_id FROM Employees WHERE employee_id = 'EMP1002'), NOW(), 'Active');
    
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