<?php
$host = 'localhost';  // Schimbă cu detaliile serverului tău
$user = 'root';       // Numele utilizatorului MySQL
$password = '';       // Parola MySQL
$database = 'gamestore'; // Numele bazei de date

// Conectează-te la baza de date
$conn = mysqli_connect($host, $user, $password, $database);

// Verifică dacă conexiunea a fost realizată cu succes
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Funcție pentru obținerea tuturor jocurilor
function get_all_games() {
    global $conn;
    $sql = "SELECT * FROM games";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Funcție pentru obținerea jocurilor în funcție de gen
function get_games_by_genre($genre) {
    global $conn;
    $sql = "SELECT * FROM games WHERE genre = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $genre);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Funcție pentru obținerea wishlist-ului unui utilizator
function get_wishlist($user_id) {
    global $conn;

    $sql = "SELECT games.id, games.title, games.cover, games.genre, games.description, games.price, games.discount_price 
            FROM wishlist 
            JOIN games ON wishlist.game_id = games.id 
            WHERE wishlist.user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $wishlistGames = mysqli_fetch_all($result, MYSQLI_ASSOC);
            return $wishlistGames;
        }
    }

    return []; // Dacă nu există jocuri în wishlist, întoarcem un array gol
}

// Funcție pentru adăugarea unui joc în wishlist
function add_to_wishlist($user_id, $game_title) {
    global $conn;

    // Verifică dacă jocul există deja în wishlist
    $sql_check = "SELECT * FROM wishlist JOIN games ON wishlist.game_id = games.id 
                  WHERE wishlist.user_id = ? AND games.title = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "is", $user_id, $game_title);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        return false; // Jocul există deja în wishlist
    }

    // Obține ID-ul jocului pe baza titlului
    $sql_game_id = "SELECT id FROM games WHERE title = ?";
    $stmt_game_id = mysqli_prepare($conn, $sql_game_id);
    mysqli_stmt_bind_param($stmt_game_id, "s", $game_title);
    mysqli_stmt_execute($stmt_game_id);
    $result_game_id = mysqli_stmt_get_result($stmt_game_id);
    $game = mysqli_fetch_assoc($result_game_id);

    if ($game) {
        $game_id = $game['id'];

        // Adaugă jocul în wishlist
        $sql_add = "INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)";
        $stmt_add = mysqli_prepare($conn, $sql_add);
        mysqli_stmt_bind_param($stmt_add, "ii", $user_id, $game_id);
        return mysqli_stmt_execute($stmt_add);
    }

    return false; // Jocul nu a fost găsit
}

// Funcție pentru a șterge un joc din wishlist
function remove_from_wishlist($user_id, $game_id) {
    global $conn;

    // Verifică dacă jocul există în wishlist
    $sql_check = "SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $game_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Dacă jocul există, îl ștergem din wishlist
        $sql_delete = "DELETE FROM wishlist WHERE user_id = ? AND game_id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $game_id);
        return mysqli_stmt_execute($stmt_delete);
    }

    return false; // Jocul nu există în wishlist
}
?>
