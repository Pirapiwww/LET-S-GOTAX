<?php
    include 'config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Path default untuk foto profil
        $photoProfile = 'profileDefault.jpg';

        // Periksa apakah email atau username sudah terdaftar
        $sql_check = "SELECT * FROM akun WHERE email = ? OR username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $email, $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error = "Email atau username sudah terdaftar!";
        } else {
            // Simpan data user dengan gambar default
            $sql_insert = "INSERT INTO akun (username, email, password, photoProfile) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $username, $email, $password, $photoProfile);

            if ($stmt_insert->execute()) {
                header("Location: home.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat mendaftar.";
            }
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
        <script src="JS Files/signUp.js"></script>
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
                <h1 class="mb-3 text-center">Sign Up</h1><hr>
                <form method="POST" action="">
                    <div class="mb-2">
                        <div class="custom">
                            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="custom">
                            <label for="username" class="form-label">Username</label>
                            <input type="username" class="form-control" id="username" name="username" placeholder="Enter your username" required>
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
                    <div><button type="submit" class="btn btn-primary w-100">Sign Up</button></div>
                    <p class="custom2">Have an account? <a href="login.php">Login</a></p>
                </form>
            </div>
        </div>
    </body>
</html>
