<?php
// Veritabanı bağlantısı
$servername = "localhost";
$username = "root"; // veritabanı kullanıcı adı
$password = ""; // veritabanı şifresi
$dbname = "user_database"; // veritabanı adı

// Veritabanı bağlantısını oluşturuyoruz
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if (!$conn) {
    die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
}

session_start();

// Kullanıcı zaten giriş yapmışsa yönlendirme
if (isset($_SESSION['user_id'])) {
    header("Location: giriş_paneli.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kullanıcı giriş işlemi
    if (isset($_POST['login'])) {
        $username = $_POST['login_username'];
        $password = $_POST['login_password'];

        // SQL enjeksiyonundan korunmak için prepared statement kullanıyoruz
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);  // "s" string tipi için
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            // Kullanıcı giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role']; // Kullanıcı rolünü de kaydediyoruz

            // Kullanıcının rolüne göre yönlendirme
            if ($_SESSION['role'] == 'admin') {
                header("Location: admin_panel.php"); // Admin paneline yönlendir
            } else {
                header("Location: giriş_paneli.php"); // Kullanıcı paneline yönlendir
            }
            exit();
        } else {
            echo "Geçersiz kullanıcı adı veya şifre!";
        }
    }

    // Kullanıcı kayıt işlemi
    if (isset($_POST['register'])) {
        $username = $_POST['register_username'];
        $email = $_POST['register_email'];
        $password = password_hash($_POST['register_password'], PASSWORD_DEFAULT);

        // SQL enjeksiyonundan korunmak için prepared statement kullanıyoruz
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);  // "sss" string tipi için
        if (mysqli_stmt_execute($stmt)) {
            echo "Kayıt başarılı!";
        } else {
            echo "Hata: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kod Yükleme Deposu</title>
    <link rel="stylesheet" href="ana_sayfaa.css">
</head>
<body>
    <div class="container">
        <h1>Kod Yükleme Deposuna Hoş Geldiniz</h1>

        <div class="forms">
            <!-- Giriş Formu -->
            <div class="login-form">
                <h2>Giriş Yap</h2>
                <form method="POST">
                    <label for="login_username">Kullanıcı Adı:</label>
                    <input type="text" id="login_username" name="login_username" required>

                    <label for="login_password">Şifre:</label>
                    <input type="password" id="login_password" name="login_password" required>

                    <input type="submit" name="login" value="Giriş Yap">
                </form>
            </div>

            <!-- Kayıt Formu -->
            <div class="register-form">
                <h2>Kayıt Ol</h2>
                <form method="POST">
                    <label for="register_username">Kullanıcı Adı:</label>
                    <input type="text" id="register_username" name="register_username" required>

                    <label for="register_email">E-posta:</label>
                    <input type="email" id="register_email" name="register_email" required>

                    <label for="register_password">Şifre:</label>
                    <input type="password" id="register_password" name="register_password" required>

                    <input type="submit" name="register" value="Kaydol">
                </form>
            </div>
        </div>
    </div>
</body>
</html>