<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connect.php';

// Initialize variables with default values
$message = '';
$events = [];
$registered_events = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Handle event registration
if (isset($_GET['register'])) {
    $event_id = intval($_GET['register']);
    
    try {
        // Check if event exists and has capacity
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND date >= CURDATE() AND status = 'active'");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if ($event) {
            // Check if user is already registered
            $stmt = $conn->prepare("SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            
            if ($stmt->rowCount() == 0) {
                // Check capacity
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
                $stmt->execute([$event_id]);
                $registration_count = $stmt->fetch()['count'];
                
                if ($registration_count < $event['capacity']) {
                    // Register user for event
                    $stmt = $conn->prepare("INSERT INTO event_registrations (user_id, event_id, registration_date) VALUES (?, ?, NOW())");
                    $stmt->execute([$user_id, $event_id]);
                    $message = "✅ Successfully registered for event!";
                } else {
                    $message = "❌ Event is already full. Cannot register.";
                }
            } else {
                $message = "❌ You are already registered for this event.";
            }
        } else {
            $message = "❌ Event not found, has already passed, or is inactive.";
        }
    } catch (PDOException $e) {
        $message = "❌ Registration failed: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: events.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch events from database with registration counts
try {
    $stmt = $conn->prepare("
        SELECT e.*, 
               COUNT(er.registration_id) as registered_count 
        FROM events e 
        LEFT JOIN event_registrations er ON e.event_id = er.event_id 
        WHERE e.date >= CURDATE() AND e.status = 'active'
        GROUP BY e.event_id 
        ORDER BY e.date ASC, e.time ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "❌ Failed to load events: " . $e->getMessage();
    $events = [];
}

// Fetch user's registered events
try {
    $stmt = $conn->prepare("SELECT event_id FROM event_registrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $registered_events = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $message = "❌ Failed to load your registrations: " . $e->getMessage();
    $registered_events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Universe Events</title>
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

        /* Events Content */
        .events-content {
            padding: 3rem 5%;
            max-width: 1400px;
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
            color: #f44336;
            border-left: 4px solid #f44336;
        }

        .message-alert.info {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
            border-left: 4px solid #2196f3;
        }

        .message-alert.warning {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        .events-header {
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

        .events-count {
            background: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            font-weight: 600;
            color: var(--primary);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .event-header {
            position: relative;
            height: 160px;
            background: var(--gradient);
        }

        .event-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .event-id {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .event-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .event-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 0.8rem;
            line-height: 1.4;
        }

        .event-description {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .event-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .event-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
            font-size: 0.9rem;
        }

        .event-detail i {
            color: var(--secondary);
            width: 16px;
        }

        .event-capacity {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .capacity-info {
            display: flex;
            flex-direction: column;
        }

        .capacity-label {
            font-size: 0.8rem;
            color: #666;
        }

        .capacity-value {
            font-weight: 600;
            color: var(--primary);
        }

        .capacity-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            margin-top: 0.3rem;
            overflow: hidden;
        }

        .capacity-fill {
            height: 100%;
            background: var(--gradient-accent);
            border-radius: 3px;
        }

        .event-actions {
            margin-top: auto;
        }

        .register-btn {
            display: block;
            width: 100%;
            padding: 0.8rem;
            text-align: center;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: var(--transition);
        }

        .register-btn:hover {
            background: var(--gradient-accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 64, 129, 0.3);
        }

        .register-btn.disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .register-btn.disabled:hover {
            background: #ccc;
            transform: none;
            box-shadow: none;
        }

        .registered-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.8rem;
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-radius: 6px;
            font-weight: 600;
        }

        .full-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.8rem;
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border-radius: 6px;
            font-weight: 600;
        }

        .no-events {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .no-events i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-events h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .no-events p {
            color: #888;
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

            .events-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
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
                <a href="events.php" style="color: var(--accent);">Events</a>
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
            <h1>Available Events</h1>
            <p>Discover and register for exciting university events</p>
        </div>
    </section>

    <!-- Events Content -->
    <div class="events-content">
        <!-- Message Alert -->
        <?php if (!empty($message)): ?>
            <div class="message-alert <?php 
                if (strpos($message, '✅') !== false) echo 'success';
                elseif (strpos($message, '❌') !== false) echo 'error';
                elseif (strpos($message, '⚠️') !== false) echo 'warning';
                else echo 'info';
            ?>">
                <i class="fas <?php 
                    if (strpos($message, '✅') !== false) echo 'fa-check-circle';
                    elseif (strpos($message, '❌') !== false) echo 'fa-exclamation-circle';
                    elseif (strpos($message, '⚠️') !== false) echo 'fa-exclamation-triangle';
                    else echo 'fa-info-circle';
                ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="events-header">
            <div class="section-title">
                <h2>Upcoming Events</h2>
                <p>Browse and register for events that interest you</p>
            </div>
            <div class="events-count">
                <i class="fas fa-calendar-alt"></i>
                <?php echo count($events); ?> Events Available
            </div>
        </div>

        <div class="events-grid">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): 
                    $isRegistered = in_array($event['event_id'], $registered_events);
                    $capacityPercent = $event['capacity'] > 0 ? min(100, ($event['registered_count'] / $event['capacity']) * 100) : 0;
                    $isFull = $event['registered_count'] >= $event['capacity'];
                ?>
                    <div class="event-card">
                        <div class="event-header">
                            <div class="event-id">#<?= $event['event_id']; ?></div>
                            <div class="event-category"><?= htmlspecialchars($event['category'] ?? 'Event'); ?></div>
                        </div>
                        <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($event['title']); ?></h3>
                            <p class="event-description"><?= htmlspecialchars($event['description']); ?></p>
                            
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="far fa-calendar"></i>
                                    <span><?= date('M j, Y', strtotime($event['date'])); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="far fa-clock"></i>
                                    <span><?= date('g:i A', strtotime($event['time'])); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($event['venue']); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-user-tie"></i>
                                    <span><?= htmlspecialchars($event['organizer']); ?></span>
                                </div>
                            </div>

                            <div class="event-capacity">
                                <div class="capacity-info">
                                    <span class="capacity-label">Registration</span>
                                    <span class="capacity-value"><?= $event['registered_count']; ?> / <?= $event['capacity']; ?></span>
                                    <div class="capacity-bar">
                                        <div class="capacity-fill" style="width: <?= $capacityPercent; ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="event-actions">
                                <?php if ($isRegistered): ?>
                                    <div class="registered-badge">
                                        <i class="fas fa-check-circle"></i>
                                        Registered
                                    </div>
                                <?php elseif ($isFull): ?>
                                    <div class="full-badge">
                                        <i class="fas fa-times-circle"></i>
                                        Event Full
                                    </div>
                                <?php else: ?>
                                    <a href="?register=<?= $event['event_id']; ?>" 
                                       class="register-btn"
                                       onclick="return confirm('Are you sure you want to register for <?= htmlspecialchars($event['title']); ?>?')">
                                        <i class="fas fa-user-plus"></i>
                                        Register Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Available</h3>
                    <p>There are currently no active events. Please check back later!</p>
                </div>
            <?php endif; ?>
        </div>
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
        // Add confirmation for registration
        document.addEventListener('DOMContentLoaded', function() {
            const registerLinks = document.querySelectorAll('.register-btn:not(.disabled)');
            registerLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to register for this event?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>