<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add':
            addVoucher();
            break;
        case 'edit':
            editVoucher();
            break;
        case 'delete':
            deleteVoucher();
            break;
    }
}

function addVoucher() {
    global $conn;
    
    try {
        // Handle file upload
        $shopLogo = uploadImage($_FILES['shopLogo']);
        
        $query = "INSERT INTO vouchers (adminId, shopName, shopLogo, voucherValue, pointCost, 
                                      description, maxStock, expiryDate) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                 
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issiisis', 
            $_SESSION['user_id'],
            $_POST['shopName'],
            $shopLogo,
            $_POST['voucherValue'],
            $_POST['pointCost'],
            $_POST['description'],
            $_POST['maxStock'],
            $_POST['expiryDate']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Voucher added successfully';
        } else {
            throw new Exception('Failed to add voucher');
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin.php?page=vouchers');
    exit;
}

function deleteVoucher() {
    global $conn;
    
    try {
        $voucherId = $_POST['voucherId'];
        
        // Get voucher info
        $query = "SELECT shopLogo FROM vouchers WHERE voucherId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherId);
        $stmt->execute();
        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        
        // Delete voucher
        $query = "DELETE FROM vouchers WHERE voucherId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherId);
        
        if ($stmt->execute()) {
            // Delete image file
            if ($voucher['shopLogo']) {
                @unlink('Images/shops/' . $voucher['shopLogo']);
            }
            $_SESSION['success'] = 'Voucher deleted successfully';
        } else {
            throw new Exception('Failed to delete voucher');
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin.php?page=vouchers');
    exit;
}

function uploadImage($file) {
    $targetDir = "Images/shops/";
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to upload image');
    }
    
    return $fileName;
}
?>