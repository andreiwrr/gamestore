<?php
require 'db.php';

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$validCategories = ['Action', 'Adventure', 'RPG', 'Sports'];

if ($selectedCategory && !in_array($selectedCategory, $validCategories)) {
    die("Invalid category selected.");
}

if ($selectedCategory) {
    $filteredGames = get_games_by_genre($selectedCategory);
    $headerMessage = "Displaying games in the '" . htmlspecialchars($selectedCategory) . "' genre";
} else {
    $filteredGames = get_all_games();
    $headerMessage = "Displaying all games";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Welcome to the Game Store</h1>

    <!-- Genre selection form -->
    <form action="index.php" method="get">
        <label for="category">Select a genre:</label>
        <select name="category" id="category">
            <option value="">All Genres</option>
            <?php foreach ($validCategories as $category): ?>
                <option value="<?= $category; ?>" <?= $selectedCategory == $category ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter Games</button>
    </form>

    <!-- Header Message -->
    <h2><?= $headerMessage; ?></h2>

    <!-- Display the filtered games -->
    <div class="game-list">
        <?php if (!empty($filteredGames)): ?>
            <?php foreach ($filteredGames as $game): ?>
                <div class="game">
                    <h3><?= htmlspecialchars($game['title']); ?></h3>
                    <img src="<?= !empty($game['cover']) ? htmlspecialchars($game['cover']) : 'default-cover.png'; ?>" 
                         alt="<?= htmlspecialchars($game['title']); ?> Cover" width="200"/>
                    <p><strong>Description:</strong> <?= htmlspecialchars($game['description']); ?></p>
                    <p><strong>Release Date:</strong> <?= htmlspecialchars($game['release_date']); ?></p>
                    <?php if (isset($game['discount_price'])): ?>
                        <p><strong>Price:</strong> <span style="text-decoration: line-through;">$<?= number_format($game['price'], 2); ?></span>
                        <strong>Discounted Price:</strong> $<?= number_format($game['discount_price'], 2); ?></p>
                    <?php else: ?>
                        <p><strong>Price:</strong> $<?= number_format($game['price'], 2); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Sorry, there are no games available in the <strong><?= htmlspecialchars($selectedCategory); ?></strong> genre right now.</p>
        <?php endif; ?>
    </div>
</body>
</html>
