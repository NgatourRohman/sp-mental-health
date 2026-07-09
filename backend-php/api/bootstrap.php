<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../core/SupabaseHelper.php';

function json_response($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function get_supabase() {
    $supabaseUrl = getenv("SUPABASE_URL");
    $supabaseKey = getenv("SUPABASE_KEY");

    if (!$supabaseUrl || !$supabaseKey) {
        json_response([
            "status" => "error",
            "message" => "SUPABASE_URL dan SUPABASE_KEY belum dikonfigurasi."
        ], 500);
    }

    return new SupabaseHelper($supabaseUrl, $supabaseKey);
}

function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(["status" => "error", "message" => "Invalid method."], 405);
    }
}

function post_value($key, $default = '') {
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function get_value($key, $default = '') {
    return isset($_GET[$key]) ? trim((string) $_GET[$key]) : $default;
}

function supabase_error($result, $fallback = "Database error.") {
    return $result['message'] ?? $fallback;
}
