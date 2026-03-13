-- Student Budget Tracker Database Schema
-- WARNING: This drops and recreates all tables. Run only for fresh setup.

CREATE DATABASE IF NOT EXISTS student_budget CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_budget;

-- Drop old tables in correct order (foreign keys first)
DROP TABLE IF EXISTS user_category_limits;
DROP TABLE IF EXISTS savings_goals;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS allowances;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users table for multi-user authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE allowances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_month_year (user_id, month, year),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    budget_limit DECIMAL(10, 2) DEFAULT 0.00,
    icon VARCHAR(10) DEFAULT '',
    color VARCHAR(20) DEFAULT 'slate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Savings goals table
CREATE TABLE savings_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL,
    current_amount DECIMAL(10, 2) DEFAULT 0.00,
    deadline DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-user category budget limits (overrides categories.budget_limit)
CREATE TABLE user_category_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    budget_limit DECIMAL(10, 2) NOT NULL,
    UNIQUE KEY unique_user_cat (user_id, category_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default categories with proper emoji characters (LKR amounts)
INSERT INTO categories (name, budget_limit, icon, color) VALUES
    ('Food', 10000.00, '🍔', 'orange'),
    ('Transport', 6000.00, '🚌', 'blue'),
    ('Printouts', 3000.00, '🖨️', 'purple'),
    ('Fun', 4000.00, '🎮', 'green'),
    ('Education', 5000.00, '📚', 'indigo'),
    ('Health', 2000.00, '💊', 'red'),
    ('Clothing', 3000.00, '👕', 'teal'),
    ('Mobile & Data', 1500.00, '📱', 'cyan'),
    ('Snacks & Drinks', 2000.00, '☕', 'amber'),
    ('Other', 2000.00, '📦', 'slate');
