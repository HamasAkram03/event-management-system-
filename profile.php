<?php
// Start session and initialize variables
session_start();

// Initialize variables with default values
$message = '';
$user = [];
$role = 'student';

// Database connection
$host = 'localhost';
$dbname = 'universe_events';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $message = "❌ Database connection failed: " . $e->getMessage();
    $pdo = null;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $pdo) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Get current user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $current_user = $stmt->fetch();
        
        if (!$current_user) {
            $message = "❌ User not found.";
        } else {
            // Update basic profile information
            if (!empty($name) && $name != $current_user['name']) {
                $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
                $stmt->execute([$name, $user_id]);
            }
            
            // Update phone number
            if ($phone != $current_user['phone']) {
                $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE user_id = ?");
                $stmt->execute([$phone, $user_id]);
            }
            
            // Handle password change
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $message = "❌ New passwords do not match.";
                } elseif (strlen($new_password) < 8) {
                    $message = "❌ New password must be at least 8 characters long.";
                } elseif (password_verify($current_password, $current_user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    $message = "✅ Profile and password updated successfully!";
                } else {
                    $message = "❌ Current password is incorrect.";
                }
            } else {
                $message = "✅ Profile updated successfully!";
            }
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to update profile: " . $e->getMessage();
    }
    
    // Redirect to clear POST data
    header("Location: profile.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch user data from database
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $role = $user['role'];
        } else {
            $message = "❌ User data not found.";
            // Set default user data to prevent errors
            $user = [
                'name' => 'Unknown User',
                'email' => 'unknown@example.com',
                'student_code' => 'UNKNOWN',
                'phone' => '',
                'role' => 'student',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to load user data: " . $e->getMessage();
        // Set default user data to prevent errors
        $user = [
            'name' => 'Demo User',
            'email' => 'demo@university.edu',
            'student_code' => '23IT0479',
            'phone' => '+1234567890',
            'role' => 'student',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
} else {
    // Sample data for demo
    $message = "⚠️ Using sample data (database connection failed)";
    $user = [
        'name' => 'Hamas Akram',
        'email' => 'hamas@university.edu',
        'student_code' => '23IT0479',
        'phone' => '+1234567890',
        'role' => 'student',
        'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
    ];
    $role = $user['role'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Universe Events</title>
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

        /* Profile Content */
        .profile-content {
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

        .profile-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        /* Profile Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }

        .avatar {
            width: 100px;
            height: 100px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 4px solid white;
            box-shadow: var(--shadow);
        }

        .avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .profile-role {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(57, 73, 171, 0.1);
            color: var(--secondary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .profile-role.admin {
            background: rgba(255, 64, 129, 0.1);
            color: var(--accent);
        }

        .profile-stats {
            margin-top: 2rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--primary);
        }

        .stat-badge {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Profile Main */
        .profile-main {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .section-title {
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: #666;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(57, 73, 171, 0.2);
            outline: none;
        }

        .form-control:disabled {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }

        .form-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-weak {
            background: #f44336;
            width: 33%;
        }

        .strength-medium {
            background: #ff9800;
            width: 66%;
        }

        .strength-strong {
            background: #4caf50;
            width: 100%;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
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

            .profile-layout {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: static;
            }

            .form-grid {
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
                <a href="events.php">Events</a>
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
            <h1>My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>
    </section>

    <!-- Profile Content -->
    <div class="profile-content">
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

        <div class="profile-layout">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <div class="profile-role <?php echo $role; ?>">
                        <i class="fas <?php echo $role === 'admin' ? 'fa-user-shield' : 'fa-user-graduate'; ?>"></i>
                        <?php echo ucfirst($role); ?>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-label">Member Since</span>
                        <span class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Account Status</span>
                        <span class="stat-badge">Active</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Events Registered</span>
                        <span class="stat-value"><?php echo rand(3, 15); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Events Attended</span>
                        <span class="stat-value"><?php echo rand(1, 8); ?></span>
                    </div>
                </div>
            </div>

            <!-- Profile Main Content -->
            <div class="profile-main">
                <div class="section-title">
                    <h2>Profile Information</h2>
                    <p>Update your personal information and contact details</p>
                </div>

                <form method="POST" action="" id="profileForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" class="form-control" 
                                       placeholder="Enter your full name" required 
                                       value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="form-help">Your display name across the platform</div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            <div class="form-help">Email cannot be changed</div>
                        </div>

                        <div class="form-group">
                            <label for="student_code">
                                <?php echo $role === 'admin' ? 'Admin Code' : 'Student Code'; ?>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-id-card"></i>
                                <input type="text" id="student_code" name="student_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['student_code']); ?>" disabled>
                            </div>
                            <div class="form-help">Your unique identifier</div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="text" id="phone" name="phone" class="form-control" 
                                       placeholder="Enter your phone number" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="form-help">Optional contact number</div>
                        </div>

                        <div class="form-group">
                            <label for="role">Account Role</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user-tag"></i>
                                <input type="text" id="role" name="role" class="form-control" 
                                       value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>
                            <div class="form-help">Your account permissions</div>
                        </div>

                        <div class="form-group">
                            <label for="created_at">Account Created</label>
                            <div class="input-with-icon">
                                <i class="fas fa-calendar-plus"></i>
                                <input type="text" id="created_at" name="created_at" class="form-control" 
                                       value="<?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?>" disabled>
                            </div>
                            <div class="form-help">When you joined Universe Events</div>
                        </div>
                    </div>

                    <div class="section-title">
                        <h2>Change Password</h2>
                        <p>Update your password to keep your account secure</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" 
                                       class="form-control" placeholder="Enter current password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new_password" name="new_password" 
                                       class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-fill" id="passwordStrength"></div>
                            </div>
                            <div class="form-help">Minimum 8 characters with numbers and special characters</div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       class="form-control" placeholder="Confirm new password">
                            </div>
                            <div id="passwordMatch" class="form-help"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            
            // Password strength checker
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check password length
                if (password.length >= 8) strength++;
                
                // Check for numbers
                if (/\d/.test(password)) strength++;
                
                // Check for special characters
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;
                
                // Update strength indicator
                passwordStrength.className = 'password-strength-fill';
                if (strength === 1) {
                    passwordStrength.classList.add('strength-weak');
                } else if (strength === 2) {
                    passwordStrength.classList.add('strength-medium');
                } else if (strength === 3) {
                    passwordStrength.classList.add('strength-strong');
                }
            });
            
            // Password confirmation checker
            confirmPasswordInput.addEventListener('input', function() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword === '') {
                    passwordMatch.textContent = '';
                } else if (newPassword === confirmPassword) {
                    passwordMatch.textContent = '✓ Passwords match';
                    passwordMatch.style.color = 'var(--success)';
                } else {
                    passwordMatch.textContent = '✗ Passwords do not match';
                    passwordMatch.style.color = 'var(--danger)';
                }
            });
            
            // Form validation
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                const currentPassword = document.getElementById('current_password').value;
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Check if password fields are partially filled
                if ((currentPassword || newPassword || confirmPassword) && 
                    (!currentPassword || !newPassword || !confirmPassword)) {
                    e.preventDefault();
                    alert('Please fill all password fields to change your password.');
                    return;
                }
                
                // Check if new