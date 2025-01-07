<?php
session_start();
require_once "config/database.php";

// Hapus pengecekan admin karena ini untuk user biasa
/*
if(!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header('Location: login.php');
    exit;
}
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        // Validasi input
        $category_name = trim($_POST['category_name']);
        $description = trim($_POST['description']);

        if (empty($category_name)) {
            throw new Exception("Nama kategori harus diisi");
        }

        // Cek apakah kategori sudah ada
        $check_query = "SELECT category_id FROM categories WHERE category_name = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$category_name]);

        if ($check_stmt->rowCount() > 0) {
            throw new Exception("Kategori dengan nama tersebut sudah ada");
        }

        // Insert kategori baru
        $query = "INSERT INTO categories (category_name, description) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$category_name, $description])) {
            $_SESSION['success'] = "Kategori berhasil ditambahkan";
        } else {
            throw new Exception("Gagal menambahkan kategori");
        }

        // Redirect kembali ke categories_list.php
        header('Location: categories_list.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: categories_list.php');
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke categories_list.php
    header('Location: categories_list.php');
    exit;
} 