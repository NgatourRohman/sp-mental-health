<?php
/**
 * proses_diagnosa.php - Production Version
 * Menampilkan hasil diagnosa mental dengan integrasi ML Service dan Supabase.
 */
header("Content-Type: application/json");
require_once '../core/SupabaseHelper.php';

$supabaseUrl  = getenv("SUPABASE_URL");
$supabaseKey  = getenv("SUPABASE_KEY");
$pythonApiUrl = getenv("PYTHON_API_URL");
$pythonApiKey = getenv("API_KEY_PASKIBRA");

if (!$supabaseUrl || !$supabaseKey || !$pythonApiUrl || !$pythonApiKey) {
    echo json_encode(["status" => "error", "message" => "Environment variables not configured."]);
    exit;
}

$supabase = new SupabaseHelper($supabaseUrl, $supabaseKey);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid method."]);
    exit;
}

$email = filter_var($_POST['user_email'] ?? '', FILTER_SANITIZE_EMAIL);
$jawabanRaw = $_POST['jawaban'] ?? '';

if (!$email || !$jawabanRaw) {
    echo json_encode(["status" => "error", "message" => "Missing required data."]);
    exit;
}

$jawabanArr = json_decode($jawabanRaw, true);
if (!is_array($jawabanArr) || count($jawabanArr) !== 15) {
    echo json_encode(["status" => "error", "message" => "Invalid features count."]);
    exit;
}

// Rate Limiting Logic
$limitResult = $supabase->fetch("rate_limits", "identifier=eq." . urlencode($email));
$now = time();

if ($limitResult['status'] === "success" && !empty($limitResult['data'])) {
    $limitData = $limitResult['data'][0];
    $lastReqTime = strtotime($limitData['last_request']);
    if (($now - $lastReqTime) < 60) {
        if ($limitData['request_count'] >= 5) {
            echo json_encode(["status" => "error", "message" => "Rate limit exceeded. Please wait 1 minute."]);
            exit;
        }
        $supabase->update("rate_limits", ["request_count" => $limitData['request_count'] + 1, "last_request" => date('c')], "identifier=eq." . urlencode($email));
    } else {
        $supabase->update("rate_limits", ["request_count" => 1, "last_request" => date('c')], "identifier=eq." . urlencode($email));
    }
} else {
    $supabase->insert("rate_limits", ["identifier" => $email, "request_count" => 1]);
}

// Smart Caching
$hashInput = hash('sha256', $jawabanRaw);
$cacheResult = $supabase->fetch("diagnosa_cache", "hash_input=eq." . $hashInput);

if ($cacheResult['status'] === "success" && !empty($cacheResult['data'])) {
    $mlResult = $cacheResult['data'][0]['result_json'];
    $isFromCache = true;
} else {
    $isFromCache = false;
}

// Feature Vector Transformation
usort($jawabanArr, function($a, $b) {
    return strcmp($a['kode_gejala'], $b['kode_gejala']);
});

$features = [];
foreach ($jawabanArr as $item) {
    $val = (float)$item['nilai'];
    $features[] = max(1.0, min(5.0, $val));
}

// Inference Call with Retry Logic
if (!$isFromCache) {
    $payload = ["features" => $features, "api_key" => $pythonApiKey];
    $tries = 0; $maxTries = 3; $pyOutput = false;

    while ($tries < $maxTries && $pyOutput === false) {
        $tries++;
        $ch = curl_init($pythonApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $pyOutput = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 500) {
            $pyOutput = false;
            if ($tries < $maxTries) sleep(2 * $tries);
        }
    }

    if (!$pyOutput) {
        echo json_encode(["status" => "error", "message" => "ML Service unavailable."]);
        exit;
    }

    $mlResult = json_decode($pyOutput, true);
    if ($httpCode === 200 && ($mlResult['status'] ?? '') === "success") {
        $supabase->insert("diagnosa_cache", ["hash_input" => $hashInput, "result_json" => $mlResult]);
    }
}

if (($mlResult['status'] ?? '') !== "success") {
    echo json_encode(["status" => "error", "message" => "Analysis failed."]);
    exit;
}

// Persistence and Mapping
$dataML = $mlResult['data'];
$label = $dataML['prediction'];
$kategori = $dataML['kategori'];

$queryFilter = "gangguan=eq." . urlencode($label) . "&tingkat=eq." . urlencode($kategori);
$detailRes = $supabase->fetch("diagnosa_detail", $queryFilter);

$detail = ($detailRes['status'] === 'success' && !empty($detailRes['data'])) 
           ? $detailRes['data'][0] 
           : [
                "deskripsi" => "Analisis untuk Gangguan $label ($kategori) telah selesai.",
                "saran" => "Konsultasikan dengan ahli profesional.",
                "rekomendasi" => "-"
             ];

$dataLog = [
    "email" => $email,
    "hasil_diagnosa" => "Gangguan $label $kategori",
    "deskripsi" => $detail['deskripsi'],
    "saran" => $detail['saran'],
    "rekomendasi" => $detail['rekomendasi'],
    "confidence" => floatval($dataML['confidence'] ?? 0)
];

$supabase->insert("hasil_diagnosa", $dataLog);

echo json_encode([
    "status" => "success",
    "message" => "Diagnosis Complete",
    "data" => [
        "hasil" => $dataLog['hasil_diagnosa'],
        "confidence" => ($dataML['confidence'] ?? 0) . "%",
        "deskripsi" => $dataLog['deskripsi'],
        "saran" => explode("\n", $dataLog['saran']),
        "rekomendasi" => $dataLog['rekomendasi']
    ]
]);
