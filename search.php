<?php
session_start();

require 'db_connect.php';

//kollar efter valid sökning, så Get kan hämta från url
if (!isset($_GET['sökning']) || empty(trim($_GET['sökning']))) {
    die("no search input.");
}

//prp query, kollar GET: sökningen, wrappar queryn i % som är lika med LIKE funktion i SQL
$search_query = trim($_GET['sökning']);
$search_query = "%{$search_query}%";

//SQL söker efter liknande text/headline
$sql = "SELECT id, header, textInput FROM Posts WHERE header LIKE :query OR textInput LIKE :query";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => $search_query]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Sök</title>
</head>
<body>
<header>
    
    <div class="header-button left-button">
        <a href="create_post.php" class="btn">Gör ett inlägg</a>
    </div>
    
    <div class="logo-con">
        <a href="index.php"><img src="img/transparent logo.png" alt="Nexlify"></a>
    </div>
    
    <div class="dropdown right-dropdown">
        <button class="dropbtn">Meny</button>
        <div class="dropdown-content">            
            <a href="profile.php">Profile</a>
            <a href="follow.php">Followers</a>
            <a href="logout.php">Logga ut</a>              
        </div>        
    </div>
</header>


    <!-- form 2 ser till så du kan söka igen inom search.php sidan-->
    <main class="search-main">
    <div class="search-container">
    <form action="search.php" method="GET" class="search-form">
    <input type="text" name="sökning" placeholder="Looking for another post?" required>
    <button type="submit">Search</button>
    </div>
</form>

    <!--Här visas resultat för sökningen, samt länkar till postID för att komma åt posten -->
        <h1 class="sök-header">Related Posts</h1>
        
        <?php if ($posts): ?>
            
                <?php foreach ($posts as $post): ?>
                    
                        <div class="search-post">
                        <h3><a class="post-länk" class href="post.php?id=<?php echo $post['id']; ?>">
                            <?php echo htmlspecialchars($post['header']); ?>
                        </a></h3>
                        <p class="sök-text"><?php echo htmlspecialchars(substr($post['textInput'], 0, 100)); ?></p>
                        </div>
                    
                <?php endforeach; ?>
            
        <?php else: ?>
            <p class="sök-text">No posts found for "<?php echo htmlspecialchars($_GET['sökning']); ?>".</p>
        <?php endif; ?>
    </main>
</body>
</html>