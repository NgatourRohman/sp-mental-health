<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$email = filter_var(get_value('email'), FILTER_SANITIZE_EMAIL);

if (!$email) {
    json_response(['status' => 'error', 'message' => 'Email tidak diberikan'], 400);
}

$result = $supabase->fetch("user_profile", "email=eq." . urlencode($email) . "&limit=1");

if ($result['status'] !== 'success') {
    json_response(['status' => 'error', 'message' => supabase_error($result)], 500);
}

$data = $result['data'][0] ?? null;
echo json_encode($data ? ['status' => 'success', 'data' => $data] : ['status' => 'not_found']);
