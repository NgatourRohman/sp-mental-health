<?php
$conn = new mysqli("localhost", "root", "", "sistem-pakar");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Data admin
$nama = "Arthur";
$email = "arthur@example.com";
$password = password_hash("arthur123", PASSWORD_DEFAULT); // password = admin123
$role = "admin";

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $password, $role);

if ($stmt->execute()) {
    echo "Admin berhasil ditambahkan.";
} else {
    echo "Gagal: " . $stmt->error;
}

$stmt->close();
$conn->close();
