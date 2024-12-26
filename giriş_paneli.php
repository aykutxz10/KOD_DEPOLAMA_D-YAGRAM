<?php
session_start(); 
// Kullanıcı oturumu kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: Ana_Sayfa.php"); // Eğer kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    exit;
}

// Dosya tablosu için bağlantı
$conn_file = mysqli_connect('localhost', 'root', '', 'file_upload_database');
if (!$conn_file) {
    die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
}

// Yorumlar tablosu için bağlantı
$conn_comment = mysqli_connect('localhost', 'root', '', 'comment_database');
if (!$conn_comment) {
    die("Yorumlar veritabanına bağlanılamadı: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Dosyaları almak için file_upload_database veritabanındaki 'files' tablosuna sorgu
$sql_files = "SELECT * FROM files";  // Tüm dosyalar getirilecek
  // Dosyalar 'file_upload_database' tablosundan alınacak
$result = mysqli_query($conn_file, $sql_files);

// Bağlantı kontrolü ve veritabanı işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'], $_POST['comment'], $_POST['rating'])) {
    // Bağlantı ve değişkenler
    $file_id = $_POST['file_id'];
    $comment = mysqli_real_escape_string($conn_comment, $_POST['comment']); // Yorum verisini güvenli hale getir
    $rating = $_POST['rating'];


    // Yorumun boş olup olmadığını kontrol et
    if (empty($comment)) {
        $error_message = "Yorum kısmı boş olamaz.";
    } else {
        // Yorum veritabanına ekle
        $sql_comment = "INSERT INTO comments (file_id, user_id, comment, created_at) 
                        VALUES ('$file_id', '$user_id', '$comment', NOW())"; // created_at otomatik olarak kaydedilecek

        if (mysqli_query($conn_comment, $sql_comment)) {
            $success_message = "Yorum başarıyla kaydedildi!";
        } else {
            $error_message = "Hata: " . mysqli_error($conn_comment);
        }
    }
}


?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa (Dashboard)</title>
    <link rel="stylesheet" href="giris_yedek.css">
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="logo-container">
        <!-- Logo Resmi -->
        <img src="kodlogo.jpeg" alt="Logo" class="logo">
        <h1>KOD DEPOLAMA SERVİSİ</h1>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <!-- Sol Menü -->
    <div class="left-sidebar">
        <div class="card">
            <h3>Profilim</h3>
            <p>Hesabınıza dair tüm bilgileri görmek için profil sayfasına gidin.</p>
            <a href="profil.php">Profilimi Görüntüle</a>
        </div>

        <div class="card">
            <h3>Yeni Proje Oluştur</h3>
            <p>Yeni bir proje başlatmak için buradan devam edebilirsiniz.</p>
            <a href="kod_yazma.html">Yeni Proje Başlat</a>
        </div>

        <div class="card">
            <h3>Yüklü Dosyalar</h3>
            <p>Yüklediğiniz dosyaları buradan görüntüleyebilirsiniz.</p>
            <a href="dosya_indirme.php">Dosyalarımı Görüntüle</a>
        </div>

        <div class="card">
            <h3>Projelerim</h3>
            <p>Yüklediğiniz ve üzerinde çalıştığınız projelerinizi buradan görebilirsiniz.</p>
            <a href="dosya_yükleme.php">Projelerimi Görüntüle</a>
        </div>

        <div class="card">
            <h3>Çıkış Yap</h3>
            <p>Hesabınızdan çıkış yapmak için buraya tıklayın.</p>
            <a href="çıkış.php">Çıkış Yap</a>
        </div>
    </div>

    <!-- Yüklenen Dosyalar ve Yorumlar -->
    <div class="file-comments">
        <h2>Yüklü Dosyalar</h2>

        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='file-card'>";
                echo "<h3><a href='" . $row['file_path'] . "' target='_blank'>" . $row['file_name'] . "</a></h3>";
                echo "<p>Boyut: " . $row['file_size'] . " bytes</p>";

                // Yorum ve Puanlama Formu
                echo "<form method='POST' action=''>";
                echo "<textarea name='comment' placeholder='Yorumunuzu buraya yazın...'></textarea><br>";
                echo "<label for='rating'>Puan Verin: </label>";
                echo "<select name='rating' id='rating'>
                        <option value='1'>1</option>
                        <option value='2'>2</option>
                        <option value='3'>3</option>
                        <option value='4'>4</option>
                        <option value='5'>5</option>
                      </select><br>";
                // Doğru 'file_id' değerini gönderiyoruz
                echo "<input type='hidden' name='file_id' value='" . $row['id'] . "'>";
                echo "<button type='submit'>Yorum ve Puan Gönder</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>Henüz dosya yüklenmedi.</p>";
        }
        ?>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>© 2024 Web Uygulama Projesi</p>
</div>

</body>
</html>

<?php
// Bağlantıları kapatıyoruz
mysqli_close($conn_file);
mysqli_close($conn_comment);
?>

