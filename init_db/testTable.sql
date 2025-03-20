-- ==============================================
-- 1. Insert Sample Employees (For device assignments)
-- ==============================================
INSERT INTO Employees (employee_id, first_name, last_name, login_id, email, phone_number) VALUES
    ('EMP1001', 'John', 'Doe', 'jdoe', 'john.doe@example.com', '555-1234'),
    ('EMP1002', 'Jane', 'Smith', 'jsmith', 'jane.smith@example.com', '555-5678');

-- ==============================================
-- 2. Insert Sample Devices (IT asset tracking)
-- ==============================================
INSERT INTO Devices (device_name, asset_tag, serial_number, category, brand, model, os, cpu, ram, storage, status, assigned_to, location, purchase_date, warranty_expiry, notes) VALUES
    ('Dell XPS 15', 'CEC-001', 'SN123456', 'laptop', 'Dell', 'XPS 15', 'Windows 10', 'Intel i7-9750H', 16, 512, 'Active', 1, 'Head Office', '2022-01-15', '2025-01-15', 'Used by IT department'),
    ('MacBook Pro', 'CEC-1002', 'SN654321', 'laptop', 'Apple', 'MacBook Pro 16', 'macOS Monterey', 'Apple M1 Max', 32, 1024, 'Active', 2, 'Remote Worker', '2023-05-20', '2026-05-20', 'For remote work'),
    ('iPhone 13', 'CEC-2001', 'IP13SN789', 'iPhone', 'Apple', 'iPhone 13', 'iOS 16', NULL, NULL, 128, 'Active', 1, 'Field Work', '2023-03-10', '2025-03-10', 'Work phone'),
    ('Samsung Galaxy Tab', 'CEC-3001', 'SGT1001', 'tablet', 'Samsung', 'Galaxy Tab S8', 'Android 13', NULL, NULL, 256, 'Active', 2, 'Warehouse', '2022-07-25', '2024-07-25', 'Tablet for inventory tracking'),
    ('MacBook Pro', 'CEC-1002', 'SN654321', 'laptop', 'Apple', 'MacBook Pro 16', 'macOS Monterey', 'Apple M1 Max', 32, 1024, 'Active', 2, 'Remote Worker', '2023-05-20', '2026-05-20', 'For remote work'),


-- ==============================================
-- 3. Insert Sample Laptops (For laptop-specific details)
-- ==============================================
INSERT INTO Laptops (device_id, backup_type, internet_policy, backup_removed, sinton_backup, midland_backup, c2_backup, actions_needed) VALUES
    (1, 'Full Backup', 'Restricted', TRUE, FALSE, TRUE, FALSE, 'Needs OS update'),
    (2, 'Incremental Backup', 'Unrestricted', FALSE, FALSE, FALSE, TRUE, 'Battery replacement required'),
    (3, 'Incremental Backup', 'Unrestricted', FALSE, FALSE, FALSE, TRUE, 'Battery replacement required');

-- ==============================================
-- 4. Insert Sample Decommissioned Laptops (For tracking decommissioned laptops)
-- ==============================================
INSERT INTO Decommissioned_Laptops (laptop_id, broken, duplicate, decommission_status, additional_notes) VALUES
    (1, TRUE, FALSE, 'Destroyed', 'Dropped and motherboard failure'),
    (2, FALSE, TRUE, 'Reassigned', 'Duplicate record, device reissued');

-- ==============================================
-- 5. Insert Sample iPhones (For tracking iPhone-specific details)
-- ==============================================
INSERT INTO iPhones (device_id, responsible_party, carrier, phone_number, previous_owner, notes) VALUES
    (3, 'IT Department', 'AT&T', '555-6789', 'John Doe', 'Company-provided mobile device');

-- ==============================================
-- 6. Insert Sample Tablets (For tracking tablet-specific details)
-- ==============================================
INSERT INTO Tablets (device_id, responsible_party, type, carrier, phone_number, imei, notes) VALUES
    (4, 'Operations', 'Work', 'T-Mobile', '555-9876', '356938035643809', 'Assigned to warehouse team');

-- ==============================================
-- 7. Insert Sample Assignments (Track which employee has which device)
-- ==============================================
INSERT INTO Assignments (device_id, emp_id, assigned_at, status) VALUES
    (1, 1, NOW(), 'Active'),
    (2, 2, NOW(), 'Active'),
    (3, 1, NOW(), 'Active'),
    (4, 2, NOW(), 'Active');