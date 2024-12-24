<?php
session_start();
include 'config.php'; // File koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAccount'])) {
    // Ambil ID pengguna dari sesi
    $akunId = $_SESSION['user_id'] ?? null;

    if (!$akunId) {
        die("Error: Anda belum login.");
    }

    // Query untuk menghapus akun berdasarkan `akunId`
    $sql_delete = "DELETE FROM akun WHERE akunId = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    if ($stmt_delete === false) {
        die("Error: Gagal menyiapkan query. " . $conn->error);
    }

    // Bind parameter
    $stmt_delete->bind_param("i", $akunId);

    // Eksekusi query
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            // Hapus sesi pengguna
            session_destroy();
            header("Location: login.php?message=account_deleted");
            exit;
        } else {
            echo "Error: Akun tidak ditemukan atau sudah dihapus.";
        }
    } else {
        echo "Error: Gagal menghapus akun. " . $stmt_delete->error;
    }

    $stmt_delete->close();
} else {
    echo "Error: Akses tidak valid.";
}
?>
