<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connect.php';

// Initialize variables with default values
$message = '';
$registrations = [];
$user_id = $_SESSION['user_id'] ?? null;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Handle registration cancellation
if (isset($_GET['cancel'])) {
    $registration_id = intval($_GET['cancel']);
    
    try {
        // Verify the registration belongs to the current user
        $stmt = $conn->prepare("SELECT * FROM event_registrations WHERE registration_id = ? AND user_id = ?");
        $stmt->execute([$registration_id, $user_id]);
        $registration = $stmt->fetch();
        
        if ($registration) {
            // Delete the registration
            $stmt = $conn->prepare("DELETE FROM event_registrations WHERE registration_id = ?");
            $stmt->execute([$registration_id]);
            $message = "✅ Registration cancelled successfully!";
        } else {
            $message = "❌ Registration not found or you don't have permission to cancel it.";
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to cancel registration: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: my_registrations.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch user's registrations from database
try {
    $stmt = $conn->prepare("
        SELECT er.registration_id, er.registration_date, er.status,
               e.event_id, e.title, e.description, e.date, e.time, e.venue, e.organizer, e.category,
               COUNT(er2.registration_id) as total_registrations,
               e.capacity
        FROM event_registrations er
        JOIN events e ON er.event_id = e.event_id
        LEFT JOIN event_registrations er2 ON e.event_id = er2.event_id
        WHERE er.user_id = ?
        GROUP BY er.registration_id
        ORDER BY e.date ASC, e.time ASC
    ");
    $stmt->execute([$user_id]);
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "❌ Failed to load your registrations: " . $e->getMessage();
    $registrations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Universe Events</title>
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

        /* Registrations Content */
        .registrations-content {
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
            color: var(--danger);
            border-left: 4px solid var(--danger);
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

        .registrations-header {
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

        .registrations-count {
            background: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            font-weight: 600;
            color: var(--primary);
        }

        .registrations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }

        .registration-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
            border-left: 4px solid var(--success);
        }

        .registration-card.cancelled {
            border-left-color: var(--danger);
            opacity: 0.7;
        }

        .registration-card.attended {
            border-left-color: var(--primary);
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .registration-header {
            position: relative;
            height: 120px;
            background: var(--gradient);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .registration-id {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .registration-status {
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

        .registration-status.registered {
            background: var(--success);
        }

        .registration-status.cancelled {
            background: var(--danger);
        }

        .registration-status.attended {
            background: var(--primary);
        }

        .event-title {
            font-size: 1.3rem;
            color: white;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .registration-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .event-description {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .registration-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .registration-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
            font-size: 0.9rem;
        }

        .registration-detail i {
            color: var(--secondary);
            width: 16px;
        }

        .registration-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .meta-info {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #666;
        }

        .meta-value {
            font-weight: 600;
            color: var(--primary);
        }

        .capacity-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .capacity-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            margin-top: 0.3rem;
            overflow: hidden;
            width: 100px;
        }

        .capacity-fill {
            height: 100%;
            background: var(--gradient-accent);
            border-radius: 3px;
        }

        .registration-actions {
            margin-top: auto;
            display: flex;
            gap: 0.8rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.8rem;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .view-btn {
            background: var(--gradient);
            color: white;
        }

        .view-btn:hover {
            background: var(--gradient-accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 64, 129, 0.3);
        }

        .cancel-btn {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .cancel-btn:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        .action-btn:disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .no-registrations {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .no-registrations i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-registrations h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .no-registrations p {
            color: #888;
            margin-bottom: 1.5rem;
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

            .registrations-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .registrations-grid {
                grid-template-columns: 1fr;
            }

            .registration-actions {
                flex-direction: column;
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
                <a href="events.php">Events</a>
                <a href="my_registrations.php" style="color: var(--accent);">My Registrations</a>
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
            <h1>My Registrations</h1>
            <p>View and manage all your event registrations</p>
        </div>
    </section>

    <!-- Registrations Content -->
    <div class="registrations-content">
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

        <div class="registrations-header">
            <div class="section-title">
                <h2>Your Event Registrations</h2>
                <p>Manage your upcoming events and view registration details</p>
            </div>
            <div class="registrations-count">
                <i class="fas fa-calendar-check"></i>
                <?php echo count($registrations); ?> Registered Events
            </div>
        </div>

        <div class="registrations-grid">
            <?php if (!empty($registrations)): ?>
                <?php foreach ($registrations as $registration): 
                    $capacityPercent = $registration['capacity'] > 0 ? min(100, ($registration['total_registrations'] / $registration['capacity']) * 100) : 0;
                    $isPastEvent = strtotime($registration['date']) < time();
                ?>
                    <div class="registration-card <?= $registration['status']; ?>">
                        <div class="registration-header">
                            <div class="registration-id">#<?= $registration['registration_id']; ?></div>
                            <div class="registration-status <?= $registration['status']; ?>">
                                <?= ucfirst($registration['status']); ?>
                            </div>
                            <h3 class="event-title"><?= htmlspecialchars($registration['title']); ?></h3>
                        </div>
                        <div class="registration-content">
                            <p class="event-description"><?= htmlspecialchars($registration['description']); ?></p>
                            
                            <div class="registration-details">
                                <div class="registration-detail">
                                    <i class="far fa-calendar"></i>
                                    <span><?= date('M j, Y', strtotime($registration['date'])); ?></span>
                                </div>
                                <div class="registration-detail">
                                    <i class="far fa-clock"></i>
                                    <span><?= date('g:i A', strtotime($registration['time'])); ?></span>
                                </div>
                                <div class="registration-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($registration['venue']); ?></span>
                                </div>
                                <div class="registration-detail">
                                    <i class="fas fa-user-tie"></i>
                                    <span><?= htmlspecialchars($registration['organizer']); ?></span>
                                </div>
                            </div>

                            <div class="registration-meta">
                                <div class="meta-info">
                                    <span class="meta-label">Registered On</span>
                                    <span class="meta-value"><?= date('M j, Y g:i A', strtotime($registration['registration_date'])); ?></span>
                                </div>
                                <div class="capacity-info">
                                    <span class="meta-label">Capacity</span>
                                    <span class="meta-value"><?= $registration['total_registrations']; ?>/<?= $registration['capacity']; ?></span>
                                    <div class="capacity-bar">
                                        <div class="capacity-fill" style="width: <?= $capacityPercent; ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="registration-actions">
                                <a href="events.php#event-<?= $registration['event_id']; ?>" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i>
                                    View Event
                                </a>
                                <?php if ($registration['status'] === 'registered' && !$isPastEvent): ?>
                                    <a href="?cancel=<?= $registration['registration_id']; ?>" 
                                       class="action-btn cancel-btn"
                                       onclick="return confirm('Are you sure you want to cancel your registration for <?= htmlspecialchars($registration['title']); ?>?')">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                <?php else: ?>
                                    <button class="action-btn" disabled>
                                        <i class="fas fa-ban"></i>
                                        <?= $isPastEvent ? 'Event Passed' : 'Cannot Cancel'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-registrations">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Registrations Found</h3>
                    <p>You haven't registered for any events yet.</p>
                    <a href="events.php" class="btn btn-primary">
                        <i class="fas fa-calendar-alt"></i>
                        Browse Events
                    </a>
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
                    <li><a href="my_registrations.php">My Registrations</a></li>
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
        // Add confirmation for cancellation
        document.addEventListener('DOMContentLoaded', function() {
            const cancelLinks = document.querySelectorAll('.cancel-btn');
            cancelLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to cancel this registration? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>