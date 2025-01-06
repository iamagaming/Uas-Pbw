<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $newFileName = $current_image; // Ambil gambar yang ada

    // Jika ada file baru diupload
    if(!empty($_FILES['image']['name'])) {
        // Proses upload gambar baru
        $target_dir = "assets/images/products/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $newFileName = time() . '_' . uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $newFileName;
        
        // Validasi file
        $uploadOk = 1;
        
        if(isset($_FILES["image"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check === false) {
                $error = "File bukan gambar.";
                $uploadOk = 0;
            }
        }
        
        if ($_FILES["image"]["size"] > 5000000) {
            $error = "Ukuran file terlalu besar (maksimal 5MB).";
            $uploadOk = 0;
        }
        
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Hanya file JPG, JPEG & PNG yang diizinkan.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            // Hapus gambar lama jika ada
            if(!empty($current_image) && file_exists($target_dir . $current_image)) {
                unlink($target_dir . $current_image);
            }
            
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $error = "Maaf, terjadi kesalahan saat upload file.";
                $uploadOk = 0;
            }
        }
    }

    if (!isset($error)) {
        // Update database
        $sql = "UPDATE products SET 
                title = ?, 
                author = ?,
                category_id = ?,
                description = ?,
                price = ?,
                stock = ?,
                image_url = ?
                WHERE product_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissssi", 
            $_POST['title'],
            $_POST['author'],
            $_POST['category_id'],
            $_POST['description'],
            $_POST['price'],
            $_POST['stock'],
            $newFileName,
            $product_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Produk berhasil diupdate!";
            header("Location: products.php");
            exit();
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
    }
} 