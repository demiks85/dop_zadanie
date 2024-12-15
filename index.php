<?php
include("connect.php");
include("date.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDZ_dop_b</title>
    <link rel="stylesheet" href="styles.css"><!--Подключение внешнего файла стилей-->
</head>
<body>

    <div class="form-container">
        <h1>Select Movies</h1>

        <!-- Форма для выбора по жанру -->
        <form action="get1.php" method="get">
            <div class="form-group">
                <label for="genreName">Select movies by genre:</label>
                <select name="genreName" id="genreName">
                    <?php
                    $select = "SELECT genre.title FROM `genre`";
                    try {
                        foreach($dbh->query($select) as $row) {
                            echo "<option value='$row[0]'>$row[0]</option>";
                        }
                    } catch(PDOException $ex) {
                        echo $ex->GetMessage();
                    }
                    ?>
                </select>
            </div>
            <input type="submit" value="Submit" class="submit-btn">
        </form>

        <!-- Форма для выбора по актеру -->
        <form action="get2.php" method="get">
            <div class="form-group">
                <label for="actorName">Select movies by actor:</label>
                <select name="actorName" id="actorName">
                    <?php
                    $select = "SELECT actor.name FROM `actor`";
                    try {
                        foreach($dbh->query($select) as $row) {
                            echo "<option value='$row[0]'>$row[0]</option>";
                        }
                    } catch(PDOException $ex) {
                        echo $ex->GetMessage();
                    }
                    $dbh = null;
                    ?>
                </select>
            </div>
            <input type="submit" value="Submit" class="submit-btn">
        </form>

        <!-- Форма для выбора по временному периоду -->
        <form action="get3.php" method="get">
            <div class="form-group">
                <label for="start_Date">Start date:</label>
                <input type="date" name="start_Date" id="start_Date" min="<?= $minDate ?>" max="<?= $maxDate ?>">

                <label for="end_Date">End date:</label>
                <input type="date" name="end_Date" id="end_Date" min="<?= $minDate ?>" max="<?= $maxDate ?>">
            </div>
            <input type="submit" value="Submit" class="submit-btn">
        </form>
    </div>

</body>
</html>
