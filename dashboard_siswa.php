<?php
session_start();
include 'config/database.php';

// 1. Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Ambil ID Siswa dari tabel 'siswa' berdasarkan id_pengguna
// Perhatikan: kolom Anda 'id_pengguna', bukan 'user_id'
$sql_student = "SELECT id, nama_lengkap FROM siswa WHERE id_pengguna = '$user_id'";
$res_student = $conn->query($sql_student);
$student = $res_student->fetch_assoc();

if (!$student) {
    die("Data siswa tidak ditemukan. Pastikan akun user sudah terhubung ke tabel siswa.");
}

$id_siswa = $student['id'];

// 3. LOGIKA KUNCI: Cek tabel 'detail_keluarga_siswa'
$sql_check = "SELECT * FROM detail_keluarga_siswa WHERE id_siswa = '$id_siswa'";
$check_res = $conn->query($sql_check);

if ($check_res->num_rows == 0) {
    // Jika kosong -> Lempar ke Form Wajib
    header("Location: modul_asesmen.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>
        .lexend-font {
            font-family: "Lexend", sans-serif;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            }
    </style>
</head>
<body class="bg-slate-50 lexend-font">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="font-bold text-blue-600 text-xl">Aplikasi BK</h1>
        <div class="flex gap-4 items-center">
            <a href="logout.php" class="text-red-500 text-sm hover:underline">Keluar</a>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-blue-600 text-white p-6 rounded-xl mb-6">
            <h2 class="text-2xl font-bold">Selamat Datang!</h2>
            <p class="opacity-90">Halo, <?= $student['nama_lengkap'] ?></span>! Silahkan pilih menu di bawah.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-md transition">
                <h3 class="font-bold text-lg text-slate-700 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days-icon lucide-calendar-days"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg> Jadwal Konsultasi</h3>
                <p class="text-slate-500 text-sm mt-2">Lihat jadwal atau buat janji temu dengan konselor.</p>
                <button class="mt-4 bg-blue-100 text-blue-700 px-4 py-2 rounded text-sm font-bold">Buka Jadwal</button>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-md transition">
                <h3 class="font-bold text-lg text-slate-700 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-pen-icon lucide-notebook-pen"><path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/><path d="M2 6h4"/><path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><path d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/></svg> Hasil Asesmen</h3>
                <p class="text-slate-500 text-sm mt-2">Lihat grafik gaya belajar dan minat karirmu.</p>
                <button class="mt-4 bg-green-100 text-green-700 px-4 py-2 rounded text-sm font-bold">Lihat Grafik</button>
            </div>
        </div>
    </div>

</body>
</html>