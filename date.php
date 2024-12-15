
<?php
include("connect.php");
    $query = "SELECT MIN(date) AS oldest_date FROM film";
    $stmt = $dbh->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $minDate = $result["oldest_date"];
    
 
    $query = "SELECT MAX(date) AS oldest_date FROM film";
    $stmt = $dbh->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $maxDate = $result["oldest_date"];
    
   
     ?>