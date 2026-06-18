-- Create database (adjust name as needed)
CREATE DATABASE IF NOT EXISTS maths_mastery;
USE maths_mastery;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','collaborator','tutor','admin') DEFAULT 'student',
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (digital goods)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    file_type ENUM('pdf','link') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','completed','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'yoco',
    payment_status VARCHAR(50) DEFAULT 'paid',
    payment_reference VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Live classes
CREATE TABLE live_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    meet_link VARCHAR(500) DEFAULT NULL,
    status ENUM('published','draft','cancelled') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product access (grants after successful purchase)
CREATE TABLE user_product_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Role upgrades (for upgrading from student to collaborator or tutor)
CREATE TABLE role_upgrades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    from_role VARCHAR(50) NOT NULL,
    to_role VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    order_id INT DEFAULT NULL,
    payment_reference VARCHAR(255) DEFAULT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    upgraded_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Class enrollments (students joining live classes)
CREATE TABLE class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('registered','attended','absent') DEFAULT 'registered',
    UNIQUE KEY unique_enrollment (user_id, class_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES live_classes(id) ON DELETE CASCADE
);

-- Support tickets (student inquiries for admin)
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
    response TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Student Demo', 'student@example.com', '$2y$10$9ed7RPo1tQgx.L9BnrrPT.dstcRfA5dvKvevfDBLFGRw0OuSu4dRe', 'student'),
('Collaborator Demo', 'collaborator@example.com', '$2y$10$6odj.QUx6c5QyDpAhhmjk.QeYyjA7tFQEewSHiLQGJrUEczhmwcqG', 'collaborator'),
('Tutor Demo', 'tutor@example.com', '$2y$10$ctKNIpQERcpLvssnMAMAPOy0rNgbYBcAa3xzFiMOQphlW460fIJT6', 'tutor');

-- Insert sample products for demo
INSERT INTO products (title, description, price, category, file_type, file_path, is_active) VALUES
('Ultimate Algebra Cheat Sheet', 'Every formula you need for finals – digital PDF.', 95.00, 'Reference', 'pdf', 'uploads/products/algebra_cheat_sheet.pdf', 1),
('Calculus Limits & Derivatives', '2-page quick reference for calculus students.', 45.00, 'Reference', 'pdf', 'uploads/products/calculus_cheat_sheet.pdf', 1),
('Complete Geometry Workbook', '100 geometry proofs with step-by-step solutions.', 250.00, 'Workbook', 'pdf', 'uploads/products/geometry_workbook.pdf', 1),
('TI-84 Calculator Guide (PDF)', 'Master your calculator for exams.', 0.00, 'Free', 'pdf', 'uploads/products/ti84_guide.pdf', 1);
