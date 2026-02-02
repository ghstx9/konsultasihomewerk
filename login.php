<?php
session_start();
// Redirect already logged-in students to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['peran'] == 'siswa') {
    header("Location: dashboard_siswa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BK Skaju</title>
    
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
        .mesh-gradient {
            background-color: #312e81; /* brand-900 */
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%),
                radial-gradient(at 80% 50%, hsla(270,50%,40%,0.4) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(240,60%,60%,0.2) 0px, transparent 50%);
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased h-screen flex overflow-hidden bg-white">

    <!-- Left Side: Visuals (Desktop) -->
    <div class="hidden lg:flex lg:w-[55%] relative mesh-gradient flex-col justify-between p-12 text-white overflow-hidden">
        
        <!-- Animated Background Shapes -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-500/30 rounded-full blur-[100px] animate-float mix-blend-screen"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-accent/20 rounded-full blur-[80px] animate-float-delayed mix-blend-screen"></div>

        <!-- Top Guide -->
        <div class="relative z-10">
            <a href="index.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white transition-colors group">
                <div class="p-2 rounded-lg bg-white/5 border border-white/10 group-hover:bg-white/10 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                </div>
                <span class="font-medium tracking-wide text-sm">Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Central Content -->
        <div class="relative z-10 max-w-lg">
            <div class="glass p-8 rounded-3xl mb-8 transform hover:scale-[1.01] transition-transform duration-500">
                <svg class="w-8 h-8 text-brand-300 mb-6 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.0547 15.592 14.4793 17.5373 14.4793H19.9653C19.8227 10.9573 16.9467 9.85067 15.2027 9.85067V7.08533C18.992 7.08533 22.8133 9.492 22.8133 14.6733V21H14.017ZM8.01067 21L8.01067 18C8.01067 16.0547 9.58533 14.4793 11.5307 14.4793H13.9587C13.816 11.0067 10.94 9.85067 9.196 9.85067V7.08533C12.9853 7.08533 16.8067 9.492 16.8067 14.6733V21H8.01067Z"/></svg>
                <h2 class="font-display font-bold text-2xl md:text-3xl leading-tight mb-4">
                    "Anda tidak harus melihat seluruh tangga, cukup ambil langkah pertama."
                </h2>
                <div class="flex items-center gap-3">
                    <div class="h-px bg-white/30 flex-1"></div>
                    <span class="text-sm font-light text-brand-100 uppercase tracking-widest">Martin Luther King Jr.</span>
                </div>
            </div>
            
            <p class="text-brand-200 text-lg leading-relaxed">
                Platform aman untuk berbagi cerita, menemukan solusi, dan mengenali potensimu lebih dalam.
            </p>
        </div>

        <!-- Footer Info -->
        <div class="relative z-10 text-xs text-brand-300/60 font-medium tracking-wider">
            &copy; 2026 Ricky Marove
        </div>
    </div>

    <!-- Right Side: Form (All Screens) -->
    <div class="w-full lg:w-[45%] flex flex-col justify-center items-center p-6 md:p-12 relative bg-white lg:shadow-[-20px_0_40px_rgba(0,0,0,0.05)] z-20 overflow-y-auto">
        
        <!-- Mobile Back Button -->
        <div class="absolute top-6 left-6 lg:hidden">
            <a href="index.php" class="p-2 text-slate-400 hover:text-slate-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
            </a>
        </div>

        <div class="w-full max-w-sm">
            <!-- Header -->
            <div class="text-center lg:text-left mb-10">
                <a href="index.php" class="inline-block mb-6 group">
                     <span class="font-display font-bold text-2xl text-slate-900 tracking-tight"><span class="text-brand-600">BK</span>Skaju</span>
                </a>
                <h1 class="font-display font-bold text-3xl text-slate-900 mb-3">Selamat Datang</h1>
                <p class="text-slate-500">Masukan kredensial akunmu untuk melanjutkan.</p>
            </div>

            <!-- Error Message -->
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-4 rounded-r-lg mb-8 flex items-start gap-3 animate-pulse text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Email atau Password yang kamu masukkan tidak valid.</p>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="auth_process.php" method="POST" class="space-y-5">
                
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Email Sekolah</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" name="email" required placeholder="nama@sekolah.sch.id"
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 placeholder:text-slate-400">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-sm font-semibold text-slate-700">Password</label>
                    </div>
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="password" id="passwordInput" required placeholder="••••••••"
                               class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all font-medium text-slate-900 placeholder:text-slate-400">
                        <button type="button" onclick="togglePassword()" class="absolute right-0 top-0 h-full px-4 text-slate-400 hover:text-brand-600 transition-colors focus:outline-none">
                            <svg id="eyeIconShow" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <svg id="eyeIconHide" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-xl transition-all shadow-xl shadow-brand-600/20 hover:scale-[1.01] active:scale-[0.98] mt-2">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Footer Link -->
            <p class="mt-8 text-center text-slate-500 font-medium">
                Belum punya akun? 
                <a href="register.php" class="text-brand-600 hover:text-brand-700 font-bold hover:underline transition-all">Daftar Akun Gratis</a>
            </p>
        </div>
    </div>

    <!-- Script for Toggle Password -->
    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const iconShow = document.getElementById('eyeIconShow');
            const iconHide = document.getElementById('eyeIconHide');
            
            if (input.type === 'password') {
                input.type = 'text';
                iconShow.classList.add('hidden');
                iconHide.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconShow.classList.remove('hidden');
                iconHide.classList.add('hidden');
            }
        }
    </script>
</body>
</html>