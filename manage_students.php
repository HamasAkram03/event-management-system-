<?php
// Start session and initialize variables
session_start();

// Initialize variables with default values
$message = '';
$students = [];

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

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle role update
if (isset($_POST['update_role']) && $pdo) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    
    try {
        // Don't allow changing your own role
        if ($user_id == $_SESSION['user_id']) {
            $message = "❌ You cannot change your own role.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->execute([$new_role, $user_id]);
            $message = "✅ User role updated successfully!";
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to update user role: " . $e->getMessage();
    }
    
    // Redirect to clear POST data
    header("Location: manage_students.php?message=" . urlencode($message));
    exit();
}

// Handle user deletion
if (isset($_GET['delete']) && $pdo) {
    $user_id = intval($_GET['delete']);
    
    try {
        // Don't allow deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            $message = "❌ You cannot delete your own account.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $message = "✅ User deleted successfully!";
        }
    } catch (PDOException $e) {
        $message = "❌ Failed to delete user: " . $e->getMessage();
    }
    
    // Redirect to clear GET parameters
    header("Location: manage_students.php?message=" . urlencode($message));
    exit();
}

// Handle password reset
if (isset($_POST['reset_password']) && $pdo) {
    $user_id = intval($_POST['user_id']);
    
    try {
        // Reset password to "password123" (you might want to make this more secure)
        $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        $message = "✅ Password reset successfully! Default password: password123";
    } catch (PDOException $e) {
        $message = "❌ Failed to reset password: " . $e->getMessage();
    }
    
    // Redirect to clear POST data
    header("Location: manage_students.php?message=" . urlencode($message));
    exit();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch all users from database
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.user_id,
                u.name,
                u.email,
                u.student_code,
                u.phone,
                u.role,
                u.created_at,
                COUNT(er.registration_id) as total_registrations,
                COUNT(CASE WHEN er.status = 'attended' THEN 1 END) as attended_events
            FROM users u
            LEFT JOIN event_registrations er ON u.user_id = er.user_id
            GROUP BY u.user_id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "❌ Failed to load users: " . $e->getMessage();
        $students = [];
    }
} else {
    // Sample data for demo
    $message = "⚠️ Using sample data (database connection failed)";
    $students = [
        [
            'user_id' => 1,
            'name' => 'Hamas Akram',
            'email' => 'hamas@university.edu',
            'student_code' => '23IT0479',
            'phone' => '+1234567890',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'total_registrations' => 7,
            'attended_events' => 5
        ],
        [
            'user_id' => 2,
            'name' => 'John Doe',
            'email' => 'john@university.edu',
            'student_code' => '23CS0123',
            'phone' => '+1234567891',
            'role' => 'student',
            'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
            'total_registrations' => 4,
            'attended_events' => 3
        ],
        [
            'user_id' => 3,
            'name' => 'Sarah Wilson',
            'email' => 'sarah@university.edu',
            'student_code' => '23EE0456',
            'phone' => '+1234567892',
            'role' => 'student',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'total_registrations' => 6,
            'attended_events' => 4
        ],
        [
            'user_id' => 4,
            'name' => 'Mike Johnson',
            'email' => 'mike@university.edu',
            'student_code' => '23ME0789',
            'phone' => '+1234567893',
            'role' => 'student',
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'total_registrations' => 2,
            'attended_events' => 1
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Universe Events</title>
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

        /* Students Content */
        .students-content {
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

        .students-header {
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

        .students-count {
            background: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            font-weight: 600;
            color: var(--primary);
        }

        .students-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .students-table {
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
            grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            font-weight: 600;
        }

        .table-row {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr auto;
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

        .student-code {
            font-weight: 600;
            color: var(--dark);
        }

        .student-phone {
            color: #555;
        }

        .student-role {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-admin {
            background: rgba(255, 64, 129, 0.1);
            color: var(--accent);
        }

        .role-student {
            background: rgba(57, 73, 171, 0.1);
            color: var(--secondary);
        }

        .student-stats {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .student-actions {
            display: flex;
            gap: 0.5rem;
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

        .role-btn {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .role-btn:hover {
            background: var(--warning);
            color: white;
        }

        .password-btn {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .password-btn:hover {
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

        .role-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .role-select {
            padding: 0.4rem 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.8rem;
            background: white;
        }

        .update-btn {
            padding: 0.4rem 0.8rem;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .update-btn:hover {
            background: #45a049;
        }

        .no-students {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .no-students i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-students h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .no-students p {
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

            .students-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .table-header, .table-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .student-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
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
            <h1>Manage Students</h1>
            <p>View and manage all student accounts</p>
        </div>
    </section>

    <!-- Students Content -->
    <div class="students-content">
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

        <div class="students-header">
            <div class="section-title">
                <h2>Student Management</h2>
                <p>Manage student accounts and permissions</p>
            </div>
            <div class="students-count">
                <i class="fas fa-users"></i>
                <?php echo count($students); ?> Total Users
            </div>
        </div>

        <div class="students-actions">
            <a href="register.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i>
                Add New User
            </a>
            <a href="export_students.php" class="btn btn-outline">
                <i class="fas fa-download"></i>
                Export to Excel
            </a>
        </div>

        <?php if (!empty($students)): ?>
            <div class="students-table">
                <div class="table-header">
                    <div>Student Information</div>
                    <div>Contact</div>
                    <div>Role</div>
                    <div>Event Stats</div>
                    <div>Member Since</div>
                    <div>Actions</div>
                </div>

                <?php foreach ($students as $student): 
                    $isCurrentUser = $student['user_id'] == $_SESSION['user_id'];
                ?>
                    <div class="table-row">
                        <div class="student-info">
                            <div class="student-name"><?= htmlspecialchars($student['name']); ?></div>
                            <div class="student-details">
                                <?= htmlspecialchars($student['email']); ?>
                            </div>
                        </div>
                        <div class="student-info">
                            <div class="student-code"><?= htmlspecialchars($student['student_code']); ?></div>
                            <div class="student-phone"><?= htmlspecialchars($student['phone'] ?: 'Not provided'); ?></div>
                        </div>
                        <div>
                            <span class="student-role role-<?= $student['role']; ?>">
                                <i class="fas <?= $student['role'] === 'admin' ? 'fa-user-shield' : 'fa-user-graduate'; ?>"></i>
                                <?= ucfirst($student['role']); ?>
                            </span>
                        </div>
                        <div class="student-stats">
                            <div class="stat-value"><?= $student['total_registrations']; ?> Registered</div>
                            <div class="stat-label"><?= $student['attended_events']; ?> Attended</div>
                        </div>
                        <div class="student-details">
                            <?= date('M j, Y', strtotime($student['created_at'])); ?>
                        </div>
                        <div class="student-actions">
                            <form method="POST" action="" class="role-form">
                                <input type="hidden" name="user_id" value="<?= $student['user_id']; ?>">
                                <select name="role" class="role-select" <?= $isCurrentUser ? 'disabled' : ''; ?> onchange="this.form.submit()">
                                    <option value="student" <?= $student['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="admin" <?= $student['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_role" value="1">
                            </form>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $student['user_id']; ?>">
                                <button type="submit" name="reset_password" class="action-btn password-btn" onclick="return confirm('Reset password for <?= htmlspecialchars($student['name']); ?>? Default password will be: password123')">
                                    <i class="fas fa-key"></i>
                                </button>
                            </form>
                            <?php if (!$isCurrentUser): ?>
                                <a href="?delete=<?= $student['user_id']; ?>" 
                                   class="action-btn delete-btn"
                                   onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($student['name']); ?>? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="action-btn" style="background: #ccc; color: #666; cursor: not-allowed;">
                                    <i class="fas fa-user"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-students">
                <i class="fas fa-users-slash"></i>
                <h3>No Users Found</h3>
                <p>There are no users registered in the system yet.</p>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Add First User
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
                    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });

            const passwordButtons = document.querySelectorAll('[name="reset_password"]');
            passwordButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to reset this user\\'s password? The new password will be: password123')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>