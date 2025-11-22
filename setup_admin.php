<?php
include 'config/database.php';

echo "<h1>Setup Admin & Database Check</h1>";
$check_table = $conn->query("SHOW TABLES LIKE 'konselor'");
if ($check_table->num_rows > 0) {
    echo "<p style='color:green'>[OK] Tabel 'konselor' ditemukan.</p>";
} else {
    echo "<p style='color:red'>[WARNING] Tabel 'konselor' TIDAK ditemukan. Pastikan Anda sudah membuatnya sesuai skema.</p>";
}

$admin_email = 'admin@sekolah.id';
$admin_pass_raw = 'admin123';
$admin_pass_hash = password_hash($admin_pass_raw, PASSWORD_DEFAULT);
$check_admin = $conn->query("SELECT id FROM user WHERE email = '$admin_email'");

if ($check_admin->num_rows > 0) {
    // Update password biar jadi hash
    $conn->query("UPDATE user SET kata_sandi = '$admin_pass_hash', peran = 'admin' WHERE email = '$admin_email'");
    echo "<p style='color:green'>[OK] Akun Admin ($admin_email) password di-update ke HASH.</p>";
} else {
    // Buat baru
    $stmt = $conn->prepare("INSERT INTO user (email, kata_sandi, peran) VALUES (?, ?, 'admin')");
    $stmt->bind_param("ss", $admin_email, $admin_pass_hash);
    if ($stmt->execute()) {
        echo "<p style='color:green'>[OK] Akun Admin ($admin_email) BERHASIL dibuat.</p>";
    } else {
        echo "<p style='color:red'>[ERROR] Gagal membuat admin: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<p>Setup Selesai. Silakan <a href='index.php'>Login disini</a>.</p>";
?>
