<?php
function add_to_wishlist($user_id, $game_id) {
    global $conn;

    // Funcție internă pentru verificarea existenței unui utilizator sau joc
    function check_exists($table, $column, $value) {
        global $conn;
        $sql = "SELECT id FROM $table WHERE $column = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            die("Eroare la pregătirea interogării: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $value);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_num_rows($result) > 0;
    }

    // 1. Verifică dacă utilizatorul există
    if (!check_exists('users', 'id', $user_id)) {
        return "Utilizatorul nu există!";
    }

    // 2. Verifică dacă jocul există
    if (!check_exists('games', 'id', $game_id)) {
        return "Jocul nu există!";
    }

    // 3. Verifică dacă jocul este deja în wishlist
    $sql_check_wishlist = "SELECT id FROM wishlist WHERE user_id = ? AND game_id = ?";
    $stmt_check_wishlist = mysqli_prepare($conn, $sql_check_wishlist);
    if (!$stmt_check_wishlist) {
        die("Eroare la pregătirea interogării pentru wishlist: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt_check_wishlist, "ii", $user_id, $game_id);
    mysqli_stmt_execute($stmt_check_wishlist);
    $wishlist_result = mysqli_stmt_get_result($stmt_check_wishlist);

    if (mysqli_num_rows($wishlist_result) > 0) {
        mysqli_stmt_close($stmt_check_wishlist);
        return "Acest joc este deja în wishlist-ul tău!";
    }

    // 4. Adaugă jocul în wishlist
    $sql_insert_wishlist = "INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert_wishlist);
    if (!$stmt_insert) {
        die("Eroare la pregătirea interogării de adăugare în wishlist: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $game_id);
    $execution_result = mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    if ($execution_result) {
        return "Jocul a fost adăugat în wishlist!";
    } else {
        return "Eroare la adăugarea jocului în wishlist: " . mysqli_error($conn);
    }
}
?>

