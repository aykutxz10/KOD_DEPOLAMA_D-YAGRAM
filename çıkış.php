<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oturum Kapanışı</title>
    <link rel="stylesheet" href="cıkıs.css">
</head>
<body>
    <div class="message-container">
        <h1>Oturumunuz sonlandırıldı</h1>
        <p>Başka bir işlem yapmak için anasayfaya yönlendiriliyorsunuz...</p>
    </div>
    <script>
        setTimeout(function(){
            window.location.href = "Ana_Sayfa.php"; 
        }, 1000); // 1 saniye sonra yönlendirme
    </script>
</body>
</html>
