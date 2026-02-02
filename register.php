<?php
session_start();
// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['peran'] == 'siswa') {
    header("Location: dashboard_siswa.php");
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : "";
$success = isset($_GET['success']) ? $_GET['success'] : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BK Skaju</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5', // Deep Indigo
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        accent: {
                            light: '#ffe4e6',
                            DEFAULT: '#f43f5e', // Coral
                            dark: '#e11d48',
                        },
                    },
                    animation: {
                        'float': 'float 8s ease-in-out infinite',
                        'float-delayed': 'float 8s ease-in-out 4s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0) scale(1)' },
                            '50%': { transform: 'translateY(-20px) scale(1.02)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .mesh-gradient-alt {
            background-color: #312e81; /* brand-900 */
            background-image: 
                radial-gradient(at 100% 100%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 0% 100%, hsla(339,49%,30%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%),
                radial-gradient(at 20% 50%, hsla(270,50%,40%,0.4) 0px, transparent 50%);
        }
        /* Custom Scrollbar for Form Area */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased h-screen flex overflow-hidden bg-white">

    <!-- Left Side: Form (All Screens) -->
    <div class="w-full lg:w-[45%] flex flex-col p-6 md:p-12 relative bg-white z-20 overflow-y-auto custom-scrollbar">
        
        <!-- Mobile Back Button -->
        <div class="mb-8 lg:hidden">
            <a href="index.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                <span class="text-sm font-medium">Kembali</span>
            </a>
        </div>

        <div class="w-full max-w-md mx-auto my-auto">
            <!-- Header -->
            <div class="mb-8">
                <a href="index.php" class="inline-block mb-6 group">
                     <span class="font-display font-bold text-2xl text-slate-900 tracking-tight"><span class="text-brand-600">BK</span>Skaju</span>
                </a>
                <h1 class="font-display font-bold text-3xl text-slate-900 mb-2">Buat Akun Baru</h1>
                <p class="text-slate-500">Isi data dirimu dengan lengkap dan benar.</p>
            </div>

            <!-- Alerts -->
            <?php if($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-4 rounded-r-lg mb-8 flex items-start gap-3 text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg mb-8 flex items-start gap-3 text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-bold">Berhasil!</p>
                        <p><?= htmlspecialchars($success) ?> <a href="login.php" class="underline hover:text-green-900">Login disini</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="auth/register_process.php" method="POST" class="space-y-8">
                
                <!-- Section: Data Sekolah -->
                <div>
                    <h3 class="text-xs font-bold text-brand-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-8 h-px bg-brand-200"></span> Data Sekolah
                    </h3>
                    
                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required 
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 placeholder:text-slate-400">
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                             <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">NIS</label>
                                <input type="text" name="nis" required maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 font-mono placeholder:text-slate-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Jenis Kelamin</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="L" required class="peer sr-only">
                                        <div class="w-full py-3 text-center border border-slate-200 bg-slate-50 rounded-xl text-slate-500 font-medium peer-checked:bg-brand-50 peer-checked:border-brand-500 peer-checked:text-brand-700 transition-all hover:bg-slate-100">L</div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="P" class="peer sr-only">
                                        <div class="w-full py-3 text-center border border-slate-200 bg-slate-50 rounded-xl text-slate-500 font-medium peer-checked:bg-brand-50 peer-checked:border-brand-500 peer-checked:text-brand-700 transition-all hover:bg-slate-100">P</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Kelas</label>
                                <div class="relative">
                                    <select name="tingkat_kelas" class="w-full appearance-none px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900">
                                        <option value="10">Kelas 10</option>
                                        <option value="11">Kelas 11</option>
                                        <option value="12">Kelas 12</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Jurusan</label>
                                <div class="relative">
                                    <select name="jurusan" class="w-full appearance-none px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900">
                                        <option value="RPL">RPL</option>
                                        <option value="TKJ">TKJ</option>
                                        <option value="DKV">DKV</option>
                                        <option value="TKL">TKL</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Data Akun -->
                <div>
                     <h3 class="text-xs font-bold text-brand-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-8 h-px bg-brand-200"></span> Data Akun
                    </h3>

                    <div class="space-y-5">
                         <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Email Sekolah</label>
                             <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                                </div>
                                <input type="email" name="email" required 
                                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 placeholder:text-slate-400">
                            </div>
                        </div>

                         <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Password</label>
                             <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <input type="password" name="password" required minlength="6"
                                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 placeholder:text-slate-400">
                            </div>
                            <p class="text-xs text-slate-400 ml-1">Minimal 6 karakter kombinasi.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-xl transition-all shadow-xl shadow-brand-600/20 hover:scale-[1.01] active:scale-[0.98]">
                        Buat Akun Saya
                    </button>
                    <p class="mt-6 text-center text-slate-500 font-medium">
                        Sudah punya akun? 
                        <a href="login.php" class="text-brand-600 hover:text-brand-700 font-bold hover:underline transition-all">Masuk disini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side: Visuals (Desktop) -->
    <div class="hidden lg:flex lg:w-[55%] relative mesh-gradient-alt flex-col justify-between p-12 text-white overflow-hidden">
        
        <!-- Animated Background Shapes -->
        <div class="absolute top-1/4 right-1/4 w-[500px] h-[500px] bg-brand-500/20 rounded-full blur-[120px] animate-float mix-blend-screen"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[400px] h-[400px] bg-accent/20 rounded-full blur-[100px] animate-float-delayed mix-blend-screen"></div>

        <!-- Top Guide -->
        <div class="relative z-10 flex justify-end">
             <a href="index.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white transition-colors group">
                <span class="font-medium tracking-wide text-sm">Kembali ke Beranda</span>
                <div class="p-2 rounded-lg bg-white/5 border border-white/10 group-hover:bg-white/10 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                </div>
            </a>
        </div>

        <!-- Central Content -->
        <div class="relative z-10 max-w-xl ml-auto text-right">

            <h2 class="font-display font-bold text-4xl md:text-5xl leading-tight mb-6">
                Mulai Perjalanan <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-200 to-white">Mengenal Dirimu</span>
            </h2>
            
            <p class="text-brand-200 text-lg leading-relaxed max-w-md ml-auto">
                Dapatkan akses ke fitur tes gaya belajar, konseling online, dan berbagai artikel pengembangan diri secara gratis.
            </p>
        </div>

        <!-- Footer Info -->
        <div class="relative z-10 text-xs text-brand-300/60 font-medium tracking-wider text-right">
            &copy; 2026 Ricky Marove
        </div>
    </div>

</body>
</html>
