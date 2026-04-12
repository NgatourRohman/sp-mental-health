<?php
header('Content-Type: application/json');
include 'koneksi.php';

$email = $_GET['email'] ?? '';

if (!$email) {
    echo json_encode([]);
    exit;
}

// Ambil data dari database
$query = "SELECT hasil_diagnosa AS kondisi, deskripsi, saran, rekomendasi, 
                 DATE_FORMAT(waktu_diagnosa, '%d-%m-%Y %H:%i') AS tanggal 
          FROM hasil_diagnosa 
          WHERE email = ? 
          ORDER BY waktu_diagnosa DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
