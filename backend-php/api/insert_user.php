<?php
$conn = new mysqli("localhost", "root", "", "sistem-pakar");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Data user (siswa)
$nama = "Siswa";
$email = "siswa@example.com";
$password = password_hash("siswa123", PASSWORD_DEFAULT); // password = siswa123
$role = "siswa";

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $password, $role);

if ($stmt->execute()) {
    echo "Siswa berhasil ditambahkan.";
} else {
    echo "Gagal: " . $stmt->error;
}

$stmt->close();
$conn->close();
