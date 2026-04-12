<?php
class SupabaseHelper {
    private $url;
    private $key;
    private $timeout = 30;

    public function __construct($url, $key) {
        $this->url = rtrim($url, '/');
        $this->key = $key;
    }

    private function execute($endpoint, $method = 'GET', $data = null) {
        $url = $this->url . "/rest/v1/" . $endpoint;
        $ch = curl_init($url);

        $headers = [
            "apikey: " . $this->key,
            "Authorization: Bearer " . $this->key,
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) return ["status" => "error", "message" => "cURL Error: " . $err];

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            return [
                "status" => "error",
                "code" => $httpCode,
                "message" => $decoded['message'] ?? "API Error"
            ];
        }

        return ["status" => "success", "data" => $decoded];
    }

    public function fetch($table, $query = "") {
        return $this->execute($table . ($query ? "?" . $query : ""), 'GET');
    }

    public function insert($table, $data) {
        return $this->execute($table, 'POST', $data);
    }

    public function update($table, $data, $criteria) {
        return $this->execute($table . "?" . $criteria, 'PATCH', $data);
    }

    public function delete($table, $criteria) {
        return $this->execute($table . "?" . $criteria, 'DELETE');
    }
}
