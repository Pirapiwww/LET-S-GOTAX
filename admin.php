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
$isLoggedIn = isset($_SESSION['admin_id']);

//column tabel Admin
$userPhoto = '';
$username = '';
$email = '';

//untuk menyimppan error
$error = '';

// Jika pengguna login
if ($isLoggedIn) {
    $userId = $_SESSION['admin_id'];

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

    if (isset($_GET['status']) && isset($_GET['akunId'])) {
        $status = $_GET['status'];
        $akunId = $_GET['akunId'];

        // Cek apakah status sudah diperbarui
        if (!isset($_SESSION['status_updated']) || $_SESSION['status_updated'] !== $akunId) {
            if ($status == 'VERIFIED') {
                $newStatus = 'VERIFIED';
                // Update status dalam database
                $updateStatusQuery = "UPDATE akun SET status = ? WHERE akunId = ?";
                $stmtStatus = $conn->prepare($updateStatusQuery);
                $stmtStatus->bind_param('si', $newStatus, $akunId);
                $stmtStatus->execute();
                $stmtStatus->close();
            } elseif ($status == 'NOT VERIFIED') {
                $newStatus = 'NOT VERIFIED';
                // Update status dalam database
                $updateStatusQuery = "UPDATE akun SET status = ? WHERE akunId = ?";
                $stmtStatus = $conn->prepare($updateStatusQuery);
                $stmtStatus->bind_param('si', $newStatus, $akunId);
                $stmtStatus->execute();
                $stmtStatus->close();

                // Hapus semua data tabel databio di akunId = ?
                $deleteQuery = "DELETE FROM databio WHERE akunId = ?";
                $stmtDelete = $conn->prepare($deleteQuery);
                $stmtDelete->bind_param('i', $akunId);
                $stmtDelete->execute();
                $stmtDelete->close();
            }
            header("Location: admin.php?pageAkun=user"); // Redirect ke halaman admin
            exit; // Pastikan tidak ada kode lain yang dieksekusi
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Inisialisasi user ID
        $userId = $_SESSION['admin_id'];

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

        // ** Bagian untuk add data tax **
        if (isset($_POST['namaTax']) && isset($_POST['plat']) && isset($_POST['pkb']) && isset($_POST['lastPay']) && isset($_POST['statusPajak']) && isset($_POST['dendaPajak']) && isset($_POST['nextPay']) && isset($_POST['jenisKendaraan']) && isset($_POST['tipeKendaraan'])) {
            // Mengambil data dari POST
            $namaTax = $_POST['namaTax'] ?? null;
            $plat = $_POST['plat'] ?? null;
            $PKB = $_POST['pkb'] ?? null;
            $statusPajak = $_POST['statusPajak'] ?? null;
            $dendaPajak = $_POST['dendaPajak'] ?? null;
            $lastPay = $_POST['lastPay'] ?? null;
            $nextPay = $_POST['nextPay'] ?? null;
            $jenisKendaraan = $_POST['jenisKendaraan'] ?? null;
            $tipeKendaraan = $_POST['tipeKendaraan'] ?? null;
            
            // Bersihkan nilai PKB dan dendaPajak dari karakter non-angka
            $PKB_clean = preg_replace('/[^0-9]/', '', $PKB);
            $dendaPajak_clean = preg_replace('/[^0-9]/', '', $dendaPajak);

            // Inisialisasi SWDKLLJ berdasarkan jenis kendaraan
            $SWDKLLJ = '';
            $SWDKLLJ_clean = '';
            if ($tipeKendaraan == 'MOTOR') {
                $SWDKLLJ = 'Rp. 32.000,-';
                $SWDKLLJ_clean = preg_replace('/[^0-9]/', '', $SWDKLLJ);
            } elseif ($tipeKendaraan == 'MOBIL') {
                $SWDKLLJ = 'Rp. 100.000,-';
                $SWDKLLJ_clean = preg_replace('/[^0-9]/', '', $SWDKLLJ);
            }

            // Validasi input
            if (empty($plat) || empty($namaTax)) {
                echo "Plat Kendaraan dan Nama Pajak wajib diisi!";
                exit;
            }
            
            // Validasi input
            if (empty($plat)) {
                $error = 'Vehicle Plat is required!';
            } elseif (empty($namaTax)) {
                $error = 'Tax Name is required!';
            } elseif (empty($PKB)) {
                $error = 'PKB is required!';
            } elseif (empty($statusPajak)) {
                $error = 'Tax Status is required!';
            } elseif (empty($dendaPajak)) {
                $error = 'Tax Fine is required!';
            } elseif (empty($lastPay)) {
                $error = 'Last Payment Date is required!';
            } elseif (empty($nextPay)) {
                $error = 'Next Payment Date is required!';
            }

            // Validasi format Plat Kendaraan (contoh format XX YYYY XX)
            $platPattern = '/^[A-Z]{2}\s\d{4}\s[A-Z]{2}$/';
            if (!empty($plat) && !preg_match($platPattern, $plat)) {
                $error = 'Vehicle Plat format is incorrect. It should follow the format XX YYYY XX (uppercase).';
            }

            // Validasi format uang (PKB dan Denda Pajak)
            $moneyPattern = '/^Rp\.\s?\d{1,3}(\.\d{3})*(,-)$/';
            if (!empty($PKB) && !preg_match($moneyPattern, $PKB)) {
                $error = 'PKB format is incorrect. It should follow the format Rp. xxx.xxx,-';
            }
            if (!empty($dendaPajak) && !preg_match($moneyPattern, $dendaPajak)) {
                $error = 'Tax Fine format is incorrect. It should follow the format Rp. xxx.xxx,-';
            }

            // Periksa apakah data pajak dengan plat kendaraan yang sama sudah ada
            $sql_check = "SELECT * FROM tax WHERE platKendaraan = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $plat);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Jika data pajak dengan plat kendaraan yang sama sudah ada
                echo "Data tax dengan plat kendaraan ini sudah terdaftar!";
            } else {
                // Hitung total pajak
                $totalTax_clean = $SWDKLLJ_clean + $PKB_clean + $dendaPajak_clean;
                $totalTax = "Rp. " . number_format($totalTax_clean, 0, ',', '.') . ",-";

                // Masukkan data baru ke tabel tax
                $sql_insert = "INSERT INTO tax (adminId, namaLengkap, platKendaraan, totalPajak, lastPay, status, dendaPajak, nextPay, jenisKendaraan, tipeKendaraan, PKB, SWDKLLJ) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);

                if ($stmt_insert) {
                    // Bind parameter dengan tipe yang sesuai
                    $stmt_insert->bind_param(
                        "issssssssss", // tipe data parameter
                        $userId,       // adminId
                        $namaTax,      // namaLengkap
                        $plat,         // platKendaraan
                        $totalTax,     // totalPajak
                        $lastPay,      // lastPay
                        $statusPajak,  // status
                        $dendaPajak,   // dendaPajak
                        $nextPay,      // nextPay
                        $jenisKendaraan, // jenisKendaraan
                        $tipeKendaraan,  // tipeKendaraan
                        $PKB,          // PKB
                        $SWDKLLJ       // SWDKLLJ
                    );
                    
                    // Eksekusi query insert
                    if ($stmt_insert->execute()) {
                        echo "Data tax berhasil ditambahkan!";
                    } else {
                        echo "Gagal menambahkan data: " . $stmt_insert->error;
                    }

                    // Tutup statement insert
                    $stmt_insert->close();
                } else {
                    echo "Gagal menyiapkan query insert: " . $conn->error;
                }
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
                        <a href="admin.php?page=Kendaraan" class="<?= $page == 'Kendaraan' ? 'active' : '' ?>">
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
                                                        } elseif ($row['status'] == 'ON PROGRESS') {
                                                            echo '<td><span class="badge bg-secondary">' . htmlspecialchars($row['status']) . '</span></td>';
                                                        }
                                                        ?>
                                                    <!-- Action untuk mengubah status -->
                                                    <td>
                                                        <p class = "text-danger"><?php echo htmlspecialchars($error); ?></p>
                                                        <?php 
                                                            $checkDatabio = "SELECT * FROM databio WHERE akunId = ?";  // Menambahkan titik koma di sini
                                                            $stmtDatabio = $conn->prepare($checkDatabio);
                                                            $stmtDatabio->bind_param('i', $row['akunId']); 
                                                            $stmtDatabio->execute();
                                                            $resultDatabio = $stmtDatabio->get_result();
                                                            

                                                            if($resultDatabio->num_rows > 0){
                                                                ?>
                                                                <a href="admin.php?akunId=<?php echo $row['akunId']; ?>&status=VERIFIED" class="btn btn-sm btn-primary">Verified Status</a>
                                                                <?php
                                                            } else {
                                                                $error = 'Cannot Verified';
                                                            }
                                                        ?>
                                                        <a href="admin.php?akunId=<?php echo $row['akunId']; ?>&status=NOT VERIFIED" class="btn btn-sm btn-danger">Unverified Status</a>
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
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
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
                    //untuk data Kendaraan
                } elseif ($page == 'Kendaraan') {
                    ?>

                    
                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Data Vehicle</h4>
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
                                <h5>Data Vehicle</h5>
                            </div>
                        </div>
                        <table class="table table-bordered align-middle" id="members-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Username Account</th>
                                    <th>Vehicle Owner Name</th>
                                    <th>Vehicle Chassis Number</th>
                                    <th>Vehicle Engine Number</th>
                                    <th>Vehicle Plat</th>
                                    <th>Vehicle Type</th>
                                    <th>Kind of Vehicle</th>
                                </tr>
                            </thead>
                                <hr>
                                <?php 
                                // Query untuk mendapatkan username dari tabel akun
                                $queryAkun1 = "SELECT username FROM akun WHERE adminId = ?";
                                $stmtAkun1 = $conn->prepare($queryAkun1);
                                $stmtAkun1->bind_param('i', $userId);
                                $stmtAkun1->execute();
                                $resultAkun1 = $stmtAkun1->get_result();

                                // Simpan username untuk digunakan nanti
                                $usernames = [];
                                if ($resultAkun1->num_rows > 0) {
                                    while ($row = $resultAkun1->fetch_assoc()) {
                                        $usernames[] = $row['username'];
                                    }
                                }

                                // Query kendaraan untuk adminId
                                $queryKendaraan = "SELECT * FROM kendaraan WHERE adminId = ?";
                                $stmtKendaraan = $conn->prepare($queryKendaraan);
                                $stmtKendaraan->bind_param('i', $userId);
                                $stmtKendaraan->execute();
                                $resultKendaraan = $stmtKendaraan->get_result();

                                if (!empty($usernames)) {
                                    foreach ($usernames as $username) {
                                        if ($resultKendaraan->num_rows > 0) {
                                            while ($rowKendaraan = $resultKendaraan->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($username); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['namaPemilik']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['No_Rangka']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['No_Mesin']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['No_Plat']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['jenisKendaraan']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowKendaraan['tipeKendaraan']); ?></td>
                                                </tr>
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
                    //untuk tax
                } elseif ($page == 'tax') {
                    ?>

                    <!-- Header -->
                    <div class="header d-flex justify-content-between align-items-center mb-4">
                        <h4>Data Tax</h4>
                    </div>

                    <div class="container mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>                                
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#AddTaxModal">Add Data</button>
                                <!-- Modal untuk add data Tax -->
                                <div class="modal fade" id="AddTaxModal" tabindex="-1" aria-labelledby="AddTaxModalLabel" aria-hidden="true">
                                    <div class="modal-dialog margin">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="AddTaxModalLabel">Add Tax Data Form</h5>
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
                                                        <label for="namaTax" class="form-label">Full Name<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="namaTax" name="namaTax" placeholder="Enter full name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="plat" class="form-label">Vehicle Plat<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="plat" name="plat" placeholder="Enter Kendaraan Plat" required>
                                                        <small id="numberHelp" class="form-text text-muted">Format : XX YYYY XX (Capital)</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="jenisKendaraan" class="form-label">Vehicle Type<span style="color: red;">*</span></label>
                                                        <select class="form-select" id="jenisKendaraan" name="jenisKendaraan" required>
                                                            <option value="">Select Type</option>
                                                            <option value="PRIBADI">Private Vehicle</option>
                                                            <option value="UMUM">Public Vehicle</option>
                                                            <option value="NIAGA">Commercial Vehicle</option>
                                                            <option value="DINAS">Official Vehicle</option>
                                                            <option value="KHUSUS">Special Vehicle</option>
                                                            <option value="LISTRIK">Electric Vehicle</option>
                                                        
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="tipeKendaraan" class="form-label">Vehicle Type<span style="color: red;">*</span></label>
                                                        <select class="form-select" id="tipeKendaraan" name="tipeKendaraan" required>
                                                            <option value="">Select Kind of Vehicle</option>
                                                            <option value="MOTOR">MOTORCYCLE</option>
                                                            <option value="MOBIL">CAR</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="pkb" class="form-label">PKB VALUE<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="pkb" name="pkb" placeholder="Enter Total Tax" required>
                                                        <small id="numberHelp" class="form-text text-muted">Format : Rp. xxx.xxx,-</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="lastPay" class="form-label">Latest Payment Date<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="lastPay" name="lastPay" placeholder="Enter Latest Payment Date" required>
                                                        <small id="numberHelp" class="form-text text-muted">Format : YYYY-MM-DD</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="dendaPajak" class="form-label">Tax Fine<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="dendaPajak" name="dendaPajak" placeholder="Enter Tax Fine" required>
                                                        <small id="numberHelp" class="form-text text-muted">Format : Rp. xxx.xxx,-</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="statusPajak" class="form-label">Status<span style="color: red;">*</span></label>
                                                        <select class="form-select" id="statusPajak" name="statusPajak" required>
                                                            <option value="">Select Status</option>
                                                            <option value="ON TIME">ON TIME</option>
                                                            <option value="OVERDUE">OVERDUE</option>
                                                        </select>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="nextPay" class="form-label">Next Payment Date<span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="nextPay" name="nextPay" placeholder="EnterNext Payment Date" required>
                                                        <small id="numberHelp" class="form-text text-muted">Format : YYYY-MM-DD</small>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary" name="submit">Add Data</button>
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
                                        <th>Vehicle Plat</th>
                                        <th>Vehicle Type</th>
                                        <th>Kind of Vehicle</th>
                                        <th>Full Name</th>
                                        <th>PKB</th>
                                        <th>SWDKLLJ</th>
                                        <th>Tax Fine</th>
                                        <th>Total Tax</th>
                                        <th>Tax Status</th>
                                        <th>Latest Payment</th>
                                        <th>Next Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        // Query untuk mengambil akun berdasarkan adminId yang sedang login
                                        $queryTax= "SELECT * FROM tax";
                                        $stmtTax = $conn->prepare($queryTax);
                                        $stmtTax->execute();
                                        $resultTax = $stmtTax->get_result();

                                        // Jika ada akun ditemukan
                                        if($resultTax->num_rows > 0) {
                                            while ($row = $resultTax->fetch_assoc()) {
                                                ?>  
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['platKendaraan']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['jenisKendaraan']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['tipeKendaraan']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['namaLengkap']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['PKB']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['SWDKLLJ']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['dendaPajak']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['totalPajak']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['lastPay']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nextPay']); ?></td>
                                                    
                                                </tr>
                                                <?php
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
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
                } elseif ($page == 'point') {
                    ?>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link" id="btn-vouchers" data-bs-toggle="tab" data-bs-target="#voucherTab">
                                Voucher Management
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link active" id="btn-points" data-bs-toggle="tab" data-bs-target="#pointTab">
                                Point Management
                            </button>
                        </li>
                    </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Points Tab -->
                    <div class="tab-pane fade show active" id="pointTab">
                        <div class="search-container mb-3">
                            <input type="text" class="form-control search-input" placeholder="Search" />
                        </div>

                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            <th>last update</th>
                                            <th>total point</th>
                                            <th>type</th>
                                            <th>status</th>
                                            <th>action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT p.*, a.username, ph.type, ph.transactionDate 
                                                FROM point p 
                                                JOIN akun a ON p.akunId = a.akunId
                                                LEFT JOIN point_history ph ON p.akunId = ph.akunId
                                                ORDER BY ph.transactionDate DESC";
                                        $result = $conn->query($query);
                                        $no = 1;
                                        
                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo date('d-m-Y H:i', strtotime($row['transactionDate'])); ?></td>
                                            <td><?php echo $row['totalPoint']; ?> points</td>
                                            <td class="<?php echo $row['type'] == 'earn' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo ucfirst($row['type']); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $row['status'] == 'valid' ? 'active' : 'inactive'; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" onclick="editPoint(<?php echo $row['akunId']; ?>)" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="#" onclick="deletePoint(<?php echo $row['akunId']; ?>)" class="btn btn-sm btn-danger">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav class="pagination-container">
                            <ul class="pagination">
                                <li class="page-item"><a class="page-link" href="#">&lt;</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">4</a></li>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                <li class="page-item"><a class="page-link" href="#">11</a></li>
                                <li class="page-item"><a class="page-link" href="#">12</a></li>
                                <li class="page-item"><a class="page-link" href="#">&gt;</a></li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Vouchers Tab -->
                    <div class="tab-pane fade" id="voucherTab">
                        <div class="search-container mb-3">
                            <input type="text" class="form-control search-input" placeholder="Search" />
                            <button class="btn btn-primary ms-2" onclick="showAddVoucherModal()">Add</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Voucher Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM vouchers ORDER BY created_at DESC";
                                    $result = $conn->query($query);
                                    $no = 1;

                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['voucherName']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($row['startDate'])); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($row['endDate'])); ?></td>
                                            <td><?php echo $row['price']; ?> points</td>
                                            <td><?php echo $row['stock']; ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $row['status'] == 'valid' ? 'active' : 'inactive'; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" onclick="editVoucher(<?php echo $row['voucherId']; ?>)" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="#" onclick="deleteVoucher(<?php echo $row['voucherId']; ?>)" class="btn btn-sm btn-danger">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav class="pagination-container">
                            <ul class="pagination">
                                <li class="page-item"><a class="page-link" href="#">&lt;</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">4</a></li>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                <li class="page-item"><a class="page-link" href="#">11</a></li>
                                <li class="page-item"><a class="page-link" href="#">12</a></li>
                                <li class="page-item"><a class="page-link" href="#">&gt;</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <?php
                }
                ?>

            </div>
        </div>
    </body>
</html>
