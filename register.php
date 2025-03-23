<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    if (!empty($firstname) && !empty($lastname) && !empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if (strlen($password) >= 8) {
            if ($password === $confirm_password)
             {
               
                $checkUser = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
                $checkUser->bindParam(':email', $email);
                $checkUser->bindParam(':username', $username);
                $checkUser->execute();

                if ($checkUser->rowCount() > 0) {
                    echo "<div class='error'>Användarnamn eller E-post finns redan!</div>";
                } else {
                   
                    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, email, password) VALUES (:firstname, :lastname, :username, :email, :password)");
                    $stmt->bindParam(':firstname', $firstname);
                    $stmt->bindParam(':lastname', $lastname);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hash_password);

                    if ($stmt->execute())
                     {
                        echo "<div class='success'>Registrering lyckades!</div>";
                    } 
                    else 
                    {
                        echo "<div class='error'>Något gick fel!</div>";
                    }
                }
            } 
            else 
            {
                echo "<div class='error'>Lösenorden matchar inte!</div>";
            }
        } else {
            echo "<div class='error'>Lösenordet måste vara minst 8 tecken långt!</div>";
        }
    } else {
        echo "<div class='error'>Alla fält måste fyllas i!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrering</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="Amer">
    <div class="body-container">
       
    <div class="body-logo">
    <a href="login.php">
        <img src="./img/transparent%20logo.png" alt="Logotyp">
    </a>
</div>

       
        <div class="body-form-container">
            <h2>Registrera dig</h2>
            <form method="post">
                <label>Förnamn:</label>
                <input type="text" name="firstname" required>
                <label>Efternamn:</label>
                <input type="text" name="lastname" required>
                <label>Användarnamn:</label>
                <input type="text" name="username" required>
                <label>Email:</label>
                <input type="email" name="email" required>
                <label>Lösenord:</label>
                <input type="password" name="password" required>
                <label>Bekräfta lösenord:</label>
                <input type="password" name="confirm_password" required>
                
                <div class="register">
                <button type="submit">Registrera</button>
                </div>
                
            </form>
            <a href="login.php">Redan medlem? Logga in här</a>
        </div>

        <div class="footer">
        <p>&copy; Alla rättigheter förbehållna. Grupp 3 </p>

    </div>
    </div>
</body>
</html>