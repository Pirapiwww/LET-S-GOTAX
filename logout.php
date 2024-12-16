<?php
    session_start();

    // Menghapus semua data sesi
    session_destroy();
    $_SESSION = [];

    // Redirect ke halaman home (guest)
    header("Location: home.php");
    exit;
?>
