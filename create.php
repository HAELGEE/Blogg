<?php      

    $servername = "localhost";
    $dbname = "nexlify";
    $username = "root";
    $password = "";
    

    try {
        // HÄR SKAPAS DATABASEN   GLÖM INTE ATT HA IGÅNG MySQL I XAMPP!!!
        if(isset($_POST['createDatabasButton'])) {
            $conn = new PDO("mysql:host=$servername", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE DATABASE nexlify";

            $conn->exec($query);
             
            ?>
            <p style='color:white;'>
                <?php  echo "Databasen $dbname skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        
        }


        // HÄR SKAPAS TABELLEN FÖR USERS   
        if(isset($_POST['createUserTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        image LONGTEXT,
                        firstname NVARCHAR(50) NOT NULL,
                        lastname NVARCHAR(50) NOT NULL,
                        email NVARCHAR(50) NOT NULL UNIQUE,
                        username NVARCHAR (50) NOT NULL UNIQUE,
                        password NVARCHAR(250) NOT NULL 
            )";

            $conn->exec($query);
             
            ?>
            <p style='color:white;'>
                <?php  echo "Tabellen för Users skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
            <br>
            <?php

            $conn = null;
        }

        // HÄR SKAPAS TABELLEN FÖR Inlägg   
        if(isset($_POST['createPostsTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                userID INT UNSIGNED NOT NULL,
                combinedID INT UNSIGNED,
                textInput TEXT NOT NULL,
                header NVARCHAR(40) NOT NULL,
                imagePath VARCHAR(255),
                image LONGTEXT,
                timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                timeUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                timeUpdatedComments TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (userID) REFERENCES Users(id) ON DELETE CASCADE
            )";

            $conn->exec($query);
             
            ?>
            <p style='color:white;'>
                <?php  echo "Tabellen för Posts skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        }

        // HÄR SKAPAS TABELLEN FÖR Kommentrarer   
        if(isset($_POST['createCommentsTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Comments (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        userID int UNSIGNED NOT NULL,
                        postID int UNSIGNED NOT NULL,
                        textInput TEXT NOT NULL,
                        timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        timeUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (userID) REFERENCES Users(id) ON DELETE CASCADE,
                        FOREIGN KEY (postID) REFERENCES Posts(id) ON DELETE CASCADE
            )";

            $conn->exec($query); 

            ?>
            <p style='color:white;'>
                <?php  echo "Tabellen för Comments skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        }

        if(isset($_POST['createLikesTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Likes (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        userID int UNSIGNED NOT NULL,
                        postID int UNSIGNED NOT NULL,
                        count int UNSIGNED,
                        UNIQUE (userID, postID),
                        FOREIGN KEY (userID) REFERENCES Users(id) ON DELETE CASCADE,
                        FOREIGN KEY (postID) REFERENCES Posts(id) ON DELETE CASCADE
            )";

            $conn->exec($query);
                         
            ?>
            <p style='color:white;'>
                <?php  echo "Tabellen för Likes skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        }      

        if(isset($_POST['createChattTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Chatt (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        senderID int UNSIGNED NOT NULL,
                        receiverID int UNSIGNED NOT NULL,
                        text TEXT NOT NULL, 
                        timeCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                       
                        FOREIGN KEY (senderID) REFERENCES Users(id) ON DELETE CASCADE,
                        FOREIGN KEY (receiverID) REFERENCES Users(id) ON DELETE CASCADE
            )";

            $conn->exec($query);
            
            ?>
                <p style='color:white;'>
                    <?php  echo "Tabellen för Chatt skapades Framgångsrikt" ?>
                </p> 
                <p style='color: green;'>
                    <?php  echo "✔" ?>
                </p>
                    br>
            <?php

            $conn = null;
        }

        if(isset($_POST['createFollowerTableButton'])) {           

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE TABLE Follower (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        followedID int UNSIGNED NOT NULL,
                        followerID int UNSIGNED NOT NULL,
                        count int UNSIGNED,
                        UNIQUE (followedID, followerID),
                        FOREIGN KEY (followedID) REFERENCES Users(id) ON DELETE CASCADE,
                        FOREIGN KEY (followerID) REFERENCES Users(id) ON DELETE CASCADE
            )";

            $conn->exec($query);
                         
            ?>
            <p style='color:white;'>
                <?php  echo "Tabellen för Follower skapades Framgångsrikt" ?>
            </p> 
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        }      


        if(isset($_POST['dropsTableButton'])) {   
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "DROP TABLE Comments";
            $query2 = "DROP TABLE Likes";            
            $query3 = "DROP TABLE Posts";  
            $query4 = "DROP TABLE Chatt";  
            $query5 = "DROP TABLE Follower"; 
            $query6 = "DROP TABLE Users";               

            $conn->exec($query);
            $conn->exec($query2);
            $conn->exec($query3);
            $conn->exec($query4);
            $conn->exec($query5);
            $conn->exec($query6);
           
            ?>
            <p style='color:white;'>
                <?php  echo "Tabellerna har tagits bort Framgångsrikt" ?>
            </p>
            <p style='color: green;'>
                <?php  echo "✔" ?>
            </p>
                <br>
            <?php

            $conn = null;
        }


    } catch (PDOException $e) {
        ?>
            <p style='color:white;'>
                <?php  echo $e->getMessage() ?>
            </p> 
            <p style='color: red;'>
                <?php  echo "X" ?>
            </p>
            <?php
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>CREATE</title>
</head>
<body>
    
        <header class="create__header">
            <a href="index.php">TILL INDEX\HEM</a>        
        </header>

        <div class="container">
            <div class="container_inside">
                <form method="POST">
                    <button name="createDatabasButton">Skapa Databas</button>                    
                    <button name="createUserTableButton">Skapa Users table</button>
                    <button name="createPostsTableButton">Skapa Posts table</button>
                    <button name="createCommentsTableButton">Skapa Comments table</button>
                    <button name="createLikesTableButton">Skapa Likes table</button>
                    <button name="createChattTableButton">Skapa Chatt table</button>
                    <button name="createFollowerTableButton">Skapa Follower table</button>
                    <button name="dropsTableButton">Drop Tables and Users</button>
                </form>
            </div>
        </div>
   
</body>
</html>