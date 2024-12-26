<?php
session_start();

// Kullanıcı girişini kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'user_database');
if (!$conn) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini almak
$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Veritabanı sorgu hatası: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

// Profil bilgilerini güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcıdan gelen verileri al ve güvenli hale getir
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);
    $github = mysqli_real_escape_string($conn, $_POST['github']);
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $profile_picture = $_POST['profile_picture']; // Fotoğraf için başka işlem yapılacak

    // Sosyal medya hesaplarını güncelle
    $sql_update = "UPDATE users SET instagram='$instagram', github='$github', facebook='$facebook' WHERE id='$user_id'";
    if (mysqli_query($conn, $sql_update)) {
        echo "Profil başarıyla güncellendi.";
        header("Refresh: 2; url=profil.php");  // Güncellemeyi yaptıktan sonra sayfayı yenile
    } else {
        echo "Hata: " . mysqli_error($conn);
    }

    // Profil fotoğrafı yükleme işlemi
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $uploaded_file = $_FILES['profile_picture'];
        $target_dir = "uploads/"; // Fotoğrafların yükleneceği klasör

        // Yüklenen dosyanın adını al
        $target_file = $target_dir . uniqid() . basename($uploaded_file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Dosya türü kontrolü (sadece JPG, PNG, JPEG dosyaları kabul edilir)
        $allowed_types = ["jpg", "png", "jpeg"];
        if (in_array($imageFileType, $allowed_types)) {
            // Dosyayı hedef dizine yükle
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {
                // Yüklenen dosyanın yolunu veritabanına kaydet
                $sql_update_picture = "UPDATE users SET profile_picture='$target_file' WHERE id='$user_id'";
                if (mysqli_query($conn, $sql_update_picture)) {
                    echo "Profil fotoğrafınız başarıyla güncellendi.";
                } else {
                    echo "Veritabanı güncelleme hatası: " . mysqli_error($conn);
                }
            } else {
                echo "Dosya yüklenirken bir hata oluştu.";
            }
        } else {
            echo "Geçersiz dosya türü. Sadece JPG, PNG veya JPEG dosyaları yükleyebilirsiniz.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim</title>
    <link rel="stylesheet" href="profilim.css">
</head>
<body>
    <div class="container">
       

        <div class="profile">
            <h2>Profil Bilgileri</h2>
            <div class="profile-picture">
                <!-- Profil fotoğrafı -->
                <?php if ($user['profile_picture'] && file_exists($user['profile_picture'])) { ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profil Fotoğrafı">
                <?php } else { ?>
                    <img src="profil_picture.jpg" alt="Profil Fotoğrafı">
                <?php } ?>
            </div>
            <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>E-posta:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Üyelik Tarihi:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>

            <!-- Sosyal medya hesaplarını göstermek -->
            <h3>Sosyal Medya Hesapları</h3>
            <p>
                <strong>Instagram:</strong> 
                <?php if ($user['instagram']) { ?>
                    <a href="https://www.instagram.com/<?php echo htmlspecialchars($user['instagram']); ?>" target="_blank">Instagram Profilim</a>
                <?php } else { echo "Paylaşılmadı"; } ?>
            </p>
            <p>
                <strong>GitHub:</strong> 
                <?php if ($user['github']) { ?>
                    <a href="https://github.com/<?php echo htmlspecialchars($user['github']); ?>" target="_blank">GitHub Profilim</a>
                <?php } else { echo "Paylaşılmadı"; } ?>
            </p>
            <p>
                <strong>Facebook:</strong> 
                <?php if ($user['facebook']) { ?>
                    <a href="https://www.facebook.com/<?php echo htmlspecialchars($user['facebook']); ?>" target="_blank">Facebook Profilim</a>
                <?php } else { echo "Paylaşılmadı"; } ?>
            </p>

            <!-- Profil güncelleme formu -->
            <h3>Profil Bilgilerini Güncelle</h3>
            <form method="POST" action="profil.php" enctype="multipart/form-data">
                <label for="instagram">Instagram:</label>
                <input type="text" id="instagram" name="instagram" value="<?php echo htmlspecialchars($user['instagram']); ?>">
                
                <label for="github">GitHub:</label>
                <input type="text" id="github" name="github" value="<?php echo htmlspecialchars($user['github']); ?>">
                
                <label for="facebook">Facebook:</label>
                <input type="text" id="facebook" name="facebook" value="<?php echo htmlspecialchars($user['facebook']); ?>">

                <!-- Profil fotoğrafı yükleme -->
                <label for="profile_picture">Profil Fotoğrafı:</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                
                <input type="submit" value="Güncelle">
            </form>

            
        </div>
    </div>
</body>
</html>
