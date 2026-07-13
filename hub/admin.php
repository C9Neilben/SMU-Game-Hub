<?php
require '../shared/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $slug === '') {
        $message = "Title and slug are required.";
    } else {
        $thumbnailPath = null;

        // Handle file upload
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
                    $thumbnailPath = 'assets/thumbs/' . $filename;
                } else {
                    $message = "Failed to upload thumbnail.";
                }
            }
        }

        // Only insert if no error so far
        if ($message === '') {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO games (title, slug, description, thumbnail) VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$title, $slug, $description, $thumbnailPath]);
                $message = "Game '$title' added successfully.";
            } catch (PDOException $e) {
                $message = "Error: slug might already exist. (" . $e->getMessage() . ")";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Add Game</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

    <header>
        <h1>Add a New Game</h1>
    </header>

    <main style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <?php if ($message): ?>
            <p style="background:#333; padding:10px; border-radius:6px;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:12px;">
            <label>Title
                <input type="text" name="title" required>
            </label>

            <label>Slug (must match folder name in /games/)
                <input type="text" name="slug" required placeholder="e.g. wordle">
            </label>

            <label>Description
                <textarea name="description" rows="3"></textarea>
            </label>

            <label>Thumbnail image
                <input type="file" name="thumbnail" accept="image/*">
            </label>

            <button type="submit">Add Game</button>
        </form>

        <a href="index.php" class="back-link">← Back to Hub</a>
    </main>

</body>
</html>