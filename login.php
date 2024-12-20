<?php
    session_start();
    include 'config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //untuk tabel akun
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query untuk validasi login (akun)
        $sql = "SELECT * FROM akun WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Periksa apakah password di database belum di-hash
            if (!password_verify($password, $user['password'])) {
                // Jika password cocok tetapi belum di-hash, hash password tersebut
                if ($password === $user['password']) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Update password yang di-hash ke database
                    $update_sql = "UPDATE akun SET password = ? WHERE akunId = ?";
                    $stmt_update = $conn->prepare($update_sql);
                    $stmt_update->bind_param("si", $hashed_password, $user['akunId']);
                    $stmt_update->execute();

                    // Login berhasil setelah hashing
                    $_SESSION['user_id'] = $user['akunId'];
                    $_SESSION['username'] = $user['username'];

                    header("Location: home.php");

                    exit;
                } else {
                    $error = "Password incorrect!";
                }
            } else {
                // Jika password sudah di-hash, langsung verifikasi
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['akunId'];
                    $_SESSION['username'] = $user['username'];

                    header("Location: home.php");
                    exit;
                } else {
                    $error = "Password incorrect!";
                }
            }
        } else {
            $error = "Email incorrect or not found!";
        }

        //untuk tabel admin
        $emailAdmin = $_POST['email'];
        $passwordAdmin = $_POST['password'];

        // Query untuk validasi login (admin)
        $sqlAdmin = "SELECT * FROM admin WHERE emailAdmin = ?";
        $stmtAdmin = $conn->prepare($sqlAdmin);
        $stmtAdmin->bind_param("s", $emailAdmin);
        $stmtAdmin->execute();
        $resultAdmin = $stmtAdmin->get_result();

        if ($resultAdmin->num_rows > 0) {
            $userAdmin = $resultAdmin->fetch_assoc();

            // Periksa apakah password di database belum di-hash
            if (!password_verify($passwordAdmin, $userAdmin['passwordAdmin'])) {
                // Jika password cocok tetapi belum di-hash, hash password tersebut
                if ($passwordAdmin === $userAdmin['passwordAdmin']) {
                    $hashed_password = password_hash($passwordAdmin, PASSWORD_DEFAULT);

                    // Update password yang di-hash ke database
                    $update_sql = "UPDATE admin SET passwordAdmin = ? WHERE adminId = ?";
                    $stmt_update = $conn->prepare($update_sql);
                    $stmt_update->bind_param("si", $hashed_password, $userAdmin['adminId']);
                    $stmt_update->execute();

                    // Login berhasil setelah hashing
                    $_SESSION['user_id'] = $user['adminId'];
                    $_SESSION['username'] = $user['usernameAdmin'];

                    header("Location: admin.php");
                    exit;
                } else {
                    $error = "Password incorrect!";
                }
            } else {
                // Jika password sudah di-hash, langsung verifikasi
                if (password_verify($passwordAdmin, $userAdmin['passwordAdmin'])) {
                    $_SESSION['user_id'] = $userAdmin['adminId'];
                    $_SESSION['username'] = $userAdmin['usernameAdmin'];

                    header("Location: admin.php");
                    exit;
                } else {
                    $error = "Password incorrect!";
                }
            }
        } else {
            $error = "Email incorrect or not found!";
        }
    }
?>


<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="CSS Files/loginSignUp.css" rel="stylesheet">
        <script src="JS Files/login.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Cantarell:ital,wght@0,400;0,700;1,400;1,700&family=Cantata+One&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

        <title>LET'S GOTAX</title>
        <link rel="icon" type="images/x-icon" href="images/let's gotax(logo).png">
    </head>
    <body>
        <div class="container login-container d-flex">
            <div id="carouselExample" class="carousel slide col-md-6 p-0 login-carousel" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://via.placeholder.com/600x800?text=Image+1" class="d-block w-100" alt="Image 1">
                    </div>
                    <div class="carousel-item">
                        <img src="https://via.placeholder.com/600x800?text=Image+2" class="d-block w-100" alt="Image 2">
                    </div>
                    <div class="carousel-item">
                        <img src="https://via.placeholder.com/600x800?text=Image+3" class="d-block w-100" alt="Image 3">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <div class="col-md-6 login-form">
                <h1 class="mb-3 text-center">Login</h1><hr>
                <form method="POST" action="">
                    <div class="mb-2">
                        <div class="custom">
                            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="custom">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <div class="mb-2" >
                        <div class="custom">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="loginBtn"><button type="submit" class="btn btn-primary w-100">Login</button></div>
                    <p class="custom2">Don't have an account? <a href="signUp.php">Sign Up</a></p>
                </form>
            </div>
        </div>
    </body>
</html>
