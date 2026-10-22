<?php
$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_NAME') ?: 'portfolio_db';
$user = getenv('DB_USER') ?: 'portfolio_user';
$pass = getenv('DB_PASSWORD') ?: '';

$projects = [];
$skills = [];
$dbError = false;

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $projects = $pdo->query(
        'SELECT id, title, description, project_url FROM projects ORDER BY id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $skills = $pdo->query(
        'SELECT name, level FROM skills ORDER BY id'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbError = true;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portfolio cá nhân - Trần Ngọc Huy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <a class="logo" href="#">HUY.PORTFOLIO</a>
    <nav>
        <a href="#about">Giới thiệu</a>
        <a href="#skills">Kỹ năng</a>
        <a href="#projects">Dự án</a>
        <a href="#contact">Liên hệ</a>
    </nav>
</header>

<main>
    <section class="hero" id="about">
        <p class="eyebrow">XIN CHÀO, TÔI LÀ</p>
        <h1>Trần Ngọc Huy</h1>
        <p>Sinh viên ngành Công nghệ thông tin, yêu thích phát triển phần mềm và triển khai hệ thống bằng Docker.</p>
        <a class="button" href="#projects">Khám phá dự án</a>
    </section>

    <?php if ($dbError): ?>
        <p class="notice">Chưa kết nối được cơ sở dữ liệu. Hãy kiểm tra cấu hình Docker.</p>
    <?php endif; ?>

    <section class="section" id="skills">
        <h2>Kỹ năng</h2>
        <div class="grid">
            <?php foreach ($skills as $skill): ?>
                <article class="card">
                    <h3><?= h($skill['name']) ?></h3>
                    <div class="progress">
                        <span style="width: <?= min(100, (int)$skill['level']) ?>%"></span>
                    </div>
                    <p><?= min(100, (int)$skill['level']) ?>%</p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section" id="projects">
        <h2>Dự án tiêu biểu</h2>
        <div class="grid">
            <?php foreach ($projects as $project): ?>
                <article class="card">
                    <h3><?= h($project['title']) ?></h3>
                    <p><?= h($project['description']) ?></p>
                    <a href="<?= h($project['project_url']) ?>">Xem chi tiết</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section contact" id="contact">
        <h2>Liên hệ</h2>
        <p>Mã sinh viên: DTC245180088</p>
        <p>Email: tranngochuy2509066@gmail.com</p>
    </section>
</main>

<footer>&copy; <?= date('Y') ?> Trần Ngọc Huy - Portfolio cá nhân</footer>
</body>
</html>
