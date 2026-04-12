<?php
header("Content-Type: application/json");
include 'koneksi.php';

$kode = $_GET['kode'] ?? '';

if (!$kode) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT g.nama, gj.nama_gejala 
        FROM gejala gj 
        JOIN gangguan g ON gj.kode_gangguan = g.kode 
        WHERE g.kode = ?
    ");
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
