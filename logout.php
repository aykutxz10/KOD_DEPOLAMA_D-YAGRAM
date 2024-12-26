<?php
session_start();  // Oturumu başlat

// Oturumdaki tüm verileri temizle
session_unset();

// Oturum kapat
session_destroy();

// Ana Sayfa'ya yönlendir
header("Location: Ana_Sayfa.php");
exit();
?>
