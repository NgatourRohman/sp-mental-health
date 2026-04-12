<?php
include 'koneksi.php';

$id = $_POST['id'] ?? '';
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$id || !$nama || !$email) {
    echo json_encode(["success" => false, "message" => "ID, nama, dan email wajib diisi."]);
    exit;
}

if ($password) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $query = $conn->prepare("UPDATE users SET nama=?, email=?, password=? WHERE id=?");
    $query->bind_param("sssi", $nama, $email, $hashed, $id);
} else {
    $query = $conn->prepare("UPDATE users SET nama=?, email=? WHERE id=?");
    $query->bind_param("ssi", $nama, $email, $id);
}

if ($query->execute()) {
    echo json_encode(["success" => true, "message" => "User berhasil diperbarui."]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal memperbarui user."]);
}
