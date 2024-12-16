<?php
    session_start();

    // Menghapus semua data sesi
    session_destroy();
    $_SESSION = [];

    // Redirect ke halaman login
    header("Location: login.php");
    exit;
?>
