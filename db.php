<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'gamestore';

// Conectare la baza de date
$conn = mysqli_connect($host, $username, $password, $dbname);

// Verificăm dacă conexiunea a reușit
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Funcția pentru obținerea tuturor jocurilor
function get_all_games() {
    global $conn;
    $sql = "SELECT id, title, description, cover, price, discount_price, genre, release_date FROM games";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo "Error retrieving games: " . mysqli_error($conn);
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC); // obține toate jocurile într-un singur pas
}

// Funcția pentru obținerea jocurilor după gen
function get_games_by_genre($genre) {
    global $conn;
    $sql = "SELECT id, title, description, cover, price, discount_price, genre, release_date FROM games WHERE genre = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        echo "Error preparing query: " . mysqli_error($conn);
        return [];
    }

    mysqli_stmt_bind_param($stmt, "s", $genre);
    if (!mysqli_stmt_execute($stmt)) {
        echo "Error executing query: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $games = mysqli_fetch_all($result, MYSQLI_ASSOC); // obține toate jocurile pe gen într-un singur pas

    mysqli_stmt_close($stmt);
    return $games;
}

// Funcția pentru adăugarea unui joc în wishlist
function add_to_wishlist($user_id, $game_id) {
    global $conn;

    // Verificăm dacă jocul există în baza de date
    $sql = "SELECT id FROM games WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $game_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $game = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Dacă jocul există, verificăm dacă este deja în wishlist
    if ($game) {
        $sql_check = "SELECT id FROM wishlist WHERE user_id = ? AND game_id = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $game_id);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        mysqli_stmt_close($stmt_check);

        if (mysqli_num_rows($check_result) == 0) {
            // Jocul nu este în wishlist, îl adăugăm
            $sql_insert = "INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $game_id);
            $success = mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
            return $success;
        } else {
            // Jocul este deja în wishlist
            return false;
        }
    } else {
        // Jocul nu există
        return false;
    }
}

// Funcția pentru obținerea jocurilor din wishlist
function get_wishlist($user_id) {
    global $conn;
    $sql = "SELECT games.id, games.title, games.description, games.cover, games.price, games.discount_price, games.genre, games.release_date 
            FROM wishlist 
            JOIN games ON wishlist.game_id = games.id 
            WHERE wishlist.user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Error executing query: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $wishlist = mysqli_fetch_all($result, MYSQLI_ASSOC); // obține toate jocurile din wishlist într-un singur pas

    mysqli_stmt_close($stmt);
    return $wishlist;
}

// Funcția pentru a șterge un joc din wishlist
function remove_game_from_wishlist($user_id, $game_id) {
    global $conn;
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND game_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $game_id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        mysqli_stmt_close($stmt);
        return false;
    }
}
?>
