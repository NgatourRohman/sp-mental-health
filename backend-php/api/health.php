<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "status" => "ok",
    "service" => "sp-mental-backend",
    "supabase_configured" => (bool) getenv("SUPABASE_URL"),
    "python_api_configured" => (bool) getenv("PYTHON_API_URL")
]);
