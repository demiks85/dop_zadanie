<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include("connect.php");

// Получаем параметры из POST-запроса
$actorName = $_POST["actorName"] ?? null;
$genreName = $_POST["genreName"] ?? null;
$start_Date = $_POST["start_Date"] ?? null;
$end_Date = $_POST["end_Date"] ?? null;
$data = json_decode($_POST["data"], true);

if (!$data) {
    die("No data to generate the PDF.");
}

// Генерация заголовка в зависимости от переданных параметров
$html = "<h1>Films";

if ($genreName) {
    $html .= " in Genre: " . htmlspecialchars($genreName);
    $namefile = "films_in_genre";
} elseif ($actorName) {
    $html .= " featuring Actor: " . htmlspecialchars($actorName);
    $namefile = "films_featuring_actor";
} elseif ($start_Date && $end_Date) {
    $html .= " from " . htmlspecialchars($start_Date) . " to " . htmlspecialchars($end_Date);
    $namefile = "films_for_time";
}

$html .= "</h1>";

// Генерация таблицы
$html .= "<table border='1' style='width: 100%; border-collapse: collapse;'>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Country</th>
                    <th>Director</th>
                </tr>
            </thead>
            <tbody>";

foreach ($data as $index => $row) {
    $html .= "<tr>
                <td>" . ($index + 1) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['date']) . "</td>
                <td>" . htmlspecialchars($row['country']) . "</td>
                <td>" . htmlspecialchars($row['director']) . "</td>
              </tr>";
}

$html .= "</tbody></table>";

try {
    // Инициализация Dompdf
    $options = new Options();
    $options->set("isHtml5ParserEnabled", true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->render();

    // Генерация PDF и сохранение
    $output = $dompdf->output(); // Получаем содержимое PDF в переменную
    $filePath = 'downloads/films_' . time() . '.pdf'; // Путь для сохранения PDF
    file_put_contents($filePath, $output); // Сохраняем файл на сервере

    // Предлагаем пользователю скачать PDF
    header("Content-type: application/pdf");
    header("Content-Disposition: attachment; filename={$namefile}.pdf");
    echo $output;

} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage();
}
