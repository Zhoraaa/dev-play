<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Editor</title>
    <style>
        #editor-container {
            display: flex;
            flex-direction: column;
            width: 400px;
        }

        textarea {
            height: 200px;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .editor-buttons button {
            margin-right: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="editor-container">
        <textarea id="editor"></textarea>
        <div class="editor-buttons">
            <button id="boldBtn">Bold</button>
            <button id="italicBtn">Italic</button>
            <button id="linkBtn">Insert Link</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="text-editor.js"></script>

    <script>
        $(document).ready(function() {
            // Обработчик кнопки Bold
            $('#boldBtn').click(function() {
                applyStyleToSelection('bold');
            });

            // Обработчик кнопки Italic
            $('#italicBtn').click(function() {
                applyStyleToSelection('italic');
            });

            // Обработчик кнопки Insert Link
            $('#linkBtn').click(function() {
                var url = prompt('Enter the URL:');
                if (url) {
                    applyLinkToSelection(url);
                }
            });

            // Обработчик изменения текста в textarea
            $('#editor').on('input', function() {
                updateFormattedText();
            });

            // Применить стиль (жирный или курсив) к выделенному тексту
            function applyStyleToSelection(style) {
                var textarea = $('#editor')[0];
                var selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);

                var startTag = '';
                var endTag = '';

                switch (style) {
                    case 'bold':
                        startTag = '**';
                        endTag = '**';
                        break;
                    case 'italic':
                        startTag = '_';
                        endTag = '_';
                        break;
                }

                var newText = textarea.value.slice(0, textarea.selectionStart) + startTag + selectedText + endTag +
                    textarea.value.slice(textarea.selectionEnd);
                textarea.value = newText;

                // Восстановление позиции курсора
                textarea.selectionStart = textarea.selectionEnd = textarea.selectionStart + startTag.length +
                    selectedText.length + endTag.length;
                textarea.focus();

                updateFormattedText();
            }

            // Применить ссылку к выделенному тексту
            function applyLinkToSelection(url) {
                var textarea = $('#editor')[0];
                var selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);

                var newText = textarea.value.slice(0, textarea.selectionStart) + '[' + selectedText + '](' + url +
                    ')' + textarea.value.slice(textarea.selectionEnd);
                textarea.value = newText;

                // Восстановление позиции курсора
                textarea.selectionStart = textarea.selectionEnd = textarea.selectionStart + selectedText.length +
                    url.length + 4;
                textarea.focus();

                updateFormattedText();
            }

            // Обновить отформатированный текст без тегов
            function updateFormattedText() {
                var textarea = $('#editor')[0];
                var formattedText = textarea.value;

                // Замена тегов для жирного текста
                formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                // Замена тегов для курсива
                formattedText = formattedText.replace(/_(.*?)_/g, '<em>$1</em>');

                // Замена тегов для ссылок
                formattedText = formattedText.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank">$1</a>');

                $('#formattedText').html(formattedText);
            }
        });
    </script>
</body>

</html>
