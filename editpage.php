<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require 'db.php';

// Preia jocul pentru editare
$titleToEdit = $_GET['title'] ?? '';
$gameToEdit = get_game_by_title($titleToEdit);

if (!$gameToEdit) {
    die("Game not found.");
}

// Gestionează procesul de actualizare a jocului
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifică CSRF Token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Validare input
    $updatedData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'genre' => $_POST['genre'],
        'price' => filter_var($_POST['price'], FILTER_VALIDATE_FLOAT),
        'cover' => $gameToEdit['cover'] // Păstrează imaginea veche dacă nu s-a schimbat
    ];

    if (empty($updatedData['title']) || empty($updatedData['description']) || empty($updatedData['genre']) || $updatedData['price'] <= 0) {
        die("Invalid input data.");
    }

    // Validare lungime titlu și descriere
    if (strlen($updatedData['title']) < 3 || strlen($updatedData['description']) < 10) {
        die("Title or description is too short.");
    }

    // Dacă s-a încărcat o imagine nouă
    if (!empty($_FILES['cover']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['cover']['type'], $allowedTypes)) {
            die("Invalid file type. Only JPG, PNG, and GIF are allowed.");
        }

        // Verifică dimensiunea fișierului
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['cover']['size'] > $maxFileSize) {
            die("File size exceeds the limit of 5MB.");
        }

        // Verifică dacă fișierul a fost încărcat corect
        if ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
            die("Error uploading cover image.");
        }

        // Dacă există o imagine veche, șterge-o
        if (file_exists($gameToEdit['cover'])) {
            unlink($gameToEdit['cover']);
        }

        // Încarcă imaginea nouă
        $coverPath = 'covers/' . basename($_FILES['cover']['name']);
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
            $updatedData['cover'] = $coverPath;
        } else {
            die("Error uploading cover image.");
        }
    }

    // Actualizează jocul
    $updateSuccess = update_game($titleToEdit, $updatedData);

    if ($updateSuccess) {
        header("Location: index.php?message=Game updated successfully");
        exit;
    } else {
        echo "Error updating the game.";
    }
}

// Generează un token CSRF
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
    <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
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
