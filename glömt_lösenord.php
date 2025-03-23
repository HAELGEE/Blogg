<?php
require 'db_connect.php';

$message = "";
$showBackButton = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) { 
        $newPasswordPlain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
        $hashedPassword = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE Users SET password = ? WHERE email = ?");
        $update->execute([$hashedPassword, $email]); 

        
        $message = "<p style='color:green;'> Ditt nya lösenord är: <strong>$newPasswordPlain</strong></p>  <br>";
        $showBackButton = true; 
    } else {
        $message = "<p style='color:red;'> E-postadressen hittades inte.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Glömt Lösenord</title>
  <link rel="stylesheet" href="style.css">
</head>
        <body class="Amer">

        <div class="body-container">

        <div class="body-logo">
            <img src="./img\transparent logo.png" alt="Logotyp">
        </div>

     
  <div class="body-form-container">

  <h2>Glömt Lösenord</h2>
  <br>
    
  
    <?php if (!empty($message)) echo $message; ?>

    <?php if ($showBackButton): ?>
      <form action="login.php" method="get">
      
        <button type="submit">⬅️ Tillbaka till inloggning</button>
    
      </form>
     
    <?php else: ?>
      <form method="post">
        <label for="email">Skriv in din e-postadress:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Skapa nytt lösenord</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="footer">
    <p>&copy; Alla rättigheter förbehållna. Grupp 3 </p>
</div>
</body>
</html>
