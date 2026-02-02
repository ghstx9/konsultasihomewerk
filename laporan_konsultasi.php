<?php
session_start();
include 'config/database.php';

// Cek Sesi Konselor
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'konselor') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Konsultasi tidak ditemukan.");
}

$id_konsultasi = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get konselor ID
$sql_konselor = "SELECT id FROM konselor WHERE id_pengguna = '$user_id'";
$res_konselor = $conn->query($sql_konselor);
$konselor = $res_konselor->fetch_assoc();
$id_konselor = $konselor['id'];

// Fetch Consultation + Report Data with validation
$sql = "
    SELECT 
        k.*,
        s.nama_lengkap as nama_siswa,
        s.nis,
        s.tingkat_kelas,
        s.jurusan,
        s.jenis_kelamin,
        c.nama_lengkap as nama_konselor,
        c.nip,
        l.inti_masalah,
        l.solusi_diberikan,
        l.perlu_tindak_lanjut,
        l.catatan_rahasia,
        l.created_at as tanggal_laporan
    FROM konsultasi k
    JOIN siswa s ON k.id_siswa = s.id
    JOIN konselor c ON k.id_konselor = c.id
    LEFT JOIN laporan_konsultasi l ON k.id = l.id_konsultasi
    WHERE k.id = ? AND k.id_konselor = ? AND k.status = 'selesai'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_konsultasi, $id_konselor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data laporan tidak ditemukan atau Anda tidak memiliki akses.");
}

$data = $result->fetch_assoc();

// Fetch latest assessment data for context (optional)
$sql_assessment = "
    SELECT kategori, skor, skor_numerik, terakhir_diperbarui
    FROM hasil_asesmen
    WHERE id_siswa = ?
    ORDER BY terakhir_diperbarui DESC
    LIMIT 3
";
$stmt_assessment = $conn->prepare($sql_assessment);
$stmt_assessment->bind_param("i", $data['id_siswa']);
$stmt_assessment->execute();
$res_assessment = $stmt_assessment->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Konseling | <?= htmlspecialchars($data['nama_siswa']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; py: 0 !important; }
            .print-shadow { box-shadow: none !important; border: 1px solid #E7E5E4 !important; }
            .animate-up { opacity: 1 !important; transform: none !important; animation: none !important; }
            .max-w-5xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .container-main { padding: 0 !important; margin: 0 !important; }
            .report-card { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
        }

        .report-section-title {
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            color: var(--color-text);
            letter-spacing: -0.025em;
        }

        .report-section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 4px;
            background: var(--color-accent);
            border-radius: 2px;
        }
    </style>
</head>
<body class="min-h-screen py-8 lg:py-12">

    <!-- Navigation (Hidden on Print) -->
    <nav class="no-print glass-nav fixed top-0 w-full z-[100] px-8 py-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="dashboard_guru.php" class="group flex items-center gap-3 text-stone-500 hover:text-teal-600 transition-all font-bold text-sm">
                <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center group-hover:bg-teal-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </div>
                Kembali ke Dashboard
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-6 py-2.5 bg-stone-900 text-white hover:bg-teal-700 hover:shadow-lg hover:shadow-teal-500/20 rounded-xl font-black text-xs transition-all tracking-widest uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                CETAK LAPORAN
            </button>
        </div>
    </nav>

    <!-- Report Container -->
    <div class="container-main max-w-5xl mx-auto px-6 mt-16 no-print:animate-up">
        <div class="report-card bg-white rounded-[2.5rem] card-shadow print-shadow overflow-hidden">
            
            <!-- Header -->
            <div class="hero-gradient p-10 lg:p-14 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl opacity-50"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div>
                        <div class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest mb-4">Dokumentasi Resmi</div>
                        <h1 class="text-4xl lg:text-5xl font-black tracking-tight mb-2">Laporan Konseling</h1>
                        <p class="text-teal-50/80 font-medium">Bimbingan dan Konseling Siswa Terintegrasi</p>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-teal-100 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Dibuat Pada</p>
                        <p class="text-2xl font-black"><?= date('d F Y', strtotime($data['tanggal_laporan'] ?? $data['created_at'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-10 lg:p-14 space-y-12">
                
                <!-- Session Information -->
                <section>
                    <h2 class="report-section-title text-2xl">Informasi Identitas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100 transition-colors hover:border-teal-200">
                                <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Nama Siswa</span>
                                <span class="font-black text- stone-900 text-xl tracking-tight"><?= htmlspecialchars($data['nama_siswa']) ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100">
                                    <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">NIS</span>
                                    <span class="font-bold text-stone-700 tracking-tight"><?= htmlspecialchars($data['nis']) ?></span>
                                </div>
                                <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100">
                                    <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Kelas</span>
                                    <span class="font-bold text-stone-700 tracking-tight">Kls <?= $data['tingkat_kelas'] ?> <?= htmlspecialchars($data['jurusan']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100 transition-colors hover:border-teal-200">
                                <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Konselor</span>
                                <span class="font-black text-stone-900 text-xl tracking-tight"><?= htmlspecialchars($data['nama_konselor']) ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100">
                                    <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Kategori</span>
                                    <span class="inline-flex font-bold text-teal-700"><?= htmlspecialchars($data['kategori_topik']) ?></span>
                                </div>
                                <div class="bg-stone-50 p-6 rounded-[2rem] border border-stone-100">
                                    <span class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Waktu Sesi</span>
                                    <span class="font-bold text-stone-700"><?= date('H:i', strtotime($data['tanggal_konsultasi'])) ?> WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Student Assessment Context -->
                <?php if($res_assessment->num_rows > 0): ?>
                <section>
                    <h2 class="report-section-title text-2xl">Konteks Kesehatan Mental</h2>
                    <div class="bg-stone-50 p-8 rounded-[2.5rem] border border-stone-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-teal-500/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <p class="text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] mb-6">Hasil Asesmen Terbaru</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php while($assessment = $res_assessment->fetch_assoc()): ?>
                                <div class="bg-white p-6 rounded-[2rem] border border-stone-100 shadow-sm transition-transform hover:scale-[1.02]">
                                    <p class="text-[9px] font-black text-teal-600 uppercase tracking-widest mb-2"><?= ucfirst(str_replace('_', ' ', $assessment['kategori'])) ?></p>
                                    <p class="font-black text-stone-900 leading-tight"><?= htmlspecialchars($assessment['skor']) ?></p>
                                    <p class="text-[9px] text-stone-400 mt-2 font-bold"><?= date('d M Y', strtotime($assessment['terakhir_diperbarui'])) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Analysis Details -->
                <div class="grid grid-cols-1 gap-10">
                    <!-- Original Complaint -->
                    <section>
                        <h2 class="report-section-title text-2xl">Keluhan Awal</h2>
                        <div class="bg-stone-50 p-8 rounded-[2.5rem] border border-stone-100">
                            <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <p class="text-stone-700 leading-relaxed font-medium italic">"<?= nl2br(htmlspecialchars($data['deskripsi_keluhan'])) ?>"</p>
                        </div>
                    </section>

                    <!-- Problem Summary -->
                    <?php if($data['inti_masalah']): ?>
                    <section>
                        <h2 class="report-section-title text-2xl">Inti Masalah</h2>
                        <div class="bg-white p-8 rounded-[2.5rem] border border-stone-200 card-shadow">
                             <div class="w-10 h-10 bg-stone-100 rounded-xl flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1C1917" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            </div>
                            <p class="text-stone-700 leading-relaxed font-medium"><?= nl2br(htmlspecialchars($data['inti_masalah'])) ?></p>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Solution Given -->
                    <?php if($data['solusi_diberikan']): ?>
                    <section>
                        <h2 class="report-section-title text-2xl">Solusi & Saran</h2>
                        <div class="bg-teal-50 px-8 py-10 rounded-[2.5rem] border border-teal-100">
                            <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-teal-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v8"/><path d="m16 6-4 4-4-4"/><path d="M12 16v6"/><path d="m8 18 4 4 4-4"/></svg>
                            </div>
                            <p class="text-teal-900 leading-relaxed font-semibold"><?= nl2br(htmlspecialchars($data['solusi_diberikan'])) ?></p>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Follow-up Status -->
                <section>
                    <h2 class="report-section-title text-2xl">Status Tindak Lanjut</h2>
                    <div class="flex">
                        <?php if($data['perlu_tindak_lanjut']): ?>
                            <div class="flex items-center gap-4 bg-rose-50 text-rose-700 px-8 py-6 rounded-[2rem] border border-rose-100 shadow-sm">
                                <div class="w-10 h-10 bg-rose-500 rounded-full flex items-center justify-center shadow-lg shadow-rose-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                </div>
                                <span class="font-black text-lg tracking-tight">Perlu Sesi Tindak Lanjut</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-4 bg-teal-50 text-teal-700 px-8 py-6 rounded-[2rem] border border-teal-100 shadow-sm">
                                <div class="w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center shadow-lg shadow-teal-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </div>
                                <span class="font-black text-lg tracking-tight">Status Selesai (Tidak Perlu Tindak Lanjut)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Confidential Notes (Konselor Only) -->
                <?php if($data['catatan_rahasia']): ?>
                <section class="no-print">
                    <h2 class="report-section-title text-2xl text-rose-800 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Catatan Internal Rahasia
                    </h2>
                    <div class="bg-rose-50/30 p-8 rounded-[2.5rem] border-2 border-rose-100">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-rose-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full">PRIVATE</span>
                            <span class="text-[10px] text-rose-500 font-bold uppercase tracking-widest">Hanya untuk visibilitas konselor</span>
                        </div>
                        <p class="text-stone-700 leading-relaxed font-medium"><?= nl2br(htmlspecialchars($data['catatan_rahasia'])) ?></p>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Signature Section -->
                <section class="mt-20 pt-16 border-t-2 border-stone-100">
                    <div class="flex flex-col md:flex-row justify-between gap-16">
                        <div class="text-center md:text-left flex-1">
                            <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-16">Mengetahui, Konselor</p>
                            <div class="inline-block border-t-2 border-stone-900 pt-3 min-w-[240px]">
                                <p class="font-black text-stone-900 text-xl tracking-tight"><?= htmlspecialchars($data['nama_konselor']) ?></p>
                                <p class="text-xs font-bold text-stone-400 tracking-widest mt-1">NIP: <?= htmlspecialchars($data['nip']) ?></p>
                            </div>
                        </div>
                        <div class="text-center md:text-right flex-1">
                            <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-16">Identifikasi Siswa</p>
                            <div class="inline-block border-t-2 border-stone-900 pt-3 min-w-[240px]">
                                <p class="font-black text-stone-900 text-xl tracking-tight"><?= htmlspecialchars($data['nama_siswa']) ?></p>
                                <p class="text-xs font-bold text-stone-400 tracking-widest mt-1">NIS: <?= htmlspecialchars($data['nis']) ?></p>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Footer -->
            <div class="bg-stone-50 p-10 text-center border-t border-stone-100">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-teal-500"></div>
                    <p class="text-[10px] font-black text-stone-400 uppercase tracking-[0.3em]">VibeCheck System Official Report</p>
                    <div class="w-1.5 h-1.5 rounded-full bg-teal-500"></div>
                </div>
                <p class="text-[10px] text-stone-400 font-medium">Dicetak pada: <?= date('d F Y, H:i') ?> WIB • Dokumentasi ini sah tanpa tanda tangan basah bila dicetak melalui sistem.</p>
            </div>

        </div>
    </div>

</body>
</html>
