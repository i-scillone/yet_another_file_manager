<!doctype html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="server/jquery.ui.widget.js"></script>
    <script src="server/jquery.fileupload.js"></script>
    <script>
    $(function(){
        $('#source').fileupload({
            url: 'server/index.php',
            dataType: 'json',
            maxChunkSize: 10000000,
            formData: { path: <?= '"'.$_REQUEST['path'].'"' ?> },
            start: function(e){
                $('.progress-bar').css('width','0');
            },
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    console.log(file.error);
                    $('.container').append(`<div class="alert alert-success">Caricato ${file.name}</div>`);
                });
            },
            progressall: function (e, data) {
                var p=data.loaded / data.total * 100;
                $('.progress-bar').css('width',p+'%');
            }
        });
    });
    </script>
</head>
<body>
<div class="container">
    <h1>Upload manager</h1>
    <p><input id="source" type="file" name="files[]" multiple class="form-control"></p>
    <div class="progress mb-2">
        <div class="progress-bar"></div>
    </div>
    <p><?= "<a href='index.php?path={$_REQUEST['path']}' class='btn btn-primary'>Torna al file manager</a>" ?></p>
</div>
</body>
</html>