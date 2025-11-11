<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - Universe Events</title>
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

        /* Logout Container */
        .logout-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 2rem;
            background: var(--gradient);
            position: relative;
            overflow: hidden;
        }

        .logout-container::before {
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

        .logout-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            text-align: center;
            transition: var(--transition);
        }

        .logout-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .logout-icon {
            width: 100px;
            height: 100px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .logout-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .logout-header h2 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .logout-header p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            margin: 2rem 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: var(--gradient-accent);
            border-radius: 3px;
            animation: progress 3s ease-in-out forwards;
        }

        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .redirect-info {
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 1rem;
        }

        .btn-primary {
            background: var(--gradient-accent);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 64, 129, 0.3);
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

            .logout-card {
                padding: 2rem 1.5rem;
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
        </div>
    </header>

    <!-- Logout Section -->
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="logout-header">
                <h2>Logging Out</h2>
                <p>You are being securely logged out of your account</p>
            </div>

            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>

            <div class="redirect-info">
                <p>You will be redirected to the homepage in <span id="countdown">3</span> seconds</p>
            </div>

            <a href="index.php" class="btn btn-primary">Return to Homepage Now</a>
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
        // Countdown timer
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'index.php';
            }
        }, 1000);
        
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>