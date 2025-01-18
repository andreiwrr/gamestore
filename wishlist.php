<?php
session_start();
require 'db.php'; // Asigură-te că ai inclus corect fișierul de conexiune la baza de date

// Verifică dacă utilizatorul este conectat
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // ID-ul utilizatorului conectat

// Obține jocurile din wishlist-ul utilizatorului
$wishlistGames = get_wishlist($user_id);

// Verifică dacă există cererea de a șterge un joc
if (isset($_GET['remove_game_id'])) {
    $game_id = $_GET['remove_game_id'];

    // Îndeplinește acțiunea de ștergere
    $sql_remove_game = "DELETE FROM wishlist WHERE user_id = ? AND game_id = ?";
    $stmt_remove_game = mysqli_prepare($conn, $sql_remove_game);
    if ($stmt_remove_game === false) {
        echo "<script>alert('Error preparing the remove query.'); window.location = 'wishlist.php';</script>";
        exit;
    }

    mysqli_stmt_bind_param($stmt_remove_game, "ii", $user_id, $game_id);
    if (mysqli_stmt_execute($stmt_remove_game)) {
        echo "<script>alert('Game removed from wishlist!'); window.location = 'wishlist.php';</script>";
    } else {
        echo "<script>alert('Error removing game.'); window.location = 'wishlist.php';</script>";
    }
    mysqli_stmt_close($stmt_remove_game);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .game-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .game {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .game:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .game img {
            width: 100%;
            height: auto;
        }

        .game-info {
            padding: 10px;
        }

        .game-info h3 {
            font-size: 18px;
            color: #333;
            margin: 0 0 10px;
        }

        .game-info p {
            font-size: 14px;
            color: #777;
            margin: 5px 0;
        }

        .price {
            color: #b8ca3e;
            font-size: 16px;
            margin-top: 5px;
        }

        .remove-button {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 15px;
            background-color: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

        .remove-button:hover {
            background-color: darkred;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .footer a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .footer a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h1>Your Wishlist</h1>

<div class="game-list">
    <?php if (count($wishlistGames) > 0): ?>
        <?php foreach ($wishlistGames as $game): ?>
            <div class="game">
                <img src="<?= htmlspecialchars($game['cover']); ?>" alt="<?= htmlspecialchars($game['title']); ?>">
                <div class="game-info">
                    <h3><?= htmlspecialchars($game['title']); ?></h3>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($game['genre']); ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($game['description']); ?></p>
                    <p class="price">
                        <?php if (isset($game['discount_price'])): ?>
                            <span style="text-decoration: line-through;">$<?= number_format($game['price'], 2); ?></span>
                            <strong>$<?= number_format($game['discount_price'], 2); ?></strong>
                        <?php else: ?>
                            <strong>$<?= number_format($game['price'], 2); ?></strong>
                        <?php endif; ?>
                    </p>
                    <a href="wishlist.php?remove_game_id=<?= $game['id']; ?>" class="remove-button">Remove</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You don't have any games in your wishlist yet.</p>
    <?php endif; ?>
</div>

<div class="footer">
    <a href="index.php">Back to Homepage</a>
</div>

</body>
</html>

