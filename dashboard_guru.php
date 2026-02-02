<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'konselor') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql_guru = "SELECT * FROM konselor WHERE id_pengguna = '$user_id'";
$res_guru = $conn->query($sql_guru);
$guru = $res_guru->fetch_assoc();
$id_konselor = $guru['id'];

// Action (Terima/Tolak)
// Action (Terima/Tolak)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_konsul = $_GET['id'];
    $action = $_GET['action'];
    
    // Validasi status yang diperbolehkan
    $status_baru = '';
    if ($action == 'approve') {
        $status_baru = 'disetujui';
    } elseif ($action == 'reject') {
        $status_baru = 'ditolak';
    }

    if ($status_baru) {
        $stmt = $conn->prepare("UPDATE konsultasi SET status=? WHERE id=?");
        $stmt->bind_param("si", $status_baru, $id_konsul);
        $stmt->execute();
    }
    
    header("Location: dashboard_guru.php");
    exit;
}

// Statistik
$today = date('Y-m-d');
$stat_today = $conn->query("SELECT COUNT(*) as total FROM konsultasi WHERE id_konselor='$id_konselor' AND status='disetujui' AND DATE(tanggal_konsultasi) = '$today'")->fetch_assoc()['total'];
$stat_pending = $conn->query("SELECT COUNT(*) as total FROM konsultasi WHERE id_konselor='$id_konselor' AND status='menunggu'")->fetch_assoc()['total'];

// Siswa Prioritas (Yang punya skor 'PERLU PERHATIAN KHUSUS')
$stat_priority = $conn->query("
    SELECT COUNT(DISTINCT ha.id_siswa) as total 
    FROM hasil_asesmen ha
    INNER JOIN (
        SELECT id_siswa, MAX(terakhir_diperbarui) as max_date
        FROM hasil_asesmen
        WHERE kategori = 'kesehatan_mental'
        GROUP BY id_siswa
    ) latest ON ha.id_siswa = latest.id_siswa AND ha.terakhir_diperbarui = latest.max_date
    WHERE ha.kategori = 'kesehatan_mental' AND ha.skor LIKE '%PERLU PERHATIAN KHUSUS%'
")->fetch_assoc()['total'];

// --- ANALYTICS LOGIC ---

// 1. Student Wellness Distribution (from latest 'kesehatan_mental' assessments)
$wellness_stats = [
    'Stabil' => 0,
    'Berisiko' => 0
];
$sql_wellness = "
    SELECT ha.skor 
    FROM hasil_asesmen ha
    INNER JOIN (
        SELECT id_siswa, MAX(terakhir_diperbarui) as max_date
        FROM hasil_asesmen
        WHERE kategori = 'kesehatan_mental'
        GROUP BY id_siswa
    ) latest ON ha.id_siswa = latest.id_siswa AND ha.terakhir_diperbarui = latest.max_date
    WHERE ha.kategori = 'kesehatan_mental'
";
$res_wellness = $conn->query($sql_wellness);
if ($res_wellness && $res_wellness->num_rows > 0) {
    while($row = $res_wellness->fetch_assoc()) {
        if (strpos($row['skor'], 'PERLU PERHATIAN KHUSUS') !== false) {
            $wellness_stats['Berisiko']++;
        } else {
            // Assuming anything not 'PERLU PERHATIAN KHUSUS' is 'Stabil' or neutral for now
            $wellness_stats['Stabil']++;
        }
    }
}

// 2. Trend Indicator: Consultation Requests (Last 7 days vs Previous 7 days)
$date_7_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
$date_14_days_ago = date('Y-m-d H:i:s', strtotime('-14 days'));

// Current Week Count
$sql_trend_curr = "SELECT COUNT(*) as total FROM konsultasi WHERE id_konselor='$id_konselor' AND created_at >= '$date_7_days_ago'";
$trend_curr = $conn->query($sql_trend_curr)->fetch_assoc()['total'];

// Previous Week Count
$sql_trend_prev = "SELECT COUNT(*) as total FROM konsultasi WHERE id_konselor='$id_konselor' AND created_at >= '$date_14_days_ago' AND created_at < '$date_7_days_ago'";
$trend_prev = $conn->query($sql_trend_prev)->fetch_assoc()['total'];

$trend_diff = $trend_curr - $trend_prev;
$trend_text = "";
$trend_color = "text-stone-500";
$trend_icon = "";

if ($trend_diff > 0) {
    $trend_text = "+" . $trend_diff . " dari minggu lalu";
    $trend_color = "text-teal-600";
    $trend_icon = "↑";
} elseif ($trend_diff < 0) {
    $trend_text = $trend_diff . " dari minggu lalu";
    $trend_color = "text-rose-500";
    $trend_icon = "↓";
} else {
    $trend_text = "Stabil dari minggu lalu";
    $trend_color = "text-stone-500";
    $trend_icon = "-";
}


// Query Data

// Permintaan Masuk (Pending) + Cek Prioritas
// Kita join ke tabel siswa, lalu subquery/join ke hasil_asesmen untuk cek status mental TERBARU
$sql_requests = "
    SELECT k.*, s.nama_lengkap, s.tingkat_kelas, s.jurusan,
    (
        SELECT COUNT(*) 
        FROM hasil_asesmen ha
        INNER JOIN (
            SELECT id_siswa, MAX(terakhir_diperbarui) as max_date
            FROM hasil_asesmen
            WHERE kategori = 'kesehatan_mental'
            GROUP BY id_siswa
        ) latest ON ha.id_siswa = latest.id_siswa AND ha.terakhir_diperbarui = latest.max_date
        WHERE ha.id_siswa = k.id_siswa 
        AND ha.kategori = 'kesehatan_mental' 
        AND ha.skor LIKE '%PERLU PERHATIAN KHUSUS%'
    ) as is_priority
    FROM konsultasi k 
    JOIN siswa s ON k.id_siswa = s.id 
    WHERE k.id_konselor = '$id_konselor' AND k.status = 'menunggu' 
    ORDER BY is_priority DESC, k.created_at ASC
";
$res_requests = $conn->query($sql_requests);

// Jadwal Mendatang (Disetujui) + Selesai (untuk akses laporan)
$sql_schedule = "
    SELECT k.*, s.nama_lengkap 
    FROM konsultasi k 
    JOIN siswa s ON k.id_siswa = s.id 
    WHERE k.id_konselor = '$id_konselor' AND (k.status = 'disetujui' OR k.status = 'selesai')
    ORDER BY k.tanggal_konsultasi DESC
";
$res_schedule = $conn->query($sql_schedule);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Konselor | Modern Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --color-bg: #FAFAF9;
            --color-surface: #FFFFFF;
            --color-surface-soft: #F5F5F4;
            --color-accent: #0D9488;
            --color-accent-light: #CCFBF1;
            --color-text: #1C1917;
            --color-text-muted: #78716C;
            --shadow-soft: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
            --shadow-elevated: 0 4px 6px rgba(0,0,0,0.03), 0 12px 24px rgba(0,0,0,0.06);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            scroll-behavior: smooth;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(231, 229, 228, 0.5);
            -webkit-backdrop-filter: blur(12px);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0D9488, #14B8A6, #2DD4BF);
        }

        .card-shadow {
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }

        .card-shadow:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-elevated);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }

        .btn-primary {
            background: var(--color-accent);
            color: white;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #0F766E;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E7E5E4;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #D6D3D1;
        }
    </style>
</head>
<body class="min-h-screen">

    <nav class="glass-nav px-8 py-5 flex justify-between items-center sticky top-0 z-[100]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 hero-gradient rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
            </div>
            <h1 class="font-extrabold text-[#1C1917] text-xl tracking-tight">Dashboard <span class="text-teal-600 font-medium">Guru</span></h1>
        </div>
        <div class="flex gap-6 items-center">
            <div class="flex flex-col items-end">
                <span class="text-stone-900 font-bold text-sm"><?= $guru['nama_lengkap'] ?></span>
                <span class="text-stone-400 text-[10px] uppercase font-bold tracking-widest">Konselor</span>
            </div>
            <div class="h-8 w-[1px] bg-stone-200"></div>
            <a href="logout.php" class="text-stone-400 font-medium text-sm hover:text-rose-500 transition-colors flex items-center gap-2">
                Keluar
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </a>
        </div>
    </nav>

    <main class="max-w-[1400px] mx-auto p-8 lg:p-12">
        
        <!-- ANALYTICS SECTION -->
        <div class="mb-12 animate-up stagger-1">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-black text-stone-900 tracking-tight">Wawasan Aktivitas</h2>
                    <p class="text-stone-500 text-sm mt-1">Pantau perkembangan kesehatan mental siswa Anda secara real-time.</p>
                </div>
                <div class="flex gap-2">
                    <span class="px-4 py-2 bg-white border border-stone-200 rounded-full text-xs font-bold text-stone-600">
                        <?= date('d M Y') ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Wellness Distribution Chart -->
                <div class="bg-white p-8 rounded-[2.5rem] card-shadow lg:col-span-2 flex flex-col md:flex-row items-center gap-10 overflow-hidden relative">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-teal-50 rounded-full blur-3xl opacity-40"></div>
                    
                    <div class="w-full md:w-1/2 relative h-56 flex justify-center">
                        <canvas id="wellnessChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-stone-900"><?= $wellness_stats['Stabil'] + $wellness_stats['Berisiko'] ?></span>
                            <span class="text-[10px] text-stone-400 font-bold uppercase tracking-widest">Total Siswa</span>
                        </div>
                    </div>

                    <div class="flex-1 w-full relative z-10">
                        <h3 class="font-extrabold text-xl text-stone-900 mb-2">Sebaran Kesejahteraan</h3>
                        <p class="text-sm text-stone-500 mb-8 leading-relaxed">Persentase kondisi kesehatan mental berdasarkan asesmen terbaru.</p>
                        
                        <div class="space-y-4">
                            <div class="group flex justify-between items-center p-4 bg-teal-50/50 rounded-2xl border border-teal-100/50 transition-all hover:bg-teal-50">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-8 bg-teal-500 rounded-full"></div>
                                    <div>
                                        <p class="text-stone-400 text-[10px] font-bold uppercase tracking-wider">Kondisi</p>
                                        <span class="text-sm font-bold text-teal-800">Stabil & Normal</span>
                                    </div>
                                </div>
                                <span class="font-black text-xl text-teal-600"><?= $wellness_stats['Stabil'] ?></span>
                            </div>
                            
                            <div class="group flex justify-between items-center p-4 bg-rose-50/50 rounded-2xl border border-rose-100/50 transition-all hover:bg-rose-50">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-8 bg-rose-500 rounded-full"></div>
                                    <div>
                                        <p class="text-stone-400 text-[10px] font-bold uppercase tracking-wider">Kondisi</p>
                                        <span class="text-sm font-bold text-rose-800">Perlu Perhatian</span>
                                    </div>
                                </div>
                                <span class="font-black text-xl text-rose-600"><?= $wellness_stats['Berisiko'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trend Indicators -->
                <div class="grid grid-rows-2 gap-8">
                     <div class="bg-white p-8 rounded-[2.5rem] card-shadow flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-full -mr-12 -mt-12 blur-2xl opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                        <p class="text-stone-400 text-[10px] font-extrabold uppercase tracking-[0.2em] mb-4 relative z-10">Trend Permintaan</p>
                        <div class="flex items-baseline gap-4 mb-2 relative z-10">
                            <h3 class="text-6xl font-black text-stone-900 tracking-tighter"><?= $trend_curr ?></h3>
                            <div class="flex flex-col">
                                <span class="text-xs font-black <?= $trend_color ?> flex items-center gap-1">
                                    <?= $trend_icon ?> <?= $trend_text ?>
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-stone-400 mt-2 font-medium relative z-10">Total permintaan mingguan.</p>
                     </div>

                     <div class="hero-gradient p-8 rounded-[2.5rem] shadow-xl shadow-teal-500/20 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/10 rounded-full -mr-20 -mb-20 blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                        <p class="text-white/70 text-[10px] font-extrabold uppercase tracking-[0.2em] mb-4 relative z-10">Prioritas Utama</p>
                        <div class="flex items-baseline gap-4 relative z-10">
                            <h3 class="text-6xl font-black text-white tracking-tighter"><?= $stat_priority ?></h3>
                            <span class="text-stone-900/40 font-black text-xs bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">Siswa Berisiko</span>
                        </div>
                        <p class="text-white/60 text-xs mt-3 font-medium relative z-10">Segera tindak lanjuti asesmen terbaru.</p>
                     </div>
                </div>
            </div>
        </div>

        <!-- ACTIVITY SUMMARY HERO -->
        <div class="bg-white p-2 rounded-[3.5rem] mb-16 animate-up stagger-2 card-shadow">
            <div class="p-10 lg:p-14 bg-stone-50 rounded-[3rem] border border-stone-100 flex flex-col lg:flex-row items-center justify-between gap-12 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#0D9488 1px, transparent 1px); background-size: 24px 24px;"></div>
                
                <div class="relative z-10 text-center lg:text-left">
                    <span class="inline-block px-4 py-1.5 bg-teal-100 text-teal-700 text-[10px] font-black uppercase tracking-widest rounded-full mb-6">Ringkasan Hari Ini</span>
                    <h2 class="text-4xl lg:text-5xl font-black text-stone-900 tracking-tight leading-tight mb-4">Efisiensi kerja Anda <br><span class="text-teal-600">dimulai di sini.</span></h2>
                    <p class="text-stone-500 max-w-md mx-auto lg:mx-0 font-medium">Data berikut merangkum beban kerja dan tanggung jawab Anda untuk hari ini.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 w-full lg:w-auto relative z-10">
                    <div class="bg-white p-8 rounded-[2rem] border border-stone-200/60 shadow-sm flex flex-col min-w-[200px] hover:border-teal-300 transition-colors">
                        <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <span class="text-stone-400 text-[10px] font-black uppercase tracking-widest mb-1">Konsultasi</span>
                        <h3 class="text-4xl font-black text-stone-900"><?= $stat_today ?></h3>
                        <p class="text-stone-400 text-xs mt-1 font-medium">Sesi Disetujui</p>
                    </div>
                    
                    <div class="bg-white p-8 rounded-[2rem] border border-stone-200/60 shadow-sm flex flex-col min-w-[200px] group hover:border-amber-300 transition-colors">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9.5C2 7 4 5 6.5 5H18c2.2 0 4 1.8 4 4v8Z"/><polyline points="15,9 18,9 18,11"/><path d="M6.5 5C9 5 11 7 11 9.5V17a2 2 0 0 1-2 2"/><line x1="6" x2="7" y1="10" y2="10"/></svg>
                        </div>
                        <span class="text-stone-400 text-[10px] font-black uppercase tracking-widest mb-1">Permintaan</span>
                        <h3 class="text-4xl font-black text-stone-900 group-hover:text-amber-600 transition-colors"><?= $stat_pending ?></h3>
                        <p class="text-stone-400 text-xs mt-1 font-medium">Masuk Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- INCOMING REQUESTS -->
            <div class="animate-up stagger-3">
                <div class="flex items-end justify-between mb-8 px-2">
                    <div>
                        <h3 class="font-black text-2xl text-stone-900 tracking-tight">Permintaan Masuk</h3>
                        <p class="text-stone-500 text-sm mt-1">Kelola permohonan sesi konsultasi siswa.</p>
                    </div>
                    <?php if($stat_pending > 0): ?>
                        <span class="bg-teal-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full shadow-lg shadow-teal-500/20 translate-y-[-4px]"><?= $stat_pending ?> BARU</span>
                    <?php endif; ?>
                </div>
                
                <div class="space-y-6 max-h-[700px] overflow-y-auto pr-4 custom-scrollbar">
                    <?php if($res_requests->num_rows > 0): ?>
                        <?php while($req = $res_requests->fetch_assoc()): ?>
                            <div class="bg-white p-8 rounded-[2.5rem] card-shadow border border-stone-100 flex flex-col relative overflow-hidden transition-all duration-300 <?= $req['is_priority'] > 0 ? 'ring-2 ring-rose-500/20 border-rose-100' : '' ?>">
                                <?php if($req['is_priority'] > 0): ?>
                                    <div class="absolute top-0 right-0">
                                        <div class="bg-rose-500 text-white text-[9px] font-black px-4 py-1 rounded-bl-2xl uppercase tracking-tighter">Prioritas</div>
                                    </div>
                                <?php endif; ?>

                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center font-black text-xl text-stone-400">
                                            <?= substr($req['nama_lengkap'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-lg text-stone-900 leading-tight">
                                                <a href="detail_siswa.php?id=<?= $req['id_siswa'] ?>" class="hover:text-teal-600 transition-colors">
                                                    <?= $req['nama_lengkap'] ?>
                                                </a>
                                            </h4>
                                            <p class="text-xs font-bold text-stone-400 uppercase tracking-widest mt-1"><?= $req['tingkat_kelas'] ?> • <?= $req['jurusan'] ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-stone-50/80 p-6 rounded-3xl mb-8 border border-stone-100">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-[10px] font-black text-teal-600 uppercase tracking-widest"><?= $req['kategori_topik'] ?></span>
                                        <span class="text-[10px] font-bold text-stone-400 px-3 py-1 bg-white rounded-full border border-stone-200">
                                            <?= date('d M • H:i', strtotime($req['tanggal_konsultasi'])) ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-stone-600 font-medium italic leading-relaxed">"<?= $req['deskripsi_keluhan'] ?>"</p>
                                </div>

                                <div class="flex gap-3 mt-auto">
                                    <a href="?action=approve&id=<?= $req['id'] ?>" class="flex-1 btn-primary text-center py-4 rounded-2xl text-xs font-black shadow-lg shadow-teal-500/10">
                                        TERIMA JADWAL
                                    </a>
                                    <a href="?action=reject&id=<?= $req['id'] ?>" onclick="return confirm('Tolak permintaan ini?')" class="px-6 py-4 bg-white border border-stone-200 text-stone-400 hover:text-rose-500 hover:border-rose-200 rounded-2xl text-xs font-black transition-all">
                                        TOLAK
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-stone-50 p-16 rounded-[2.5rem] border-2 border-dashed border-stone-200 text-center">
                            <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D6D3D1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 13-1.25-1.25a2 2 0 0 0-2.83 0L17 12.5a2 2 0 0 1-2.83 0l-1.92-1.92a2 2 0 0 0-2.83 0L8.5 11.5a2 2 0 0 1-2.83 0L4 10a2 2 0 0 0-2.83 0L1 10"/><path d="M1 17h22"/><path d="M1 21h22"/></svg>
                            </div>
                            <p class="text-stone-400 font-extrabold text-sm uppercase tracking-widest">Antrian Bersih</p>
                            <p class="text-stone-300 text-xs mt-2 font-medium">Tidak ada permintaan konsultasi yang tertunda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SCHEDULE & HISTORY -->
            <div class="animate-up stagger-4">
                <div class="mb-8 px-2">
                    <h3 class="font-black text-2xl text-stone-900 tracking-tight">Jadwal & Riwayat</h3>
                    <p class="text-stone-500 text-sm mt-1">Sesi yang disetujui dan riwayat aktivitas laporan.</p>
                </div>
                
                <div class="bg-white p-4 rounded-[3rem] card-shadow border border-stone-50 min-h-[500px]">
                    <?php if($res_schedule->num_rows > 0): ?>
                        <div class="space-y-4 max-h-[700px] overflow-y-auto pr-4 custom-scrollbar">
                            <?php while($sch = $res_schedule->fetch_assoc()): ?>
                                <div class="group p-6 bg-stone-50/50 rounded-[2rem] border border-stone-100 hover:bg-white hover:border-stone-200 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 flex flex-col md:flex-row gap-6 md:items-center">
                                    <div class="flex items-center gap-6 flex-grow">
                                        <div class="hero-gradient text-white w-20 h-20 rounded-[2rem] flex flex-col items-center justify-center flex-shrink-0 shadow-lg shadow-teal-500/10 transition-transform group-hover:scale-105">
                                            <span class="text-[9px] font-black uppercase tracking-[0.2em] mb-0.5 opacity-80"><?= date('M', strtotime($sch['tanggal_konsultasi'])) ?></span>
                                            <span class="text-2xl font-black leading-none"><?= date('d', strtotime($sch['tanggal_konsultasi'])) ?></span>
                                        </div>
                                        <div class="flex-grow">
                                            <p class="text-stone-400 text-[9px] font-black uppercase tracking-widest mb-1"><?= date('H:i', strtotime($sch['tanggal_konsultasi'])) ?> WIB • <?= $sch['kategori_topik'] ?></p>
                                            <h4 class="font-black text-lg text-stone-900">
                                                <a href="detail_siswa.php?id=<?= $sch['id_siswa'] ?>" class="hover:text-teal-600 transition-colors">
                                                    <?= $sch['nama_lengkap'] ?>
                                                </a>
                                            </h4>
                                        </div>
                                    </div>
                                    
                                    <div class="md:shrink-0">
                                        <?php if($sch['status'] == 'selesai'): ?>
                                            <a href="laporan_konsultasi.php?id=<?= $sch['id'] ?>" class="flex items-center gap-2 px-6 py-4 bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-2xl text-[11px] font-black transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                LIHAT LAPORAN
                                            </a>
                                        <?php else: ?>
                                            <a href="tulis_laporan.php?id=<?= $sch['id'] ?>" class="flex items-center gap-2 px-6 py-4 bg-white border border-stone-200 text-stone-600 hover:text-teal-600 hover:border-teal-200 rounded-2xl text-[11px] font-black transition-all group-hover:bg-stone-50">
                                                ISI LAPORAN
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-16 text-center">
                            <div class="w-20 h-20 bg-stone-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D6D3D1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                            </div>
                            <p class="text-stone-300 font-extrabold text-sm uppercase tracking-widest">Kosong</p>
                            <p class="text-stone-200 text-xs mt-2 font-medium">Belum ada jadwal konsultasi yang disetujui.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Initialize Chart -->
    <script>
        const ctx = document.getElementById('wellnessChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Stabil', 'Berisiko'],
                    datasets: [{
                        data: [<?= $wellness_stats['Stabil'] ?>, <?= $wellness_stats['Berisiko'] ?>],
                        backgroundColor: [
                            '#0D9488', // Teal 600
                            '#F43F5E'  // Rose 500
                        ],
                        hoverBackgroundColor: [
                            '#0F766E',
                            '#E11D48'
                        ],
                        borderWidth: 0,
                        hoverOffset: 12,
                        borderRadius: 20,
                        spacing: 8
                    }]
                },
                options: {
                    cutout: '82%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1C1917',
                            titleFont: { size: 14, weight: 'bold', family: "'Plus Jakarta Sans'" },
                            bodyFont: { size: 13, family: "'Plus Jakarta Sans'" },
                            padding: 16,
                            cornerRadius: 16,
                            displayColors: false
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    </script>

</body>
</html>
