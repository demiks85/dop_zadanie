<?php
$dsn = "mysql:host=localhost;dbname=lb_pdo_films";
$user = 'root';
$pass = '';


try {
    $dbh = new PDO($dsn, $user, $pass);
}
catch(PDOException $ex){
    echo $ex->GetMessage();
}
