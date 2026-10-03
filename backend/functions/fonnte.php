<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/fonnte.php";

function kirimWhatsApp($nomor, $pesan) {
    global $conn;

    $nomor = preg_replace('/[^0-9]/', '', $nomor);

    // Nomor Indonesia: 08xxxxxxxx -> 628xxxxxxxx.
    if (substr($nomor, 0, 1) === "0") {
        $nomor = "62" . substr($nomor, 1);
    }

    $status = "error";
    $responseText = "";

    if (FONNTE_TOKEN === "TOKEN_FONNTE_ANDA" || trim(FONNTE_TOKEN) === "") {
        $responseText = "Token Fonnte belum diisi di config/fonnte.php.";
    } else {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => FONNTE_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => [
                "target" => $nomor,
                "message" => $pesan,
                "countryCode" => "62"
            ],
            CURLOPT_HTTPHEADER => [
                "Authorization: " . FONNTE_TOKEN
            ]
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($curlError) {
            $responseText = "cURL error: " . $curlError;
        } else {
            $responseText = $response ?: "Fonnte tidak mengembalikan response.";
        }

        $decoded = json_decode($responseText, true);

        if (is_array($decoded)) {
            if (($decoded["status"] ?? false) === true || ($decoded["status"] ?? "") === "true") {
                $status = "success";
            } else {
                $status = "error";

                if (!empty($decoded["reason"])) {
                    $responseText = "Fonnte: " . $decoded["reason"];
                }
            }
        } elseif ($httpCode >= 200 && $httpCode < 300) {
            $status = "success";
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO log_whatsapp (no_tujuan, pesan, status, response)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $nomor, $pesan, $status, $responseText);
    $stmt->execute();

    return [
        "success" => ($status === "success"),
        "response" => $responseText
    ];
}
