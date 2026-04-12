<?php
header("Content-Type: application/json");
include 'koneksi.php';

try {
    $result = $conn->query("SELECT * FROM gangguan ORDER BY kode");

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
