<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav style="background-color: #333; padding: 10px;">
    <!-- Left side links -->
    <a href="index.php" style="color: white; margin-right: 15px;">Home</a>
    <a href="about.php" style="color: white; margin-right: 15px;">About</a>
    <a href="events.php" style="color: white; margin-right: 15px;">Events</a>

    <!-- Right side links -->
    <span style="float: right;">
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php if($_SESSION['role'] === 'student'): ?>
            <a href="dashboard.php" style="color: white; margin-right: 15px;">Student Dashboard</a>
        <?php elseif($_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php" style="color: white; margin-right: 15px;">Admin Dashboard</a>
        <?php endif; ?>
        <span style="color: white; margin-right: 15px;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="logout.php" style="color: white;">Logout</a>
    <?php else: ?>
        <a href="login.php" style="color: white; margin-right: 15px;">Login</a>
        <a href="register.php" style="color: white;">Register</a>
    <?php endif; ?>
    </span>
</nav>
