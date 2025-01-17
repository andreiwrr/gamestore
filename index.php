<?php
session_start();
require 'db.php';

// Verifică dacă utilizatorul este conectat
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // ID-ul utilizatorului conectat

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
if ($selectedCategory != '') {
    $filteredGames = get_games_by_genre($selectedCategory);
} else {
    $filteredGames = get_all_games();
}

if (isset($_GET['add_to_wishlist'])) {
    $game_title = $_GET['add_to_wishlist'];
    $success = add_to_wishlist($user_id, $game_title);
    if ($success) {
        echo "<p>Jocul a fost adăugat în wishlist!</p>";
    } else {
        echo "<p>Jocul este deja în wishlist sau nu există.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1b2838;
            color: #c7d5e0;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #66c0f4;
        }

        .game-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .game {
            background-color: #2a475e;
            border: 1px solid #1b2838;
            border-radius: 5px;
            width: 300px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .game:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        .game img {
            width: 100%;
            height: auto;
        }

        .game-content {
            padding: 15px;
        }

        .game h3 {
            font-size: 18px;
            margin: 0 0 10px;
            color: #66c0f4;
        }

        .game p {
            font-size: 14px;
            line-height: 1.5;
            margin: 5px 0;
        }

        .price {
            margin: 10px 0;
        }

        .price span {
            font-size: 16px;
            color: #b8ca3e;
        }

        .discount {
            text-decoration: line-through;
            color: #c0c0c0;
            margin-right: 10px;
        }
        
        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF5733; 
            color: white;
            font-size: 16px;
            text-decoration: none; 
            border-radius: 5px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); 
            transition: background-color 0.3s, transform 0.3s; 
        }

        .logout-button:hover {
            background-color: #C70039; 
            transform: translateY(-3px); 
        }
        .wishlist-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #FF5733; /* Culoarea de fundal */
    color: white; /* Culoarea textului */
    font-size: 16px;
    text-decoration: none;
    border-radius: 5px; /* Colțuri rotunjite */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Umbră */
    transition: background-color 0.3s, transform 0.3s; /* Tranziție pentru hover */
}

.wishlist-button:hover {
    background-color: #C70039; /* Culoare schimbată la hover */
    transform: translateY(-3px); /* Mișcare la hover */
}
    </style>
</head>
<body>
    <div class="container">
        <a href="wishlist.php" class="wishlist-button">View Wishlist</a>
        <a href="logout.php?logout=true" class="logout-button">Logout</a>

        <h1>Game Store</h1>

        <!-- Formularul de selectare a categoriei -->
        <form action="index.php" method="get" style="text-align: center; margin-bottom: 20px;">
            <label for="category" style="font-size: 18px;">Selectați un gen:</label>
            <select name="category" id="category" style="padding: 5px; font-size: 16px;">
                <option value="">Toate jocurile</option>
                <option value="Action" <?= $selectedCategory == 'Action' ? 'selected' : '' ?>>Action</option>
                <option value="Adventure" <?= $selectedCategory == 'Adventure' ? 'selected' : '' ?>>Adventure</option>
                <option value="RPG" <?= $selectedCategory == 'RPG' ? 'selected' : '' ?>>RPG</option>
                <option value="Sports" <?= $selectedCategory == 'Sports' ? 'selected' : '' ?>>Sports</option>
            </select>
            <button type="submit" style="padding: 5px 10px; font-size: 16px;">Filtrare</button>
        </form>

        <!-- Afișarea jocurilor filtrate -->
        <div class="game-list">
            <?php if (count($filteredGames) > 0): ?>
                <?php foreach ($filteredGames as $game): ?>
                    <div class="game">
                        <img src="<?= htmlspecialchars($game['cover']); ?>" alt="<?= htmlspecialchars($game['title']); ?>">
                        <div class="game-content">
                            <h3><?= htmlspecialchars($game['title']); ?></h3>
                            <p><strong>Gen:</strong> <?= htmlspecialchars($game['genre']); ?></p>
                            <p><strong>Descriere:</strong> <?= htmlspecialchars($game['description']); ?></p>
                            <p><strong>Data lansării:</strong> <?= htmlspecialchars($game['release_date']); ?></p>
                            <p class="price">
                                <?php if (isset($game['discount_price'])): ?>
                                    <span class="discount">$<?= number_format($game['price'], 2); ?></span>
                                    <span>$<?= number_format($game['discount_price'], 2); ?></span>
                                <?php else: ?>
                                    <span>$<?= number_format($game['price'], 2); ?></span>
                                <?php endif; ?>
                            </p>
                            <a href="index.php?add_to_wishlist=<?= urlencode($game['title']); ?>">Adaugă la wishlist</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nu există jocuri în această categorie.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>