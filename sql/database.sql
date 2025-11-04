-- Drop database if exists and create new
DROP DATABASE IF EXISTS component_tracker_new;
CREATE DATABASE component_tracker_new;
USE component_tracker_new;

-- Users table (Lab Incharges + Admin)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'lab_incharge') DEFAULT 'lab_incharge',
    lab_id INT NULL,
    phone VARCHAR(15),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Labs table
CREATE TABLE labs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lab_name VARCHAR(100) NOT NULL UNIQUE,
    lab_code VARCHAR(20) NOT NULL UNIQUE,
    lab_incharge_id INT NULL,
    location VARCHAR(255),
    capacity INT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_incharge_id) REFERENCES users(id)
);

-- Components table
CREATE TABLE components (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    component_id VARCHAR(50) NOT NULL UNIQUE,
    lab_id INT NOT NULL,
    category ENUM('Electronics', 'Mechanical', 'Chemical', 'Computer', 'Other') NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    total_issued INT DEFAULT 0,
    available_quantity INT DEFAULT 0,
    minimum_stock INT DEFAULT 0,
    specification TEXT,
    manufacturer VARCHAR(100),
    purchase_date DATE,
    price DECIMAL(10,2),
    status ENUM('Available', 'In Use', 'Under Maintenance', 'Disposed') DEFAULT 'Available',
    last_maintenance DATE,
    next_maintenance DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_id) REFERENCES labs(id)
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    course VARCHAR(100),
    semester INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Component issues table
CREATE TABLE component_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    issue_id VARCHAR(20) UNIQUE NOT NULL,
    component_id INT NOT NULL,
    student_id INT NULL,
    to_lab_id INT NULL,
    issued_by INT NOT NULL,
    issued_from_lab INT NOT NULL,
    quantity_issued INT NOT NULL,
    issue_date DATE NOT NULL,
    expected_return_date DATE,
    actual_return_date DATE NULL,
    purpose TEXT,
    status ENUM('issued', 'returned', 'overdue', 'lost') DEFAULT 'issued',
    condition_issued ENUM('Excellent', 'Good', 'Fair', 'Poor'),
    condition_returned ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    FOREIGN KEY (to_lab_id) REFERENCES labs(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_from_lab) REFERENCES labs(id) ON DELETE CASCADE
);

-- Insert sample labs FIRST (before users)
INSERT INTO labs (lab_name, lab_code, location, capacity, description) VALUES
('Electronics Lab', 'ELEC001', 'Building A, Room 101', 30, 'Basic electronics components and equipment'),
('Computer Lab', 'COMP002', 'Building B, Room 205', 40, 'Computer systems and networking equipment'),
('Physics Lab', 'PHY003', 'Building C, Room 110', 25, 'Physics experiment equipment'),
('Chemistry Lab', 'CHEM004', 'Building D, Room 301', 35, 'Chemistry lab equipment and chemicals'),
('Mechanical Lab', 'MECH005', 'Building E, Room 415', 20, 'Mechanical tools and equipment');

-- Create admin user (no lab_id initially)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@college.edu', MD5('admin123'), 'admin');

-- Create sample lab incharges with lab_id
INSERT INTO users (name, email, password, role, lab_id, phone) VALUES 
('Dr. Sharma', 'sharma@college.edu', MD5('lab123'), 'lab_incharge', 1, '9876543210'),
('Prof. Gupta', 'gupta@college.edu', MD5('lab123'), 'lab_incharge', 2, '9876543211'),
('Dr. Patel', 'patel@college.edu', MD5('lab123'), 'lab_incharge', 3, '9876543212');

-- Update labs with incharges
UPDATE labs SET lab_incharge_id = 2 WHERE id = 1;
UPDATE labs SET lab_incharge_id = 3 WHERE id = 2;
UPDATE labs SET lab_incharge_id = 4 WHERE id = 3;

-- Insert sample components
INSERT INTO components (name, component_id, lab_id, category, quantity, minimum_stock, specification, manufacturer, price, status) VALUES
('Arduino Uno', 'ARD-001', 1, 'Electronics', 15, 5, 'Microcontroller board based on ATmega328P', 'Arduino', 1200.00, 'Available'),
('Resistor Pack', 'RES-001', 1, 'Electronics', 200, 50, 'Assorted resistors 100Ω to 1MΩ', 'ElectroKit', 350.00, 'Available'),
('LED Red 5mm', 'LED-001', 1, 'Electronics', 100, 20, '5mm Red LED, 2.1V forward voltage', 'LEDCo', 150.00, 'Available'),
('Desktop Computer', 'COMP-001', 2, 'Computer', 25, 5, 'Intel i5, 8GB RAM, 512GB SSD', 'Dell', 45000.00, 'In Use'),
('Network Switch', 'NET-001', 2, 'Computer', 3, 1, '24-port Gigabit Ethernet Switch', 'TP-Link', 8000.00, 'Available');

-- Insert sample students
INSERT INTO students (student_id, name, email, phone, course, semester) VALUES
('STU001', 'Rahul Sharma', 'rahul@college.edu', '9876543210', 'B.Tech Computer Science', 4),
('STU002', 'Priya Patel', 'priya@college.edu', '9876543211', 'B.Tech Electronics', 3),
('STU003', 'Amit Kumar', 'amit@college.edu', '9876543212', 'B.Tech Mechanical', 5);

-- Set initial available quantity
UPDATE components SET available_quantity = quantity;