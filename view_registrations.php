<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connect.php';

// Initialize variables with default values
$message = '';
$registrations = [];

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle registration status update
if (isset($_POST['update_status'])) {
    $registration_id = intval($_POST['registration_id']);
    $new_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE event_registrations SET status = ? WHERE registration_id = ?");
        $stmt->execute([$new_status, $registration_id]);
        $message = "✅ Registration status updated successfully!";
    } catch (PDOException $e) {
        $message = "❌ Failed to update registration status: " . $e->getMessage();
    }
    
    // Redirect to clear POST data
    header("Location: view_registrations.php?message=" . urlencode($message));
    exit();
}

// Handle registration deletion
if (isset($_GET['delete'])) {
    $registration_id = intval($_GET['delete']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM event_registrations WHERE registration_id = ?");
        $stmt->execute([$registration_id]);
        $message = "✅ Registration deleted successfully!";
    } catch (PDOException $e) {
        $message = "❌ Failed to delete registration: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: view_registrations.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch all registrations from database
try {
    $stmt = $conn->prepare("
        SELECT 
            er.registration_id,
            er.registration_date,
            er.status,
            s.student_id,
            s.name as student_name,
            s.email as student_email,
            s.student_code,
            e.event_id,
            e.title as event_title,
            e.date as event_date,
            e.time as event_time,
            e.venue as event_venue
        FROM event_registrations er
        JOIN students s ON er.user_id = s.student_id
        JOIN events e ON er.event_id = e.event_id
        ORDER BY er.registration_date DESC
    ");
    $stmt->execute();
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "❌ Failed to load registrations: " . $e->getMessage();
    $registrations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations - Universe Events</title>
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

        .registrations-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .registrations-table {
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
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            font-weight: 600;
        }

        .table-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto;
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

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .student-name {
            font-weight: 600;
            color: var(--primary);
        }

        .student-details {
            font-size: 0.9rem;
            color: #666;
        }

        .event-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .event-title {
            font-weight: 600;
            color: var(--dark);
        }

        .event-details {
            font-size: 0.9rem;
            color: #666;
        }

        .registration-date {
            color: #555;
        }

        .registration-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-registered {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .status-attended {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .registration-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
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

        .status-btn {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .status-btn:hover {
            background: var(--warning);
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

        .status-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .status-select {
            padding: 0.4rem 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.8rem;
            background: white;
            cursor: pointer;
        }

        .update-btn {
            padding: 0.4rem 0.8rem;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .update-btn:hover {
            background: #45a049;
        }

        .no-registrations {
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
                grid-template-columns: 1fr 1fr 1fr auto;
            }
            
            .table-header div:nth-child(4),
            .table-row div:nth-child(4) {
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

            .registrations-header {
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

            .registration-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .table-row {
                position: relative;
                padding: 1rem;
            }

            .table-row > div {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f0f0f0;
            }

            .table-row > div:last-child {
                border-bottom: none;
            }

            .table-row > div::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-right: 1rem;
                min-width: 120px;
            }

            .registration-actions {
                grid-column: 1;
                justify-content: center;
                margin-top: 1rem;
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
                <a href="manage_events.php">Manage Events</a>
                <a href="view_registrations.php" style="color: var(--accent);">View Registrations</a>
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
            <h1>View Registrations</h1>
            <p>Manage student event participation and registrations</p>
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
                <h2>All Registrations</h2>
                <p>Manage student event participation and registrations</p>
            </div>
            <div class="registrations-count">
                <i class="fas fa-users"></i>
                <?php echo count($registrations); ?> Total Registrations
            </div>
        </div>

        <div class="registrations-actions">
            <a href="events.php" class="btn btn-outline">
                <i class="fas fa-calendar-alt"></i>
                View Events
            </a>
            <a href="manage_events.php" class="btn btn-primary">
                <i class="fas fa-cog"></i>
                Manage Events
            </a>
        </div>

        <?php if (!empty($registrations)): ?>
            <div class="registrations-table">
                <div class="table-header">
                    <div>Student Information</div>
                    <div>Event Details</div>
                    <div>Registration Date</div>
                    <div>Event Date & Time</div>
                    <div>Status</div>
                    <div>Actions</div>
                </div>

                <?php foreach ($registrations as $registration): ?>
                    <div class="table-row">
                        <div class="student-info" data-label="Student Information">
                            <div class="student-name"><?= htmlspecialchars($registration['student_name']); ?></div>
                            <div class="student-details">
                                <?= htmlspecialchars($registration['student_code']); ?> • 
                                <?= htmlspecialchars($registration['student_email']); ?>
                            </div>
                        </div>
                        <div class="event-info" data-label="Event Details">
                            <div class="event-title"><?= htmlspecialchars($registration['event_title']); ?></div>
                            <div class="event-details"><?= htmlspecialchars($registration['event_venue']); ?></div>
                        </div>
                        <div class="registration-date" data-label="Registration Date">
                            <?= date('M j, Y g:i A', strtotime($registration['registration_date'])); ?>
                        </div>
                        <div class="event-details" data-label="Event Date & Time">
                            <div><?= date('M j, Y', strtotime($registration['event_date'])); ?></div>
                            <div><?= date('g:i A', strtotime($registration['event_time'])); ?></div>
                        </div>
                        <div data-label="Status">
                            <span class="registration-status status-<?= $registration['status']; ?>">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                <?= ucfirst($registration['status']); ?>
                            </span>
                        </div>
                        <div class="registration-actions" data-label="Actions">
                            <form method="POST" action="" class="status-form">
                                <input type="hidden" name="registration_id" value="<?= $registration['registration_id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="registered" <?= $registration['status'] == 'registered' ? 'selected' : ''; ?>>Registered</option>
                                    <option value="attended" <?= $registration['status'] == 'attended' ? 'selected' : ''; ?>>Attended</option>
                                    <option value="cancelled" <?= $registration['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" value="1" class="update-btn">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                            <a href="?delete=<?= $registration['registration_id']; ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this registration? This action cannot be undone.')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-registrations">
                <i class="fas fa-users-slash"></i>
                <h3>No Registrations Found</h3>
                <p>There are no event registrations in the system yet.</p>
                <a href="events.php" class="btn btn-primary">
                    <i class="fas fa-calendar-alt"></i>
                    View Events
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
                    <li><a href="view_registrations.php">View Registrations</a></li>
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
        // Add confirmation for deletion
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('.delete-btn');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this registration? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>