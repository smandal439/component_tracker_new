-- Add issues table
CREATE TABLE IF NOT EXISTS issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    component_id INT NOT NULL,
    lab_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_type ENUM('Maintenance', 'Repair', 'Replacement', 'Other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Active', 'Resolved', 'Cancelled') DEFAULT 'Active',
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    reported_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    resolved_date TIMESTAMP NULL,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);