<?php
include("connect.php");

// Получаем имя актера из GET-запроса
$actorName = $_GET["actorName"] ?? '';
$orderBy = $_GET["orderBy"] ?? 'f.name'; // Поле для сортировки (по умолчанию 'name')
$orderDirection = $_GET["orderDirection"] ?? 'ASC'; // Направление сортировки (по умолчанию 'ASC')

$allowedOrderBy = ['f.name', 'f.date', 'f.country', 'f.director'];
$allowedOrderDirection = ['ASC', 'DESC', 'NONE'];

if (!in_array($orderBy, $allowedOrderBy)) {
    $orderBy = 'f.name';
}
if (!in_array($orderDirection, $allowedOrderDirection)) {
    $orderDirection = 'ASC';
}

// Определяем направление сортировки 
$nextOrderDirection = 'ASC'; // По умолчанию по возрастанию
if ($orderDirection === 'ASC') {
    $nextOrderDirection = 'DESC';
} elseif ($orderDirection === 'DESC') {
    $nextOrderDirection = 'NONE';
}

try {
    if ($actorName) {
        // SQL-запрос для получения данных
        $sqlSelect = "SELECT f.name, f.date, f.country, f.director
                      FROM film f
                      JOIN film_actor fa ON f.ID_FILM = fa.FID_Film
                      JOIN actor a ON fa.FID_Actor = a.ID_Actor
                      WHERE a.name = :actorName";

        // Если сортировка задана, добавляем ORDER BY в запрос
        if ($orderDirection !== 'NONE') {
            $sqlSelect .= " ORDER BY $orderBy $orderDirection";
        }

        $stmt = $dbh->prepare($sqlSelect);
        $stmt->bindValue(":actorName", $actorName);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_OBJ); // Извлекаем данные как объекты
    } else {
        throw new Exception("Actor name is required.");
    }
} catch (PDOException $ex) {
    echo "<p>Error: " . $ex->getMessage() . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Films by Actor</title>
    <link rel="stylesheet" href="styles_get.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="pdf_export2.js" defer></script>
</head>
<body>
    <h1>Films featuring <?= htmlspecialchars($actorName) ?></h1>


    <?php if (!empty($res)): ?>
        <div class="button-container">
            <button class="back-button" onclick="window.location.href='index.php';">Back to selection</button>

            <div class="dropdown">
                <button class="download-button">Download results as</button>
                <div class="dropdown-content">
                    <form action="export_csv.php" method="POST" target="_blank">
                        <input type="hidden" name="actorName" value="<?= htmlspecialchars($actorName) ?>">
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="csv-button">Download as CSV</button>
                    </form>
                    <form action="export_pdf.php" method="POST" target="_blank">
                        <input type="hidden" name="actorName" value="<?= htmlspecialchars($actorName) ?>">
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="pdf-button">Download as PDF (dompdf)</button>
                    </form>
                    <button onclick="exportToPDF()" class="js-pdf-button">Download as PDF (jsPDF)</button>
                    <input type="hidden" id="actorName" value="<?= htmlspecialchars($actorName) ?>">
                </div>
            </div>
        </div>

        <table id="results-table" border="1"> 
            <thead>
                <tr>
                    <th>No</th>
                    <th>
                        <a href="?actorName=<?= urlencode($actorName) ?>&orderBy=f.name&orderDirection=<?= $nextOrderDirection ?>">
                            Name <?= $orderBy === 'f.name' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?actorName=<?= urlencode($actorName) ?>&orderBy=f.date&orderDirection=<?= $nextOrderDirection ?>">
                            Date <?= $orderBy === 'f.date' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?actorName=<?= urlencode($actorName) ?>&orderBy=f.country&orderDirection=<?= $nextOrderDirection ?>">
                            Country <?= $orderBy === 'f.country' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?actorName=<?= urlencode($actorName) ?>&orderBy=f.director&orderDirection=<?= $nextOrderDirection ?>">
                            Director <?= $orderBy === 'f.director' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($res as $index => $row): ?>
                    <tr>
                    <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row->name) ?></td>
                        <td><?= htmlspecialchars($row->date) ?></td>
                        <td><?= htmlspecialchars($row->country) ?></td>
                        <td><?= htmlspecialchars($row->director) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No films found for the actor <?= htmlspecialchars($actorName) ?>.</p>
    <?php endif; ?>


</body>
</html>
