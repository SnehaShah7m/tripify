-- Create Database
CREATE DATABASE IF NOT EXISTS tripify_db;
USE tripify_db;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Trips Table
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    image_url TEXT,
    youtube_link TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Sample Users (passwords are in plain text)
INSERT INTO users (username, email, password) VALUES 
('demo_user', 'demo@example.com', 'password123'),
('john_doe', 'john@example.com', 'password123'),
('jane_smith', 'jane@example.com', 'password123'),
('traveler', 'travel@example.com', 'password123');

-- Insert Sample Trips
INSERT INTO trips (user_id, title, description, image_url, youtube_link) VALUES 
(1, 'Beautiful Bali Adventure', 'Amazing trip to Bali with stunning beaches and cultural experiences. Visited Ubud, Kuta, and enjoyed local cuisine.', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
(1, 'Paris City of Lights', 'Romantic getaway to Paris. Visited Eiffel Tower, Louvre Museum, and enjoyed French pastries.', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=600', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
(1, 'Tokyo Journey', 'Incredible experience in Tokyo. Amazing food, technology, and culture.', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=600', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
(2, 'New York City Adventure', 'The city that never sleeps! Visited Times Square, Central Park, and Statue of Liberty.', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=600', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
(3, 'London Calling', 'Exploring London - Big Ben, London Eye, Buckingham Palace, and amazing fish & chips!', 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=600', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');