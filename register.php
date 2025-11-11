<?php
// Start session
session_start();

// Initialize variables
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $student_code = trim($_POST['student_code'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($student_code) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Here you would typically:
        // 1. Validate email format
        // 2. Check if email/student code already exists
        // 3. Hash the password
        // 4. Insert into database
        // 5. Send confirmation email
        
        // For demo purposes, we'll just show a success message
        $success = 'Registration successful! You can now login to your account.';
        
        // Clear form data
        $_POST = array();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Universe Events</title>
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

        /* Registration Container */
        .registration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 2rem;
            background: var(--gradient);
            position: relative;
            overflow: hidden;
        }

        .registration-container::before {
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

        .registration-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            transition: var(--transition);
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .registration-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .registration-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .registration-icon i {
            font-size: 2rem;
            color: white;
        }

        .registration-header h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .registration-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
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

        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 2px;
        }

        .requirement.met {
            color: var(--success);
        }

        .requirement.unmet {
            color: #666;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            border-left: 4px solid #f44336;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            background: var(--gradient-accent);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 64, 129, 0.3);
        }

        .register-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .registration-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .registration-footer a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .registration-footer a:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 4rem 5% 2rem;
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

            .registration-card {
                padding: 2rem 1.5rem;
            }

            .form-row {
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
                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </header>

    <!-- Registration Section -->
    <div class="registration-container">
        <div class="registration-card">
            <div class="registration-header">
                <div class="registration-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Student Account</h2>
                <p>Join Universe Events and discover amazing campus activities</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registrationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student_code">Student Code *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="student_code" name="student_code" class="form-control" placeholder="e.g., 23IT0479" required value="<?php echo isset($_POST['student_code']) ? htmlspecialchars($_POST['student_code']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-fill" id="passwordStrength"></div>
                    </div>
                    <div class="password-requirements">
                        <div class="requirement unmet" id="lengthReq">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            At least 8 characters
                        </div>
                        <div class="requirement unmet" id="numberReq">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            Contains a number
                        </div>
                        <div class="requirement unmet" id="specialReq">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            Contains a special character
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                    </div>
                    <div id="passwordMatch" class="password-requirements"></div>
                </div>

                <button type="submit" class="register-btn" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="registration-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
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
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('submitBtn');
            
            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check password length
                if (password.length >= 8) {
                    strength++;
                    document.getElementById('lengthReq').classList.add('met');
                    document.getElementById('lengthReq').classList.remove('unmet');
                } else {
                    document.getElementById('lengthReq').classList.remove('met');
                    document.getElementById('lengthReq').classList.add('unmet');
                }
                
                // Check for numbers
                if (/\d/.test(password)) {
                    strength++;
                    document.getElementById('numberReq').classList.add('met');
                    document.getElementById('numberReq').classList.remove('unmet');
                } else {
                    document.getElementById('numberReq').classList.remove('met');
                    document.getElementById('numberReq').classList.add('unmet');
                }
                
                // Check for special characters
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                    strength++;
                    document.getElementById('specialReq').classList.add('met');
                    document.getElementById('specialReq').classList.remove('unmet');
                } else {
                    document.getElementById('specialReq').classList.remove('met');
                    document.getElementById('specialReq').classList.add('unmet');
                }
                
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
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword === '') {
                    passwordMatch.innerHTML = '';
                } else if (password === confirmPassword) {
                    passwordMatch.innerHTML = '<div class="requirement met"><i class="fas fa-check-circle"></i> Passwords match</div>';
                } else {
                    passwordMatch.innerHTML = '<div class="requirement unmet"><i class="fas fa-times-circle"></i> Passwords do not match</div>';
                }
            });
            
            // Form validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    passwordMatch.innerHTML = '<div class="requirement unmet"><i class="fas fa-times-circle"></i> Please fix password mismatch before submitting</div>';
                    confirmPasswordInput.focus();
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long');
                    passwordInput.focus();
                }
            });
        });
    </script>
</body>
</html>