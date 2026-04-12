<?php
include 'koneksi.php';

$sql = "SELECT * FROM relasi ORDER BY id ASC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
