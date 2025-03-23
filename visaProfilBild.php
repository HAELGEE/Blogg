<?php

if (!$_SESSION['user_id']) {
header("location: login.php");
exit;
} else {
require 'db_connect.php';
}

if($_SESSION['guest'] == true) {

    try {
        
        $userID = $_SESSION['GuestID'];

        $stmt = $pdo->prepare("SELECT image FROM Users WHERE id = :id");
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $base64Image = $result['image'];

        // if($base64Image) {       
        //     echo '<img style ="width: 300px; " src="data:image/*;base64,' . $base64Image . '" />';
        // } 

        $_SESSION['guest'] == false;

    } catch (PDOException $e) {
        echo "<p style='color: red;'>Fel: " . $e->getMessage() . "</p";
    }

} else {

    try {
        $_SESSION['check'] = true;
        $userID = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT id, image FROM Posts WHERE userID = :userID ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $base64Image = $result['image'];

        $_SESSION['pictureID'] = $result['id'];

        if($base64Image) {       
            echo '<img style ="width: 300px; " src="data:image/*;base64,' . $base64Image . '" />';
        } 
        /*else 
        {
            echo "<p style='color: red;'>Ingen bild hittades med detta ID.</p>";
        }*/
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Fel: " . $e->getMessage() . "</p";
    }
}
?>