<?php
session_start();
$INLOGGAD = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

if (isset($_GET['guest_id'])) {
    $_SESSION['GuestID'] = $_GET['guest_id'];
}

$user_id = $_SESSION['GuestID'];
$visitProfile = $_SESSION['GuestID'];

 # en kontroll så man inte Besöker sin egna sida
if($user_id == $_SESSION['user_id']) {
    header("Location: profile.php");
    exit;
}

$message = '';


// Hämta aktuell användardata från databasen
$stmt = $pdo->prepare("SELECT id, username, firstname, lastname, email, image FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: guest_profile.php");
    exit;
}


// Hämta inlägg från DB för den inloggade användaren
$stmt = $pdo->prepare("SELECT * FROM posts WHERE userID = :userID ORDER BY timeCreated DESC");
$stmt->execute([':userID' => $user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);



 # Här kommer logiken ifrån Follow

    $query = '  SELECT * 
                FROM Follower                 
                WHERE followedID = :id
            ';  
    $stmt = $pdo->prepare($query);
    
    $stmt->execute(['id' => $_SESSION['user_id']]);  # Skall ändra till USERID
    $follower = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counterFollower = 0;
    $counterFollowed = 0;

    # Denna skall räkna hur många det är som följer användaren
    foreach($follower as $follow) { 

        if($follow['followerID']) {
            $counterFollowed += 1;
        }
        
    }

    $query = '  SELECT * 
                FROM Follower                 
                WHERE followerID = :id
            ';  
    $stmt = $pdo->prepare($query);
    
    $stmt->execute(['id' => $_SESSION['user_id']]);  # Skall ändra till USERID
    $follower2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    # Denna skall räkna hur många användaren följer
    foreach($follower2 as $follow) {
        
        if($follow['followedID']) {
            $counterFollower += 1;
        }
    }

    $query = '  SELECT * 
                FROM Follower    
            ';  
    $stmt = $pdo->prepare($query);
    
    $stmt->execute(); 
    $followAll = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bool = true;  
    $deleteID = 0;
    
    
    

    if($_SERVER['REQUEST_METHOD'] == "POST") {

        if(isset($_POST['follow-button'])) {
            $query2 = "INSERT INTO Follower (followedID, followerID) VALUES (:followedID, :followerID)";                
            $stmt2 = $pdo->prepare($query2);               

            $stmt2->execute([
                'followerID' => $_SESSION['user_id'], # Här skall man implentera USERID
                'followedID' => $user_id  # Här lägger man till den användaren man är inne på
            ]);   
            header("location: guest_profile.php");  # skall ändras så man resetar den sidan man är på (profilen)             
        }

        if(isset($_POST['unfollow-button'])) {
            
            $query = "DELETE FROM Follower WHERE id = :id";                
            $stmt = $pdo->prepare($query);     
            
            $stmt->execute([
                'id' => $_SESSION['deleteid'], # Här skall man implentera USERID                    
            ]);    
            $delete = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            header("location: guest_profile.php");  
        }

    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['Send_message'])) {
        
            // Hämtar alla users för att veta vilket ID man skall skicka meddelande till
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE firstname = :firstname");
            $stmt->execute([':firstname' => $user['firstname']]);
            $userName = $stmt->fetch(PDO::FETCH_ASSOC);
        
            $receiver_id = $userName['id'];      
            
            $post_content = trim($_POST['textInput']);
            if (!empty($post_content)) {
                $stmt = $pdo->prepare("INSERT INTO chatt (text, senderID, receiverID, timeCreated) VALUES (:text, :senderID, :receiverID, NOW())");
                if ($stmt->execute([
                    ':senderID'    => $_SESSION['user_id'],
                    ':receiverID'    => $receiver_id,
                    ':text' => $post_content
                ])) { ?>
                    <p style='color: white; position: absolute; top: 21rem;'> <?php echo "Meddelande skickades!"; ?></p><?php
                } else { ?>
                    <p style='color: white; position: absolute; top: 21rem;'> <?php echo"Något blev fel när det skulle skickas"; ?></p><?php
                }
            } else { ?>
                <p style='color: red; position: absolute; top: 21rem;'> <?php echo "Inlägget får inte vara tomt"; ?></p><?php
            }
        }
    }
    
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
    <div class="header-button left-button">        
        <a href="create_post.php" class="btn">Gör ett inlägg</a>        
    </div>   
    
    <form action="search.php" method="GET" class="search-form">
    <input type="text" name="sökning" placeholder="Search for posts..." required>
    <button type="submit">Search</button>
    </form>
            
    <div class="logo-con">
        <a href="index.php"><img src="img/transparent logo.png" alt="Nexlify"></a>
    </div>
        
    <div class="dropdown">
        <button class="dropbtn">Meny</button>
        <div class="dropdown-content">
            <?php if (!$INLOGGAD) : ?>
                <a href="login.php">Log in</a>
            <?php else : ?>
                <a href="profile.php">Profile</a>
                <a href="follow.php">Followers</a>
                <a href="logout.php">Logga ut</a>
            <?php endif; ?>
        </div>
    </div>
</header>



<main>
    <!-- Profilsektionen -->
    <div class="profile-info">
        <div class="profile-info-box">
            <?php if($user['image'] == null): ?>
                <img src="logo.jpeg" alt="Profilbild">
            <?php else: ?>
               <img src="data:image/*;base64, <?php echo $user['image'] ?>"> <!-- Här skall bilden för användaren visas om det finns någon -->
            <?php endif ?>
        </div>
        <h2 style="color: white;"><?php echo htmlspecialchars($user['username']); ?></h2>
    </div>
    
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <!-- Visa profil -->
    <form class="update-profile-form" method="POST" action="" style="display: flex; align-items: center; justify-content: center;">       
        <div class="form-group" style="display: flex; align-items: center; justify-content: center; flex-direction: column; ">
            <label for="username">Förnamn:</label>
            <p class="guest_profile__p"><?php echo htmlspecialchars($user['firstname']); ?></p>
        </div>
        <div class="form-group" style="display: flex; align-items: center; justify-content: center; flex-direction: column; ">
            <label for="username">Efternamn:</label>
            <p class="guest_profile__p"><?php echo htmlspecialchars($user['lastname']); ?></p>
        </div>
        <div class="form-group" style="display: flex; align-items: center; justify-content: center; flex-direction: column; ">
            <label for="email">E-post:</label>
            <p class="guest_profile__p"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        
        <?php foreach($followAll as $follow): ?>  
                
                <!-- DET FÖRSTA ÄR USERID ---------  DET ANDRA ÄR PROFILSIDAN MAN ÄR INNE PÅ -->
                <?php if($follow['followerID'] == $_SESSION['user_id'] && $follow['followedID'] == $user_id): ?> <!-- HÄR SKALL ÄVEN EN KONTROLL AV USERS SAMT EN KONTROLL EMOT ANVÄNDARENS PROFIL. SÅ MAN INTE KAN GILLA SIN EGNA SIDA-->
                    <?php $bool = false; ?>
                    <?php $_SESSION['deleteid'] = $follow['id']; ?>
                <?php endif ?>
            <?php endforeach ?>           
                    
        <!-- HÄR SKALL ÄNDRAS TILL DEN sidans ID man är inne på-->
         <div class="guest-buttons">
             <?php if($visitProfile != $_SESSION['user_id']): ?> <!-- KONTROLLERA SÅ ATT INTE PROFILSIDAN MAN ÄR INNE PÅ ÄR ENS EGNA PROFIL -->
                 <?php if($bool): ?>
                         <button name="follow-button">Follow</button>
                     <?php else: ?>
                         <button name="unfollow-button">Unfollow</button>
                 <?php endif ?>                
             <?php endif ?>
         </div>
        
        </form>

    <!-- Skicka DM -->    
        <form class="create-post-form" method="POST">
            <div class="form-group">
                
                <div class="meddelande-button">
                    <button class="DM_funktion" id="toggleEditForm">Skriv ett Meddelande</button>
                </div>

                    <div id="editForm2" class="edit-form" style="display: none;">
                        <h2>Chatt</h2>
                        <?php if (!empty($error_message)): ?>
                            <p style="color: red;"> <?php echo $error_message; ?></p>
                        <?php endif; ?>

                        <form action="guest_profile.php?id=<?php echo $user_id; ?>" method="post">                    

                            <label for="textInput">Text:</label>
                            <textarea id="textInput" name="textInput" rows="20" required placeholder="Inputs chatt message here..."></textarea>

                            <button type="submit" style="margin-top: 20px;" name="Send_message">Skicka Meddelande</button>
                        </form>
                    </div>
                    <script>
                        document.getElementById('toggleEditForm').addEventListener('click', function () {
                            var editForm = document.getElementById('editForm2');
                            editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
                        });
                    </script>
            </div>
            
        </form>
    
    
    <!-- Inlägg och notifieringar sida vid sida -->
    <div class="content-columns">
        <div class="posts">
        <h3>Senaste inlägg</h3>
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php if($post['combinedID'] != null): ?>
                <div class="post">
                    <h4 style="margin-bottom: 0.7rem;">
                        <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="text-decoration: none; color: white;">
                            <?php echo nl2br(htmlspecialchars($post['header'])); ?>
                        </a>
                    </h4>
                    <p style="margin-bottom: 0.9rem;">
                        <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>" style="text-decoration: none; color: white;">
                            <?php echo nl2br(htmlspecialchars($post['textInput'])); ?>
                        </a>
                    </p>
                    <small>Postat: <?php echo htmlspecialchars($post['timeCreated']); ?></small>
                </div>
                <?php endif ?>
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

</div>
</main>
</body>
</html>
