<?php
require_once __DIR__ . "/../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../functions/fonnte.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Method tidak diperbolehkan."
    ]);
    exit;
}

$nama = trim($_POST["nama"] ?? "");
$email = trim($_POST["email"] ?? "");
$no_hp = trim($_POST["no_hp"] ?? "");
$alamat = trim($_POST["alamat"] ?? "");
$password = $_POST["password"] ?? "";

if ($nama === "" || $email === "" || $no_hp === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Nama, email, nomor HP, dan password wajib diisi."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Format email tidak valid."
    ]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode([
        "status" => "error",
        "message" => "Password minimal 6 karakter."
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email sudah terdaftar."
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (nama, email, no_hp, alamat, password)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssss",
    $nama,
    $email,
    $no_hp,
    $alamat,
    $hashedPassword
);

if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan data pengguna."
    ]);
    exit;
}

$pesan = "Halo {$nama} 👋\n\n"
       . "Selamat datang di Sistem Autentikasi.\n"
       . "Registrasi akun Anda berhasil.\n\n"
       . "Email: {$email}\n\n"
       . "Silakan login untuk mengakses dashboard.";

$wa = kirimWhatsApp($no_hp, $pesan);

echo json_encode([
    "status" => "success",
    "message" => "Pendaftaran berhasil. Notifikasi WhatsApp diproses.",
    "whatsapp" => $wa["success"] ? "terkirim" : "gagal",
    "whatsapp_response" => $wa["response"]
]);
