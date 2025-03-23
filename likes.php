<?php
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
    
    # Kod för likes. (KOPIERAD RAKT ÖVER IFRÅN POST.php)
        $bool = false;
        $likeID = "";
        
        $sql = 'SELECT * 
                        FROM Likes                 
                        WHERE userID = :id';                     
        $stmt_comments = $pdo->prepare($sql);
        $stmt_comments->execute(['id' => $_SESSION['user_id']]);
        $comments2 = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);    
        
        foreach($comments2 as $comment) {
            if($comment['userID'] == $_SESSION['user_id'] && $comment['postID'] == $_GET['id']){
                $bool = true;
                $likeID = $comment['id'];
            }    
        }    

        $id = (int) $post['id']; 

        if($bool) { 
            $query = 'DELETE FROM Likes               
                    WHERE id = :id';                     
            $stmt_comments = $pdo->prepare($query);
            $stmt_comments->execute(['id' => $likeID]);
            $delete = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
            header("location: post.php?id=$id");
        } else {       
            $userID = $_SESSION['user_id'];
            
            $stmt = $pdo->prepare('INSERT INTO Likes (postID, userID) VALUES (:postID, :userID)');
            if ($stmt->execute([
                ':postID' => $_GET['id'],
                ':userID' => $_SESSION['user_id']       
            ]));    
                        
            #Till för att växla färg på LikeButton beroende på om man gillat det eller inte.
            header("location: post.php?id=$id");
        }
        
    

    // //Hämtar alla Post för att se om man gillat eller inte. För att sätta färg
    // $sql_comments = 'SELECT * 
    //                 FROM Likes                 
    //                 WHERE userID = :id';                     
    // $stmt_comments = $pdo->prepare($sql_comments);
    // $stmt_comments->execute(['id' => $_SESSION['user_id']]);
    // $comments3 = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);    

    // $color = false;
    // foreach($comments3 as $comment) {        
    //         if($comment['userID'] == $_SESSION['user_id'] && $comment['postID'] == $_GET['id']){
    //             $color = true;
    //         }
    // }


    // // Här visas Countern "räknaren" på likes
    // $counter = (int) 0;

    // $sql_comments = 'SELECT * 
    //                 FROM Likes                 
    //                 WHERE postID = :postID';                     
    // $stmt_comments = $pdo->prepare($sql_comments);
    // $stmt_comments->execute(['postID' => $post_id]);
    // $likes = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

    // foreach($likes as $like) {
    //     $counter += 1;
    // }
?>