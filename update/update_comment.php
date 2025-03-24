<?php 
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $new_text = trim($_POST['textInput']);

    if (!empty($new_text)) {
        $update_sql = 'UPDATE Comments SET textInput = :textInput WHERE id = :post_id';
        $stmt_update = $pdo->prepare($update_sql);
        $stmt_update->execute([           
            'textInput' => $new_text,
            'post_id' => $post_id
        ]);        
    } else {
        $error_message = "Both fields must be filled.";
    }
}

?>