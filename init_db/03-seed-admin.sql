-- 03-seed-admin.sql
USE cec_it_management;

INSERT IGNORE INTO Users (login, password_hash, role)
SELECT
  'admin',
  '$2y$10$4cjmwr6fsIMAD0i6kkwE.OCHdC.CK0WZ5sieB65heqZMUDn3n02.W',
  'Manager'
WHERE NOT EXISTS (
  SELECT 1 FROM Users WHERE login = 'admin'
);