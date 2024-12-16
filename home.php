<?php
    // Memulai sesi untuk menyimpan data pengguna
    session_start();

    // Include konfigurasi database
    include 'config.php';

    // Periksa apakah pengguna sudah login
    $isLoggedIn = isset($_SESSION['user_id']);
    $userPhoto = '';
    $username = '';

    if ($isLoggedIn) {
        $userId = $_SESSION['user_id'];
        $query = "SELECT photoProfile, username FROM akun WHERE akunId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $userPhoto = $user['photoProfile'];
            $username = $user['username'];
        }
        $stmt->close();
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="CSS Files/home.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Cantarell:ital,wght@0,400;0,700;1,400;1,700&family=Cantata+One&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
        <title>LET'S GOTAX</title>
        <link rel="icon" type="images/x-icon" href="images/let's gotax(logo).png">

    </head>
    <body>
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container">
                <!-- Logo di kiri -->
                <a class="navbar-brand me-auto" href="#">
                    <img src="images/let's gotax(logo).png" class="navLogo">
                    <img src="images/let's gotax (logo2).png" class="navLogo2">
                </a>

                <!-- Tombol responsif -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menu navigasi di tengah -->
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
                </div>

                <!-- Login / Profil di kanan -->
                <div class="ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="btn btn-light rounded-circle p-0" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="Images/photoProfile/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="rounded-circle" width="40" height="40">
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                                <li class="dropdown-item text-center">
                                    <img src="Images/photoProfile/<?php echo htmlspecialchars($userPhoto); ?>" class="rounded-circle mb-2" width="80" height="80">
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($username); ?></p>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="home.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                                    </svg><span class="spanCustom"> Home</span>
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-coin" viewBox="0 0 16 16">
                                    <path d="M5.5 9.511c.076.954.83 1.697 2.182 1.785V12h.6v-.709c1.4-.098 2.218-.846 2.218-1.932 0-.987-.626-1.496-1.745-1.76l-.473-.112V5.57c.6.068.982.396 1.074.85h1.052c-.076-.919-.864-1.638-2.126-1.716V4h-.6v.719c-1.195.117-2.01.836-2.01 1.853 0 .9.606 1.472 1.613 1.707l.397.098v2.034c-.615-.093-1.022-.43-1.114-.9zm2.177-2.166c-.59-.137-.91-.416-.91-.836 0-.47.345-.822.915-.925v1.76h-.005zm.692 1.193c.717.166 1.048.435 1.048.91 0 .542-.412.914-1.135.982V8.518z"/>
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="M8 13.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11m0 .5A6 6 0 1 0 8 2a6 6 0 0 0 0 12"/>
                                    </svg>    
                                    <span class="spanCustom">Point</span>
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                    <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                                    <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                                    <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                                    </svg>
                                    <span class="spanCustom">History</span>
                                </a></li>
                                <li><a class="dropdown-item" href="account.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                                    </svg>
                                    <span class="spanCustom">Account</span>
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                                    </svg>
                                    <span class="spanCustom">Settings</span>
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                                    </svg>
                                    <span class="spanCustom">Log Out</span>
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary rounded-pill px-4 py-2">Login / Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Section Home -->
        <section id="home">
            <div class="hero-section">
                <div class="container">
                    <div class="content">
                        <h1>Quick & Secure </h1>
                        <h1>Vehicle Tax Payment</h1>
                        <p class="cantarell">Easily manage and pay your vehicle tax from anywhere.</p>
                        <a href="#" class="btn btn-warning btn-lg ">
                            <span class="cantarell">
                                Start Payment
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0z"/>
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
            </div>    
        </section>
        
        <!-- Section About -->
        <section id="about">
            <div class="container mt-5">
                <div class="row content-wrapper">
                    <div class="col-md-5">
                        <!-- Gambar -->
                        <img src="Images/Home/orang senyum.png" alt="Two women smiling at laptop" class="img-fluid rounded aboutImg">
                    </div>
                    <div class="col-md-7 text-content">
                        <!-- Teks -->
                        <h1>Trusted by over 10,000 vehicle owners</h1>
                        <p class="custom figtree">
                            The Motor Vehicle Tax Payment Application is a digital platform designed to simplify vehicle tax payments online, offering fast, secure, and efficient services. Supporting government digital transformation initiatives, the app features innovative tools like tax reminders, payment tracking, and transaction history access.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Section Services -->
        <section id="services" class="py-5 bg1 text-white">
            <div class="container text-center mt-5">
                <h1 class="mb-4 custom largeText">We Provide The Best <span class="text-warning">Services</span></h1>
                <h4 class="mb-5 custom2 figtree">Effortless Vehicle Tax Payments Anytime, Anywhere.</h4>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-light p-3 border-0 rounded-3 h-100 ">
                            <span class="service-image2"><img src="Images/Home/taxcheck.png" alt="Tax Check" class="mb-3 service-img"></span>
                            <h5 class="custom color1">Tax Check</h5>
                            <h5>Service</h5>
                            <p class="text-muted small justify custom3 figtree">Effortlessly access your vehicle details in just one click.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-light p-3 border-0 rounded-3 h-100">
                            <span class="service-image2"><img src="Images/Home/taxPayment.png" alt="Tax Check" class="mb-3 service-img"></span>
                            <h5 class="custom color1">Tax Payment Service</h5>
                            <h5>Service</h5>
                            <p class="text-muted small justify custom3 figtree">Securely pay your vehicle taxes anytime, anywhere.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-light p-3 border-0 rounded-3 h-100">
                            <span class="service-image2"><img src="Images/Home/ownership.png" alt="Tax Check" class="mb-3 service-img"></span>
                            <h5 class="custom color1">Ownership Transfer</h5>
                            <h5>Service</h5>
                            <p class="text-muted small justify custom3 figtree">Streamline the process of transferring vehicle ownership with ease.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-light p-3 border-0 rounded-3 h-100">
                            <span class="service-image2"><img src="Images/Home/complaint.png" alt="Tax Check" class="mb-3 service-img"></span>
                            <h5 class="custom color1">Complaint Handling</h5>
                            <h5>Service</h5>
                            <p class="text-muted small justify custom3 figtree">Submit and track complaints for any tax-related issues promptly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-5 bg-light">
            <div class="container mt-5">
                <div class="row content-wrapper2">
                    <div class="col-md-4">
                        <h2 class="mb-4 largeText">Frequently</h2>
                        <h2 class="mb-4 largeText">Asked Questions</h2>
                        <p class="mb-5 figtree">Answers to Your Most Common Questions</p>
                        <a href="#contact" class="btn btn-warning btn-lg cantarell">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                            </svg>
                            Contact Us
                        </a>
                    </div>
                    <div class="col-md-7 text-content">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="faq1">
                                    <button class="accordion-button cantarell" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        Is my data safe?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse show figtree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes, we use advanced encryption to protect your personal information.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="faq2">
                                    <button class="accordion-button collapsed cantarell" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        How do I pay my vehicle tax online?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse figtree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Follow the steps on our secure platform to complete your payment.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="faq3">
                                    <button class="accordion-button collapsed cantarell" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                        How long does ownership transfer take?
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse figtree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ownership transfer is typically completed within 5-7 business days.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq4">
                                    <button class="accordion-button collapsed cantarell" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                        Who can I contact for further assistance?
                                    </button>
                                </h2>
                                <div id="collapse4" class="accordion-collapse collapse figtree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You can reach our support team through email or our hotline number.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-4 bg3 text-white ">
            <div class="container text-center">
                <h3 class="mb-2 text-warning">LET'S GOTAX</h3>
                <p class="small">Simplify Your Tax Journey, Anytime, Anywhere!</p>
                <div>
                    <span><img src="Images/Home/gmail.png" width="70"></span>
                    <span><img src="Images/Home/facebook.png" width="70"></span>
                    <span><img src="Images/Home/instagram.png" width="70"></span>
                </div>
                <p>&copy; 2024 UNITY (LET'S GOTAX). All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>