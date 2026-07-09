<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$data = json_decode(file_get_contents("php://input"), true);
$updated = 0;

if (!is_array($data)) {
    json_response(["status" => "error", "message" => "Payload tidak valid."], 400);
}

foreach ($data as $item) {
    if (!isset($item['id'], $item['bobot'])) continue;
    
    $id = $item['id'];
    $bobot = (float)$item['bobot'];

    $result = $supabase->update("relasi", ["bobot" => $bobot], "id=eq." . urlencode($id));
    if ($result['status'] === 'success') {
        $updated++;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "$updated relasi diperbarui"
]);
