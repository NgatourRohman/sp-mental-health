<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$id = post_value('id');

if (!$id) {
    json_response(["status" => "error", "message" => "ID tidak ditemukan"], 400);
}

$result = $supabase->delete("gangguan", "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal menghapus gangguan.")], 500);
}

echo json_encode(["status" => "success", "message" => "Data gangguan berhasil dihapus"]);
