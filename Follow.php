<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
$INLOGGAD = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
      
    require 'db_connect.php';

        $query = '  SELECT Follower.*, U.*
                    FROM Follower  
                    LEFT JOIN Users as U on followerID = U.id               
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
                $counterFollower += 1;
            }
            
        }

        $query2 = '  SELECT Follower.*, U.*
                    FROM Follower  
                    LEFT JOIN Users as U on followerID = U.id                     
                    WHERE followerID = :id
                ';  
        $stmt2 = $pdo->prepare($query2);
        
        $stmt2->execute(['id' => $_SESSION['user_id']]);  # Skall ändra till USERID
        $follower2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        # Denna skall räkna hur många användaren följer
        foreach($follower2 as $follow) {
            
            if($follow['followedID']) {
                $counterFollowed += 1;
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
        
        $userID = 3;

        if($_SERVER['REQUEST_METHOD'] == "POST") {

            if(isset($_POST['follow-button'])) {
                $query2 = "INSERT INTO Follower (followedID, followerID) VALUES (:followedID, :followerID)";                
                $stmt2 = $pdo->prepare($query2);               

                $stmt2->execute([
                    'followerID' => $_SESSION['user_id'], # Här skall man implentera USERID
                    'followedID' => 2  # Här lägger man till den användaren man är inne på
                ]);   
                header("location: follow.php");  # skall ändras så man resetar den sidan man är på (profilen)             
            }

            if(isset($_POST['unfollow-button'])) {
                
                $query = "DELETE FROM Follower WHERE id = :id";                
                $stmt = $pdo->prepare($query);     
                
                $stmt->execute([
                    'id' => $_SESSION['deleteid'], # Här skall man implentera USERID                    
                ]);    
                $delete = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                header("location: follow.php");  # skall ändras så man resetar den sidan man är på (profilen)
            }
            
        }
        
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body class="body_follow">
    <header>
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

<main class="index-main">
    <form method="POST">        
        <div class="body_follow__div">

        <div>
            <p>Following: <?php echo $counterFollowed ?></p>                    
           <?php foreach($follower2 as $follow) { 

                $query = '  SELECT id, firstname, lastname
                FROM Users                                      
                WHERE id = :id
                ';  
                $stmt = $pdo->prepare($query);

                $stmt->execute(['id' => $follow['followedID']]); 
                $follower3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($follower3 as $follow) { ?>
                <a href="guest_profile.php?guest_id=<?php echo $follow['id']; ?>" class="a_normal">
                    <?php echo htmlspecialchars($follow['firstname'] . " " . $follow['lastname']);?>
                    <br>
                </a>
               <?php }
            } ?>
        </div>
       
        <div>
            <p>Followers: <?php echo $counterFollower ?></p>
            
            <?php if($counterFollower > 0): ?>
                <?php foreach($follower as $follow) { 

                    $query = '  SELECT id, firstname, lastname
                                FROM Users                                      
                                WHERE id = :id';  
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(['id' => $follow['followerID']]);  
                    $followerData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($followerData as $fData) { ?>
                        <a href="guest_profile.php?guest_id=<?php echo htmlspecialchars($fData['id']); ?>" class="a_normal">
                            <?php echo htmlspecialchars($fData['firstname'] . " " . $fData['lastname']); ?>
                            <br>
                        </a>
                <?php }
                } ?>
           <?php endif ?>
        </div>


    </div>
</main>
</body>
</html>