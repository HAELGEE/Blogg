<?php
session_start();
require 'db_connect.php';

// kollar session så man inte kan kommentera om man inte är inloggad
if (!isset($_SESSION['user_id'])) {
    die("You need to be logged in to comment.");
}

// kollar så man inte skickar in något tomt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && !empty($_POST['comment'])) {
    $comment = $_POST['comment'];
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; 

    //statmenten för att inserta kommentar in i comments table
    $sql = 'INSERT INTO comments (userID, postID, textInput, timeCreated) 
            VALUES (:user_id, :post_id, :comment, NOW())';
    
    $stmt = $pdo->prepare($sql);
    
   
    $stmt->execute([
        'user_id' => $user_id,
        'post_id' => $post_id,
        'comment' => $comment
    ]);

    
    header("Location: post.php?id=$post_id");
    exit; 
} else {
   
    header("Location: post.php?id=$post_id");
    exit;
}