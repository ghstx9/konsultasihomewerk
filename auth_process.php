<?php
session_start();
include 'config/database.php';

$email = $_POST['email'];
$password = $_POST['password']; // Input dari form login

// Query ke tabel 'user'
$sql = "SELECT * FROM user WHERE email='$email' AND kata_sandi='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Set Session Sesuai Kolom DB Anda
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['peran'] = $row['peran']; // 'siswa' atau 'konselor'
    $_SESSION['email'] = $row['email'];

    // Redirect berdasarkan peran
    if ($row['peran'] == 'siswa') {
        header("Location: dashboard_siswa.php");
    } else if ($row['peran'] == 'konselor') {
        // Nanti buat file dashboard_konselor.php
        echo "Halo Konselor, dashboard Anda sedang dibuat."; 
    } else {
        echo "Halo Admin.";
    }
} else {
    header("Location: index.php?error=1");
}
?>