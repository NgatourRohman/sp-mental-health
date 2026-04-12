<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include 'koneksi.php';

$response = [];

try {
    if (!$conn) {
        throw new Exception("Koneksi database gagal.");
    }

    $queryUser = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    $queryGangguan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM gangguan");
    $queryGejala = mysqli_query($conn, "SELECT COUNT(*) AS total FROM gejala");

    if (!$queryUser || !$queryGangguan || !$queryGejala) {
        throw new Exception("Salah satu query gagal dieksekusi.");
    }

    $response = [
        "users" => mysqli_fetch_assoc($queryUser)['total'] ?? 0,
        "gangguan" => mysqli_fetch_assoc($queryGangguan)['total'] ?? 0,
        "gejala" => mysqli_fetch_assoc($queryGejala)['total'] ?? 0
    ];
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        "users" => 0,
        "gangguan" => 0,
        "gejala" => 0,
        "error" => $e->getMessage()
    ];
}

echo json_encode($response);
