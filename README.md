Universe Events - University Event Management System
A comprehensive web-based event management system designed for universities to manage and promote campus events, student registrations, and administrative operations.

🚀 System Overview
Universe Events is a full-stack PHP web application that provides a complete solution for managing university events. The system offers different interfaces for students and administrators, enabling seamless event discovery, registration, and management.

✨ Key Features
👨‍🎓 Student Features
Browse Events - View all upcoming events with detailed information

Event Registration - One-click registration for available events

Personal Dashboard - Manage personal event registrations

Registration Management - View and cancel event registrations

Real-time Availability - See event capacity and registration status

👨‍💼 Admin Features
Event Management - Create, edit, and delete events

Registration Oversight - View all student registrations

Status Management - Update registration statuses (registered/attended/cancelled)

Capacity Control - Set and monitor event capacity limits

User Management - Admin role-based access control

🛠 Technology Stack
Backend: PHP 7.4+

Database: MySQL

Frontend: HTML5, CSS3, JavaScript

Styling: Custom CSS with Font Awesome icons

Security: Prepared statements, input validation, session management

Responsive Design: Mobile-first approach

📋 Prerequisites
Web server (Apache/Nginx)

PHP 7.4 or higher

MySQL 5.7 or higher

Modern web browser

🚀 Installation & Setup
1. Database Setup
sql
-- Create database
CREATE DATABASE universe_events;

-- Use the database
USE universe_events;

-- Create students table
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    student_code VARCHAR(50),
    phone VARCHAR(20),
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create events table
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    venue VARCHAR(255) NOT NULL,
    organizer VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    category VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create event_registrations table
CREATE TABLE event_registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (user_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id)
);

-- Insert sample admin user
INSERT INTO students (name, email, password, student_code, role) 
VALUES ('Admin User', 'admin@university.edu', 'hashed_password', 'ADMIN001', 'admin');
2. File Structure Setup
text
universe_events/
├── includes/
│   └── db_connect.php
├── admin/
│   ├── dashboard.php
│   ├── manage_events.php
│   ├── create_event.php
│   ├── edit_event.php
│   └── view_registrations.php
├── assets/
│   ├── css/
│   └── js/
├── index.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── events.php
└── my_registrations.php
3. Configuration
Update database credentials in includes/db_connect.php:

php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "universe_events";
    private $username = "your_username";
    private $password = "your_password";
    // ... rest of the class
}
?>
4. Web Server Configuration
Place files in your web server's document root

Ensure PHP has MySQL extension enabled

Set proper file permissions (755 for directories, 644 for files)

🎯 Usage Guide
For Students:
Registration & Login

Create a student account

Log in to access event features

Browse Events

Visit the events page to see all available events

Filter by category and view event details

Register for Events

Click "Register Now" on any available event

View your registrations in "My Registrations"

Manage Participation

Cancel registrations for future events

Track attendance history

For Administrators:
Admin Access

Log in with admin credentials

Access admin dashboard

Event Management

Create new events with detailed information

Edit existing event details

Activate/deactivate events as needed

Registration Management

View all student registrations

Update registration statuses

Monitor event capacity and attendance

🔒 Security Features
Password Hashing - Secure password storage

SQL Injection Prevention - Prepared statements throughout

Session Management - Secure user authentication

Role-based Access Control - Separate student and admin privileges

Input Validation - Client and server-side validation

XSS Protection - Output escaping for user-generated content

📱 Responsive Design
The system is fully responsive and works seamlessly on:

Desktop computers

Tablets

Mobile devices

🔧 Customization
Adding New Event Categories
Update the category selection in create_event.php:

php
<select name="category" class="form-control" required>
    <option value="workshop">Workshop</option>
    <option value="seminar">Seminar</option>
    <option value="conference">Conference</option>
    <option value="cultural">Cultural Event</option>
    <option value="sports">Sports Event</option>
    <option value="career">Career Fair</option>
    <option value="social">Social Gathering</option>
    <option value="academic">Academic</option>
    <option value="other">Other</option>
</select>
Modifying Styling
Update CSS variables in the <style> sections of each file:

css
:root {
    --primary: #1a237e;
    --secondary: #3949ab;
    --accent: #ff4081;
    /* ... other variables */
}
🐛 Troubleshooting
Common Issues:
Database Connection Failed

Verify database credentials in db_connect.php

Check if MySQL service is running

Ensure database and tables exist

Login Issues

Verify user exists in database

Check password hashing method

Ensure sessions are enabled

Permission Errors

Check file permissions

Verify web server has database access

Event Registration Fails

Check if event capacity is reached

Verify user isn't already registered

Check database constraints

📞 Support
For technical support or questions about implementation:

Check the database configuration

Verify file permissions

Review PHP error logs

Ensure all prerequisite software is properly installed

👨‍💻 Developer
Hamas Akram
*23IT0479*
University Event Management System Project

📄 License
This project is developed for educational purposes as part of a university coursework project.


