<?php
require '../shared/db.php';

$stmt = $pdo->query("SELECT * FROM games WHERE is_active = 1 ORDER BY created_at DESC");
$games = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMU Game Hub</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

    <header>
        <h1>SMU Game Hub</h1>
        <a href="admin.php" class="add-game-btn">Manage Games</a>
    </header>

    <main class="grid">
        <?php if (empty($games)): ?>
            <p>No games yet. Check back soon!</p>
        <?php else: ?>
            <?php foreach ($games as $game): ?>
                <a class="game-card" href="../games/<?= htmlspecialchars($game['slug']) ?>/index.php">
                    <img src="../<?= htmlspecialchars($game['thumbnail'] ?: 'assets/thumbs/default.png') ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                    <h2><?= htmlspecialchars($game['title']) ?></h2>
                    <p><?= htmlspecialchars($game['description']) ?></p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>
</html>