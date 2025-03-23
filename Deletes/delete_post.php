<?php
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
    require 'db_connect.php';

    $_SESSION['checking'] = false;

    //Kollar så användare är inloggad
    $INLOGGAD = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

    $query = "DELETE FROM Posts WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_SESSION['delete']]);
    $stmt->execute(['id' => $_SESSION['pictureDelete']]);

?>
