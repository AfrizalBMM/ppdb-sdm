<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - PPDB SD Muhammadiyah Wonorejo</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans min-h-screen flex items-center justify-center p-6">
    <div class="max-w-4xl w-full text-center">
        <div class="mb-8 animate-float">
            @yield('image')
        </div>
        
        <h1 class="text-6xl md:text-8xl font-black text-primary mb-4 fade-in" style="animation-delay: 0.1s">
            @yield('code')
        </h1>
        
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-4 fade-in" style="animation-delay: 0.2s">
            @yield('message')
        </h2>
        
        <p class="text-slate-500 mb-8 max-w-lg mx-auto fade-in" style="animation-delay: 0.3s">
            @yield('description')
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in" style="animation-delay: 0.4s">
            <a href="javascript:history.back()" class="btn-secondary w-full sm:w-auto">
                <span class="mr-2">←</span> Kembali
            </a>
            <a href="/" class="btn-primary w-full sm:w-auto">
                Ke Beranda
            </a>
        </div>

        @yield('footer')
    </div>
</body>
</html>
