<?php
require 'db_connect.php';

session_start();

//Kollar så användare är inloggad
$INLOGGAD = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);


    // Fetcha från posts för main content (Stora bilden)
    $sql_main = 'SELECT p.id, p.userID, p.textInput, p.header, p.imagePath, u.username, p.combinedID
                FROM Posts p
                LEFT JOIN Users u ON p.userID = u.id  
                WHERE p.combinedID IS NOT NULL              
                ORDER BY p.timeCreated DESC LIMIT 1
                ';

    $stmt_main = $pdo->prepare($sql_main);
    $stmt_main->execute();
    $main_post = $stmt_main->fetch(PDO::FETCH_ASSOC);

if($main_post != NULL) {

    if(!empty($main_post['imagePath'])) {
        //hämtar vald 'BILD' genom postID
        $pictureID = $main_post['imagePath'];
    } else {
        $pictureID = null;
    }

    $sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.image, u.username
                FROM Posts p
                LEFT JOIN Users u ON p.userID = u.id
                WHERE p.id = :post_id';
    $stmt_post = $pdo->prepare($sql_post);
    $stmt_post->execute(['post_id' => $pictureID]);
    $post2 = $stmt_post->fetch(PDO::FETCH_ASSOC);
}  
    // Fetcha från posts de senaste 4 inläggen för Recent Headlines
    $sql_thumbnails = 'SELECT p.id, p.textInput, p.header, p.imagePath, u.username, p.combinedID
                        FROM Posts p
                        LEFT JOIN Users u ON p.userID = u.id
                        WHERE p.combinedID IS NOT NULL
                        ORDER BY p.timeCreated DESC LIMIT 4';

    $stmt_thumbnails = $pdo->prepare($sql_thumbnails);
    $stmt_thumbnails->execute();
    $thumbnail_posts = $stmt_thumbnails->fetchAll(PDO::FETCH_ASSOC);


    // Fetcha alla posts
    $all_thumbnails = 'SELECT p.id, p.textInput, p.header, p.imagePath, u.username, p.combinedID
                    FROM Posts p
                    LEFT JOIN Users u ON p.userID = u.id
                    WHERE p.combinedID IS NOT NULL
                    ORDER BY p.timeCreated DESC';

    $stmt_allthumbnails = $pdo->prepare($all_thumbnails);
    $stmt_allthumbnails->execute();
    $all_posts = $stmt_allthumbnails->fetchAll(PDO::FETCH_ASSOC);


    // Fetcha från posts de senaste 4 inläggen för Recent Mostlikes
    $sql_most_liked = 'SELECT p.id, p.userID, p.textInput, p.header, p.imagePath, u.username,
    COUNT(l.postID) as like_count
    FROM Posts p
    LEFT JOIN Users u ON p.userID = u.id
    LEFT JOIN likes l ON p.id = l.postID
    WHERE p.combinedID IS NOT NULL
    GROUP BY p.id, p.userID, p.textInput, p.header, p.imagePath, u.username
    ORDER BY like_count DESC
    LIMIT 4';

    $stmt_most_liked = $pdo->prepare($sql_most_liked);
    $stmt_most_liked->execute();
    $most_liked_posts = $stmt_most_liked->fetchAll(PDO::FETCH_ASSOC);

    #En counter för Most Liked posts
    $counter = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Nexlify</title>
</head>
<body>
<!-- <div class="sticky-ad">
    <a href="ad-page.php" class="ad-link">
        <img src="ad.gif" alt="Sticky Ad" class="ad-image">
    </a>
</div> -->
<header> 
    <div class="header-button left-button">
        <?php if ($INLOGGAD) : ?>
            <a href="create_post.php" class="btn">Gör ett inlägg</a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
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

<main class="index-main">
    <div class="main-content">        
            <?php if ($main_post): ?>
                <h2>Main Headline</h2>
                <div class="post-preview">
                <a href="post.php?id=<?= $main_post['id']; ?>" style="text-decoration: none; color: inherit;">
            <?php if ($main_post['imagePath']): ?>
                <img src="data:image/*;base64, <?php echo $post2['image'] ?>" 
                alt="<?php echo htmlspecialchars($post['header']); ?>" 
                style="max-width: 100%; height: auto;">
            <?php endif; ?>        
        <h3><?= htmlspecialchars($main_post['header']); ?></h3>
        <p><?= htmlspecialchars($main_post['textInput']); ?></p>
    </a>
</div>
<p>Posted by: <?= htmlspecialchars($main_post['username']); ?></p>
<?php else: ?>
    <h2>Main Headline</h2>
    <div class="post-preview">
        <p>No posts posted</p>
    </div>
    <?php endif; ?>
</div>

<aside class="index-aside">
    <!-- Recent Headlines -->
    <h2>Recent Headlines</h2>
    <div class="post-thumbnails">    
        <?php if ($thumbnail_posts): ?>
            <?php foreach ($thumbnail_posts as $post): ?>
                <a href="post.php?id=<?= $post['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="thumbnail">
                    <?php 
                                //hämtar vald 'BILD' genom postID
                        $pictureID = $main_post['imagePath'];

                        $sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.image, u.username
                                    FROM Posts p
                                    LEFT JOIN Users u ON p.userID = u.id
                                    WHERE p.id = :post_id';
                        $stmt_post = $pdo->prepare($sql_post);
                        $stmt_post->execute(['post_id' => $post['imagePath']]);
                        $post2 = $stmt_post->fetch(PDO::FETCH_ASSOC); 
                    ?>
                        <?php if ($post['imagePath']): ?>                        
                            <img src="data:image/*;base64, <?php echo $post2['image'] ?>" 
                            alt="<?php echo htmlspecialchars($post['header']); ?>" 
                            style="max-width: 100%; height: auto;">
                        <?php endif; ?>
                        <div class="text-container">
                            <h4><?= htmlspecialchars($post['header']); ?></h4>
                            <p><?= htmlspecialchars($post['textInput']); ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>            
        <?php else: ?>
            <div class="thumbnail">
                <p>No posts posted</p>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Most Like Posts -->
        <h2>Most Liked Posts</h2>
        <div class="post-thumbnails">            
            <?php if ($most_liked_posts): ?> 
                <?php if($most_liked_posts[0]['like_count'] > 0): ?>               
                    <?php foreach($most_liked_posts as $post): ?>                    
                        <?php if($post['like_count'] >= 1): ?>                        
                            <a href="post.php?id=<?= $post['id']; ?>" style="text-decoration: none; color: inherit;">
                            
                            <div class="thumbnail">
                            <?php 
                                    //hämtar vald 'BILD' genom postID
                                $pictureID = $main_post['imagePath'];

                                $sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.image, u.username
                                            FROM Posts p
                                            LEFT JOIN Users u ON p.userID = u.id
                                            WHERE p.id = :post_id';
                                $stmt_post = $pdo->prepare($sql_post);
                                $stmt_post->execute(['post_id' => $post['imagePath']]);
                                $post2 = $stmt_post->fetch(PDO::FETCH_ASSOC); 
                            ?>
                                <?php if ($post['imagePath']): ?>                        
                                    <img src="data:image/*;base64, <?php echo $post2['image'] ?>" 
                                    alt="<?php echo htmlspecialchars($post['header']); ?>" 
                                    style="max-width: 100%; height: auto;">
                                <?php endif; ?>

                                <div class="text-container">
                                <h4><?= htmlspecialchars($post['header']); ?> 
                                    (<?= $post['like_count'] ?> likes)</h4>
                                <p><?= htmlspecialchars($post['textInput']); ?></p>
                            </div>

                            </div>
                            </a> 
                            
                        <?php endif ?>
                    <?php endforeach; ?>  
                <?php else: ?>
                    <div class="thumbnail">
                        <p>No posts have been liked yet</p>
                    </div>         
                <?php endif; ?>
            <?php else: ?>
                <div class="thumbnail">
                    <p>No posts have been liked yet</p>
                </div>      
            <?php endif; ?>
        </div>
    </aside>

    <!-- All Posts -->
    <div class="all_posts">
    <h2>All Posts</h2>
    <div class="all-post-container">
        <?php if ($all_posts): ?>
            <?php foreach ($all_posts as $post): ?>
                <a href="post.php?id=<?= $post['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="all-post-item">
                        <div class="all-post-item-inner">
                            <div class="all-post-item-front">
                            <?php 
                                    //hämtar vald 'BILD' genom postID
                            $pictureID = $main_post['imagePath'];

                            $sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.image, u.username
                                        FROM Posts p
                                        LEFT JOIN Users u ON p.userID = u.id
                                        WHERE p.id = :post_id';
                            $stmt_post = $pdo->prepare($sql_post);
                            $stmt_post->execute(['post_id' => $post['imagePath']]);
                            $post2 = $stmt_post->fetch(PDO::FETCH_ASSOC); 
                        ?>
                            <?php if ($post['imagePath']): ?>                        
                        <img src="data:image/*;base64, <?php echo $post2['image'] ?>" 
                        alt="<?php echo htmlspecialchars($post['header']); ?>" 
                        style="max-width: 100%; height: auto;">
                    <?php endif; ?>
                                <h4><?= htmlspecialchars($post['header']); ?></h4>
                            </div>
                            <div class="all-post-item-back">
                                <p><?= htmlspecialchars($post['textInput']); ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="all-post-item">
                <div class="all-post-item-inner">
                    <div class="all-post-item-front">
                        <p>No posts posted</p>
                    </div>
                    <div class="all-post-item-back">
                        <p>No posts posted</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</main>

<div class="div_create_post">
    <img src="./img/Swish-codes.gif" style="width: 150px; margin-right: 1rem;">
    <a href="ad-page.php">
        <img src="ad.gif" alt="Sticky Ad" class="ad-image" style="width: 500px; height: 125px; margin-top: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
    </a>
    <img src="./img/Swish-codes.gif" style="width: 150px; margin-left: 1rem;">
</div>


<div id="adPopup" class="popup">
    <div class="popup-content">
        <a href="ad-page.php" class="ad-link">
            <img src="ad.gif" alt="Sticky Ad" class="popup-ad-image">
        </a>
        <a href="login.php" class="popup-login-link">Proceed to Login</a>
        <a href="#" class="popup-close-link">Close</a>
    </div>
</div>

</body>
</html>