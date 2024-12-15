<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
    $data = json_decode($_POST['data']); // Преобразуем JSON-строку обратно в массив

    // Проверяем, является ли первый элемент массива объектом или массивом
    $isObject = isset($data[0]) && is_object($data[0]);


    $actorName = $_POST["actorName"] ?? null;
    $genreName = $_POST["genreName"] ?? null;
    $start_Date = $_POST["start_Date"] ?? null;
    $end_Date = $_POST["end_Date"] ?? null;
    

    if (!$data) {
        die("No data to generate the PDF.");
    }


    if ($genreName) {
        $namefile = "films_in_genre" ;
    } elseif ($actorName) {
        $namefile = "films_featuring_actor" ;
    } elseif ($start_Date && $end_Date) {
        $namefile = "films_for_time" ;
    }
    // Устанавливаем заголовки для скачивания файла
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename={$namefile}.csv");

    // Открываем поток для вывода
    $output = fopen('php://output', 'w');

    // Пишем заголовки столбцов (общие для обоих типов данных)
    fputcsv($output, ['No', 'Name', 'Date', 'Country', 'Director']);

    // Пишем строки данных
    $rowNumber = 1; // Счётчик строк
    foreach ($data as $row) {
        if ($isObject) {
            // Данные в формате объектов
            fputcsv($output, [
                $rowNumber,
                $row->name,
                $row->date,
                $row->country,
                $row->director
            ]);
        } else {
            // Данные в формате ассоциативных массивов
            fputcsv($output, [
                $rowNumber,
                $row['name'],
                $row['date'],
                $row['country'],
                $row['director']
            ]);
        }
        $rowNumber++;
    }

    // Закрываем поток
    fclose($output);
    exit; // Завершаем выполнение скрипта
}
?>
