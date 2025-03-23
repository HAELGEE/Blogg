<?php
require 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username_email']) && isset($_POST['password'])) {
        $username_email = trim($_POST['username_email']);
        $password = trim($_POST['password']);

        if (!empty($username_email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username_email OR email = :username_email");
            $stmt->bindParam(':username_email', $username_email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo "<div class='success'>Inloggning lyckades! Välkommen, " . htmlspecialchars($user['username']) . "!</div>";                
                header("Location: index.php");
                exit();
            } else {
                echo "<div class='error'>Fel användarnamn/e-post eller lösenord!</div>";
            }
        } else {
            echo "<div class='error'>Alla fält måste fyllas i!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="Amer">
<div class="body-container">

        <div class="body-logo">
            <img src="./img\transparent logo.png" alt="Logotyp">
        </div>


    <div class="body-form-container">
        <h2>Logga in</h2>
        <br>
        <form method="post">
            <label>Användarnamn eller Email:</label>
            <input type="text" name="username_email" placeholder="Användarnamn eller Email" required>
            <label>Lösenord:</label>
            <input type="password" name="password" placeholder="Lösenord" required>
            <div class="before_remember-me">
                <div class="register">
                    <button type="submit" style="width: 8rem;">Logga in</button>
                </div>
                <div class="remember-me">
                    <input type="checkbox" name="remember_me" id="remember_me" style="width: 2rem; margin-bottom: 0.4rem;">
                    <label for="remember_me">Kom ihåg mig</label>
                </div>
            </div>
            <br>
        </form>
        <a href="register.php" class="amer_a">Inte medlem? Registrera dig här</a>
        <a href="ChangePassword.php" class="amer_a">Byt lösenord?</a>
        <a href="glömt_lösenord.php" class="amer_a">Glömt lösenord?</a>
    </div>
</div>

<div class="footer">
    <p>&copy; Alla rättigheter förbehållna. Grupp 3 </p>
</div>
</body>
</html>