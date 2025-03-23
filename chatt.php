<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Du måste vara inloggad för att använda chatten.");
}

$user_id = $_SESSION['user_id'];

// Hämta alla konversationer där användaren är antingen avsändare eller mottagare
$stmt = $pdo->prepare("
    SELECT c.text, c.senderID, c.receiverID, c.timeCreated, u.username AS sender_name 
    FROM chatt c
    JOIN users u ON c.senderID = u.id
    WHERE c.senderID = :userID OR c.receiverID = :userID
    ORDER BY c.timeCreated ASC
");
$stmt->execute([':userID' => $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hämta lista över personer som har chattat med inloggad användare
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username 
    FROM users u
    JOIN chatt c ON (c.senderID = u.id OR c.receiverID = u.id)
    WHERE u.id != :userID
");
$stmt->execute([':userID' => $user_id]);
$chat_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Om användaren skickar ett svar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message_text = trim($_POST['message_text']);

    if (!empty($receiver_id) && !empty($message_text)) {
        $stmt = $pdo->prepare("INSERT INTO chatt (text, senderID, receiverID, timeCreated) 
                               VALUES (:text, :senderID, :receiverID, NOW())");
        $stmt->execute([
            ':text' => $message_text,
            ':senderID' => $user_id,
            ':receiverID' => $receiver_id
        ]);
    }
    header("Location: profile.php");
    exit;
}
?>