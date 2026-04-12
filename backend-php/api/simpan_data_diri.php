<?php
header('Content-Type: application/json');
include 'koneksi.php';

$email = $_POST['user_email'] ?? '';
$nama = $_POST['nama'] ?? '';
$tgl_lahir = $_POST['tgl_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$no_telp = $_POST['no_telp'] ?? '';

// Validasi
if (!$email || !$nama || !$tgl_lahir || !$jenis_kelamin || !$alamat || !$no_telp) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
    exit;
}

// Cek apakah data user sudah ada
$stmt = $conn->prepare("SELECT * FROM user_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE user_profile SET nama=?, tgl_lahir=?, jenis_kelamin=?, alamat=?, no_telp=? WHERE email=?");
    $stmt->bind_param("ssssss", $nama, $tgl_lahir, $jenis_kelamin, $alamat, $no_telp, $email);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO user_profile (email, nama, tgl_lahir, jenis_kelamin, alamat, no_telp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $email, $nama, $tgl_lahir, $jenis_kelamin, $alamat, $no_telp);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
