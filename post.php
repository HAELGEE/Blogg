<?php
session_start();
require 'db_connect.php';
$INLOGGAD = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("The requested post doesnt exist.");
}

$post_id = $_GET['id'];

//hämtar vald post genom postID
$sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.imagePath, u.username
             FROM Posts p
             LEFT JOIN Users u ON p.userID = u.id
             WHERE p.id = :post_id';
$stmt_post = $pdo->prepare($sql_post);
$stmt_post->execute(['post_id' => $post_id]);
$post = $stmt_post->fetch(PDO::FETCH_ASSOC);

//Phillips kod POST UPDATE
if (!$post) {
    die("Post not found.");
}


# Den första If-satsen med !isset($_POST['likeButton']) är till för att få bort felmeddelande över header!
if(!isset($_POST['likeButton']) && !isset($_POST['delete_button'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['userID']) {
        $new_header = trim($_POST['header']);
        $new_text = trim($_POST['textInput']);

        if (!empty($new_header) && !empty($new_text)) {
            $update_sql = 'UPDATE Posts SET header = :header, textInput = :textInput WHERE id = :post_id';
            $stmt_update = $pdo->prepare($update_sql);
            $stmt_update->execute([
                'header' => $new_header,
                'textInput' => $new_text,
                'post_id' => $post_id
            ]);
            header("Location: post.php?id=" . $post_id);
            exit();
        } else {
            $error_message = "Both fields must be filled.";
        }
    }
}


//hämtar vald 'BILD' genom postID
$pictureID = $post['imagePath'];

$sql_post = 'SELECT p.id, p.userID, p.textInput, p.header, p.image, u.username
             FROM Posts p
             LEFT JOIN Users u ON p.userID = u.id
             WHERE p.id = :post_id';
$stmt_post = $pdo->prepare($sql_post);
$stmt_post->execute(['post_id' => $pictureID]);
$post2 = $stmt_post->fetch(PDO::FETCH_ASSOC);

//Hämtar kommentarerna för valt inlägg
$sql_comments = 'SELECT c.textInput, c.timeCreated, u.username, c.userID 
                 FROM Comments c
                 LEFT JOIN Users u ON c.userID = u.id
                 WHERE c.postID = :post_id
                 ORDER BY c.timeCreated DESC';
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute(['post_id' => $post_id]);
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);


// Skriver kod om likes
if(isset($_POST['likeButton'])){
        
    require 'likes.php';
    
}

//Hämtar alla Post för att se om man gillat eller inte. För att sätta färg
if($INLOGGAD) {
    $sql_comments = 'SELECT * 
                    FROM Likes                 
                    WHERE userID = :id';                     
    $stmt_comments = $pdo->prepare($sql_comments);
    $stmt_comments->execute(['id' => $_SESSION['user_id']]);
    $comments3 = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);    

    $color = false;
    foreach($comments3 as $comment) {        
            if($comment['userID'] == $_SESSION['user_id'] && $comment['postID'] == $_GET['id']){
                $color = true;
            }
    }
}


// Här visas Countern "räknaren" på likes
$counter = (int) 0;

$sql_comments = 'SELECT * 
                 FROM Likes                 
                 WHERE postID = :postID';                     
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute(['postID' => $post_id]);
$likes = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

foreach($likes as $like) {
    $counter += 1;
}

$button = false;

# Bygger logiken för att ta bort ett inlägg
if($_SERVER['REQUEST_METHOD'] == "POST") {
    if(isset($_POST['delete_button'])) {
        $button = true;    
    
        if(isset($_POST['ja'])) {
            $_SESSION['delete'] = $post['id']; 
            $_SESSION['pictureDelete'] = $post['imagePath'];
            require 'delete_post.php'; 
            header("Location: index.php");
            exit;
        }  elseif(isset($_POST['nej'])) {
            header("Location: post.php?id=$post_id");
            exit;
        }
    }    
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kommentar</title>
        <link rel="stylesheet" href="style.css">
    </head>
<body class="body_main">
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

    <main class="post-main">

            
    
        
        <h1 style="color: white;"><?php echo htmlspecialchars($post['header']); ?></h1>
        <p style="color: white; margin-bottom: 2rem;">Posted by:
            <a href="guest_profile.php" class="a_normal">                   
                <?php $_SESSION['GuestID'] = $post['userID']?> 
                <?php echo htmlspecialchars($post['username']); ?>                
            </a>
        </p>
<?php if ($post['imagePath'] != null): ?>    
    <img class="post-image" src="data:image/*;base64,<?php echo $post2['image'] ?>" alt="<?php echo htmlspecialchars($post['header']); ?>" >
<?php endif; ?>
<p style="color: white; margin-top: 2rem; margin-bottom: 0.6rem;"><?php echo htmlspecialchars($post['textInput']); ?></p>

<hr>

<div>
    <form method="POST" class="commentsAndLike">        
        <h2 style="color: white; margin-top: 0.6rem; margin-bottom: 0.6rem;">Comments</h2>  
    
        <?php if($INLOGGAD && $post['userID'] == $_SESSION['user_id']): ?>
            <button name="delete_button" style="background-color: red; width: 8rem;">Delete</button>
        <?php endif ?>
        <div class="commentsAndLike">

            <?php if(!empty($_SESSION['user_id'])): ?>
                <p style="color: white;"><?php echo "Likes: $counter" ?></p>
                <button type="submit" name="likeButton" style="background-color: transparent;">       
        
                    <?php if($color): ?>
                        <img class="thumbsup" src="./img/thumbs-up-24.png" alt="" style="width:130%; background-color: green;">     
                    <?php else: ?>
                        <img  class="thumbsup" src="./img/thumbs-up-24.png" alt="" style="width:130%; background-color: white;">
                    <?php endif ?>
            
                </button>
            <?php endif ?>

        </div>
    </form>
</div>
<?php if ($comments): ?>
    <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <p style="margin-bottom: 0.5rem; margin-top: 0.3rem;"><strong>
                <a href="guest_profile.php?guest_id=<?= $comment['userID'] ?>" class="a_normal" >                     
                    <?php echo htmlspecialchars($comment['username']);?>:
                </a>
            </strong></p>
            <div class="comments-text">
                <p style="color: white; "><?php echo nl2br(htmlspecialchars($comment['textInput'])); ?></p>
                <p style="color: white; margin-top: 0.2rem;"><small><?php echo $comment['timeCreated']; ?></small></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="color: white;">No comments yet. Be the first to comment!</p>
<?php endif; ?>

<hr>

<!-- Kommentar formen för submit -->
<?php if (isset($_SESSION['user_id'])): ?>
    <form action="post_comment.php" method="post">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <textarea name="comment" placeholder="Write a comment!" required></textarea>
        <button class="P-Comment" type="submit">Post Comment</button>
    </form>
<?php else: ?>
    <p style="color: white;">
    <a href="login.php" style="text-decoration: none; display: inline-block;">
        <button style="background-color: #007BFF; color: white; border-radius: 10px; padding: 9px; margin: 5px; border: none; cursor: pointer;">
            Log in </button>
    </a> to comment.</p>
<?php endif; ?>

<hr>


<!-- Edit Post -->
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['userID']): ?>

<div class="edit-post-container">
    <div class="post-control">
        <button id="toggleEditForm" class="edit-form-button">Edit Post</button>
    </div>

    <div id="editForm" class="edit-form" style="display: none;">
        <h2>Edit Post</h2>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"> <?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="post.php?id=<?php echo $post_id; ?>" method="post">
            <label for="header">Header:</label>
            <input type="text" id="header" name="header" value="<?php echo htmlspecialchars($post['header']); ?>" required>

            <label for="textInput">Text:</label>
            <textarea id="textInput" name="textInput" rows="6" required><?php echo htmlspecialchars($post['textInput']); ?></textarea>

            <button type="submit" style="margin-top: 20px;">Update Post</button>
        </form>
    </div>
</div>


<!-- En popup för Ja eller Nej vid delete av Post -->
<?php if($button): ?>
    <div class="edit-post-container" style="background-color: transparent; height: 0rem;">
        <div id="editForm" class="edit-form" style="display: block; height: 8rem; margin-top: 20rem;">
            <h2>Vill du fortsätta med en delete?</h2>
            <?php if (!empty($error_message)): ?>
                <p style="color: red;"> <?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="post.php?id=<?php echo $post_id; ?>" method="POST" class="delete_form">
                <input type="hidden" name="delete_button" value="1"> 
                <button type="submit" name="ja">Ja</button>
                <button type="submit" name="nej">Nej</button>
            </form>
        </div>
    </div>
<?php endif ?>


<script>
    document.getElementById('toggleEditForm').addEventListener('click', function () {
        var editForm = document.getElementById('editForm');
        editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
    });
</script>
<?php endif; ?>
</main>
</body>
</html>