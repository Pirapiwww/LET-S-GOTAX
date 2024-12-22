<?php
    // Memulai sesi untuk menyimpan data pengguna
    session_start();

    // Include konfigurasi database
    include 'config.php';

    // Inisialisasi variabel untuk data
    $namaLengkap = '';
    $plat = '';
    $totalTax = '';
    $error = '';

    // Periksa apakah form telah disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil dan bersihkan data dari form (gunakan strtolower dan htmlspecialchars untuk sanitasi)
        $checkNama = strtolower(trim($_POST['nama'] ?? ''));
        $checkPlat = strtolower(trim($_POST['plat'] ?? ''));

        // Periksa apakah plat dan nama cocok
        if ($checkNama && $checkPlat) {
            // Query untuk mengambil data jika cocok (gunakan LOWER untuk perbandingan case-insensitive)
            $query = "SELECT * FROM tax WHERE LOWER(platKendaraan) = ? AND LOWER(namaLengkap) = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $checkPlat, $checkNama); // Bind parameter
            $stmt->execute();
            $result = $stmt->get_result();

            // Jika ada data yang ditemukan
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $plat = $user['platKendaraan'];
                $totalTax = $user['totalPajak'];
                $namaLengkap = $user['namaLengkap'];
            } else {
                $error = 'Data tidak ditemukan, silahkan periksa kembali nama atau plat kendaraan Anda.';
            }
            $stmt->close();
        } else {
            $error = 'Nama dan Plat kendaraan harus diisi dengan benar.';
        }
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="CSS Files/taxchecking.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Cantarell:ital,wght@0,400;0,700;1,400;1,700&family=Cantata+One&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
        <title>LET'S GOTAX</title>
        <link rel="icon" type="images/x-icon" href="images/let's gotax(logo).png">
    </head>
    <body class="d-flex flex-column min-vh-100">
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container">
                <a class="navbar-brand me-auto" href="home.php">
                    <img src="images/let's gotax(logo).png" class="navLogo">
                    <img src="images/let's gotax (logo2).png" class="navLogo2">
                </a>
                
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="home.php#home">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="home.php#about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="home.php#services">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="home.php#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="ms-auto">
                    <a href="login.php" class="btn btn-primary rounded-pill px-4 py-2">Login / Sign Up</a>
                </div>
            </div>
        </nav>

        <div class="hero container my-5 flex-grow-1">
            <div class="row mt-5">
                <div class="col-md-6 alignLeft mt-5">
                    <h2>Your Tax Bill Information.</h2>
                    <p class="mb-4">To view vehicle tax bills, customers can fill out the check account tax bill form by filling in the vehicle number and customer name.</p>
                    <hr>
            
                    <h4>Customer Data</h4>
                    <div class="card p-4">
                        <h6><strong>Vehicle Number :</strong> 
                            <?php if ($plat): ?>
                                <h4><span class="badge bg-info"><?php echo htmlspecialchars($plat); ?></span></h5>
                            <?php else: ?>
                                <span class="badge bg-secondary">-</span>
                            <?php endif; ?>
                        </h6>
                        <h6><strong>Customer Name :</strong> 
                            <?php if ($namaLengkap): ?>
                                <h4><span class="badge bg-info"><?php echo htmlspecialchars($namaLengkap); ?></span></h5>
                            <?php else: ?>
                                <span class="badge bg-secondary">-</span>
                            <?php endif; ?>
                        </h6>
                        <h6><strong>Total Bill :</strong> 
                            <?php if ($totalTax): ?>
                                <h4><span class="badge bg-success"><?php echo htmlspecialchars($totalTax); ?></span></h5>
                            <?php else: ?>
                                <span class="badge bg-secondary">-</span>
                            <?php endif; ?>
                        </h6>
                    </div>
                </div>

                <div class="col-md-5 offset-md-1 mt-5">
                    <h4>Search Form</h4>
                    <div class="card p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="plat" class="form-label">Vehicle Number</label>
                                <input type="text" class="form-control" id="plat" name="plat" placeholder="Enter Vehicle Number">
                                <small id="numberHelp" class="form-text text-muted">Format vehicle number : YY XXXX YY</small>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Customer Full Name</label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Enter Customer Full Name" >
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Check</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-4 bg3 text-white mt-auto">
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
