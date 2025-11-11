<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connect.php';

// Initialize variables with default values
$message = '';
$formData = [
    'title' => '',
    'description' => '',
    'date' => '',
    'time' => '',
    'venue' => '',
    'organizer' => '',
    'capacity' => '',
    'category' => ''
];

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date' => $_POST['date'] ?? '',
        'time' => $_POST['time'] ?? '',
        'venue' => trim($_POST['venue'] ?? ''),
        'organizer' => trim($_POST['organizer'] ?? ''),
        'capacity' => $_POST['capacity'] ?? '',
        'category' => $_POST['category'] ?? 'workshop'
    ];

    // Validate form data
    $errors = [];

    if (empty($formData['title'])) {
        $errors[] = "Event title is required";
    }

    if (empty($formData['description'])) {
        $errors[] = "Event description is required";
    }

    if (empty($formData['date'])) {
        $errors[] = "Event date is required";
    } elseif (strtotime($formData['date']) < strtotime(date('Y-m-d'))) {
        $errors[] = "Event date cannot be in the past";
    }

    if (empty($formData['time'])) {
        $errors[] = "Event time is required";
    }

    if (empty($formData['venue'])) {
        $errors[] = "Event venue is required";
    }

    if (empty($formData['organizer'])) {
        $errors[] = "Event organizer is required";
    }

    if (empty($formData['capacity']) || !is_numeric($formData['capacity']) || $formData['capacity'] <= 0) {
        $errors[] = "Valid event capacity is required";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO events (title, description, date, time, venue, organizer, capacity, category, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $stmt->execute([
                $formData['title'],
                $formData['description'],
                $formData['date'],
                $formData['time'],
                $formData['venue'],
                $formData['organizer'],
                $formData['capacity'],
                $formData['category']
            ]);

            $message = "✅ Event created successfully!";
            
            // Clear form data
            $formData = [
                'title' => '',
                'description' => '',
                'date' => '',
                'time' => '',
                'venue' => '',
                'organizer' => '',
                'capacity' => '',
                'category' => 'workshop'
            ];

        } catch (PDOException $e) {
            $message = "❌ Failed to create event: " . $e->getMessage();
        }
    } else {
        $message = "❌ " . implode("<br>❌ ", $errors);
    }
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Universe Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #3949ab;
            --accent: #ff4081;
            --light: #f5f7ff;
            --dark: #0d1440;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --gradient: linear-gradient(135deg, #1a237e, #3949ab);
            --gradient-accent: linear-gradient(135deg, #3949ab, #ff4081);
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header & Navigation */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: var(--accent);
            font-size: 1.8rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: var(--primary);
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent);
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--gradient-accent);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 64, 129, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--secondary);
            border: 1px solid var(--secondary);
        }

        .btn-outline:hover {
            background-color: var(--secondary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        /* Page Header */
        .page-header {
            background: var(--gradient);
            color: white;
            padding: 3rem 5%;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,208C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
        }

        .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Form Content */
        .form-content {
            padding: 3rem 5%;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        .message-alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .message-alert.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .message-alert.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .message-alert.info {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
            border-left: 4px solid #2196f3;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: #666;
        }

        .back-link {
            background: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .event-form {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(57, 73, 171, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-help {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        .btn-reset {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-reset:hover {
            background: #e0e0e0;
            color: #333;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 4rem 5% 2rem;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #bbb;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #bbb;
            font-size: 0.9rem;
        }

        .footer-newsletter {
            margin-top: 1.5rem;
        }

        .footer-newsletter input {
            padding: 0.7rem;
            border: none;
            border-radius: 4px 0 0 4px;
            width: 70%;
        }

        .footer-newsletter button {
            padding: 0.7rem 1rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: var(--transition);
        }

        .footer-newsletter button:hover {
            background: #e91e63;
        }

        /* My Info Section */
        .my-info {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: white;
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .form-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .event-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="navbar">
            <div class="logo">
                <i class="fas fa-globe-americas"></i>
                <h1>Universe Events</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="events.php">Events</a>
                <a href="manage_events.php">Manage Events</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="auth-buttons">
                <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header-content">
            <h1>Create New Event</h1>
            <p>Add a new event to the university events calendar</p>
        </div>
    </section>

    <!-- Form Content -->
    <div class="form-content">
        <!-- Message Alert -->
        <?php if (!empty($message)): ?>
            <div class="message-alert <?php 
                if (strpos($message, '✅') !== false) echo 'success';
                elseif (strpos($message, '❌') !== false) echo 'error';
                else echo 'info';
            ?>">
                <i class="fas <?php 
                    if (strpos($message, '✅') !== false) echo 'fa-check-circle';
                    elseif (strpos($message, '❌') !== false) echo 'fa-exclamation-circle';
                    else echo 'fa-info-circle';
                ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-header">
            <div class="section-title">
                <h2>Event Information</h2>
                <p>Fill in the details for your new event</p>
            </div>
            <a href="manage_events.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Events
            </a>
        </div>

        <form method="POST" class="event-form">
            <div class="form-group">
                <label for="title" class="form-label">
                    <i class="fas fa-heading"></i>
                    Event Title *
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control" 
                    value="<?= htmlspecialchars($formData['title']); ?>"
                    placeholder="Enter event title"
                    required
                >
                <div class="form-help">A clear and descriptive title for your event</div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">
                    <i class="fas fa-align-left"></i>
                    Event Description *
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-control" 
                    placeholder="Describe the event, including objectives, activities, and what attendees can expect"
                    required
                ><?= htmlspecialchars($formData['description']); ?></textarea>
                <div class="form-help">Provide detailed information about the event</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date" class="form-label">
                        <i class="far fa-calendar"></i>
                        Event Date *
                    </label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        class="form-control" 
                        value="<?= htmlspecialchars($formData['date']); ?>"
                        min="<?= date('Y-m-d'); ?>"
                        required
                    >
                    <div class="form-help">Select the date when the event will take place</div>
                </div>

                <div class="form-group">
                    <label for="time" class="form-label">
                        <i class="far fa-clock"></i>
                        Event Time *
                    </label>
                    <input 
                        type="time" 
                        id="time" 
                        name="time" 
                        class="form-control" 
                        value="<?= htmlspecialchars($formData['time']); ?>"
                        required
                    >
                    <div class="form-help">Select the start time for the event</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="venue" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Venue *
                    </label>
                    <input 
                        type="text" 
                        id="venue" 
                        name="venue" 
                        class="form-control" 
                        value="<?= htmlspecialchars($formData['venue']); ?>"
                        placeholder="e.g., Student Center, Room 101"
                        required
                    >
                    <div class="form-help">Where the event will be held</div>
                </div>

                <div class="form-group">
                    <label for="organizer" class="form-label">
                        <i class="fas fa-user-tie"></i>
                        Organizer *
                    </label>
                    <input 
                        type="text" 
                        id="organizer" 
                        name="organizer" 
                        class="form-control" 
                        value="<?= htmlspecialchars($formData['organizer']); ?>"
                        placeholder="e.g., Computer Science Department"
                        required
                    >
                    <div class="form-help">Department or group organizing the event</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="capacity" class="form-label">
                        <i class="fas fa-users"></i>
                        Capacity *
                    </label>
                    <input 
                        type="number" 
                        id="capacity" 
                        name="capacity" 
                        class="form-control" 
                        value="<?= htmlspecialchars($formData['capacity']); ?>"
                        placeholder="e.g., 50"
                        min="1"
                        max="1000"
                        required
                    >
                    <div class="form-help">Maximum number of attendees allowed</div>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">
                        <i class="fas fa-tag"></i>
                        Category *
                    </label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="workshop" <?= $formData['category'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                        <option value="seminar" <?= $formData['category'] === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                        <option value="conference" <?= $formData['category'] === 'conference' ? 'selected' : ''; ?>>Conference</option>
                        <option value="cultural" <?= $formData['category'] === 'cultural' ? 'selected' : ''; ?>>Cultural Event</option>
                        <option value="sports" <?= $formData['category'] === 'sports' ? 'selected' : ''; ?>>Sports Event</option>
                        <option value="career" <?= $formData['category'] === 'career' ? 'selected' : ''; ?>>Career Fair</option>
                        <option value="social" <?= $formData['category'] === 'social' ? 'selected' : ''; ?>>Social Gathering</option>
                        <option value="academic" <?= $formData['category'] === 'academic' ? 'selected' : ''; ?>>Academic</option>
                        <option value="other" <?= $formData['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <div class="form-help">Select the most appropriate category</div>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-reset btn-lg">
                    <i class="fas fa-undo"></i>
                    Reset Form
                </button>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i>
                    Create Event
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Universe Events</h3>
                <p>The premier platform for discovering and managing university events, connecting students with opportunities to learn, network, and grow.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="manage_events.php">Manage Events</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Event Categories</h3>
                <ul class="footer-links">
                    <li><a href="#">Academic Workshops</a></li>
                    <li><a href="#">Cultural Events</a></li>
                    <li><a href="#">Sports Activities</a></li>
                    <li><a href="#">Career Fairs</a></li>
                    <li><a href="#">Student Clubs</a></li>
                    <li><a href="#">Guest Lectures</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Info</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> 123 University Ave, Campus City</li>
                    <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                    <li><i class="fas fa-envelope"></i> events@university.edu</li>
                    <li><i class="fas fa-clock"></i> Mon-Fri: 9:00 AM - 5:00 PM</li>
                </ul>
                <div class="footer-newsletter">
                    <h4>Subscribe to Newsletter</h4>
                    <div style="display: flex; margin-top: 10px;">
                        <input type="email" placeholder="Your email address">
                        <button type="submit">Subscribe</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Name & Registration -->
        <div class="my-info">
            <p>Hamas Akram | 23IT0479</p>
        </div>

        <div class="copyright">
            <p>&copy; <?php echo date("Y"); ?> Universe Events Portal. All rights reserved. | Designed with <i class="fas fa-heart" style="color: var(--accent);"></i> for students</p>
        </div>
    </footer>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const today = new Date().toISOString().split('T')[0];
            
            if (!dateInput.value) {
                dateInput.value = today;
            }

            // Form reset handler
            document.querySelector('button[type="reset"]').addEventListener('click', function() {
                dateInput.value = today;
                
                // Reset category to default
                document.getElementById('category').value = 'workshop';
            });

            // Form submission validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const description = document.getElementById('description').value.trim();
                const date = document.getElementById('date').value;
                const time = document.getElementById('time').value;
                const venue = document.getElementById('venue').value.trim();
                const organizer = document.getElementById('organizer').value.trim();
                const capacity = document.getElementById('capacity').value;

                if (!title || !description || !date || !time || !venue || !organizer || !capacity) {
                    alert('Please fill in all required fields.');
                    e.preventDefault();
                    return;
                }

                // Check if date is in the past
                const selectedDate = new Date(date + 'T' + time);
                const now = new Date();
                
                if (selectedDate < now) {
                    alert('Event date and time cannot be in the past.');
                    e.preventDefault();
                    return;
                }

                // Check capacity
                if (capacity < 1 || capacity > 1000) {
                    alert('Capacity must be between 1 and 1000.');
                    e.preventDefault();
                    return;
                }
            });
        });
    </script>
</body>
</html>