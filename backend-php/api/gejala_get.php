<?php
header("Content-Type: application/json");
include 'koneksi.php';

try {
    $sql = "
        SELECT 
            g.id,
            g.kode_gejala,
            g.nama_gejala,
            g.kode_gangguan,
            gg.nama AS nama_gangguan,
            g.aktif
        FROM gejala g
        LEFT JOIN gangguan gg ON g.kode_gangguan = gg.kode
        ORDER BY g.kode_gangguan, g.kode_gejala
    ";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data: " . $e->getMessage()
    ]);
}
