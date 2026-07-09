<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$result = $supabase->fetch("users", "select=id,nama,email,role,created_at&order=created_at.asc");

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

echo json_encode($result['data'] ?? []);
