<?php
session_start();

// Kullanıcı oturumu kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $user_id = $_SESSION['user_id']; // Oturumdaki kullanıcı ID'si

    // Dosya bilgileri
    $file_name = basename($_FILES['file']['name']);  // Dosya adını alıyoruz
    $file_tmp_name = $_FILES['file']['tmp_name'];    // Geçici dosya yolu
    $file_size = $_FILES['file']['size'];            // Dosya boyutu

    // Yükleme yapılacak dizin
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/DENEME2/yükle/';  // Dosyanın kaydedileceği dizin
    $file_path = $upload_dir . $file_name;  // Tam dosya yolu

    // Dosya uzantısı ve boyut kontrolü (isteğe bağlı)
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt', 'rar', 'zip']; // Kabul edilen uzantılar
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);  // Dosya uzantısını alıyoruz

    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        echo "Bu tür dosyalar yüklenemez. Geçerli uzantılar: jpg, jpeg, png, pdf, docx, txt, rar, zip.";
        exit();
    }

    // Dosya boyutunu kontrol et (örneğin, 10 MB)
    if ($file_size > 10 * 1024 * 1024) {
        echo "Dosya boyutu 10MB'yi aşamaz.";
        exit();
    }

    // Dosya yükleme işlemi
    if (move_uploaded_file($file_tmp_name, $file_path)) {
        // Veritabanı bağlantısı
        $conn = mysqli_connect('localhost', 'root', '', 'file_upload_database');
        if (!$conn) {
            die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
        }

        // Dosya bilgilerini veritabanına kaydet
        $sql_file = "INSERT INTO files (file_name, file_path, file_size, uploaded_by) 
                     VALUES (?, ?, ?, ?)";
        $stmt_file = mysqli_prepare($conn, $sql_file);
        if ($stmt_file === false) {
            die("Sorgu hatası: " . mysqli_error($conn));
        }

        // Parametreleri bağla
        mysqli_stmt_bind_param($stmt_file, 'ssii', $file_name, $file_path, $file_size, $user_id);

        // Dosya verisini veritabanına kaydet
        if (mysqli_stmt_execute($stmt_file)) {
            echo "Dosya başarıyla veritabanına kaydedildi!";
        } else {
            echo "Dosya veritabanına kaydedilemedi: " . mysqli_error($conn);
        }

        // Veritabanı bağlantısını kapat
        mysqli_close($conn);
    } else {
        echo "Dosya yüklenemedi.";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <link rel="stylesheet" href="dosya_yükleme.css">
</head>
<body>
    <div class="container">
        <h1>Dosya Yükle</h1>

        <!-- Dosya Yükleme Formu -->
        <form method="POST" enctype="multipart/form-data">
            <label for="file">Dosya Seç:</label>
            <input type="file" name="file" id="file" required>
            <br><br>
            <input type="submit" value="Dosya Yükle">
        </form>

        <h2>Yüklenen Dosyalar</h2>
        <ul>
            <?php
            // Yüklenen dosyaları listele
            $conn_file = mysqli_connect('localhost', 'root', '', 'file_upload_database');
            if (!$conn_file) {
                die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
            }

            $user_id = $_SESSION['user_id'];
            $sql_files = "SELECT * FROM files WHERE uploaded_by = '$user_id'";
            $result = mysqli_query($conn_file, $sql_files);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<li><a href='" . $row['file_path'] . "' target='_blank'>" . $row['file_name'] . "</a> - <small>" . $row['file_size'] . " bytes</small></li>";
                }
            } else {
                echo "<li>Henüz dosya yüklenmedi.</li>";
            }

            mysqli_close($conn_file);
            ?>
        </ul>
    </div>
</body>
</html>
