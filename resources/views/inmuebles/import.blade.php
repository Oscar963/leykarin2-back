<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Inmuebles</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ddd; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .preview-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .preview-table th, .preview-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .preview-table th { background-color: #f2f2f2; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
    </style>
</head>
<body>
    <h1>Importar Inmuebles desde Excel</h1>
    
    <div id="alerts"></div>

    <form id="importForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="excel_file">Seleccionar archivo Excel:</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required>
            <small>Formatos permitidos: .xlsx, .xls, .csv (máximo 10MB)</small>
        </div>

        <div class="form-group">
            <button type="button" onclick="previewFile()">Vista Previa</button>
            <button type="submit">Importar</button>
            <a href="/api/inmuebles/template" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 10px 20px;">Descargar Plantilla</a>
        </div>
    </form>

    <div id="preview-section" style="display: none;">
        <h3>Vista Previa</h3>
        <div id="preview-info"></div>
        <div id="preview-table"></div>
    </div>

    <script>
        // Configurar CSRF token para todas las peticiones AJAX
        document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Manejar envío del formulario
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const fileInput = document.getElementById('excel_file');
            
            if (!fileInput.files[0]) {
                showAlert('Por favor selecciona un archivo', 'error');
                return;
            }
            
            formData.append('excel_file', fileInput.files[0]);
            
            showAlert('Importando archivo...', 'success');
            
            fetch('/api/inmuebles/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('importForm').reset();
                    document.getElementById('preview-section').style.display = 'none';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Error durante la importación: ' + error.message, 'error');
            });
        });
        
        // Vista previa del archivo
        function previewFile() {
            const formData = new FormData();
            const fileInput = document.getElementById('excel_file');
            
            if (!fileInput.files[0]) {
                showAlert('Por favor selecciona un archivo', 'error');
                return;
            }
            
            formData.append('excel_file', fileInput.files[0]);
            
            fetch('/api/inmuebles/preview', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPreview(data.data);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Error al generar vista previa: ' + error.message, 'error');
            });
        }
        
        // Mostrar vista previa
        function showPreview(data) {
            const previewSection = document.getElementById('preview-section');
            const previewInfo = document.getElementById('preview-info');
            const previewTable = document.getElementById('preview-table');
            
            previewInfo.innerHTML = `
                <p><strong>Archivo:</strong> ${data.file_name}</p>
                <p><strong>Total de filas:</strong> ${data.total_rows}</p>
                <p><strong>Mostrando:</strong> Primeras 4 filas</p>
            `;
            
            if (data.headers && data.headers.length > 0) {
                let tableHTML = '<table class="preview-table"><thead><tr>';
                data.headers.forEach(header => {
                    tableHTML += `<th>${header}</th>`;
                });
                tableHTML += '</tr></thead><tbody>';
                
                data.rows.forEach(row => {
                    tableHTML += '<tr>';
                    Object.values(row).forEach(cell => {
                        tableHTML += `<td>${cell || ''}</td>`;
                    });
                    tableHTML += '</tr>';
                });
                
                tableHTML += '</tbody></table>';
                previewTable.innerHTML = tableHTML;
            }
            
            previewSection.style.display = 'block';
        }
        
        // Mostrar alertas
        function showAlert(message, type) {
            const alertsDiv = document.getElementById('alerts');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            alertsDiv.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            
            // Auto-hide success alerts
            if (type === 'success') {
                setTimeout(() => {
                    alertsDiv.innerHTML = '';
                }, 5000);
            }
        }
    </script>
</body>
</html> 