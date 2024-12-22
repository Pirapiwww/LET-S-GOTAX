<?php
    session_start();
    include 'config.php';

    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
    $subPage = isset($_GET['subPage']) ? $_GET['subPage'] : 'personal';

    $isLoggedIn = isset($_SESSION['user_id']);
    $userPhoto = '';
    $username = '';
    $status= '';

    // untuk tabel databio
    $namaLengkap = '';
    $alamat = '';
    $alamatNow = '';
    $nik = '';
    $kelamin = '';
    $noHP = '';
    $tanggalLahir = '';
    $selfieKTP = '';
    $photoKTP = '';

    $error = '';

    if ($isLoggedIn) {
        // untuk tabel akun
        $userId = $_SESSION['user_id'];
        $query = "SELECT photoProfile, username, status FROM akun WHERE akunId = ?";
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

        // untuk tabel databio
        $query2 = "SELECT * FROM databio WHERE akunId = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $user2 = $result2->fetch_assoc();
            $namaLengkap = $user2['namaLengkap'];
            $alamat = $user2['alamat'];
            $alamatNow = $user2['alamatNow'];
            $nik = $user2['nik'];
            $noHP = $user2['noHP'];
            $tanggalLahir = $user2['tanggalLahir'];
            $selfieKTP = $user2['photoKTPSelfie'];
            $photoKTP = $user2['photoKTP'];
            $kelamin = $user2['kelamin'];
        }
        $stmt2->close();

        // untuk pilih data plat
        if (isset($_GET['select']) && isset($_GET['vehicle_id'])) {
            $akunId = $_GET['select'];
            $idKendaraan = $_GET['vehicle_id'];
        
            // Validasi input untuk mencegah SQL injection
            if (!is_numeric($akunId) || !is_numeric($idKendaraan)) {
                header("Location: tax.php?page=vehicle");
                exit;
            }
        
            // Query untuk memeriksa apakah akunId dan id_kendaraan valid
            $checkQuery = "SELECT * FROM vehicle WHERE akunId = ? AND id_kendaraan = ?";
            $stmtCheck = $conn->prepare($checkQuery);
        
            if ($stmtCheck) {
                $stmtCheck->bind_param('ii', $akunId, $idKendaraan);
                $stmtCheck->execute();
                $resultCheck = $stmtCheck->get_result();
        
                // Jika data ditemukan
                if ($resultCheck->num_rows > 0) {
                    // Reset semua statusPilih menjadi 'UNSELECTED'
                    $resetQuery = "UPDATE vehicle SET statusPilih = 'UNSELECTED' WHERE statusPilih = 'SELECTED'";
                    $stmtReset = $conn->prepare($resetQuery);
        
                    if ($stmtReset) {
                        $stmtReset->execute();
                        $stmtReset->close();
                    } else {
                        // Error jika prepare reset gagal
                        error_log("Error preparing reset query: " . $conn->error);
                    }
        
                    // Set statusPilih menjadi 'SELECTED' untuk id_kendaraan yang dipilih
                    $updateQuery = "UPDATE vehicle SET statusPilih = 'SELECTED' WHERE akunId = ? AND id_kendaraan = ?";
                    $stmtUpdate = $conn->prepare($updateQuery);
        
                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param('ii', $akunId, $idKendaraan);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    } else {
                        // Error jika prepare update gagal
                        error_log("Error preparing update query: " . $conn->error);
                    }
                }
        
                $stmtCheck->close();
            } else {
                // Error jika prepare check gagal
                error_log("Error preparing check query: " . $conn->error);
            }
        
            // Redirect ke halaman admin
            header("Location: tax.php?page=vehicle");
            exit;
        }
        
        

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ** Bagian untuk add akun admin **
            if (isset($_POST['namaPemilik'], $_POST['rangka'], $_POST['mesin'], $_POST['plat'], $_POST['jenisVehicle'])) {
                // Mengambil data dari form
                $namaPemilik = $_POST['namaPemilik'] ?? null;
                $noRangka = $_POST['rangka'] ?? null;
                $noMesin = $_POST['mesin'] ?? null;
                $noPlat = $_POST['plat'] ?? null;
                $jenisVehicle = $_POST['jenisVehicle'] ?? null;
                $statusPilih = 'UNSELECTED';
                $adminId = '1';
            
                // Cek apakah data kendaraan sudah terdaftar berdasarkan No. Rangka
                $sql_check = "SELECT * FROM vehicle WHERE No_Rangka = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("s", $noRangka);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
            
                if ($result_check->num_rows > 0) {
                    // Jika data kendaraan sudah ada
                    $error = "Data kendaraan sudah ada!";
                } else {
                    // Menambahkan data kendaraan ke database
                    $sql_insert = "INSERT INTO vehicle (adminId, akunId, No_Rangka, No_Mesin, No_Plat, namaPemilik, statusPilih, jenisKendaraan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("ssssssss", $adminId, $userId, $noRangka, $noMesin, $noPlat, $namaPemilik, $statusPilih, $jenisVehicle);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
            
                // Menutup statement dan redirect ke halaman admin
                $stmt_check->close();
                header("Location: tax.php?page=vehicle");
                exit;
            } else {
                $error = "Harap lengkapi semua data yang diperlukan!";
            }            
        }
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="JS Files/tax.js"></script>
        <link href="CSS Files/tax.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

                <!-- Login / Profil di kanan -->
                <div class="ms-auto">
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
                                    <span class="spanCustom">Log Out</span>
                                </a></li>
                            </ul>
                        </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid mt-5">
            <div class="row">
                <div class="col-md-2 sidebar py-4">
                    <h4 class="text-white text-center mt-5">Vehicle Tax</h4>
                    <a href="tax.php?page=home" class="<?= $page == 'home' ? 'active' : '' ?> mt-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                        </svg>
                        Home Page
                    </a>
                    <a href="tax.php?page=owner" class="<?= $page == 'owner' ? 'active' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-vcard" viewBox="0 0 16 16">
                        <path d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4m4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5M9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8m1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5"/>
                        <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96q.04-.245.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 1 1 12z"/>
                        </svg>
                        Ownership Transfer
                    </a>
                    <a href="tax.php?page=pay" class="<?= $page == 'pay' ? 'active' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                        <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                        </svg>
                        Tax Payment
                    </a>
                    <a href="tax.php?page=vehicle" class="<?= $page == 'vehicle' ? 'active' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                        <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17s2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276"/>
                        <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.8.8 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155s4.037-.084 5.592-.155A1.48 1.48 0 0 0 15 9.611v-.413q0-.148-.03-.294l-.335-1.68a.8.8 0 0 0-.43-.563 1.8 1.8 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3z"/>
                        </svg>
                        Data Vehicle
                    </a>
                </div>

                    <?php
                    //untuk homepage
                if ($page == 'home') {
                    ?>
                    <div class="col-md-10 p-4 mt-5">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white"></div>
                            </div>
                        </div>
                    </div>
                    <?php
                    //untuk ownership transfer 
                }elseif ($page == 'owner') {
                    ?>

                
                    <?php
                    //untuk tax payment
                }elseif ($page == 'pay') {
                    if ($subPage == 'personal') {
                        ?>
                        <!-- Main Content -->
                        <main class="col-md-10 ms-sm-auto px-md-4">
                            <div class="py-4">
                                <!-- Progress Bar -->
                                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2">1</span> Personal Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">2</span> Vehicle Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">3</span> Tax Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">4</span> Payment</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">5</span> Receipt</h6>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Form Section -->
                                <div class="bg-warning text-dark py-2 px-3 mb-4 rounded">
                                    <h5 class="mb-0">Personal Detail</h5>
                                </div>

                                <div class="bg-light p-4 rounded border">
                                    <h6>
                                        Full Name (KTP) <span class="ps-5"><span class="ps-1"> : <?php echo htmlspecialchars($namaLengkap); ?></span></span>
                                    </h6>
                                    <h6>
                                        Gender<span class="ps-5"><span class="ps-5"><span class="ps-4">: 
                                        <?php 
                                            if($kelamin == 'LAKI-LAKI'){
                                                ?>
                                                MALE
                                                <?php
                                            } elseif($kelamin == 'PEREMPUAN'){
                                                ?> 
                                                FEMALE
                                                <?php
                                            }
                                        ?>
                                    </span></span></span>
                                    </h6>

                                    <h6>
                                        NIK (KTP)<span class="ps-5"><span class="ps-5"><span class="ps-1"> : <?php echo htmlspecialchars($nik); ?></span></span></span>
                                    </h6>
                                    <h6>
                                        Handphone Number<span class="ps-4">: <?php echo htmlspecialchars($noHP); ?></span>
                                    </h6>
                                    <h6>
                                        Address (KTP) <span class="ps-5"><span class="ps-3"><span class="ps-1">: <?php echo htmlspecialchars($alamat); ?></span></span></span>
                                    </h6>
                                    <h6>
                                        Current Address <span class="ps-5"><span class="ps-1">: <?php echo htmlspecialchars($alamatNow); ?></span>
                                    </h6>
                                </div>
                                <!-- Navigation Buttons -->
                                <div class="d-flex justify-content-end mt-3">
                                    <!-- Next Button -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=vehiclePay" class="btn btn-dark">Next <span class="ms-2">&rarr;</span></a>
                                </div>
                        <?php
                    } elseif ($subPage == 'vehiclePay'){
                        ?>
                        <!-- Main Content -->
                        <main class="col-md-10 ms-sm-auto px-md-4">
                            <div class="py-4">
                                <!-- Progress Bar -->
                                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Personal Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">2</span> Vehicle Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">3</span> Tax Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">4</span> Payment</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">5</span> Receipt</h6>
                                        </li>
                                    </ul>
                                </div>
                                <!-- Form Section -->
                                <div class="bg-warning text-dark py-2 px-3 mb-4 rounded">
                                    <h5 class="mb-0">Vehicle Detail</h5>
                                </div>
                                <div class="bg-light p-4 rounded border">
                                    <?php 
                                        $queryVehicle1 = "SELECT * FROM vehicle WHERE akunId = ?";
                                        $stmtVehicle1 = $conn->prepare($queryVehicle1);
                                        $stmtVehicle1->bind_param('i', $userId);  // Ganti dengan userId dari sesi atau admin
                                        $stmtVehicle1->execute();
                                        $resultVehicle1 = $stmtVehicle1->get_result();

                                        if($resultVehicle1->num_rows > 0) {
                                            while ($row1 = $resultVehicle1->fetch_assoc()) {
                                                if($row1['statusPilih'] == 'SELECTED'){
                                                    ?>
                                                    <h6>
                                                        Vehicle Owner Name <span class="ps-5"><span class="ps-1"> : <?php echo htmlspecialchars($row1['namaPemilik']); ?></span></span>
                                                    </h6>
                                                    <h6>
                                                        Vehicle Plat Number<span class="ps-5"><span class="ps-2"> : <?php echo htmlspecialchars($row1['No_Plat']); ?></span></span>
                                                    </h6>
                                                    <h6>
                                                        Vehicle Chassis Number<span class="ps-4"><span class="ps-2"> : <?php echo htmlspecialchars($row1['No_Rangka']); ?></span></span>
                                                    </h6>
                                                    <h6>
                                                        Vehicle Engine Number <span class="ps-3"><span class="ps-3"><span class="ps-1"> : <?php echo htmlspecialchars($row1['No_Mesin']); ?></span></span></span>
                                                    </h6>
                                                    <h6>
                                                        Vehicle Type <span class="ps-5"><span class="ps-5"><span class="ps-3"><span class="ps-1">: 
                                                        <?php
                                                        if($row1['jenisKendaraan'] == 'PRIBADI'){
                                                            ?>
                                                            PRIVATE VEHICLE
                                                            <?php
                                                        } elseif($row1['jenisKendaraan'] == 'UMUM'){
                                                            ?>
                                                            PUBLIC VEHICLE
                                                            <?php
                                                        } elseif($row1['jenisKendaraan'] == 'NIAGA'){
                                                            ?>
                                                            COMMERCIAL VEHICLE
                                                            <?php
                                                        } elseif($row1['jenisKendaraan'] == 'DINAS'){
                                                            ?>
                                                            OFFICIAL VEHICLE
                                                            <?php
                                                        } elseif($row1['jenisKendaraan'] == 'KHUSUS'){
                                                            ?>
                                                            SPECIAL VEHICLE
                                                            <?php
                                                        } elseif($row1['jenisKendaraan'] == 'LISTRIK'){
                                                            ?>
                                                            ELECTRIC VEHICLE
                                                            <?php
                                                        }   
                                                            ?>
                                                        </span></span></span></span>
                                                    </h6>
                                                    <?php
                                                }
                                            }
                                        }
                                    ?> 
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <!-- Previous Button (kiri) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=personal" class="btn btn-dark"><span class="ms-2">&larr;</span> Previous</a>

                                    <!-- Next Button (kanan) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=tax" class="btn btn-dark ms-auto">Next <span class="ms-2">&rarr;</span></a>
                                </div>

                        <?php
                    } elseif ($subPage == 'tax'){
                        ?>
                        <!-- Main Content -->
                        <main class="col-md-10 ms-sm-auto px-md-4">
                            <div class="py-4">
                                <!-- Progress Bar -->
                                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Personal Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Vehicle Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">3</span> Tax Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">4</span> Payment</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">5</span> Receipt</h6>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Form Section -->
                                <div class="bg-warning text-dark py-2 px-3 mb-4 rounded">
                                    <h5 class="mb-0">Tax Detail</h5>
                                </div>

                                <div class="bg-light p-4 rounded border">
                                    <h5></h5>
                                    
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <!-- Previous Button (kiri) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=vehiclePay" class="btn btn-dark"><span class="ms-2">&larr;</span> Previous</a>

                                    <!-- Next Button (kanan) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=pay" class="btn btn-dark ms-auto">Next <span class="ms-2">&rarr;</span></a>
                                </div>

                        <?php
                    } elseif ($subPage == 'pay'){
                        ?>
                        <!-- Main Content -->
                        <main class="col-md-10 ms-sm-auto px-md-4">
                            <div class="py-4">
                                <!-- Progress Bar -->
                                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Personal Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Vehicle Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Tax Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">4</span> Payment</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-secondary text-light mx-3">5</span> Receipt</h6>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Form Section -->
                                <div class="bg-warning text-dark py-2 px-3 mb-4 rounded">
                                    <h5 class="mb-0"> Payment</h5>
                                </div>

                                <div class="bg-light p-4 rounded border">
                                    <h5></h5>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <!-- Previous Button (kiri) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=tax" class="btn btn-dark"><span class="ms-2">&larr;</span> Previous</a>

                                    <!-- Next Button (kanan) -->
                                    <a href="tax.php?page=<?= $page ?>&subPage=receipt" class="btn btn-dark ms-auto">Pay Tax <span class="ms-2">&rarr;</span></a>
                                </div>

                        <?php
                    } elseif ($subPage == 'receipt'){
                        ?> 
                        <!-- Main Content -->
                        <main class="col-md-10 ms-sm-auto px-md-4">
                            <div class="py-4">
                                <!-- Progress Bar -->
                                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Personal Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Vehicle Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Tax Detail</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                </svg>
                                            </span> Payment</h6>
                                        </li>
                                        <li class="nav-item">
                                            <h6><span class="badge bg-warning text-dark me-2 ms-3">5</span> Receipt</h6>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Form Section -->
                                <div class="bg-warning text-dark py-2 px-3 mb-4 rounded">
                                    <h5 class="mb-0">Receipt</h5>
                                </div>

                                <div class="bg-light p-4 rounded border">
                                    <h5></h5>    
                                </div>
                                <!-- Navigation Buttons -->
                                <div class="d-flex justify-content-end mt-3">
                                    <!-- Next Button -->
                                    <a href="tax.php?page=home" class="btn btn-dark">Finish Payment<span class="ms-2">&rarr;</span></a>
                                </div>

                        <?php
                    }
                        ?>
                        </div>
                    </main>
                    <?php
                    //untuk add vehicle 
                }elseif ($page == 'vehicle') {
                    ?>
                    <!-- Main Content -->
                    <main class="col-md-10 ms-sm-auto px-md-4">
                        <div class="py-4">
                            <!-- Form Section -->
                            <div class="bg-warning text-dark py-2 px-3 mb-4 rounded mt-3">
                                <h5 class="mb-0">Add Data Vehicle</h5>
                            </div>
                            <div class="bg-light p-4 rounded border">
                                <form method = "POST" action="">
                                    <div class="mb-2">
                                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                                    </div>
                                    <div class="mb-2">
                                        <label for="namaPemilk" class="form-label">Vehicle Owner Name<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="namaPemilik" name="namaPemilik" placeholder="Enter Vehicle Owner" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="rangka" class="form-label">Vehicle Chassis Number<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="rangka" name="rangka" placeholder="Enter Vehicle chassis number" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="mesin" class="form-label">Vehicle Engine Number<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="mesin" name="mesin" placeholder="Enter Vehicle Engine Number" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="plat" class="form-label">Vehicle Plat<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="plat" name="plat" placeholder="Enter Vehicle Plat" required>
                                        <small id="numberHelp" class="form-text text-muted">Format : XX YYYY XX (Capital)</small>
                                    </div>
                                    <div class="mb-2">
                                        <label for="jenisVehicle" class="form-label">Vehicle Type<span style="color: red;">*</span></label>
                                        <select class="form-select" id="jenisVehicle" name="jenisVehicle" required>
                                            <option value="">Select Type</option>
                                            <option value="PRIBADI">Private Vehicle</option>
                                            <option value="UMUM">Public Vehicle</option>
                                            <option value="NIAGA">Commercial Vehicle</option>
                                            <option value="DINAS">Official Vehicle</option>
                                            <option value="KHUSUS">Special Vehicle</option>
                                            <option value="LISTRIK">Electric Vehicle</option>                
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="submit">Add Data Vehicle</button>
                                </form>
                            </div>

                            <div class="bg-warning text-dark py-2 px-3 mb-4 rounded mt-2">
                                <h5 class="mb-0">List Data Vehicle</h5>
                            </div>
                            
                            <div class="container mt-2">
                                <div class="overflow-auto" style="white-space: nowrap;">
                                <?php 
                                    $queryVehicle = "SELECT * FROM vehicle WHERE akunId = ?";
                                    $stmtVehicle = $conn->prepare($queryVehicle);
                                    $stmtVehicle->bind_param('i', $userId);  // Ganti dengan userId dari sesi atau admin
                                    $stmtVehicle->execute();
                                    $resultVehicle = $stmtVehicle->get_result();

                                    if($resultVehicle->num_rows > 0) {
                                        while ($row = $resultVehicle->fetch_assoc()) {
                                            if($row['statusPilih'] == 'SELECTED'){
                                                ?> 
                                                <div class="d-inline-block me-3">
                                                    <div class="card" style="width: 18rem;">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Owner Name : <?php echo htmlspecialchars($row['namaPemilik']); ?></h5>
                                                            <p class="card-text">Plat : <?php echo htmlspecialchars($row['No_Plat']); ?></p>
                                                            <p class="badge bg-success">Selected</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div class="d-inline-block me-3">
                                                    <div class="card" style="width: 18rem;">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Owner Name : <?php echo htmlspecialchars($row['namaPemilik']); ?></h5>
                                                            <p class="card-text">Plat : <?php echo htmlspecialchars($row['No_Plat']); ?></p>
                                                            <a href="tax.php?page=vehicle&select=<?php echo $row['akunId']; ?>&vehicle_id=<?php echo $row['id_kendaraan']; ?>" class="btn btn-primary select-btn">Select</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                    } 
                                ?>
                                </div>
                            </div>
                        </div>

                        </div>
                    </main>
                    <?php
                }
                    ?>
            </div>
        </div>
    </body>
</html>
