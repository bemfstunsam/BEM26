<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BEM FST UNSAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark background */
            color: #F3F4F6; /* Light text */
        }
        .bg-orange-bem {
            background-color: #F97316;
        }
        .text-orange-bem {
            color: #F97316;
        }
        .border-orange-bem {
            border-color: #F97316;
        }
        .hover\:bg-orange-bem-dark:hover {
            background-color: #EA580C;
        }
        .shadow-orange {
            box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3), 0 4px 6px -2px rgba(249, 115, 22, 0.2);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">

    <header class="bg-gray-900/80 sticky top-0 z-50 backdrop-blur-sm shadow-md">
        <nav class="container mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="foto/logo.png" alt="Logo BEM FST UNSAM" class="h-12 w-12 mr-3 rounded-full object-cover border-2 border-orange-500/40">
                    <a href="index.php?page=beranda" class="text-xl font-bold text-white">BEM FST UNSAM</a>
                </div>
                <div class="hidden md:flex items-center space-x-3">
                    <a href="index.php?page=beranda" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition-colors duration-300 shadow-sm">Beranda</a>
                    <a href="index.php?page=berita" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition-colors duration-300 shadow-sm">Berita</a>
                    <a href="index.php?page=program-kerja" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition-colors duration-300 shadow-sm">Program Kerja</a>
                    <a href="index.php?page=profil" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition-colors duration-300 shadow-sm">Profil</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden mt-4 flex flex-col space-y-2 pb-2">
                <a href="index.php?page=beranda" class="block py-2.5 px-4 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg text-center shadow-sm">Beranda</a>
                <a href="index.php?page=berita" class="block py-2.5 px-4 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg text-center shadow-sm">Berita</a>
                <a href="index.php?page=program-kerja" class="block py-2.5 px-4 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg text-center shadow-sm">Program Kerja</a>
                <a href="index.php?page=profil" class="block py-2.5 px-4 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg text-center shadow-sm">Profil</a>
            </div>
        </nav>
    </header>

    <main>
