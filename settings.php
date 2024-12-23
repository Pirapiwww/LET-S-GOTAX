<?php
// Tentukan halaman default
$page = isset($_GET['page']) ? $_GET['page'] : 'account';

// Menampilkan semua error agar lebih mudah mendeteksi masalah
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memulai sesi untuk menyimpan data pengguna
session_start();

// Include konfigurasi database
include 'config.php';

// Periksa apakah pengguna sudah login
$isLoggedIn = isset($_SESSION['user_id']);

//column tabel akun
$userPhoto = '';
$username = '';
$email = '';
$status = '';

//column tabel databio
$namaLengkap = '';
$alamat = '';
$alamatNow = '';
$nik = '';
$selfieKTP = '';
$photoKTP = '';
$noHP = '';
$kelamin = '';
$tanggalLahir = '';

$adminId = '1';


//untuk menyimppan error
$error = '';

// Jika pengguna login
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];

    // Query untuk mengambil data dari tabel akun
    $query = "SELECT photoProfile, username, email, status FROM akun WHERE akunId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userPhoto = $user['photoProfile'];
        $username = $user['username'];
        $email = $user['email'];
        $status = $user['status'];
    }
    $stmt->close();

    // Query untuk mengambil data dari tabel databio
    $query2 = "SELECT * FROM databio WHERE akunId = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param('i', $userId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2->num_rows > 0) {
        $dataUser = $result2->fetch_assoc();
        $namaLengkap = $dataUser['namaLengkap'];
        $alamat = $dataUser['alamat'];
        $alamatNow = $dataUser['alamatNow'];
        $nik = $dataUser['nik'];
        $selfieKTP = $dataUser['photoKTPSelfie'];
        $photoKTP = $dataUser['photoKTP'];
        $noHP = $dataUser['noHP'];
        $kelamin = $dataUser['kelamin'];
        $tanggalLahir = $dataUser['tanggalLahir'];
    }
    $stmt2->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Inisialisasi user ID
        $userId = $_SESSION['user_id'];

        // ** Bagian 1: Update Username, Email, dan Password (account settings) **
        if (isset($_POST['username']) || isset($_POST['email']) || isset($_POST['password'])) {
            $newUsername = $_POST['username'] ?? null;
            $newEmail = $_POST['email'] ?? null;
            $newPassword = $_POST['password'] ?? null;

            if (!empty($newPassword)) {
                $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordQuery = "UPDATE akun SET password = ? WHERE akunId = ?";
                $stmt = $conn->prepare($updatePasswordQuery);
                $stmt->bind_param('si', $newPassword, $userId);
                $stmt->execute();
                $stmt->close();
                echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
            } else if (!empty($newUsername)) {
                $updateQuery = "UPDATE akun SET username = ? WHERE akunId = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $newUsername, $userId);
                $stmt->execute();
                $stmt->close();
                echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
            } else if (!empty($newEmail)) {
                $updateQuery = "UPDATE akun SET email = ? WHERE akunId = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $newEmail, $userId);
                $stmt->execute();
                $stmt->close();
                echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
            }
        }

        // ** Bagian 2 : untuk add namaLengkap, alamat, nik, selfieKTP, photoKTP, noHP, dan kelamin **
        if (isset($_POST['namaLengkap']) && isset($_POST['nik']) && isset($_POST['noHP']) && isset($_POST['kelamin']) && isset($_POST['alamat']) && isset($_POST['tanggalLahir']) && isset($_POST['alamatNow']) && isset($_FILES['photoKTP']) && $_FILES['photoKTP']['error'] == 0 && isset($_FILES['selfieKTP']) && $_FILES['selfieKTP']['error'] == 0) {

            $newNamaLengkap = $_POST['namaLengkap'] ?? null;
            $newAlamat = $_POST['alamat'] ?? null;
            $newAlamatNow = $_POST['alamatNow'] ?? null;
            $newNIK = $_POST['nik'] ?? null;
            $newNoHP = $_POST['noHP'] ?? null;
            $newKelamin = $_POST['kelamin'] ?? null;
            $newTanggalLahir = $_POST['tanggalLahir'] ?? null;
            $adminId = 1;

            $newStatus = 'ON PROGRESS';

            // Validasi NIK (16 digit)
            if (!empty($newNIK) && !preg_match('/^\d{16}$/', $newNIK)) {
                $error = "NIK harus terdiri dari 16 digit angka.";
            }
            // Validasi No HP (10 hingga 12 digit)
            if (!empty($newNoHP) && !preg_match('/^\d{10,12}$/', $newNoHP)) {
                $error = "Nomor HP harus terdiri dari 10 hingga 12 digit angka.";
            }

            if (empty($error)) {
                if ($result2->num_rows == 0) {
                    $addQuery = "INSERT INTO databio (akunId, adminId, namaLengkap, alamat, nik, noHP, kelamin, alamatNow, tanggalLahir) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($addQuery);
                    $stmt->bind_param('iisssssss', $userId, $adminId, $newNamaLengkap, $newAlamat, $newNIK, $newNoHP, $newKelamin, $newAlamatNow, $newTanggalLahir);
                    $stmt->execute();
                    $stmt->close();

                    $updateStatus = "UPDATE akun SET status = ? WHERE akunId = ?";
                    $stmtStatus = $conn->prepare($updateStatus);
                    $stmtStatus->bind_param('si', $newStatus, $userId);
                    $stmtStatus->execute();
                    $stmtStatus->close();

                    echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
                } else {
                    $error = 'Tidak bisa mengubah data personal apabila sudah di submit';
                }
            }

            // Bagian untuk Photo KTP
            $targetDir1 = "Images/data/photoKTP/"; // Folder penyimpanan file
            $fileName1 = time() . "_" . basename($_FILES["photoKTP"]["name"]); // Nama file baru (dengan timestamp untuk unik)
            $targetFilePath1 = $targetDir1 . $fileName1; // Lokasi lengkap file
            $fileType1 = pathinfo($targetFilePath1, PATHINFO_EXTENSION); // Ekstensi file

            // Bagian untuk Selfie KTP
            $targetDir2 = "Images/data/selfieKTP/"; // Folder penyimpanan file
            $fileName2 = time() . "_" . basename($_FILES["selfieKTP"]["name"]); // Nama file baru (dengan timestamp untuk unik)
            $targetFilePath2 = $targetDir2 . $fileName2; // Lokasi lengkap file
            $fileType2 = pathinfo($targetFilePath2, PATHINFO_EXTENSION); // Ekstensi file

            // Validasi jenis file (hanya gambar)
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'bmp');

            // Cek apakah file Photo KTP dan Selfie KTP sudah ada di database
            $query = "SELECT photoKTP, photoKTPSelfie FROM databio WHERE akunId = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            // Cek apakah kedua file sudah ada
            if ($row['photoKTP'] != null || $row['photoKTPSelfie'] != null) {
                $error = 'Tidak bisa mengubah data personal apabila sudah di submit';
            } else {
                // Proses foto KTP dan Selfie KTP hanya jika belum diupload sebelumnya
                if (in_array(strtolower($fileType1), $allowTypes) && in_array(strtolower($fileType2), $allowTypes)) {
                    // Pindahkan file baru untuk Photo KTP
                    if (move_uploaded_file($_FILES["photoKTP"]["tmp_name"], $targetFilePath1)) {
                        // Simpan nama file baru ke database
                        $updateFileQuery = "UPDATE databio SET photoKTP = ? WHERE akunId = ?";
                        $stmt = $conn->prepare($updateFileQuery);
                        $stmt->bind_param('si', $fileName1, $userId);
                        $stmt->execute();
                    } else {
                        echo "Maaf, terjadi kesalahan saat mengunggah file KTP.";
                    }

                    // Pindahkan file baru untuk Selfie KTP
                    if (move_uploaded_file($_FILES["selfieKTP"]["tmp_name"], $targetFilePath2)) {
                        // Simpan nama file baru ke database
                        $updateFileQuery2 = "UPDATE databio SET photoKTPSelfie = ? WHERE akunId = ?";
                        $stmt2 = $conn->prepare($updateFileQuery2);
                        $stmt2->bind_param('si', $fileName2, $userId);
                        $stmt2->execute();
                        $stmt2->close();
                        echo "File berhasil diupload, file lama dihapus.";
                    } else {
                        echo "Maaf, terjadi kesalahan saat mengunggah file Selfie KTP.";
                    }
                } else {
                    echo "Hanya file gambar (JPG, PNG, JPEG, GIF, BMP) yang diperbolehkan untuk KTP dan Selfie KTP.";
                }
            }
        }

        // ** Bagian 4: untuk Update Foto Profil **
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
                $query = "SELECT photoProfile FROM akun WHERE akunId = ?";
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
                    $updateFileQuery = "UPDATE akun SET photoProfile = ? WHERE akunId = ?";
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

        // ** Bagian 5 : untuk add massage **
        if (isset($_POST['usernameContact']) && isset($_POST['title']) && isset($_POST['massage'])) {
            // Ambil data dari input
            $username = $_POST['usernameContact'] ?? null;
            $title = $_POST['title'] ?? null;
            $massage = $_POST['massage'] ?? null;

            // Default Admin ID
            $adminId = 1;

            // Validasi input
            if (!$title || !$massage) {
                die("Error: Title atau pesan tidak boleh kosong.");
            }

            // Query untuk memasukkan data ke tabel contact
            $sql_insert = "INSERT INTO contact (adminId, titleContact, massageContact) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert === false) {
                die("Error: Gagal menyiapkan query. " . $conn->error);
            }

            // Bind parameter
            $stmt_insert->bind_param("iss", $adminId, $title, $massage);

            // Eksekusi query
            if ($stmt_insert->execute()) {
                echo "Pesan berhasil disimpan ke dalam database.";
            } else {
                echo "Error: Gagal menyimpan pesan. " . $stmt_insert->error;
            }

            // Tutup statement
            $stmt_insert->close();
        } else {
            echo "Error: Data input tidak lengkap.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="JS Files/settings.js"></script>
    <link href="CSS Files/settings.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="home.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z" />
                                    </svg><span class="spanCustom"> Home</span>
                                </a></li>
                            <?php
                            if ($status == 'VERIFIED') {
                            ?>
                                <li><a class="dropdown-item" href="tax.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                                            <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17s2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276" />
                                            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.8.8 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155s4.037-.084 5.592-.155A1.48 1.48 0 0 0 15 9.611v-.413q0-.148-.03-.294l-.335-1.68a.8.8 0 0 0-.43-.563 1.8 1.8 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3z" />
                                        </svg>
                                        <span class="spanCustom">Tax</span>
                                    </a></li>
                                <li><a class="dropdown-item" href="point.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                            <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z" />
                                            <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z" />
                                        </svg>
                                        <span class="spanCustom">Point</span>
                                    </a></li>
                            <?php
                            }
                            ?>
                            <li><a class="dropdown-item" href="settings.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
                                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
                                    </svg>
                                    <span class="spanCustom">Settings</span>
                                </a></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                    </svg>
                                    <span class="spanCustom">Log Out</span>
                                </a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="mt-5">
        <!-- Formulir Settings -->
        <div class="container mt-5">
            <div class="main-container justify-content-center mt-5">
                <div class="row">
                    <div class="col-md-3 sidebar">
                        <div class="text-center mb-4">
                            <img src="Images/photoProfile/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="rounded-circle" width="150" height="150">
                            <h5 class="mb-0 fw-bold spacing"><?php echo htmlspecialchars($username); ?></h5>
                            <h5 class="mt-3">
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
                            </h5>
                            <!-- Button untuk membuka modal -->
                            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#changeImageModal">Change</button>

                            <!-- Modal untuk upload gambar -->
                            <div class="modal fade" id="changeImageModal" tabindex="-1" aria-labelledby="changeImageModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
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
                        <div class="list-group">
                            <a href="settings.php?page=account" class="list-group-item list-group-item-action <?= $page == 'account' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                                </svg>
                                <span class="spanCustom">Account</span>
                            </a>
                            <a href="settings.php?page=personal" class="list-group-item list-group-item-action <?= $page == 'personal' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard2-data" viewBox="0 0 16 16">
                                    <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5z" />
                                    <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z" />
                                    <path d="M10 7a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V9a1 1 0 0 0-1-1" />
                                </svg>
                                <span class="spanCustom">Data Personal</span>
                            </a>
                            <a href="settings.php?page=contact" class="list-group-item list-group-item-action <?= $page == 'contact' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z" />
                                </svg>
                                <span class="spanCustom">Contact Us</span>
                            </a>
                        </div>
                        <p style="padding-top: 20px;"><a href="#" class="text-danger">Delete Account</a></p>
                    </div>
                    <?php
                    //untuk account 
                    if ($page == 'account') {
                    ?>
                        <div class="col-md-9">
                            <h3 class="mb-4">Account Settings</h3>
                            <hr>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <div class="custom">
                                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Change Username (optional)</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Change Email (optional)</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Change Password (optional)</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your new Password">
                                </div>
                                <button type="submit" class="btn btn-primary" name="submit">Save Changes</button>
                            </form>
                        </div>

                    <?php
                        // untuk data personal
                    } elseif ($page == 'personal') {
                    ?>
                        <div class="col-md-9">
                            <h3 class="mb-4">Data Personal Settings</h3>
                            <hr>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <div>
                                        NOTE :
                                        <ul>
                                            <li>Please fill in the personal data form to change the account status to "VERIFIED"</li>
                                            <li>Accounts with "VERIFIED" status will get access to the "Tax" segment</li>
                                            <li>Every tax payment in the "Tax" segment will get points (see the "Point" segment for more information)</li>
                                            <li>Tax and point segments will not appear in the menu if personal data has not been filled in or the account status is "NOT VERIFIED"</li>
                                            <li>Personal data can only be submitted once</li>
                                            <li>After filling in the personal data status data to be 'ON PROGRESS'. Wait until it becomes 'VERIFIED', if it becomes 'NOT VERIFIED', it means there is an error. Please refill personal data if the account status becomes 'NOT VERIFIED' again.</li>
                                        </ul>
                                        <hr>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="custom">
                                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="namaLengkap" class="form-label">Full Name (KTP)<span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="namaLengkap" name="namaLengkap" value="<?php echo $namaLengkap; ?>" placeholder="Enter your full name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="kelamin" class="form-label">Gender (KTP)<span style="color: red;">*</span></label>
                                    <select id="kelamin" name="kelamin" class="form-control" required>
                                        <option value="" disabled selected>Select your gender</option>
                                        <option value="LAKI-LAKI" <?php echo ($kelamin == 'LAKI-LAKI') ? 'selected' : ''; ?>>Male</option>
                                        <option value="PEREMPUAN" <?php echo ($kelamin == 'PEREMPUAN') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggalLahir" class="form-label">Place and date of birth (KTP)<span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="tanggalLahir" name="tanggalLahir" value="<?php echo $tanggalLahir; ?>" placeholder="Enter your place and date of birth" required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Address (KTP)<span style="color: red;">*</span></label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" maxlength="160" placeholder="Enter your address"><?php echo $alamat; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="alamatNow" class="form-label">Current Address<span style="color: red;">*</span></label>
                                    <textarea class="form-control" id="alamatNow" name="alamatNow" rows="2" maxlength="160" placeholder="Enter your current address"><?php echo $alamatNow; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="noHP" class="form-label">Phone Number<span style="color: red;">*</span></label>
                                    <input type="text" name="noHP" id="noHP" class="form-control" value="<?php echo $noHP; ?>" placeholder="Enter exactly 10-12 digits of Phone Number" pattern="^\d{10,12}$" required>
                                    <small id="numberHelp" class="form-text text-muted">Please enter exactly 10-12 digits of Handphone Number (numeric only).</small>
                                </div>
                                <div class="mb-3">
                                    <label for="nik" class="form-label">NIK (KTP)<span style="color: red;">*</span></label>
                                    <input type="text" name="nik" id="nik" class="form-control" value="<?php echo $nik; ?>" placeholder="Enter exactly 16 digits of NIK" pattern="^\d{16}$" required>
                                    <small id="numberHelp" class="form-text text-muted">Please enter exactly 16 digits of NIK (numeric only).</small>
                                </div>
                                <div class="mb-3">
                                    <label for="photoKTP" class="form-label">KTP Image<span style="color: red;">*</span></label>
                                    <input type="file" name="photoKTP" class="form-control" id="photoKTP" accept="image/*" required>
                                    <small id="imageHelp" class="form-text text-muted">Only image files are allowed (JPG, PNG, GIF).</small>
                                </div>
                                <div class="mb-3">
                                    <label for="selfieKTP" class="form-label">Selfie with KTP<span style="color: red;">*</span></label>
                                    <input type="file" class="form-control" name="selfieKTP" id="selfieKTP" accept="image/*" required>
                                    <small id="imageHelp" class="form-text text-muted">Only image files are allowed (JPG, PNG, GIF).</small>
                                </div>
                                <button type="submit" class="btn btn-primary" id="savePassword">Save Changes</button>
                            </form>
                        </div>

                    <?php
                        // untuk tax history
                    } elseif ($page == 'contact') {
                    ?>
                        <div class="col-md-9">
                            <h3 class="mb-4">Contact Us</h3>
                            <hr>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <div class="custom">
                                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="usernameContact" class="form-label">Username :</label>
                                    <input type="text" class="form-control" id="usernameContact" name="usernameContact" value=<?php echo htmlspecialchars($username); ?> required>
                                </div>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Massage Title :</label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Enter Massage Title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="massage" class="form-label">Massage :</label>
                                    <textarea class="form-control" id="massage" name="massage" rows="2" maxlength="160" placeholder="Enter your massage"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" id="savePassword">Send Massage</button>
                            </form>
                        </div>
                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</body>

</html>