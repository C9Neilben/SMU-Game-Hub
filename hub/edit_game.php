<?php
// edit_game.php — pre-filled form to update an existing game
require '../shared/db.php';

$gameId = (int)($_GET['id'] ?? $_POST['game_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM games WHERE game_id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch();

if (!$game) {
    die("Game not found. <a href='admin.php'>Back to admin</a>");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $slug === '') {
        $message = "Title and slug are required.";
    } else {
        $thumbnailPath = $game['thumbnail']; // keep existing unless a new file is uploaded

        if (!empty($_FILES['thumbnail']['name'])) {
            $uploadDir = '../assets/thumbs/';
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $allowed)) {
                $message = "Invalid image type. Use jpg, png, webp, or gif.";
            } else {
                $filename = $slug . '_' . time() . '.' . $ext;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $destination)) {
                    // Delete old thumbnail file if it exists, to avoid orphaned images
                    if ($game['thumbnail'] && file_exists('../' . $game['thumbnail'])) {
                        unlink('../' . $game['thumbnail']);
                    }
                    $thumbnailPath = 'assets/thumbs/' . $filename;
                } else {
                    $message = "Failed to upload new thumbnail.";
                }
            }
        }

        if ($message === '') {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE games SET title = ?, slug = ?, description = ?, thumbnail = ? WHERE game_id = ?"
                );
                $stmt->execute([$title, $slug, $description, $thumbnailPath, $gameId]);
                header('Location: admin.php');
                exit;
            } catch (PDOException $e) {
                $message = "Error: slug might already exist. (" . $e->getMessage() . ")";
            }
        }
    }

    // Refresh $game with attempted values so the form doesn't lose input on error
    $game['title'] = $title;
    $game['slug'] = $slug;
    $game['description'] = $description;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Game - SMU Game Hub</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

    <header>
        <h1>Edit Game</h1>
    </header>

    <main style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <?php if ($message): ?>
            <p style="background:#333; padding:10px; border-radius:6px;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" name="game_id" value="<?= $game['game_id'] ?>">

            <label>Title
                <input type="text" name="title" value="<?= htmlspecialchars($game['title']) ?>" required>
            </label>

            <label>Slug (must match folder name in /games/)
                <input type="text" name="slug" value="<?= htmlspecialchars($game['slug']) ?>" required>
            </label>

            <label>Description
                <textarea name="description" rows="3"><?= htmlspecialchars($game['description']) ?></textarea>
            </label>

            <label>Current thumbnail
                <br>
                <img src="../<?= htmlspecialchars($game['thumbnail'] ?: 'assets/thumbs/default.png') ?>" style="width:100px; border-radius:8px; margin-top:6px;">
            </label>

            <label>Replace thumbnail (optional)
                <input type="file" name="thumbnail" accept="image/*">
            </label>

            <button type="submit">Save Changes</button>
        </form>

        <p style="margin-top:20px;"><a href="admin.php" class="back-link">← Back to Admin</a></p>
    </main>

</body>
</html>