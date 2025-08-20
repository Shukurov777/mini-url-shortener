<?php
// Подключение к БД
$pdo = new PDO("mysql:host=localhost;dbname=urlshort;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Функция генерации короткого кода
function generateCode($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

// Создание короткой ссылки
if (isset($_POST['url']) && !empty($_POST['url'])) {
    $longUrl = trim($_POST['url']);
    $code = generateCode();

    // Проверяем, чтобы код был уникальным
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE code = ?");
    while ($stmt->execute([$code]) && $stmt->fetch()) {
        $code = generateCode();
    }

    $stmt = $pdo->prepare("INSERT INTO urls (code, long_url) VALUES (?, ?)");
    $stmt->execute([$code, $longUrl]);

    $shortUrl = "http://localhost/php-url-shortener/index.php?c=" . $code;
}

// Редирект по короткой ссылке
if (isset($_GET['c'])) {
    $code = $_GET['c'];
    $stmt = $pdo->prepare("SELECT long_url FROM urls WHERE code = ?");
    $stmt->execute([$code]);

    if ($row = $stmt->fetch()) {
        header("Location: " . $row['long_url']);
        exit;
    } else {
        echo "<h2>Ссылка не найдена!</h2>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>URL Shortener</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>🔗 URL Shortener</h1>

    <form method="POST">
      <input type="url" name="url" placeholder="Введите длинную ссылку..." required>
      <button type="submit">Сократить</button>
    </form>

    <?php if (!empty($shortUrl)): ?>
      <div class="result">
        <p>Ваша короткая ссылка:</p>
        <a href="<?= $shortUrl ?>" target="_blank"><?= $shortUrl ?></a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>