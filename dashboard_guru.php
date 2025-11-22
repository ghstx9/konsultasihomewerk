<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'konselor') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM konselor WHERE id_pengguna = '$user_id'";
$res = $conn->query($sql);
$guru = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Konselor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>.lexend-font { font-family: "Lexend", sans-serif; }</style>
</head>
<body class="bg-slate-50 lexend-font">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="font-bold text-blue-600 text-xl">Panel Konselor</h1>
        <div class="flex gap-4 items-center">
            <span class="text-slate-500 text-sm">Halo, <?= $guru['nama_lengkap'] ?></span>
            <a href="logout.php" class="text-red-500 text-sm hover:underline">Keluar</a>
        </div>
    </nav>

    <div class="container mx-auto p-10 text-center">
        <div class="bg-white p-10 rounded-2xl shadow-sm max-w-2xl mx-auto">
            <div class="text-6xl mb-4">👋</div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Selamat Datang, Bapak/Ibu Guru!</h2>
            <p class="text-slate-500">
                Dashboard konselor sedang dalam pengembangan. <br>
                Anda dapat melihat jadwal dan data siswa di sini nantinya.
            </p>
        </div>
    </div>

</body>
</html>
