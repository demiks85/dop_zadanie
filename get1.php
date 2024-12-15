<?php
include("connect.php");

// Получаем имя жанра и параметры сортировки из GET-запроса
$genreName = $_GET["genreName"] ?? '';
$orderBy = $_GET["orderBy"] ?? 'f.name'; // Поле для сортировки (по умолчанию 'name')
$orderDirection = $_GET["orderDirection"] ?? 'ASC'; // Направление сортировки (по умолчанию 'ASC')

// Валидируем параметры сортировки
$allowedOrderBy = ['f.name', 'f.date', 'f.country', 'f.director'];
$allowedOrderDirection = ['ASC', 'DESC', 'NONE'];

if (!in_array($orderBy, $allowedOrderBy)) {
    $orderBy = 'f.name';
}
if (!in_array($orderDirection, $allowedOrderDirection)) {
    $orderDirection = 'ASC';
}

// Определяем направление сортировки для следующего клика
$nextOrderDirection = 'ASC'; // По умолчанию по возрастанию
if ($orderDirection === 'ASC') {
    $nextOrderDirection = 'DESC';
} elseif ($orderDirection === 'DESC') {
    $nextOrderDirection = 'NONE';
}

try {
    // SQL-запрос для получения фильмов по жанру с сортировкой
    $sqlSelect = "SELECT f.name, f.date, f.country, f.director
                  FROM film f
                  JOIN film_genre fg ON f.ID_FILM = fg.FID_Film
                  JOIN genre g ON fg.FID_Genre = g.ID_Genre 
                  WHERE g.title = :genreName";

    // Если сортировка задана, добавляем ORDER BY в запрос
    if ($orderDirection !== 'NONE') {
        $sqlSelect .= " ORDER BY $orderBy $orderDirection";
    }

    $stmt = $dbh->prepare($sqlSelect);
    $stmt->bindValue(":genreName", $genreName);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем результаты как ассоциативный массив

} catch (PDOException $ex) {
    echo "Error: " . $ex->getMessage();
    $res = [];
}

$dbh = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Films by Genre</title>
    <link rel="stylesheet" href="styles_get.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="pdf_export2.js" defer></script>
</head>
<body>
    <h1>Films in Genre: <?= htmlspecialchars($genreName) ?></h1>

    <?php if (!empty($res)): ?>
        <div class="button-container">
            <button class="back-button" onclick="window.location.href='index.php';">Back to selection</button>

            <div class="dropdown">
                <button class="download-button">Download results as</button>
                <div class="dropdown-content">
                    <form action="export_csv.php" method="POST" target="_blank">
                        <input type="hidden" name="genreName" value="<?= htmlspecialchars($genreName) ?>">
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="csv-button">Download as CSV</button>
                    </form>
                    <form action="export_pdf.php" method="POST" target="_blank">
                        <input type="hidden" name="genreName" value="<?= htmlspecialchars($genreName) ?>">
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="pdf-button">Download as PDF (dompdf)</button>
                    </form>
                    <button onclick="exportToPDF()" class="js-pdf-button">Download as PDF (jsPDF)</button>
                    <input type="hidden" id="genreName" value="<?= htmlspecialchars($genreName) ?>">
                </div>
            </div>
        </div>

        <table id="results-table" border="1"> 
            <thead>
                <tr>
                    <th>No</th>
                    <th>
                        <a href="?genreName=<?= urlencode($genreName) ?>&orderBy=f.name&orderDirection=<?= $nextOrderDirection ?>">
                            Name <?= $orderBy === 'f.name' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?genreName=<?= urlencode($genreName) ?>&orderBy=f.date&orderDirection=<?= $nextOrderDirection ?>">
                            Date <?= $orderBy === 'f.date' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?genreName=<?= urlencode($genreName) ?>&orderBy=f.country&orderDirection=<?= $nextOrderDirection ?>">
                            Country <?= $orderBy === 'f.country' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?genreName=<?= urlencode($genreName) ?>&orderBy=f.director&orderDirection=<?= $nextOrderDirection ?>">
                            Director <?= $orderBy === 'f.director' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($res as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['country']) ?></td>
                        <td><?= htmlspecialchars($row['director']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No films found in the genre <?= htmlspecialchars($genreName) ?>.</p>
    <?php endif; ?>

</body>
</html>
