<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$id = post_value('id');

if (!$id) {
    json_response(["status" => "error", "message" => "ID gejala tidak ditemukan."], 400);
}

$result = $supabase->delete("gejala", "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal menghapus data gejala.")], 500);
}

echo json_encode(["status" => "success", "message" => "Data gejala berhasil dihapus."]);
