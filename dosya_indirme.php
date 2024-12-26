<?php
session_start();

// Kullanıcı girişini kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");  // Giriş yapmamış kullanıcıyı yönlendir
    exit();
}

// Veritabanı bağlantısını oluştur
$conn = mysqli_connect('localhost', 'root', '', 'file_upload_database');
if (!$conn) {
    die("Veritabanına bağlanılamadı: " . mysqli_connect_error());  // Veritabanı hatası
}

// Veritabanından dosyaları al
$files = [];
$sql = "SELECT * FROM files";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $files[] = $row;
}

// Dosya indirme işlemi
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $file_id = $_GET['id'];

    // Dosyanın veritabanındaki bilgilerini al
    $sql = "SELECT * FROM files WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $file_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $file = mysqli_fetch_assoc($result);
        $file_path = $file['file_path'];
        $file_name = $file['file_name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION); // Dosya uzantısını al

        // Dosyanın var olup olmadığını kontrol et
        if (file_exists($file_path) && is_readable($file_path)) {
            // Dosya uzantısına göre doğru Content-Type ayarlama
            switch ($file_extension) {
                case 'jpg':
                case 'jpeg':
                    $content_type = 'image/jpeg';
                    break;
                case 'png':
                    $content_type = 'image/png';
                    break;
                case 'gif':
                    $content_type = 'image/gif';
                    break;
                case 'pdf':
                    $content_type = 'application/pdf';
                    break;
                case 'zip':
                    $content_type = 'application/zip';
                    break;
                case 'txt':
                    $content_type = 'text/plain';
                    break;
                default:
                    $content_type = 'application/octet-stream'; // Genel dosya türü
                    break;
            }

            // Dosya indirme başlıklarını ayarla
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $content_type);
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));

            // Dosyayı oku ve indir
            readfile($file_path);
            exit();
        } else {
            echo "Dosya bulunamadı veya erişilemiyor.";
        }
    } else {
        echo "Geçersiz dosya ID'si.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya İndir</title>
    <link rel="stylesheet" href="dosya_indir.css">
</head>
<body>
    <div class="container">
        <h1>Dosyalar</h1>

        <!-- Yüklenen Dosyaların Listelenmesi -->
        <h2>Yüklenen Dosyalar:</h2>
        <?php if (count($files) > 0): ?>
            <ul>
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="dosya_indirme.php?id=<?= $file['id'] ?>" download><?= htmlspecialchars($file['file_name']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Henüz dosya yüklenmedi.</p>
        <?php endif; ?>
    </div>
</body>
</html>
