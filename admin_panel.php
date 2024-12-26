<?php
// Veritabanı bağlantısı için gerekli bilgiler
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP için varsayılan şifre

// Veritabanlarına bağlanmak için her biri için ayrı bağlantılar
$conn_comment_database = mysqli_connect($servername, $username, $password, "comment_database");
$conn_file_upload_database = mysqli_connect($servername, $username, $password, "file_upload_database");
$conn_user_database = mysqli_connect($servername, $username, $password, "user_database");

// Bağlantı kontrolü
if (!$conn_comment_database) {
    die("comment_database'ye bağlanılamadı: " . mysqli_connect_error());
}

if (!$conn_file_upload_database) {
    die("file_upload_database'ye bağlanılamadı: " . mysqli_connect_error());
}

if (!$conn_user_database) {
    die("user_database'ye bağlanılamadı: " . mysqli_connect_error());
}

session_start();

// Admin girişini kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");  // Admin değilse login sayfasına yönlendir
    exit();
}

// Yorumları silme
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn_comment_database, $sql);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_panel.php"); // Yönlendirme
        exit();
    } else {
        echo "Hata: " . mysqli_error($conn_comment_database);
    }
}

// Dosyaları silme
if (isset($_GET['delete_file'])) {
    $file_id = $_GET['delete_file'];
    $sql = "DELETE FROM files WHERE id = ?";
    $stmt = mysqli_prepare($conn_file_upload_database, $sql);
    mysqli_stmt_bind_param($stmt, "i", $file_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_panel.php"); // Yönlendirme
        exit();
    } else {
        echo "Hata: " . mysqli_error($conn_file_upload_database);
    }
}

// Kullanıcıları silme
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn_user_database, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_panel.php"); // Yönlendirme
        exit();
    } else {
        echo "Hata: " . mysqli_error($conn_user_database);
    }
}
// Arama işlemi
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']); // Kullanıcıdan gelen veriyi temizle
}

// Veritabanından arama sonuçları çekme
if (!empty($search_query)) {
    // Yorumları, dosyaları ve kullanıcıları arama
    $sql = "SELECT id, comment AS name, 'Yorum' AS type FROM comments WHERE comment LIKE ? 
            UNION 
            SELECT id, file_name AS name, 'Dosya' AS type FROM files WHERE file_name LIKE ? 
            UNION 
            SELECT id, username AS name, 'Kullanıcı' AS type FROM users WHERE username LIKE ?";

    // Arama kriterini bind etme
    $stmt = mysqli_prepare($conn, $sql);
    
    $search_term = "%" . $search_query . "%"; // Arama terimi için % ekleniyor (LIKE kullanımı için)

    // Parametreleri bağlama
    mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);

    // Sorguyu çalıştırma
    mysqli_stmt_execute($stmt);

    // Sonuçları al
    $result = mysqli_stmt_get_result($stmt);

    // Sonuçları listele
    if (mysqli_num_rows($result) > 0) {
        echo "<h2>Arama Sonuçları</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>İsim / Yorum / Dosya Adı</th><th>Tür</th></tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>"; // Yorum, dosya adı veya kullanıcı adı
            echo "<td>" . $row['type'] . "</td>"; // Tür
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "Arama kriterlerine uygun sonuç bulunamadı.";
    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="admin_panel.css">
</head>
<body>
    <!-- Logo -->
    <div class="logo-container">
        <img src="ANONİM.png" alt="Logo" class="logo">
    </div>

    <div class="search-container">
        <form action="admin_paneli.php" method="get">
            <input type="text" name="search" placeholder="Arama yap..." class="search-input">
            <button type="submit" class="search-button">Ara</button>
        </form>
    </div>

    <h1>Admin Paneline Hoşgeldiniz</h1>

    <h2>Yorumları Sil</h2>
    <table>
        <tr>
            <th>Yorum ID</th>
            <th>Yorum</th>
            <th>Sil</th>
        </tr>
        
        <?php
        // Yorumları listele
        $sql = "SELECT * FROM comments";
        $result = mysqli_query($conn_comment_database, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['comment'] . "</td>";
            echo "<td><a href='admin_panel.php?delete_comment=" . $row['id'] . "'>Sil</a></td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h2>Dosyaları Sil</h2>
    <table>
        <tr>
            <th>Dosya ID</th>
            <th>Dosya Adı</th>
            <th>Sil</th>
        </tr>
        <?php
        // Dosyaları listele
        $sql = "SELECT * FROM files";
        $result = mysqli_query($conn_file_upload_database, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['file_name'] . "</td>";
            echo "<td><a href='admin_panel.php?delete_file=" . $row['id'] . "'>Sil</a></td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h2>Kullanıcıları Sil</h2>
    <table>
        <tr>
            <th>Kullanıcı ID</th>
            <th>Kullanıcı Adı</th>
            <th>Sil</th>
        </tr>
        <?php
        // Kullanıcıları listele
        $sql = "SELECT * FROM users WHERE role != 'admin'"; // Admin rolünde olmayanları listele
        $result = mysqli_query($conn_user_database, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td><a href='admin_panel.php?delete_user=" . $row['id'] . "'>Sil</a></td>";
            echo "</tr>";
        }
        ?>
        </table>
    
        <!-- Çıkış butonu -->
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Çıkış Yap</a>
        </div>
    
    </body>
    </html>
