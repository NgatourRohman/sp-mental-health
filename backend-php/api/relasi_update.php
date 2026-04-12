<?php
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$updated = 0;

foreach ($data as $item) {
    if (!isset($item['id'], $item['bobot'])) continue;
    
    $id = (int)$item['id'];
    $bobot = (float)$item['bobot'];
    
    $stmt = $conn->prepare("UPDATE relasi SET bobot=? WHERE id=?");
    $stmt->bind_param("di", $bobot, $id);
    
    if ($stmt->execute()) {
        $updated++;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "$updated relasi diperbarui"
]);
