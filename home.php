<?php
    // Memulai sesi untuk menyimpan data pengguna
    session_start();

    // Include konfigurasi database
    include 'config.php';

    // Periksa apakah pengguna sudah login
    $isLoggedIn = isset($_SESSION['user_id']);
    $userPhoto = '';
    $username = '';
    $status = '';

    if ($isLoggedIn) {
        $userId = $_SESSION['user_id'];
        $query = "SELECT photoProfile, username,status FROM akun WHERE akunId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $userPhoto = $user['photoProfile'];
            $username = $user['username'];
            $status = $user['status'];
        }
        $stmt->close();

        // Cek apakah ada data di backupTax (kalau ada hapus dan kembalikan ke tax)
        $query3 = "SELECT * FROM backuptax WHERE akunId = ?";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param('i', $userId);
        $stmt3->execute();
        $result3 = $stmt3->get_result();

        if ($result3->num_rows > 0) {
            $user3 = $result3->fetch_assoc();
            $idKendaraan = $user3['id_kendaraan'];

            $query = "SELECT No_Plat FROM kendaraan WHERE id_kendaraan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $idKendaraan);
            $stmt->execute();
            $result = $stmt->get_result();

            $Noplat = '';

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $Noplat = $user['No_Plat']; // Perbaikan di sini untuk memastikan akses ke array $user
            }

            $updateQuery = "UPDATE tax SET totalPajak = ?, lastPay = ?, status = ?, dendaPajak = ?, nextPay = ? WHERE platKendaraan = ?";
            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param('ssssss', $user3['backupTotalPajak'], $user3['backupLastPay'], $user3['backupStatus'], $user3['backupDendaPajak'], $user3['backupNextPay'], $Noplat);

            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Hapus data untuk id_kendaraan yang tertera
            $deleteQuery = "DELETE FROM backuptax WHERE id_kendaraan = ?"; // Perbaikan pada sintaks SQL DELETE
            $stmtDelete = $conn->prepare($deleteQuery);
            $stmtDelete->bind_param('i', $idKendaraan);

            $stmtDelete->execute();
            $stmtDelete->close();
        }
        $stmt3->close();
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
                <a class="navbar-brand me-auto" href="home.php">
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
                                    <?php
                                        // Menampilkan status berdasarkan nilai status
                                        if ($status == 'VERIFIED') {
                                            echo '<span class="badge bg-success">' . htmlspecialchars($status) . '</span>';
                                        } elseif ($status == 'NOT VERIFIED') {
                                            echo '<span class="badge bg-danger">' . htmlspecialchars($status) . '</span>';
                                        } elseif ($status == 'ON PROGRESS') {
                                            echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                        }
                                    ?>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="home.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                                    </svg><span class="spanCustom"> Home</span>
                                </a></li>
                                <li><a class="dropdown-item" href="notif.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                                    </svg>
                                    <span class="spanCustom">Notification</span>
                                </a></li>
                                <?php 
                                    if($status == 'VERIFIED') {
                                        ?>
                                        <li><a class="dropdown-item" href="tax.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                                            <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17s2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276"/>
                                            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.8.8 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155s4.037-.084 5.592-.155A1.48 1.48 0 0 0 15 9.611v-.413q0-.148-.03-.294l-.335-1.68a.8.8 0 0 0-.43-.563 1.8 1.8 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3z"/>
                                            </svg>
                                            <span class="spanCustom">Tax</span>
                                        </a></li>
                                        <li><a class="dropdown-item" href="point.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                            <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z"/>
                                            <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z"/>
                                            </svg>
                                            <span class="spanCustom">Point</span>
                                        </a></li>
                                        <?php
                                    }
                                ?>
                                <li><a class="dropdown-item" href="settings.php">
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
                                    <span class="spanCustom"> Log Out</span>
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
                        <?php if ($isLoggedIn): ?>
                            <a href="tax.php" class="btn btn-warning btn-lg ">
                                <span class="cantarell">
                                    Start Payment
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0z"/>
                                    </svg>
                                </span>
                            </a>
                        <?php else: ?>
                            <a href="taxchecking.php" class="btn btn-warning btn-lg ">
                                <span class="cantarell">
                                    Tax Checking
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0z"/>
                                    </svg>
                                </span>
                            </a>
                    <?php endif; ?>
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
                            <h5 class="custom color1">Tax Payment</h5>
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
                        <?php if ($isLoggedIn): ?>
                            <a href="settings.php?page=contact" class="btn btn-warning btn-lg cantarell">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                                </svg>
                                Contact Us
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-warning btn-lg cantarell">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                                </svg>
                                Contact Us
                            </a>
                        <?php endif; ?>            
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