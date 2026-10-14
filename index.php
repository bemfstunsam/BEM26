<?php
session_start();
// Tentukan halaman default
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Daftar halaman yang diizinkan untuk keamanan
$allowed_pages = [
    'beranda', 'berita', 'program-kerja', 'profil', 'suic',
    'suic-drone', 'suic-plantation', 'suic-fishery', 'suic-science', 'suic-product'
];

// Jika halaman yang diminta tidak ada dalam daftar, alihkan ke beranda
if (!in_array($page, $allowed_pages)) {
    $page = 'beranda';
}

// Memasukkan file header
include 'header.php';

// Memasukkan konten halaman yang sesuai
include 'pages/' . $page . '.php';

// Memasukkan file footer
include 'footer.php';
?>
