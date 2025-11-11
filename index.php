<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universe Events - University Events Portal</title>
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
            overflow-x: hidden;
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

        /* Hero Section */
        .hero {
            background: var(--gradient);
            color: white;
            padding: 5rem 5%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
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

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-buttons .btn {
            min-width: 150px;
        }

        /* Features Section */
        .features {
            padding: 5rem 5%;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            border-top: 4px solid var(--accent);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .feature-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .feature-card p {
            color: #666;
        }

        /* Events Section */
        .events {
            padding: 5rem 5%;
            background-color: #f5f7fa;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .event-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            height: 200px;
            background: var(--gradient);
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .event-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
        }

        .event-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 2;
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .event-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .event-description {
            color: #666;
            margin-bottom: 1rem;
        }

        .event-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Stats Section */
        .stats {
            padding: 4rem 5%;
            background: var(--gradient);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 5rem 5%;
            background-color: white;
            text-align: center;
        }

        .cta-content {
            max-width: 700px;
            margin: 0 auto;
        }

        .cta h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .cta p {
            color: #666;
            margin-bottom: 2rem;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                width: 100%;
                max-width: 250px;
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
                <a href="#home">Home</a>
                <a href="#events">Events</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="auth-buttons">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Explore the Universe of Campus Events</h1>
            <p>Connect, learn, and grow through our diverse range of academic, cultural, and social events designed for students.</p>
            <div class="hero-buttons">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="#events" class="btn btn-outline">Browse Events</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">My Dashboard</a>
                    <a href="#events" class="btn btn-outline">Browse Events</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="section-title">
            <h2>Why Choose Universe Events</h2>
            <p>Our platform offers everything you need to discover and participate in university events</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Easy Registration</h3>
                <p>Register for events with just a few clicks. No complicated forms or processes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Personalized Notifications</h3>
                <p>Get alerts about events that match your interests and academic profile.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Track Your Participation</h3>
                <p>Keep a record of all events you've attended and certificates earned.</p>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events" id="events">
        <div class="section-title">
            <h2>Upcoming Events</h2>
            <p>Check out the exciting events happening on campus this month</p>
        </div>
        <div class="events-grid">
            <div class="event-card">
                <div class="event-image">
                    <div class="event-category">Technology</div>
                </div>
                <div class="event-content">
                    <div class="event-date">
                        <i class="far fa-calendar-alt"></i>
                        <span>October 15, 2023</span>
                    </div>
                    <h3 class="event-title">Annual Tech Symposium</h3>
                    <p class="event-description">Join us for a day of innovation, tech talks, and networking with industry leaders.</p>
                    <div class="event-meta">
                        <span><i class="fas fa-map-marker-alt"></i> Engineering Building</span>
                        <span><i class="fas fa-users"></i> 120 Registered</span>
                    </div>
                </div>
            </div>
            <div class="event-card">
                <div class="event-image">
                    <div class="event-category">Cultural</div>
                </div>
                <div class="event-content">
                    <div class="event-date">
                        <i class="far fa-calendar-alt"></i>
                        <span>October 22, 2023</span>
                    </div>
                    <h3 class="event-title">Cultural Fest 2023</h3>
                    <p class="event-description">Experience diverse cultures through music, dance, food, and art from around the world.</p>
                    <div class="event-meta">
                        <span><i class="fas fa-map-marker-alt"></i> University Grounds</span>
                        <span><i class="fas fa-users"></i> 250 Registered</span>
                    </div>
                </div>
            </div>
            <div class="event-card">
                <div class="event-image">
                    <div class="event-category">Career</div>
                </div>
                <div class="event-content">
                    <div class="event-date">
                        <i class="far fa-calendar-alt"></i>
                        <span>November 5, 2023</span>
                    </div>
                    <h3 class="event-title">Career Fair</h3>
                    <p class="event-description">Connect with top employers and explore internship and job opportunities.</p>
                    <div class="event-meta">
                        <span><i class="fas fa-map-marker-alt"></i> Student Center</span>
                        <span><i class="fas fa-users"></i> 180 Registered</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">150+</div>
                <div class="stat-label">Events This Year</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5,000+</div>
                <div class="stat-label">Active Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">85%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">University Partners</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-content">
            <h2>Ready to Explore the Universe of Events?</h2>
            <p>Join thousands of students who are already discovering and participating in amazing events on campus.</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-primary">Create Your Account</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

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
                <li><a href="#home">Home</a></li>
                <li><a href="#events">Events</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
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
    <div class="my-info" style="text-align:center; margin-top:15px; font-weight:bold;">
        <p>Hamas Akram | 23IT0479</p>
    </div>

    <div class="copyright" style="text-align:center; margin-top:10px;">
        <p>&copy; <?= date("Y"); ?> Universe Events Portal. All rights reserved. | Designed with <i class="fas fa-heart" style="color: var(--accent);"></i> for students</p>
    </div>
</footer>

</body>
</html>