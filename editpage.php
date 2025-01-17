<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$titleToEdit = $_GET['title'] ?? '';
$gameToEdit = get_game_by_title($titleToEdit);

if (!$gameToEdit) {
    die("Game not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $updatedData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'genre' => $_POST['genre'],
        'price' => (float)$_POST['price'],
        'cover' => $gameToEdit['cover']
    ];

    if (empty($updatedData['title']) || empty($updatedData['description']) || empty($updatedData['genre']) || $updatedData['price'] <= 0) {
        die("Invalid input data.");
    }

    if (!empty($_FILES['cover']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['cover']['type'], $allowedTypes)) {
            die("Invalid file type. Only JPG, PNG, and GIF are allowed.");
        }

        $coverPath = 'covers/' . basename($_FILES['cover']['name']);
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
            $updatedData['cover'] = $coverPath;
        }
    }

    $updateSuccess = update_game($titleToEdit, $updatedData);

    if ($updateSuccess) {
        header("Location: index.php?message=Game updated successfully");
        exit;
    } else {
        echo "Error updating the game.";
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Game - <?= htmlspecialchars($gameToEdit['title']); ?></title>
</head>
<body>
    <h1>Edit Game - <?= htmlspecialchars($gameToEdit['title']); ?></h1>
    <form method="post" action="save_edit.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($gameToEdit['title']); ?>" required>
        <br>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required><?= htmlspecialchars($gameToEdit['description']); ?></textarea>
        <br>

        <label for="genre">Genre:</label>
        <input type="text" name="genre" id="genre" value="<?= htmlspecialchars($gameToEdit['genre']); ?>" required>
        <br>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" value="<?= htmlspecialchars($gameToEdit['price']); ?>" required>
        <br>

        <label for="cover">Cover Image:</label>
        <input type="file" name="cover" id="cover">
        <br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>