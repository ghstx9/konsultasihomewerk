<?php
session_start();
include 'config/database.php';

// Enforce login check
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'siswa') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student ID
$sql_student = "SELECT id, nama_lengkap FROM siswa WHERE id_pengguna = '$user_id'";
$res_student = $conn->query($sql_student);
$student = $res_student->fetch_assoc();
$id_siswa = $student['id'];

// Fetch mental health history
$sql_history = "SELECT skor, skor_numerik, terakhir_diperbarui, ringkasan_hasil 
                FROM hasil_asesmen 
                WHERE id_siswa = '$id_siswa' AND kategori = 'kesehatan_mental' 
                ORDER BY terakhir_diperbarui ASC";
$res_history = $conn->query($sql_history);

$dates = [];
$scores = [];
$history_rows = [];

while ($row = $res_history->fetch_assoc()) {
    $dates[] = date('d M Y', strtotime($row['terakhir_diperbarui']));
    // Fallback if numerical score is null (for old records)
    $val = ($row['skor_numerik'] !== null) ? $row['skor_numerik'] : ($row['skor'] == 'Stabil' ? 80 : 40);
    $scores[] = $val;
    $history_rows[] = $row;
}

// Latest analysis
$latest = end($history_rows);
$trend_text = "Pertahankan kesehatan mentalmu!";
if (count($scores) >= 2) {
    $diff = $scores[count($scores)-1] - $scores[count($scores)-2];
    if ($diff > 5) $trend_text = "Ada peningkatan positif dari asesmen sebelumnya. Bagus!";
    elseif ($diff < -5) $trend_text = "Skor kamu sedikit menurun. Jangan ragu untuk bercerita ke guru BK ya.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Progress Kesehatan Mental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .lexend-font { font-family: "Lexend", sans-serif; }
    </style>
</head>
<body class="bg-slate-50 lexend-font min-h-screen text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="dashboard_siswa.php" class="p-2 hover:bg-slate-100 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <h1 class="font-bold text-xl">Progress Mental</h1>
            </div>
            <span class="text-slate-500 text-sm"><?= htmlspecialchars($student['nama_lengkap']) ?></span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6 space-y-8">
        
        <!-- Top Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Skor Terbaru</p>
                <h3 class="text-3xl font-bold <?= ($latest['skor_numerik'] ?? 0) >= 60 ? 'text-teal-600' : 'text-rose-500' ?>">
                    <?= $latest['skor_numerik'] ?? '-' ?>
                </h3>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Status</p>
                <h3 class="text-xl font-bold text-slate-700"><?= $latest['skor'] ?? 'Belum ada data' ?></h3>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Trend</p>
                <p class="text-sm text-slate-600 font-medium"><?= $trend_text ?></p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-8">
                <h3 class="font-bold text-lg text-slate-800">Grafik Perkembangan</h3>
                <a href="modul_asesmen_3.php" class="text-sm font-bold text-teal-600 hover:underline">Update Asesmen</a>
            </div>
            <div class="h-80 w-full">
                <?php if (count($scores) > 0): ?>
                    <canvas id="mentalChart"></canvas>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-20"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        <p>Belum ada data history asesmen.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h3 class="font-bold text-slate-800">Riwayat Lengkap</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Skor</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach (array_reverse($history_rows) as $row): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-slate-600"><?= date('d M Y, H:i', strtotime($row['terakhir_diperbarui'])) ?></td>
                            <td class="px-6 py-4">
                                <span class="font-bold <?= ($row['skor_numerik'] ?? 0) >= 60 ? 'text-teal-600' : 'text-rose-500' ?>">
                                    <?= $row['skor_numerik'] ?? '-' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= $row['skor'] ?></td>
                            <td class="px-6 py-4 text-sm text-slate-400">
                                <?php 
                                    $answers = json_decode($row['ringkasan_hasil'], true);
                                    if ($answers && $answers['q5_bullying'] == 'Ya') echo '⚠️ Terdeteksi Bullying';
                                    else echo '-';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        <?php if (count($scores) > 0): ?>
        const ctx = document.getElementById('mentalChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(20, 184, 166, 0.2)');
        gradient.addColorStop(1, 'rgba(20, 184, 166, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Skor Kesehatan Mental',
                    data: <?= json_encode($scores) ?>,
                    borderColor: '#14b8a6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#14b8a6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 12,
                        titleFont: { size: 10 },
                        bodyFont: { weight: 'bold' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
