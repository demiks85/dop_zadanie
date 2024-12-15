<?php
include("connect.php");

// Получение параметров из GET-запроса
$start_Date = $_GET["start_Date"] ?? null;
$end_Date = $_GET["end_Date"] ?? null;
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

// Определяем направление сортировки для следующего клика
$nextOrderDirection = 'ASC'; // По умолчанию по возрастанию
if ($orderDirection === 'ASC') {
    $nextOrderDirection = 'DESC';
} elseif ($orderDirection === 'DESC') {
    $nextOrderDirection = 'NONE';
}

try {
    // Проверяем, что обе даты указаны
    if ($start_Date && $end_Date) {
        $sqlSelect = "SELECT f.name, f.date, f.country, f.director
                      FROM film f
                      WHERE date BETWEEN :start_Date AND :end_Date";


        // Если сортировка задана, добавляем ORDER BY в запрос
        if ($orderDirection !== 'NONE') {
            $sqlSelect .= " ORDER BY $orderBy $orderDirection";
        }
        $stmt = $dbh->prepare($sqlSelect);
        $stmt->bindValue(":start_Date", $start_Date);
        $stmt->bindValue(":end_Date", $end_Date);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    } else {
        throw new Exception("Please specify both start and end dates.");
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
    <title>Film Results</title>
    <link rel="stylesheet" href="styles_get.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="pdf_export2.js" defer></script>
</head>
<body>
    <h1>Films from <?= htmlspecialchars($start_Date) ?> to <?= htmlspecialchars($end_Date) ?> </h1>
    <?php if (!empty($res)): ?>


        <div class="button-container">
            <button class="back-button" onclick="window.location.href='index.php';">Back to selection</button>

            <div class="dropdown">
            <button class="download-button">Download results as</button>
                <div class="dropdown-content">
                    <form action="export_csv.php" method="POST" target="_blank">
                        <input type="hidden" name="start_Date" value="<?= htmlspecialchars($start_Date) ?>">
                        <input type="hidden" name="end_Date" value="<?= htmlspecialchars($end_Date) ?>">
                        
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="csv-button">Download as CSV</button>
                    </form>
                    <form action="export_pdf.php" method="POST" target="_blank">
                        <input type="hidden" name="start_Date" value="<?= htmlspecialchars($start_Date) ?>">
                        <input type="hidden" name="end_Date" value="<?= htmlspecialchars($end_Date) ?>">
                        
                        <input type="hidden" name="data" value='<?= htmlspecialchars(json_encode($res), ENT_QUOTES, 'UTF-8') ?>'>
                        <button type="submit" class="pdf-button">Download as PDF (dompdf)</button>
                    </form>
                    <button onclick="exportToPDF()" class="js-pdf-button">Download as PDF (jsPDF)</button>
                    <input type="hidden" id="start_Date" value="<?= htmlspecialchars($start_Date) ?>">
                    <input type="hidden" id="end_Date" value="<?= htmlspecialchars($end_Date) ?>">
                </div>
            </div>
        </div>


        <table id="results-table" border="1"> 
            <thead>
                <tr>
                    <th>No</th>
                    <th>
                        <a href="?start_Date=<?= urlencode($start_Date) ?>&end_Date=<?= urlencode($end_Date) ?>&orderBy=f.name&orderDirection=<?= $nextOrderDirection ?>">
                            Name <?= $orderBy === 'f.name' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?start_Date=<?= urlencode($start_Date) ?>&end_Date=<?= urlencode($end_Date) ?>&orderBy=f.date&orderDirection=<?= $nextOrderDirection ?>">
                            Date <?= $orderBy === 'f.date' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?start_Date=<?= urlencode($start_Date) ?>&end_Date=<?= urlencode($end_Date) ?>&orderBy=f.country&orderDirection=<?= $nextOrderDirection ?>">
                            Country <?= $orderBy === 'f.country' ? ($orderDirection === 'ASC' ? '↑' : ($orderDirection === 'DESC' ? '↓' : '')) : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?start_Date=<?= urlencode($start_Date) ?>&end_Date=<?= urlencode($end_Date) ?>&orderBy=f.director&orderDirection=<?= $nextOrderDirection ?>">
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
        <p>No films found from <?= htmlspecialchars($start_Date) ?> to <?= htmlspecialchars($end_Date) ?>.</p>
    <?php endif; ?>

</body>
</html>


