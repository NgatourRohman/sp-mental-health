<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Validasi parameter email
if (!isset($_GET['email'])) {
    echo json_encode(["error" => "Parameter email tidak ditemukan"]);
    exit;
}

$email = $_GET['email'];

// 2. Koneksi ke database
$conn = new mysqli("localhost", "root", "", "sistem-pakar"); // Ganti sesuai konfigurasi
if ($conn->connect_error) {
    echo json_encode(["error" => "Koneksi database gagal: " . $conn->connect_error]);
    exit;
}

// 3. Query data diagnosa terbaru
$sql = "SELECT hasil_diagnosa AS kondisi, deskripsi, saran, rekomendasi 
        FROM hasil_diagnosa 
        WHERE email = ? 
        ORDER BY waktu_diagnosa DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Pisahkan saran ke dalam array jika multiline
    $row['saran'] = preg_split('/\r\n|\r|\n/', $row['saran']);

    echo json_encode($row);
} else {
    echo json_encode(["error" => "Tidak ada hasil diagnosa untuk email tersebut."]);
}

$stmt->close();
$conn->close();
