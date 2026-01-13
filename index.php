<?php
session_start();
// Redirect logged-in students to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['peran'] == 'siswa') {
    header("Location: dashboard_siswa.php");
    exit;
}
// Redirect others if needed
if (isset($_SESSION['user_id'])) {
     if ($_SESSION['peran'] == 'konselor') header("Location: dashboard_guru.php");
     if ($_SESSION['peran'] == 'admin') header("Location: dashboard_admin.php");
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Konseling Sekolah - Skaju</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Lexend', 'sans-serif'],
                    },
                    colors: {
                        primary: '#6C5CE7',
                        secondary: '#a55eea',
                        accent: '#F9F7FF',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fade-in-up': 'fadeInUp 1s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }
        .hero-blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
        }
        .text-shadow {
            text-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased overflow-x-hidden bg-white">

    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 top-0 transition-all duration-300">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 p-2.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <span class="font-bold text-2xl tracking-tight text-slate-900">BK<span class="text-primary">Skaju</span></span>
            </div>

            <div class="flex gap-4">
                <a href="login.php" class="px-6 py-3 text-sm font-bold text-slate-700 hover:text-primary transition rounded-xl hover:bg-slate-50">Masuk</a>
                <a href="register.php" class="px-6 py-3 text-sm font-bold bg-primary text-white rounded-xl hover:bg-secondary transition shadow-lg shadow-primary/30 transform hover:-translate-y-0.5 active:translate-y-0">Daftar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <!-- Blobs -->
        <div class="hero-blob top-0 left-0 w-[800px] h-[800px] bg-purple-200/40 rounded-full mix-blend-multiply animate-blob"></div>
        <div class="hero-blob bottom-0 right-0 w-[600px] h-[600px] bg-blue-200/40 rounded-full mix-blend-multiply animate-blob animation-delay-2000"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                <div class="lg:w-1/2 text-center lg:text-left">
                    <span class="inline-block py-1.5 px-4 rounded-full bg-indigo-50 border border-indigo-100 text-primary text-sm font-bold uppercase tracking-wider mb-8 animate-fade-in-up shadow-sm">
                        Platform Konseling Digital
                    </span>
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-slate-900 leading-[1.1] mb-8 tracking-tight">
                        Teman Cerita <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-indigo-500">Masa Depanmu.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto lg:mx-0 mb-10 leading-relaxed">
                        Kami hadir untuk mendengarkan, memahami, dan membantumu menemukan potensi terbaik dalam dirimu. Aman, nyaman, dan profesional.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="login.php" class="px-8 py-4 bg-primary text-white rounded-2xl font-bold text-lg hover:bg-secondary transition shadow-xl shadow-primary/30 w-full sm:w-auto transform hover:-translate-y-1">
                            Mulai Konsultasi
                        </a>
                        <a href="#fitur" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-2xl font-bold text-lg hover:bg-slate-50 transition w-full sm:w-auto hover:shadow-lg hover:border-slate-300">
                            Pelajari Dulu
                        </a>
                    </div>
                </div>
                
                <div class="lg:w-1/2 relative lg:right-0">
                    <div class="relative w-full max-w-lg mx-auto animate-float">
                        <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full transform scale-90 translate-y-10"></div>
                        <img src="assets/img/hero.png" alt="Ilustrasi Konseling Sekolah" class="relative z-10 w-full drop-shadow-2xl">
                    </div>
                </div>
            </div>

            <!-- Stats/Trust -->
            <div class="mt-24 pt-10 border-t border-slate-200/70 max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center md:text-left">
                    <h4 class="text-4xl font-bold text-slate-800 mb-1">500+</h4>
                    <p class="text-slate-500 font-medium">Siswa Terbantu</p>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-4xl font-bold text-slate-800 mb-1">24/7</h4>
                    <p class="text-slate-500 font-medium">Akses Layanan</p>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-4xl font-bold text-slate-800 mb-1">100%</h4>
                    <p class="text-slate-500 font-medium">Privasi Dijaga</p>
                </div>
                 <div class="text-center md:text-left">
                    <h4 class="text-4xl font-bold text-slate-800 mb-1">VAK</h4>
                    <p class="text-slate-500 font-medium">Tes Gaya Belajar</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-20 lg:py-32 bg-slate-50 relative overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-6">Layanan Unggulan</h2>
                <p class="text-lg text-slate-600">Ekosistem lengkap untuk mendukung perkembangan mental dan akademik siswa secara holistik.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <!-- Feature 1 -->
                <div class="bg-white p-10 rounded-[2rem] shadow-sm hover:shadow-2xl hover:-translate-y-2 transition duration-300 group border border-slate-100/50">
                    <div class="w-20 h-20 bg-purple-50 rounded-3xl flex items-center justify-center mb-8 group-hover:scale-110 transition duration-500">
                        <img src="assets/img/icon_counseling.png" alt="Icon Konseling" class="w-12 h-12 object-contain">
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Konseling Online</h3>
                    <p class="text-slate-500 leading-relaxed text-lg">Curhat dengan guru BK kapanpun kamu butuh, tanpa takut dihakimi. Jadwal fleksibel.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-10 rounded-[2rem] shadow-sm hover:shadow-2xl hover:-translate-y-2 transition duration-300 group border border-slate-100/50">
                    <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center mb-8 group-hover:scale-110 transition duration-500">
                        <img src="assets/img/icon_vak.png" alt="Icon VAK" class="w-12 h-12 object-contain">
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Tes Gaya Belajar</h3>
                    <p class="text-slate-500 leading-relaxed text-lg">Kenali cara otakmu belajar. Visual, Auditori, atau Kinestetik? Temukan di sini.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-10 rounded-[2rem] shadow-sm hover:shadow-2xl hover:-translate-y-2 transition duration-300 group border border-slate-100/50">
                    <div class="w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mb-8 group-hover:scale-110 transition duration-500">
                        <img src="assets/img/icon_privacy.png" alt="Icon Privasi" class="w-12 h-12 object-contain">
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Privasi Aman</h3>
                    <p class="text-slate-500 leading-relaxed text-lg">Sistem terenkripsi dan etika profesional menjamin ceritamu tetap rahasia.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 lg:py-28 relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="bg-primary rounded-[3rem] p-12 md:p-24 text-center text-white relative overflow-hidden shadow-2xl shadow-primary/40 group">
                <!-- Background Pattern -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl group-hover:scale-110 transition duration-1000"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full -ml-20 -mb-20 blur-3xl group-hover:scale-110 transition duration-1000"></div>
                
                <div class="relative z-10 max-w-3xl mx-auto">
                    <h2 class="text-4xl md:text-6xl font-bold mb-8 leading-tight">Mulai Perubahan Positif Hari Ini</h2>
                    <p class="text-indigo-100 text-xl mb-12 font-medium">Setiap langkah kecil menuju kesehatan mental yang lebih baik sangatlah berharga. Kami siap menemanimu.</p>
                    <a href="register.php" class="inline-block bg-white text-primary px-12 py-5 rounded-2xl font-bold text-xl hover:bg-purple-50 transition shadow-xl transform hover:-translate-y-1 hover:shadow-2xl">
                        Daftar Akun Gratis
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-20 pb-10 border-t border-slate-100">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/5 border border-primary/10 p-2.5 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-2xl text-slate-800">BK<span class="text-primary">Skaju</span></span>
                </div>
                <div class="flex gap-8 text-slate-500 font-medium">
                    <a href="#" class="hover:text-primary transition">Privacy</a>
                    <a href="#" class="hover:text-primary transition">Terms</a>
                    <a href="#" class="hover:text-primary transition">Support</a>
                </div>
            </div>
            <div class="text-center border-t border-slate-100 pt-8">
                <p class="text-slate-400 font-medium">© 2026 Ricky Marove. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>