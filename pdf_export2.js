document.addEventListener("DOMContentLoaded", () => {
    window.exportToPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();


  // Получаем данные из полей ввода
  const actorName = document.getElementById('actorName')?.value;
  const genreName = document.getElementById('genreName')?.value;
  const start_Date = document.getElementById('start_Date')?.value;
  const end_Date = document.getElementById('end_Date')?.value;

  // Определяем заголовок и имя файла
  let title = "";
  let namefile = "";
  if (genreName) {
      title = "Films in Genre: " + genreName;
      namefile = "in_genre";
  } else if (actorName) {
      title = "Films featuring " + actorName;
      namefile = "featuring_actor";
  } else if (start_Date && end_Date) {
      title = "Films from " + start_Date + " to " + end_Date;
      namefile = "for_time";
  }

  // Добавляем заголовок в PDF
  doc.text(title, 10, 10);

  const table = document.getElementById("results-table");

        // Создаем таблицу в PDF
        doc.autoTable({
            html: table, // Используем весь HTML-таблицу
            startY: 20,  // Начальная позиция для таблицы
            theme: 'grid', 
            styles: {
                fontSize: 10,    // Размер шрифта
                cellPadding: 3,  // Отступы внутри ячеек
                valign: 'middle', // Выравнивание текста по вертикали
            },
            headStyles: {
                fillColor: [41, 128, 185], // Цвет фона для заголовков
                textColor: [255, 255, 255], // Цвет текста в заголовках
                fontStyle: "bold",  // Жирное начертание для заголовков
            },
        });

        // Сохраняем файл как PDF
        doc.save("films_" + namefile + ".pdf");
    };
});
