<?php
require_once 'bootstrap.php';

$supabase = get_supabase();

$id = get_value('id');
if (!$id) {
    json_response(["success" => false, "message" => "ID tidak valid."], 400);
}

$result = $supabase->delete("users", "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["success" => false, "message" => supabase_error($result, "Gagal menghapus user.")], 500);
}

echo json_encode(["success" => true, "message" => "User berhasil dihapus."]);
