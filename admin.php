<?php
// Tentukan halaman default
$page = isset($_GET['page']) ? $_GET['page'] : 'account';
$pageAkun = isset($_GET['pageAkun']) ? $_GET['pageAkun'] : 'user';

// Menampilkan semua error agar lebih mudah mendeteksi masalah
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memulai sesi untuk menyimpan data pengguna
session_start();

// Include konfigurasi database
include 'config.php';

// Periksa apakah pengguna sudah login
$isLoggedIn = isset($_SESSION['user_id']);

//column tabel Admin
$userPhoto = '';
$username = '';
$email = '';

//untuk menyimppan error
$error = '';

// Jika pengguna login
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];

    // Query untuk mengambil data dari tabel akun
    $query = "SELECT * FROM admin WHERE adminId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userPhoto = $user['profileAdmin'];
        $username = $user['usernameAdmin'];
        $email = $user['emailAdmin'];
    }
    $stmt->close();

    // query untuk perulangan adminId
    $queryAdmins = "SELECT adminId FROM admin";
    $resultAdmins = $conn->query($queryAdmins);

    // untuk delete admin
    if (isset($_GET['delete'])) {
        $deleteAdminId = $_GET['delete'];
    
        // Cek apakah adminId yang akan dihapus ada di tabel akun
        $checkQuery = "SELECT * FROM akun WHERE adminId = ?";
        $stmtCheck = $conn->prepare($checkQuery);
        $stmtCheck->bind_param('i', $deleteAdminId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
    
        if ($resultCheck->num_rows > 0) {
            // Jika adminId ditemukan di tabel akun, ubah adminId di tabel akun menjadi 1 (SuperAdmin)
            $updateQuery = "UPDATE akun SET adminId = 1 WHERE adminId = ?";
            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param('i', $deleteAdminId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    
        // Hapus admin dari tabel admin
        $deleteQuery = "DELETE FROM admin WHERE adminId = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->bind_param('i', $deleteAdminId);
        $stmtDelete->execute();
        $stmtDelete->close();
    
        // Redirect setelah proses delete
        header("Location: admin.php?pageAkun=admin"); // Redirect ke halaman admin
        exit; // Pastikan tidak ada kode lain yang dieksekusi
    }    

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Inisialisasi user ID
        $userId = $_SESSION['user_id'];

        // Memproses perubahan adminId untuk setiap akun
        foreach ($_POST as $key => $value) {
            // Pastikan key sesuai dengan pola adminChange_akunId
            if (strpos($key, 'adminChange_') === 0) {
                // Mendapatkan akunId dari key
                $akunId = str_replace('adminChange_', '', $key);
                $newAdminId = $value;

                // Update adminId untuk akun tertentu
                $updateQuery = "UPDATE akun SET adminId = ? WHERE akunId = ?";
                $stmtUpdate = $conn->prepare($updateQuery);
                $stmtUpdate->bind_param('ii', $newAdminId, $akunId);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        }

        // ** Bagian untuk add akun admin **
        if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
            $usernameAdmin = $_POST['username'] ?? null;
            $emailAdmin = $_POST['email'] ?? null;
            $passwordAdmin = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $profileAdmin = 'profileDefault.jpg';
        
            // Cek apakah email atau username sudah terdaftar
            $sql_check = "SELECT * FROM admin WHERE emailAdmin = ? OR usernameAdmin = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ss", $emailAdmin, $usernameAdmin);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
        
            if ($result_check->num_rows > 0) {
                // Jika email atau username sudah terdaftar
                $error = "Email atau username sudah terdaftar!";
            } else {
                // Simpan data user dengan gambar default
                $sql_insert = "INSERT INTO admin (usernameAdmin, emailAdmin, passwordAdmin, profileAdmin) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssss", $usernameAdmin, $emailAdmin, $passwordAdmin, $profileAdmin);
        
                // Eksekusi query insert
                if ($stmt_insert->execute()) {
                    // Jika berhasil
                    echo "Admin berhasil ditambahkan!";
                } else {
                    // Jika terjadi error saat eksekusi insert
                    $error = "Terjadi kesalahan saat menambahkan admin!";
                }
        
                // Menutup statement setelah selesai
                $stmt_insert->close();
            }
        
            // Menutup statement cek setelah selesai
            $stmt_check->close();
        }
        

        // ** Bagian untuk Update Foto Profil **
        if (isset($_FILES['newProfile']) && $_FILES['newProfile']['error'] == 0) {
            $defaultImage = "profileDefault.jpg";
            $targetDir = "Images/photoProfile/"; // Folder penyimpanan file
            $fileName = time() . "_" . basename($_FILES["newProfile"]["name"]); // Nama file baru (dengan timestamp untuk unik)
            $targetFilePath = $targetDir . $fileName; // Lokasi lengkap file
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION); // Ekstensi file

            // Validasi jenis file (hanya gambar)
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'bmp');
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Ambil nama file lama dari database berdasarkan akunId pengguna
                $query = "SELECT profileAdmin FROM admin WHERE akunId = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $oldFileName = $row['photoProfile'];
                    $oldFilePath = $targetDir . $oldFileName;

                    // Hapus file lama jika bukan file default
                    if ($oldFileName != $defaultImage && file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Hapus file lama
                    }
                }
                $stmt->close();

                // Pindahkan file baru ke folder tujuan
                if (move_uploaded_file($_FILES["newProfile"]["tmp_name"], $targetFilePath)) {
                    // Simpan nama file baru ke database
                    $updateFileQuery = "UPDATE admin SET profileAdmin = ? WHERE adminId = ?";
                    $stmt = $conn->prepare($updateFileQuery);
                    $stmt->bind_param('si', $fileName, $userId);
                    $stmt->execute();
                    $stmt->close();
                    echo "<script>window.location.href = window.location.href;</script>";  // Refresh halaman
                    echo "File berhasil diupload, file lama (jika bukan default) dihapus.";
                } else {
                    echo "Maaf, terjadi kesalahan saat mengunggah file.";
                }
            } else {
                echo "Hanya file gambar (JPG, PNG, JPEG, GIF, BMP) yang diperbolehkan.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="CSS Files/admin.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300..900&display=swap" rel="stylesheet">
        <title>LET'S GOTAX</title>
        <link rel="icon" type="images/x-icon" href="images/let's gotax(logo).png">
    </head>
    <body>
        <!-- Sidebar -->
        <div class="d-flex">
                <div class="col-md-3 sidebar marginSideBar">
                    <div class="text-center mb-4">
                        <img src="Images/photoProfile/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="rounded-circle" width="150" height="150">
                        <h5 class="mb-0 fw-bold spacing"><?php echo htmlspecialchars($username); ?></h5>
                        <!-- Button untuk membuka modal -->
                        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#changeImageModal">Change</button>

                        <!-- Modal untuk upload gambar -->
                        <div class="modal fade" id="changeImageModal" tabindex="-1" aria-labelledby="changeImageModalLabel" aria-hidden="true">
                            <div class="modal-dialog margin">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="changeImageModalLabel">Change Profile Picture</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="fileInput" class="customLink">Choose a new profile picture</label>
                                                <input type="file" class="form-control" id="fileInput" name="newProfile" accept="image/*" onchange="previewImage(event)">
                                            </div>
                                            <div class="form-group mt-3">
                                                <img id="preview" src="#" alt="Image Preview" class="img-fluid" style="display: none;">
                                            </div>
                                            <div class="form-group mt-3">
                                                <button type="submit" class="btn btn-primary">Save Image</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <nav>
                        <a href="admin.php?page=account" class="<?= $page == 'account' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                            Account
                        </a>
                        <a href="admin.php?page=personal" class="<?= $page == 'personal' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard-data" viewBox="0 0 16 16">
                            <path d="M4 11a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm6-4a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zM7 9a1 1 0 0 1 2 0v3a1 1 0 1 1-2 0z"/>
                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
                            </svg>
                            Data Personal
                        </a>
                        <a href="admin.php?page=vehicle" class="<?= $page == 'vehicle' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                            <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17s2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276"/>
                            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.8.8 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155s4.037-.084 5.592-.155A1.48 1.48 0 0 0 15 9.611v-.413q0-.148-.03-.294l-.335-1.68a.8.8 0 0 0-.43-.563 1.8 1.8 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3z"/>
                            </svg>
                            Data Vehicle
                        </a>
                        <a href="admin.php?page=tax" class="<?= $page == 'tax' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
                            <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
                            </svg>
                            Tax
                        </a>
                        <a href="admin.php?page=point" class="<?= $page == 'point' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                            <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z"/>
                            <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z"/>
                            </svg>
                            Point
                        </a>
                        <a href="admin.php?page=complaint" class="<?= $page == 'complaint' ? 'active' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stickies-fill" viewBox="0 0 16 16">
                            <path d="M0 1.5V13a1 1 0 0 0 1 1V1.5a.5.5 0 0 1 .5-.5H14a1 1 0 0 0-1-1H1.5A1.5 1.5 0 0 0 0 1.5"/>
                            <path d="M3.5 2A1.5 1.5 0 0 0 2 3.5v11A1.5 1.5 0 0 0 3.5 16h6.086a1.5 1.5 0 0 0 1.06-.44l4.915-4.914A1.5 1.5 0 0 0 16 9.586V3.5A1.5 1.5 0 0 0 14.5 2zm6 8.5a1 1 0 0 1 1-1h4.396a.25.25 0 0 1 .177.427l-5.146 5.146a.25.25 0 0 1-.427-.177z"/>
                            </svg>
                            Complaint
                        </a>
                        <a href="logout.php" class="text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>
                            <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                            </svg>
                            Log Out
                        </a>
                    </nav>
                </div>

            <!-- Navigation Bar -->
            <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container">
                <!-- Logo di kiri -->
                <a class="navbar-brand me-auto" href="admin.php">
                    <img src="images/let's gotax(logo).png" class="navLogo">
                    <img src="images/let's gotax (logo2).png" class="navLogo2">
                </a>
            </div>
        </nav>

            <!-- Main Content -->
            <div class="main-content p-4 w-100 mt-5">
                    <?php
                    //untuk account 
                if ($page == 'account') {
                    ?>
                    
                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Account</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="container mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <a href="admin.php?pageAkun=user" class="btn btn-outline-primary <?= $pageAkun == 'user' ? 'active' : '' ?>" >Users</a>
                                <a href="admin.php?pageAkun=admin" class="btn btn-outline-primary <?= $pageAkun == 'admin' ? 'active' : '' ?>" >Admin</a>
                            </div>

                        <?php
                        //untuk user 
                    if ($pageAkun == 'user') {
                        ?>
                        </div>
                            <table class="table table-bordered align-middle" id="members-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <th>Next Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        // Query untuk mengambil akun berdasarkan adminId yang sedang login
                                        $queryAkun = "SELECT * FROM akun WHERE adminId = ?";
                                        $stmtAkun = $conn->prepare($queryAkun);
                                        $stmtAkun->bind_param('i', $userId);  // Ganti dengan userId dari sesi atau admin
                                        $stmtAkun->execute();
                                        $resultAkun = $stmtAkun->get_result();

                                        // Jika ada akun ditemukan
                                        if($resultAkun->num_rows > 0) {
                                            while ($row = $resultAkun->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td><img src="Images/photoProfile/<?php echo htmlspecialchars($row['photoProfile']); ?>" alt="Profile" class="rounded-circle" width="40" height="40"></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <?php
                                                    // Menampilkan status berdasarkan nilai status
                                                    if ($row['status'] == 'VERIFIED') {
                                                        echo '<td><span class="badge bg-success">' . htmlspecialchars($row['status']) . '</span></td>';
                                                    } elseif ($row['status'] == 'NOT VERIFIED') {
                                                        echo '<td><span class="badge bg-danger">' . htmlspecialchars($row['status']) . '</span></td>';
                                                    }
                                                    ?>
                                                    
                                                    <!-- Action untuk mengubah status -->
                                                    <td>
                                                        <a href="admin.php?status=VERIFIED" class="btn btn-sm btn-primary">Verified Status</a>
                                                        <a href="admin.php?status=NOT VERIFIED" class="btn btn-sm btn-primary">Unverified Status</a>
                                                    </td>

                                                    <!-- Form untuk mengubah adminId -->
                                                    <td>
                                                        <form method="POST">
                                                            <label class="paddingBtm" for="adminChange_<?php echo $row['akunId']; ?>">Change adminId</label>
                                                            <select class="form-control" id="adminChange_<?php echo $row['akunId']; ?>" name="adminChange_<?php echo $row['akunId']; ?>">
                                                                <?php
                                                                    // Mengambil daftar adminId
                                                                    $resultAdmins = $conn->query("SELECT adminId FROM admin"); // Pastikan query ini sesuai dengan struktur DB Anda
                                                                    if($resultAdmins->num_rows > 0){
                                                                        while ($rowAdmin = $resultAdmins->fetch_assoc()) {
                                                                            ?>
                                                                            <option value="<?php echo htmlspecialchars($rowAdmin['adminId']); ?>"
                                                                                <?php echo $rowAdmin['adminId'] == $row['adminId'] ? 'selected' : ''; ?>>
                                                                                <?php echo htmlspecialchars($rowAdmin['adminId']); ?>
                                                                            </option>
                                                                            <?php
                                                                        }
                                                                    }
                                                                ?>
                                                            </select>
                                                            <input class="btn btn-sm btn-primary marginTop" type="submit" value="Change Admin">
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php
                                                // ** Bagian untuk change status ** (Sudah ada di kode Anda)
                                                if (isset($_GET['status'])) {
                                                    $status = $_GET['status'];
                                                    if ($status == 'VERIFIED' || $status == 'NOT VERIFIED') {
                                                        $newStatus = $status;
                                                        $updateStatusQuery = "UPDATE akun SET status = ? WHERE akunId = ?";
                                                        $stmtStatus = $conn->prepare($updateStatusQuery);
                                                        $stmtStatus->bind_param('si', $newStatus, $row['akunId']);
                                                        $stmtStatus->execute();
                                                        $stmtStatus->close();
                                                    }
                                                }

                                                // ** Bagian untuk mengubah adminId ** setelah form disubmit
                                                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                                    foreach ($_POST as $key => $value) {
                                                        // Mengecek jika key sesuai dengan pola adminChange_akunId
                                                        if (strpos($key, 'adminChange_') === 0) {
                                                            $akunId = str_replace('adminChange_', '', $key); // Mendapatkan akunId dari key
                                                            $newAdminId = $value;

                                                            // Update adminId untuk akun tertentu
                                                            $updateAdminQuery = "UPDATE akun SET adminId = ? WHERE akunId = ?";
                                                            $stmtAdmin = $conn->prepare($updateAdminQuery);
                                                            $stmtAdmin->bind_param('ii', $newAdminId, $akunId);
                                                            $stmtAdmin->execute();
                                                            $stmtAdmin->close();
                                                        }
                                                    }
                                                }
                                            }
                                        }  
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        //untuk admin 
                    } elseif ($pageAkun == 'admin') {
                        ?>
                            <div>                                
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#AddAdminModal">Add Admin</button>
                                <!-- Modal untuk add Admin -->
                                <div class="modal fade" id="AddAdminModal" tabindex="-1" aria-labelledby="AddAdminModalLabel" aria-hidden="true">
                                    <div class="modal-dialog margin">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="AddAdminModalLabel">Add Admin Form</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <div class="mb-3">
                                                        <div class="custom">
                                                            <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="username" class="form-label">Username<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="email" class="form-label">Email<span style="color: red;">*</span></label>
                                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="password" class="form-label">Password<span style="color: red;">*</span></label>
                                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary" name="submit">Add Admin</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <table class="table table-bordered align-middle" id="members-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>AdminId</th>
                                        <th>Photo</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        // Query untuk mengambil akun berdasarkan adminId yang sedang login
                                        $queryAdmin = "SELECT * FROM admin";
                                        $stmtAdmin = $conn->prepare($queryAdmin);
                                        $stmtAdmin->execute();
                                        $resultAdmin = $stmtAdmin->get_result();

                                        // Jika ada akun ditemukan
                                        if($resultAdmin->num_rows > 0) {
                                            while ($row = $resultAdmin->fetch_assoc()) {
                                                ?>  
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['adminId']); ?></td>
                                                    <td><img src="Images/photoProfile/<?php echo htmlspecialchars($row['profileAdmin']); ?>" alt="Profile" class="rounded-circle" width="40" height="40"></td>
                                                    <td><?php echo htmlspecialchars($row['usernameAdmin']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['emailAdmin']); ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['adminId'] != 1){ // Cek jika bukan adminId 1 (SuperAdmin)
                                                                ?>
                                                                <a href="admin.php?pageAkun=admin&delete=<?php echo $row['adminId']; ?>" class="btn btn-danger">Delete</a>
                                                                <?php
                                                            }
                                                        ?>                                                    
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        <?php
                    } 
                    ?>
                </div>

                    <?php
                    //untuk data personal 
                } elseif ($page == 'personal') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Data Personal</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="container mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button class="btn btn-outline-primary active" id="btn-members">Users</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5>Data Personal</h5>
                            </div>
                        </div>
                        <table class="table table-bordered align-middle" id="members-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Gender</th>
                                    <th>Place and date of birth</th>
                                    <th>Handphone Number</th>
                                    <th>Home Address</th>
                                    <th>Current Address</th>
                                    <th>NIK</th>
                                </tr>
                            </thead>
                                <?php 
                                    $queryAkun1 = "SELECT username FROM akun WHERE adminId = ?";
                                    $stmtAkun1 = $conn->prepare($queryAkun1);
                                    $stmtAkun1->bind_param('i', $userId);
                                    $stmtAkun1->execute();
                                    $resultAkun1 = $stmtAkun1->get_result();

                                    $queryPersonal = "SELECT * FROM databio WHERE adminId = ?";
                                    $stmtPersonal = $conn->prepare($queryPersonal);
                                    $stmtPersonal->bind_param('i', $userId);
                                    $stmtPersonal->execute();
                                    $resultPersonal = $stmtPersonal->get_result();

                                    if($resultAkun1->num_rows > 0) {
                                        while ($row = $resultAkun1->fetch_assoc()) {
                                            if($resultPersonal->num_rows > 0) {
                                                while ($rowPersonal = $resultPersonal->fetch_assoc()) {
                                                ?>
                                                <hr>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    
                                                    <td><?php echo htmlspecialchars($rowPersonal['namaLengkap']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['kelamin']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['tanggalLahir']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['noHP']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['alamat']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['alamatNow']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowPersonal['nik']); ?></td>
                                                    
                                                <?php
                                                }
                                            }
                                        }
                                    }  
                                ?>
                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 ">
                            <div>
                                <h5>Image Personal</h5>
                            </div>
                        </div>
                        <table class="table table-bordered align-middle" id="members-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>KTP Image</th>
                                    <th>Selfie with KTP</th>
                                </tr>
                            </thead>
                                <?php 
                                    $queryAkun1 = "SELECT username FROM akun WHERE adminId = ?";
                                    $stmtAkun1 = $conn->prepare($queryAkun1);
                                    $stmtAkun1->bind_param('i', $userId);
                                    $stmtAkun1->execute();
                                    $resultAkun1 = $stmtAkun1->get_result();

                                    $queryPersonal = "SELECT * FROM databio WHERE adminId = ?";
                                    $stmtPersonal = $conn->prepare($queryPersonal);
                                    $stmtPersonal->bind_param('i', $userId);
                                    $stmtPersonal->execute();
                                    $resultPersonal = $stmtPersonal->get_result();

                                    if($resultAkun1->num_rows > 0) {
                                        while ($row = $resultAkun1->fetch_assoc()) {
                                            if($resultPersonal->num_rows > 0) {
                                                while ($rowPersonal = $resultPersonal->fetch_assoc()) {
                                                ?>
                                                <hr>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    
                                                    <td>
                                                        <div class="card">
                                                            <img src="Images/data/photoKTP/<?php echo $rowPersonal['photoKTP']; ?>" class="card-img-top image-thumbnail">
                                                            <div class="card-body">
                                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#imageModal1" data-bs-img-src="Images/data/selfieKTP/<?php echo $rowPersonal['photoKTPSelfie']; ?>">
                                                                    Preview
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="modal fade" id="imageModal1" tabindex="-1" aria-labelledby="imageModalLabel1" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="imageModalLabel1">Preview Image</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Gambar yang akan ditampilkan di modal -->
                                                                        <img id="modalImage" src="Images/data/photoKTP/<?php echo $rowPersonal['photoKTP']; ?>" class="img-fluid" alt="Image Preview">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    </td>
                                                    <td>
                                                        <div class="card">
                                                            <img src="Images/data/selfieKTP/<?php echo $rowPersonal['photoKTPSelfie']; ?>" class="card-img-top image-thumbnail" alt="Photo">
                                                            <div class="card-body">
                                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-img-src="Images/data/selfieKTP/<?php echo $rowPersonal['photoKTPSelfie']; ?>">
                                                                    Preview
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <!-- Modal untuk menampilkan gambar -->
                                                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="imageModalLabel">Preview Image</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Gambar yang akan ditampilkan di modal -->
                                                                        <img id="modalImage" src="Images/data/selfieKTP/<?php echo $rowPersonal['photoKTPSelfie']; ?>" class="img-fluid" alt="Image Preview">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php
                                                }
                                            }
                                        }
                                    }  
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    //untuk data vehicle
                } elseif ($page == 'vehicle') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Dashboard</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>54</h5>
                                <p>Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>79</h5>
                                <p>Projects</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>124</h5>
                                <p>Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded income">
                                <h5>$6K</h5>
                                <p>Income</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Projects -->
                    <div class="mb-4">
                        <h5>Recent Projects</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Project Title</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>UI/UX Design</td>
                                    <td>UI Team</td>
                                    <td class="text-info">Review</td>
                                </tr>
                                <tr>
                                    <td>Web Development</td>
                                    <td>Frontend</td>
                                    <td class="text-success">In Progress</td>
                                </tr>
                                <tr>
                                    <td>Ushop App</td>
                                    <td>Mobile Team</td>
                                    <td class="text-danger">Pending</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- New Customers -->
                    <div>
                        <h5>New Customers</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                        </ul>
                    </div>

                    <?php
                    //untuk tax
                } elseif ($page == 'tax') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Dashboard</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>54</h5>
                                <p>Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>79</h5>
                                <p>Projects</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>124</h5>
                                <p>Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded income">
                                <h5>$6K</h5>
                                <p>Income</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Projects -->
                    <div class="mb-4">
                        <h5>Recent Projects</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Project Title</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>UI/UX Design</td>
                                    <td>UI Team</td>
                                    <td class="text-info">Review</td>
                                </tr>
                                <tr>
                                    <td>Web Development</td>
                                    <td>Frontend</td>
                                    <td class="text-success">In Progress</td>
                                </tr>
                                <tr>
                                    <td>Ushop App</td>
                                    <td>Mobile Team</td>
                                    <td class="text-danger">Pending</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- New Customers -->
                    <div>
                        <h5>New Customers</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                        </ul>
                    </div>

                    <?php
                    //untuk point
                } elseif ($page == 'point') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Dashboard</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>54</h5>
                                <p>Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>79</h5>
                                <p>Projects</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded">
                                <h5>124</h5>
                                <p>Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box p-3 rounded income">
                                <h5>$6K</h5>
                                <p>Income</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Projects -->
                    <div class="mb-4">
                        <h5>Recent Projects</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Project Title</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>UI/UX Design</td>
                                    <td>UI Team</td>
                                    <td class="text-info">Review</td>
                                </tr>
                                <tr>
                                    <td>Web Development</td>
                                    <td>Frontend</td>
                                    <td class="text-success">In Progress</td>
                                </tr>
                                <tr>
                                    <td>Ushop App</td>
                                    <td>Mobile Team</td>
                                    <td class="text-danger">Pending</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- New Customers -->
                    <div>
                        <h5>New Customers</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Customer">
                                <span>Lewis S. Cunningham - CEO Excerpt</span>
                            </li>
                        </ul>
                    </div>

                    <?php
                    //untuk complaint
                } elseif ($page == 'complaint') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Complaint</h4>
                    </div>

                    <!-- Stats Section -->
                    <div class="container mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button class="btn btn-outline-primary active" id="btn-members">Users</button>
                            </div>
                        </div>
                        <table class="table table-bordered align-middle" id="members-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Title Complaint</th>
                                    <th>Complaint</th>
                                </tr>
                            </thead>
                                <?php 
                                    $queryAkun1 = "SELECT username FROM akun WHERE adminId = ?";
                                    $stmtAkun1 = $conn->prepare($queryAkun1);
                                    $stmtAkun1->bind_param('i', $userId);
                                    $stmtAkun1->execute();
                                    $resultAkun1 = $stmtAkun1->get_result();

                                    $queryContact = "SELECT * FROM contact WHERE adminId = ?";
                                    $stmtContact = $conn->prepare($queryContact);
                                    $stmtContact->bind_param('i', $userId);
                                    $stmtContact->execute();
                                    $resultContact = $stmtContact->get_result();

                                    if($resultAkun1->num_rows > 0) {
                                        while ($row = $resultAkun1->fetch_assoc()) {
                                            if($resultContact->num_rows > 0) {
                                                while ($rowContact = $resultContact->fetch_assoc()) {
                                                ?>
                                                <hr>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowContact['titleContact']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowContact['massageContact']); ?></td>
                                                <?php
                                                }
                                            }
                                        }
                                    }  
                                ?>
                            </tbody>
                        </table>
                    <?php
                }
                    ?>

            </div>
        </div>
    </body>
</html>
