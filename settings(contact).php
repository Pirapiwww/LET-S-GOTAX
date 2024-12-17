<?php
// Menampilkan semua error agar lebih mudah mendeteksi masalah
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memulai sesi untuk menyimpan data pengguna
session_start();

// Include konfigurasi database
include 'config.php';

// Periksa apakah pengguna sudah login
$isLoggedIn = isset($_SESSION['user_id']);
$userPhoto = '';
$username = '';
$email = '';
$error = '';

// Jika pengguna login
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT photoProfile, username, email FROM akun WHERE akunId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userPhoto = $user['photoProfile'];
        $username = $user['username'];
        $email = $user['email'];
    }
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Inisialisasi user ID
        $userId = $_SESSION['user_id'];
    
        // ** Bagian 1: Update Username, Email, dan Password **
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
            }
            else if (!empty($newUsername)) {
                $updateQuery = "UPDATE akun SET username = ? WHERE akunId = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $newUsername, $userId);
                $stmt->execute();
                $stmt->close();
                echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
            }
            else if (!empty($newEmail)) {
                $updateQuery = "UPDATE akun SET email = ? WHERE akunId = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $newEmail, $userId);
                $stmt->execute();
                $stmt->close();
                echo "<script>window.location.href = window.location.href;</script>"; // Refresh halaman
            }
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
    }
}
?>

<!doctype html>
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
                <a class="navbar-brand me-auto" href="#">
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
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="home.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                                    </svg><span class="spanCustom"> Home</span>
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
                                <li><a class="dropdown-item" href="settings(account).php">
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
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <!-- Formulir Settings -->
        <div class="container mt-5">
            <div class="main-container">
                <div class="row">
                    <div class="col-md-3 sidebar">
                        <div class="text-center mb-4">
                            <img src="Images/photoProfile/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="rounded-circle" width="150" height="150">
                            <h5 class="mb-0 fw-bold spacing"><?php echo htmlspecialchars($username); ?></h5>
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
                            <a href="settings(account).php" class="list-group-item list-group-item-action">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                                </svg>
                                <span class="spanCustom">Account</span>
                            </a>
                            <a href="settings(personal).php" class="list-group-item list-group-item-action">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard2-data" viewBox="0 0 16 16">
                                <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5z"/>
                                <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                                <path d="M10 7a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V9a1 1 0 0 0-1-1"/>
                                </svg>
                                <span class="spanCustom">Data Personal</span>
                            </a>
                            <a href="settings(tax).php" class="list-group-item list-group-item-action">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                                <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17s2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276"/>
                                <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.8.8 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155s4.037-.084 5.592-.155A1.48 1.48 0 0 0 15 9.611v-.413q0-.148-.03-.294l-.335-1.68a.8.8 0 0 0-.43-.563 1.8 1.8 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3z"/>
                                </svg>
                                <span class="spanCustom">Tax History</span>
                            </a>
                            <a href="settings(point).php" class="list-group-item list-group-item-action">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z"/>
                                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z"/>
                                </svg>
                                <span class="spanCustom">Point History</span>
                            </a>
                            <a href="settings(contact).php" class="list-group-item list-group-item-action active">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                </svg>
                                <span class="spanCustom">Contact Us</span>
                            </a>
                        </div>
                        <p style="padding-top: 20px;"><a href="#" class="text-danger">Delete Account</a></p>
                    </div>

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
                                <label for="name" class="form-label">Username :</label>
                                <input type="text" class="form-control" id="username" value = <?php echo htmlspecialchars($username); ?> required>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Massage Title :</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Massage Title" required>
                            </div>
                            <div class="mb-3">
                                <label for="massage" class="form-label">Massage :</label>
                                <textarea class="form-control" id="massage" rows="2" maxlength="160" placeholder="Enter your massage"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="savePassword">Send Massage</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
