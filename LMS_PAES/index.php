<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ELMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* Flexbox-based layout for full-page height */
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        .content {
            flex: 1; /* Make the content area grow to fill available space */
        }

        footer {
            background-color: #ffc107;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-warning py-3">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="Images/abeLogo.png" alt="ELMS Logo" height="40">
                <span class="ms-2">PAES REVIEWER</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="LandingPage.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="StudentRegistration.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Section -->
    <div class="content">
        <!-- Hero Section -->
        <header class="bg-dark text-light py-5">
            <div class="container text-center">
                <h1>Welcome to PAES-REVIEW</h1>
                <p class="lead">Your Gateway to Accessible and Effective E-Learning</p>
                <a href="StudentRegistration.php" class="btn btn-primary btn-lg mt-3">Get Started</a>
            </div>
        </header>

        <!-- Features Section -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5">Why Choose ELMS?</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Immersive Courses</h5>
                                <p class="card-text">Engage with comprehensive courses designed to build your skills.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Self-Paced Learning</h5>
                                <p class="card-text">Learn at your own pace with flexible modules and lessons.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Expert Resources</h5>
                                <p class="card-text">Access high-quality content curated by experienced instructors.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 ELMS. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

