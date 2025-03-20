USE cec_it_management;

-- ✅ Users Table
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ✅ Devices Table
CREATE TABLE IF NOT EXISTS Devices (
    device_id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(100) NOT NULL,
    asset_tag VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('Active', 'Pending Return', 'IT Office') NOT NULL DEFAULT 'IT Office',
    assigned_to INT DEFAULT NULL,
    FOREIGN KEY (assigned_to) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ✅ AssetHistory Table
CREATE TABLE IF NOT EXISTS AssetHistory (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (device_id) REFERENCES Devices(device_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
