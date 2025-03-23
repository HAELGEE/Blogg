<?php
 require 'db_connect.php';

 if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    
    if (strlen($newPassword) < 8) {
      echo "<div class='error'>Det nya lösenordet måste vara minst 8 tecken</div>";
    }
    
    elseif ($newPassword !== $confirmPassword) {
        echo "<div class='error'> Nytt lösenord och bekräftat lösenord måste vara samma</div>";
    }
    else {
        
        $stmt = $pdo->prepare("SELECT password FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
           
            if (password_verify($oldPassword, $user['password'])) {
                
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE Users SET password = ? WHERE email = ?");
                $update->execute([$hashedNewPassword, $email]);

                echo "<div class='success'>Lösenord har uppdaterats!</div>";
            } else {
                echo "<div class='error'>Felaktig gammal lösenord</div>";
            }
        } else {
            echo "<div class='error'>E-postadressen hittades inte</div>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Byt Lösenord</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<body class="Amer">
<div class="body-container">

        <div class="body-logo">
        <a href="login.php">
            <img src="./img\transparent logo.png" alt="Logotyp">
        </div>

   <div class="body-form-container">
    <h2>Byt Lösenord</h2>
    <br>
    <form method="post">
      <label for="email">E-postadress:</label>
      <input type="email" id="email" name="email" required>

      <label for="old_password">Nuvarande Lösenord:</label>
      <input type="password" id="old_password" name="old_password" required>

      <label for="new_password">Nytt Lösenord:</label>
      <input type="password" id="new_password" name="new_password" required>

      <label for="confirm_password">Bekräfta Nytt Lösenord:</label>
      <input type="password" id="confirm_password" name="confirm_password" required><br>

      <div class="register">
      <button type="submit">Byt Lösenord</button>
      </div>
    </form>
    <br>
    <a href="login.php">Logga in här</a>
  </div>

  <div class="footer">
    <p>&copy; Alla rättigheter förbehållna. Grupp 3 </p>
</div>

</body>
</html>
