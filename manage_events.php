<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connect.php';

// Initialize variables with default values
$message = '';
$events = [];

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    
    try {
        // Check if event exists
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if ($event) {
            // First delete related registrations to maintain referential integrity
            $stmt = $conn->prepare("DELETE FROM event_registrations WHERE event_id = ?");
            $stmt->execute([$event_id]);
            
            // Then delete the event
            $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->execute([$event_id]);
            $message = "✅ Event deleted successfully!";
        } else {
            $message = "❌ Event not found.";
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to delete event: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: manage_events.php?message=" . urlencode($message));
    exit();
}

// Handle event status toggle
if (isset($_GET['toggle_status'])) {
    $event_id = intval($_GET['toggle_status']);
    
    try {
        // Get current status
        $stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if ($event) {
            $new_status = $event['status'] == 'active' ? 'inactive' : 'active';
            
            // Update status
            $stmt = $conn->prepare("UPDATE events SET status = ? WHERE event_id = ?");
            $stmt->execute([$new_status, $event_id]);
            
            $status_text = $new_status == 'active' ? 'activated' : 'deactivated';
            $message = "✅ Event {$status_text} successfully!";
        } else {
            $message = "❌ Event not found.";
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to update event status: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: manage_events.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch all events from database
try {
    $stmt = $conn->prepare("
        SELECT e.*, 
               COUNT(er.registration_id) as registered_count 
        FROM events e 
        LEFT JOIN event_registrations er ON e.event_id = er.event_id 
        GROUP BY e.event_id 
        ORDER BY e.date ASC, e.time ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "❌ Failed to load events: " . $e->getMessage();
    $events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Universe Events</title>
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

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #da190b;
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

        .events-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .events-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .table-header {
            background: var(--gradient);
            color: white;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            font-weight: 600;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            align-items: center;
            transition: var(--transition);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-row:hover {
            background: #f8f9fa;
        }

        .event-title {
            font-weight: 600;
            color: var(--primary);
        }

        .event-details {
            font-size: 0.9rem;
            color: #666;
        }

        .event-venue {
            color: #555;
        }

        .event-capacity {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .capacity-info {
            font-weight: 600;
            color: var(--primary);
        }

        .capacity-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }

        .capacity-fill {
            height: 100%;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .event-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-upcoming {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .status-ongoing {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-active {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(158, 158, 158, 0.1);
            color: #757575;
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.5rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .edit-btn {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .edit-btn:hover {
            background: #2196f3;
            color: white;
        }

        .delete-btn {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .delete-btn:hover {
            background: var(--danger);
            color: white;
        }

        .toggle-btn {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .toggle-btn:hover {
            background: var(--warning);
            color: white;
        }

        .no-events {
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
        @media (max-width: 1024px) {
            .table-header, .table-row {
                grid-template-columns: 2fr 1fr 1fr 1fr auto;
            }
            
            .table-header div:nth-child(5),
            .table-row div:nth-child(5) {
                display: none;
            }
        }

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

            .table-header, .table-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .table-header div {
                display: none;
            }

            .table-row::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-bottom: 0.5rem;
            }

            .event-actions {
                justify-content: flex-start;
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
                <a href="manage_events.php" style="color: var(--accent);">Manage Events</a>
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
            <h1>Manage Events</h1>
            <p>Create, edit, and manage all university events</p>
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
                <h2>Event Management</h2>
                <p>Create new events and manage existing ones</p>
            </div>
            <div class="events-count">
                <i class="fas fa-calendar-alt"></i>
                <?php echo count($events); ?> Total Events
            </div>
        </div>

        <div class="events-actions">
            <a href="create_event.php" class="btn btn-success">
                <i class="fas fa-plus"></i>
                Add New Event
            </a>
            <a href="events.php" class="btn btn-outline">
                <i class="fas fa-eye"></i>
                View Public Events
            </a>
        </div>

        <?php if (!empty($events)): ?>
            <div class="events-table">
                <div class="table-header">
                    <div>Event Details</div>
                    <div>Date & Time</div>
                    <div>Venue</div>
                    <div>Capacity</div>
                    <div>Event Status</div>
                    <div>Active Status</div>
                    <div>Actions</div>
                </div>

                <?php foreach ($events as $event): 
                    $capacityPercent = $event['capacity'] > 0 ? min(100, ($event['registered_count'] / $event['capacity']) * 100) : 0;
                    $eventDate = strtotime($event['date'] . ' ' . $event['time']);
                    $currentTime = time();
                    
                    // Determine event timeline status
                    if ($eventDate < $currentTime) {
                        $timelineStatus = 'completed';
                        $timelineText = 'Completed';
                    } elseif ($eventDate <= ($currentTime + 3600)) { // Within 1 hour
                        $timelineStatus = 'ongoing';
                        $timelineText = 'Ongoing';
                    } else {
                        $timelineStatus = 'upcoming';
                        $timelineText = 'Upcoming';
                    }
                    
                    // Determine active/inactive status
                    $activeStatus = $event['status'];
                    $activeText = ucfirst($event['status']);
                ?>
                    <div class="table-row" data-label="Event: <?= htmlspecialchars($event['title']); ?>">
                        <div>
                            <div class="event-title"><?= htmlspecialchars($event['title']); ?></div>
                            <div class="event-details">
                                <?= htmlspecialchars($event['organizer']); ?> • 
                                <?= htmlspecialchars($event['category'] ?? 'General'); ?>
                            </div>
                        </div>
                        <div>
                            <div><?= date('M j, Y', strtotime($event['date'])); ?></div>
                            <div class="event-details"><?= date('g:i A', strtotime($event['time'])); ?></div>
                        </div>
                        <div class="event-venue"><?= htmlspecialchars($event['venue']); ?></div>
                        <div class="event-capacity">
                            <div class="capacity-info"><?= $event['registered_count']; ?>/<?= $event['capacity']; ?></div>
                            <div class="capacity-bar">
                                <div class="capacity-fill" style="width: <?= $capacityPercent; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <span class="event-status status-<?= $timelineStatus; ?>">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                <?= $timelineText; ?>
                            </span>
                        </div>
                        <div>
                            <span class="event-status status-<?= $activeStatus; ?>">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                <?= $activeText; ?>
                            </span>
                        </div>
                        <div class="event-actions">
                            <a href="edit_event.php?id=<?= $event['event_id']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            <a href="?toggle_status=<?= $event['event_id']; ?>" 
                               class="action-btn toggle-btn"
                               onclick="return confirm('Are you sure you want to <?= $activeStatus == 'active' ? 'deactivate' : 'activate'; ?> \"<?= htmlspecialchars($event['title']); ?>\"?')">
                                <i class="fas fa-power-off"></i>
                                <?= $activeStatus == 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="?delete=<?= $event['event_id']; ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete \"<?= htmlspecialchars($event['title']); ?>\"? This action cannot be undone and will remove all associated registrations.')">
                                <i class="fas fa-trash"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-plus"></i>
                <h3>No Events Created Yet</h3>
                <p>Get started by creating your first event!</p>
                <a href="create_event.php" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Create Your First Event
                </a>
            </div>
        <?php endif; ?>
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
        // Add confirmation for actions
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('.delete-btn');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });

            const toggleLinks = document.querySelectorAll('.toggle-btn');
            toggleLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const action = this.textContent.trim();
                    if (!confirm(`Are you sure you want to ${action} this event?`)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>