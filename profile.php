<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Inkludera nödvändiga filer, t.ex. databasanslutning och chattfunktionen
require_once 'db_connect.php';
include 'chatt.php';

// Test-användare:
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['update_profile'])) {

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

    $blogHeader = isset($_POST['post_header']);
    $blogText = isset($_POST['post_content']);
    $time = date_create();
    $getTime = date_format($time, "Y-m-d H:i:s");

    if(isset($_POST['upload'])) {

        if(!isset($_FILES['image2'])) {
            header("location: profile.php");
            exit;
        } 
        try {
            $userID = $_SESSION['user_id'];
            $code = $_POST['post_content'];
                                     
            if(isset($_FILES['image2']) && $_FILES['image2']['error'] == 0) {
                $imageData = file_get_contents($_FILES['image2']['tmp_name']);
                $base64Image = base64_encode($imageData);
                
                $query = "INSERT INTO Posts (imagePath, image, userID) VALUES (NULL, :image, :userID)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(":image", $base64Image, PDO::PARAM_STR);            
                $stmt->bindValue(":userID", $userID, PDO::PARAM_INT);

                if($stmt->execute()) {
                    echo "<p style='color: white;'>Bild uppladdad!</p>";
                    $_SESSION['check'] = true;
                } else {
                    echo "<p style='color: red;'>Fel vid uppladdning</p>";
                }
            } else {
                echo "<p style='color: red;'>Ingen bild valdes att ladda upp!</p>";
                header("Location: profile.php");
            }
        
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Fel: " . $e->getMessage() . "</p>";
        }          
    }
    
    if (isset($_POST['create_post'])) {   
        $_SESSION['check'];

        if($_SESSION['check']) {
            $stmt = $pdo->prepare("INSERT INTO posts (textInput, header, userID, timeCreated, combinedID, imagePath) 
                    VALUES (:textInput, :header, :userID, :timeCreated, :combinedID, :imagePath)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (textInput, header, userID, timeCreated, combinedID, imagePath) 
                    VALUES (:textInput, :header, :userID, :timeCreated, :combinedID, imagePath = NULL)");
        }
        
        $userID = $_SESSION['user_id'];        
        $number = 1;


        $stmt->bindParam(':textInput', $blogText);
        $stmt->bindParam(':header', $blogHeader);
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':timeCreated', $getTime);
        $stmt->bindParam(':combinedID', $number);
        if($_SESSION['check']) {
            $stmt->bindParam(':imagePath', $_SESSION['pictureID']);
            $_SESSION['check'] = false;
        }

        if ($stmt->execute()) {
            echo "<div class='success'>Posten lyckades!</div>";
            
        } else {
            echo "<div class='error' style='color: red;'>Något gick fel med databasen: " . $stmt->errorInfo() . "</div>";
        }
    }

    if(isset($_POST['profilePicture'])) {

        if(!isset($_FILES['image3'])) {
            header("location: profile.php");
            exit;
        } 
        try {
            $userID = $_SESSION['user_id'];
                                     
            if(isset($_FILES['image3']) && $_FILES['image3']['error'] == 0) {
                $imageData = file_get_contents($_FILES['image3']['tmp_name']);
                $base64Image = base64_encode($imageData);
                
                $query = "UPDATE Users SET image = :image WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(":image", $base64Image, PDO::PARAM_STR);            
                $stmt->bindValue(":id", $userID, PDO::PARAM_INT);

                if($stmt->execute()) {
                    echo "<p style='color: white;'>Bild uppladdad!</p>";
                    $_SESSION['check'] = true;
                } else {
                    echo "<p style='color: red;'>Fel vid uppladdning</p>";
                }
            } else {
                echo "<p style='color: red;'>Ingen bild valdes att ladda upp!</p>";
                header("Location: profile.php");
            }
        
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Fel: " . $e->getMessage() . "</p>";
        }          
    }
    
    if(isset($_POST['removePicture'])) {
        try {
            $userID = $_SESSION['user_id'];
                
            $query = "UPDATE Users SET image = :image WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":image", $null, PDO::PARAM_STR);            
            $stmt->bindValue(":id", $userID, PDO::PARAM_INT);

            if($stmt->execute()) {
                echo "<p style='color: white;'>Bild borttagen</p>";
            } else {
                echo "<p style='color: red;'>Något gick fel!</p>";
            }
            
        
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Fel: " . $e->getMessage() . "</p>";
        }          
    }
}
    
// Hämta aktuell användardata från databasen
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php");
    exit;
}

$toggle = "none";
if(isset($_POST['picture_change'])) {
    $toggle = "block";
}

// Hämta inlägg från DB för den inloggade användaren

$stmt = $pdo->prepare("SELECT id, header, textInput, timeCreated FROM posts WHERE userID = :userID ORDER BY timeCreated DESC");
$stmt->execute([':userID' => $user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("
    SELECT c.textInput, c.timeCreated, p.header, p.id AS post_id
    FROM comments c 
    JOIN posts p ON c.postID = p.id 
    WHERE p.userID = :userID AND c.userID != :userID
    ORDER BY c.timeCreated DESC
");
$stmt->execute([':userID' => $user_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Profil - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="body_main">
<header>

<form action="search.php" method="GET" class="search-form">
    <input type="text" name="sökning" placeholder="Search for posts..." required>
    <button type="submit">Search</button>
    </form>
            
    <!-- "Gör ett inlägg" knappen längst till vänster -->
    <div class="header-button left-button">
        <a href="create_post.php" class="btn">Gör ett inlägg</a>
    </div>
    <!-- Logotypen centrerad -->
    <div class="logo-con">
        <a href="index.php"><img src="img/transparent logo.png" alt="Nexlify"></a>
    </div>
    <!-- Dropdown-menyn "Meny" längst till höger -->
    <div class="dropdown right-dropdown">
        <button class="dropbtn">Meny</button>
        <div class="dropdown-content">            
            <a href="profile.php">Profile</a>
            <a href="follow.php">Followers</a>
            <a href="logout.php">Logga ut</a>           
        </div>        
    </div>
</header>

<main">
    <!-- Profilsektionen -->
    <div class="profile-info">
        <h2 style="color:white; margin-top: 1rem;"><?php echo htmlspecialchars($user['username']); ?></h2>
        <div class="profile-info-box">
            <?php if($user['image'] == null): ?>
                <img src="logo.jpeg" alt="Profilbild">
            <?php else: ?>
                <img src="data:image/*;base64, <?php echo $user['image'] ?>">
            <?php endif ?>
        </div>
            <form  method="POST" enctype="multipart/form-data">                
                <button class="DM_funktion" id="toggleEditForm" name="picture_change">Ändra bild</button>
                <div class="form-group2">
                <button name="follower_button"><a href="follow.php" name="follower_button_a">Followers</a></button>
        </div>

                <div id="editForm2" class="edit-form" style="display: <?php echo $toggle ?>;">
                    <h2>Ändra bild</h2>
                    <?php if (!empty($error_message)): ?>
                        <p style="color: red;"> <?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <form method="post">                    

                    <?php if(!isset($_POST['profilePicture'])):?>
                        <input type="file" name="image3" id="image" accept="image/*" style="width: 13rem;">
                        <button type="submit" name="profilePicture" style="margin-top: 1rem;">Ladda upp!</button> 
                    <?php else: ?>
                        <?php require_once 'visaBild.php'; ?>
                        <button type="submit" name="ok">OK</button> 
                    <?php endif ?>  
                    
                    <button type="submit" name="removePicture" style="margin-top: 5rem;">Ta bort bild</button> 
                    </form>
                </div>
                <script>
                    document.getElementById('toggleEditForm').addEventListener('click', function () {
                        var editForm = document.getElementById('editForm2');
                        editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
                    });
                </script>   
            </form>
    </div>
    
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <!-- Uppdatera profil -->
    <form class="update-profile-form" method="post" action="">
        <div class="form-group">
            <label for="username">Användarnamn:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>">
        </div>
        <div class="form-group">
            <label for="email">E-post:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <button type="submit" name="update_profile">Uppdatera profil</button>
    </form>

    <!-- Skapa inlägg -->
    <form method="POST" enctype="multipart/form-data" class="form_create_post">
        <div class="form-group">
            <label for="post_header">Skapa ett inlägg:</label>
            <input type="text" name="post_header" id="post_header" placeholder="Ange rubrik" required>
        </div>
        <div class="form-group">
            
            <textarea name="post_content" id="post_content" placeholder="Vad vill du dela?"></textarea>
        </div>
        <?php if(!isset($_POST['upload'])):?>
            <input type="file" name="image2" id="image" accept="image/*" style="width: 13rem;">
            <button type="submit" name="upload">Ladda upp!</button> 
        <?php else: ?>
            <?php require_once 'visaBild.php'; ?>
        <?php endif ?>
        <button type="submit" name="create_post">Publicera</button>
    </form>

    <!-- Chat-ruta -->
<div class="chat-container">
    <div class="chat-header">
        <h3>Chatt</h3>
    </div>
    <div class="chat-users">
        <h4>Konversationer</h4>
        <?php foreach ($chat_users as $chat_user): ?>
            <form method="POST">
                <button type="submit" name="selected_user" value="<?php echo $chat_user['id']; ?>">
                    <?php echo htmlspecialchars($chat_user['username']); ?>
                </button>
            </form>
        <?php endforeach; ?>
    </div>

    <div class="chat-body">
        <h4>Meddelanden</h4>
        <?php 
        if (isset($_POST['selected_user'])) {
            $selected_user_id = $_POST['selected_user'];

            foreach ($messages as $msg) {
                if (
                    ($msg['senderID'] == $selected_user_id && $msg['receiverID'] == $user_id) || 
                    ($msg['senderID'] == $user_id && $msg['receiverID'] == $selected_user_id)
                ) {
                    echo "<div class='chat-message'>";
                    echo "<strong>" . htmlspecialchars($msg['sender_name']) . ":</strong> " . htmlspecialchars($msg['text']);
                    echo "<small>" . htmlspecialchars($msg['timeCreated']) . "</small>";
                    echo "</div>";
                }
            }
        } else {
            echo "<p>Välj en konversation</p>";
        }
        ?>
    </div>

    <form method="POST" class="chat-form">
        <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id ?? ''; ?>">
        <input type="text" name="message_text" placeholder="Skriv ett svar..." required>
        <button type="submit" name="reply_message">Skicka</button>
    </form>
</div>
    
    <!-- Inlägg och notifieringar sida vid sida -->
    <div class="content-columns">
    <div class="posts">
    <h3>Senaste inlägg</h3>
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <h4>
                    <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                        <?php echo nl2br(htmlspecialchars($post['header'])); ?>
                    </a>
                </h4>
                <p>
                    <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                        <?php echo nl2br(htmlspecialchars($post['textInput'])); ?>
                    </a>
                </p>
                <small>Postat: <?php echo htmlspecialchars($post['timeCreated']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Inga inlägg ännu.</p>
    <?php endif; ?>
</div>
        <div class="notifications">
    <h3>Nya kommentarer</h3>
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p>
                    <a href="post.php?id=<?php echo htmlspecialchars($comment['post_id']); ?>">
                        <?php echo nl2br(htmlspecialchars($comment['textInput'])); ?>
                    </a>
                </p>
                <small>
                    Kommentar tid: <?php echo htmlspecialchars($comment['timeCreated']); ?> 
                    | På inlägg: <?php echo htmlspecialchars($comment['header']); ?>
                </small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Inga nya kommentarer.</p>
    <?php endif; ?>
</div>
</main>
</body>
</html>
