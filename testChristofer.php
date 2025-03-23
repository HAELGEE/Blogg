<?php
session_start();

// Test-användare:
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db_connect.php';


$user_id = $_SESSION['user_id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);

    $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
    if ($stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':id'       => $user_id
    ])) {
        $message = "Profil uppdaterad!";
    } else {
        $message = "Fel vid uppdatering av profil.";
    }
}








    // KOD BÖRJAR HÄR FÖR CHATT



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post2'])) {
    
    // Hämtar alla users för att veta vilket ID man skall skicka meddelande till
    $stmt = $pdo->prepare("SELECT * FROM users WHERE firstname = :firstname");
    $stmt->execute([':firstname' => $_POST['receiver']]);
    $userName = $stmt->fetch(PDO::FETCH_ASSOC);

    $receiver_id = $userName['id'];      //denna raden skall bytas mot användarnamn och inte ID
    
    $post_content = trim($_POST['post_content2']);
    if (!empty($post_content)) {
        $stmt = $pdo->prepare("INSERT INTO chatt (text, senderID, receiverID, timeCreated) VALUES (:text, :senderID, :receiverID, NOW())");
        if ($stmt->execute([
            ':senderID'    => $user_id,
            ':receiverID'    => $receiver_id,
            ':text' => $post_content
        ])) {
            $message = "Inlägg publicerat!";
        } else {
            $message = "Fel vid publicering av inlägg.";
        }
    } else {
        $message = "Inlägget får inte vara tomt.";
    }
}

// Hämta aktuell användardata från databasen
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php");
    exit;
}



// Hämta inlägg från DB för den inloggade användaren
$stmt = $pdo->prepare("SELECT text, senderID, receiverID, text, timeCreated
                                FROM chatt 
                                WHERE senderID = :senderID OR receiverID = :receiverID
                                ORDER BY timeCreated DESC");

$stmt->execute([
    ':senderID' => $user_id,
    ':receiverID' => $user_id
]);

$posts2 = $stmt->fetchAll(PDO::FETCH_ASSOC);



$userchatt = [];
// Här fixar jag så jag har alla userIDs som har chattat och tagit bort den usern som är inloggad.
foreach($posts2 as $post) {

    $sender = $post['senderID'];

    
        if(!in_array($sender, $userchatt)) {
            $userchatt[] = $sender;
        }
    
}

  // KOD SLUTAR HÄR FÖR CHATT
?>



<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Profil - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
   
    <!-- Logotypen centrerad -->
    <div class="logo-con">
        <a href="index.php"><img src="img/transparent logo.png" alt="Nexlify"></a>
    </div>
    
</header>
    <nav>
      
        <!-- Fler länkar kan läggas till -->
    </nav>
    <div class="container">
        <div class="main-content">
            <div class="profile-info">
                <img src="profile.jpg" alt="Profilbild">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            <?php if ($message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            
            <!-- UPPDATERA PROFIL -->
            <form class="update-profile-form" method="post" action="">
                <label for="username">Användarnamn:</label><br>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>"><br>
                <label for="email">E-post:</label><br>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br>
                <button type="submit" name="update_profile">Uppdatera profil</button>
            </form>





            <!-- KOD BÖRJAR HÄR FÖR CHATT  -->




            <!-- SKAPA INLÄGG -->
            <form class="create-post-form" method="post" action="">
                <input type="text" name="receiver" placeholder="Vem vill du skicka till?">
                <label for="post_content">Ny chatt:</label><br>
                <textarea name="post_content2" id="post_content" placeholder="Text here!"></textarea><br>
                <button type="submit" name="create_post2">Publicera</button>
            </form>
            
            <!-- LISTA INLÄGG FRÅN DATABAS -->
            <div class="posts">
                <h3>Senaste Chatt historiken</h3>

               
                <?php if (!empty($posts2)): ?>
                    
                            <!-- Denna är till för att fixa ut användarnamnen -->
                    <?php foreach ($userchatt as $userID): ?>
                        <div>   
                            <!-- Kontroll så att man skapar chattar utifrån andra användare än den som är inloggad -->
                            <?php                                 
                                $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                                
                                $stmt2->execute([':id' => $userID]);
                                $userName = $stmt2->fetch(PDO::FETCH_ASSOC);
                                
                                
                                // All kommunication IN emot användaren hämtas här.
                                 $stmt = $pdo->prepare("SELECT text, senderID, receiverID, text, timeCreated
                                FROM chatt 
                                WHERE (senderID = :senderID AND receiverID = :receiverID)
                                OR (senderID = :receiverID AND receiverID = :senderID)
                                ORDER BY timeCreated ASC");
                            
                                $stmt->execute([
                                ':senderID' => $userID,
                                ':receiverID' => $user_id   // <-- DENNA SKALL TA ANVÄNDAREN IFRÅN SIG SJÄLV IFRÅN FOREACH LOOPEN ÖVER!!
                                ]);
                                $chattMessages  = $stmt->fetchAll(PDO::FETCH_ASSOC);  

                            ?>

                            <?php if($userID != $user_id): ?>  
                                <details>
                                    
                                    <summary class="no_arrow"><h3 style="color: Green;" tabindex="0" class="master"><?php echo nl2br(htmlspecialchars($userName['username'])); ?></h3></summary>
                                                                            
                                    <div class="post">
                                    
                                        <?php foreach($chattMessages  as $chatt): ?>
                                            <?php if($chatt['senderID'] == $user_id): ?>

                                                <h3 style="color: black; justify-self: end;"><?php echo nl2br(htmlspecialchars($user['username'])); ?></h3>
                                                <p style="color: black; margin-top: -1rem; justify-self: end;"><?php echo nl2br(htmlspecialchars($chatt['text'])); ?></p>
                                                <p style="color: black; justify-self: end;"><small>Postat: <?php echo htmlspecialchars($chatt['timeCreated']); ?></small></p>

                                            <?php else: ?>

                                                <h3 style="color: red;"><?php echo nl2br(htmlspecialchars($userName['username'])); ?></h3>
                                                <p style="color: red; margin-top: -1rem;"><?php echo nl2br(htmlspecialchars($chatt['text'])); ?></p>
                                                <small style="color: red;">Postat: <?php echo htmlspecialchars($chatt['timeCreated']); ?></small> 
                                                
                                            <?php endif ?>   
                                        <?php endforeach ?>                                        
                                       
                                    </div>
                                    
                                </details>
                            <?php endif ?> 
                        </div>       

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>Ingen chatthistorik ännu</p>

                <?php endif; ?>

            </div>





            <!-- KOD SLUTAR HÄR FÖR CHATT  -->



















        </div>
        <div class="notifications">
            <h4>Notifieringar</h4>
            <p>Nya kommentarer på dina blogginlägg</p>            
        </div>
    </div>
</body>
</html>
