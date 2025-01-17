<?php
function add_to_wishlist($user_id, $game_id) {
    global $conn;

    // 1. Verifică dacă utilizatorul există în tabela 'users'
    $sql_user_check = "SELECT id FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($conn, $sql_user_check);
    if (!$stmt_user) {
        die("Eroare la pregătirea interogării pentru utilizator: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $user_result = mysqli_stmt_get_result($stmt_user);

    if (mysqli_num_rows($user_result) == 0) {
        mysqli_stmt_close($stmt_user);
        return "Utilizatorul nu există!";
    }

    // 2. Verifică dacă jocul există în tabela 'games'
    $sql_game_check = "SELECT id FROM games WHERE id = ?";
    $stmt_game = mysqli_prepare($conn, $sql_game_check);
    if (!$stmt_game) {
        die("Eroare la pregătirea interogării pentru joc: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_game, "i", $game_id);
    mysqli_stmt_execute($stmt_game);
    $game_result = mysqli_stmt_get_result($stmt_game);

    if (mysqli_num_rows($game_result) == 0) {
        mysqli_stmt_close($stmt_game);
        return "Jocul nu există!";
    }

    // 3. Verifică dacă jocul este deja în wishlist-ul utilizatorului
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

    // Închide toate statement-urile
    mysqli_stmt_close($stmt_check_wishlist);
    mysqli_stmt_close($stmt_game);
    mysqli_stmt_close($stmt_user);
}
?>
