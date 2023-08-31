{if isset($import_success) && $import_success}
    <div class="alert alert-success">CSV import successful!</div>
{/if}

{if isset($import_error) && $import_error}
    <div class="alert alert-danger">Error uploading or processing CSV.</div>
{/if}

<style>
    /* Agrega tus estilos aquí */
    .import-form-container {
        margin: 20px;
        padding: 20px;
        border: 1px solid #ccc;
        background-color: #f5f5f5;
    }

    label {
        font-weight: bold;
    }

    #csv_file {
        margin-top: 10px;
    }

    button[type="submit"] {
        margin-top: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
    }

    /* Agrega más estilos según sea necesario */
</style>

<div class="import-form-container">
    <form id="csv_import_form" action="{$smarty.server.REQUEST_URI}" method="post" enctype="multipart/form-data">
        <label for="csv_file">Select CSV file:</label>
        <input type="file" name="csv_file" id="csv_file" required>
        <button type="submit" name="submitCsvImport">Import CSV</button>
    </form>
</div>
