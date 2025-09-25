<!doctype html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="jquery.min.js"></script>
    <style>
        p { margin: 0 0 4px 0; }
        .cleanLink { text-decoration: none; }
        .oper * { margin: 0 4px 4px 0; }
    </style>
    <script>
    $(function(){
        $('#all').on('change',function(ev){
            let checked=this.checked;
            $('td input[type="checkbox"]').prop('checked',checked);
        });
        $('.toClipboard').on('click',function(ev){
            var p=this.value;
            navigator.clipboard.writeText(p);
            alert("Copio nella Clipboard\n"+p);                          
        });
        $('#delete').on('click',function(ev){
            if (!confirm('Vuoi cancellare il/i file selezionato/i?')) {
                ev.preventDefault();
            }
        });
        $('#newFile').on('click',function(ev){
            var name=prompt('Nome del nuovo file?');
            if (name) this.value=name;
            else ev.preventDefault();
        });
        $('.rename').on('click',function(ev){
            var name=prompt('Nuovo nome?');
            if (name) {
                $('#mainForm').append(`<input name="newName" type="hidden" value="${name}">`);
            } else ev.preventDefault();
        });
        $('#newDir').on('click',function(ev){
            var name=prompt('Nome della nuova directory?');
            if (name) this.value=name;
            else ev.preventDefault();
        });
        $('#copy, #move').on('click',function(ev){
            var name=prompt('Destinazione?');
            if (name) this.value=name;
            else ev.preventDefault();
        });
        $('#zip').on('click',function(ev){
            console.log(this);
            var name=prompt('Nome del file Zip?');
            if (name) this.value=name;
            else ev.preventDefault();
        });
    });
    </script>
</head>
<body>
<?php
require 'global.php';
$path = strtr($_REQUEST['path'] ?? __DIR__,'\\','/');
$url= substr($path,strlen($_SERVER['DOCUMENT_ROOT']));
$it=new IntlDateFormatter(
    'it_IT',IntlDateFormatter::MEDIUM,IntlDateFormatter::MEDIUM,'Europe/Rome'
);
toLog($_REQUEST);
?>
    <div class="container">
<?php
echo "<h2>$path</h2>\n";
if (isset($_POST['delete'])) {
    foreach ($_POST['sel'] as $f) {
        $full=$path.'/'.$f;
        if (is_dir($full)) $r=rmdir($full);
        else $r=unlink($full);
        if (!$r) echo "<div class='alert alert-warning'>$f non cancellato</div>\n";
    }
} elseif (isset($_POST['newFile'])) {
    $full=$path.'/'.$_POST['newFile'];
    file_put_contents($full,'');
} elseif (isset($_POST['newDir'])) {
    $full=$path.'/'.$_POST['newDir'];
    mkdir($full);
} elseif (isset($_POST['copy']) || isset($_POST['move'])) {
    if (isset($_POST['copy'])) $dest=$_POST['copy'];
    else $dest=$_POST['move'];
    if (file_exists($dest)) {
        foreach ($_POST['sel'] as $f) {
            $from=$path.'/'.$f;
            $to=$dest.'/'.$f;
            if (isset($_POST['copy'])) copy($from,$to);
            else rename($from,$to);
        }
    } else {
        echo "<div class='alert alert-danger'>$dest non esiste!</div>\n";
    }
} elseif (isset($_POST['zip'])) {
    $z=new ZipArchive();
    $r=$z->open($path.'/'.$_POST['zip'],ZipArchive::CREATE);
    if ($r===true) {
        foreach ($_POST['sel'] as $f) {
            $z->addFile($f);
        }
        $z->close();
    } else {
        echo '<div class="alert alert-danger">'.zipErr($r).'</div>';
    }
} elseif (isset($_POST['unzip'])) {
    $z=new ZipArchive();
    $r=$z->open($_POST['unzip']);
    if ($r===true) {
        $z->extractTo($path);
        $z->close();
    } else {
        echo '<div class="alert alert-danger">'.zipErr($r).'</div>';
    }
} elseif (isset($_POST['rename'])) {
    $r=rename($_POST['rename'],$_POST['path'].'/'.$_POST['newName']);
    if ($r==false) {
        echo "<div class='alert alert-danger'>Impossibile cancellare $_POST[newName]</div>";
    }
}
?>
        <form id="mainForm" name="mainForm" method="post">
            <?= "<input name='path' type='hidden' value='$path'>" ?>
            <button class='btn btn-primary' title="Rileggi la dir."><i class='bi bi-arrow-clockwise'></i></button>
            <?php printf("<a href='index.php?path=%s' class='btn btn-primary' title='Dir. superiore'><i class='bi bi-arrow-up'></i></a>",realpath($path.'/..')); ?>
            <button id="newFile" name="newFile" type="submit" class="btn btn-primary" title="Nuovo file"><i class="bi bi-file-earmark-plus"></i></button>
            <button id="newDir" name="newDir" type="submit" class="btn btn-primary" title="Nuova dir."><i class="bi bi-folder-plus"></i></button>
            <button id="copy" name="copy" type="submit" class="btn btn-primary" title="Copia"><i class="bi bi-copy"></i></button>
            <button id="move" name="move" type="submit" class="btn btn-primary" title="Sposta"><i class="bi bi-arrows-move"></i></button>
            <button id="delete" name="delete" type="submit" class="btn btn-primary" title="Cancella"><i class="bi bi-trash"></i></button>
            <button id="zip" name="zip" type="submit" class="btn btn-primary" title="Crea file Zip"><i class="bi bi-file-zip"></i></button>
            <?= "<a href='upload.php?path={$path}' class='btn btn-primary' title='Upload'><i class='bi bi-upload'></i></a>" ?>
            <table class="table table-hover">
                <tr><th><input id="all" type="checkbox"></th><th>Nome</th><th>Permessi</th><th>Dimensione</th><th>Data</th><th>Operazioni</th></tr>
<?php
$d=scandir($path);
foreach ($d as $f) {
    if ($f=='.' || $f=='..') continue;
    $full=realpath($path.DIRECTORY_SEPARATOR.$f);
    $stat=stat($full);
    printf(
        '<tr><td><input name="sel[]" type="checkbox" value="%s"></td><td>',
        $f
    );
    if ($stat['mode'] & 040000) {
        echo '<i class="bi bi-folder"></i> ';
        printf('<a href="index.php?path=%s">%s</a>',urlencode($full),htmlspecialchars($f));
    } else {
        echo '<i class="bi bi-file-earmark"></i> ';
        printf('<a href="%s" class="cleanLink" target="_blank">%s</a>',$url.'/'.$f,htmlspecialchars($f));
    }
    echo '</td><td class="font-monospace">'.modeToText($stat['mode']);
    echo '</td><td>'.formatSize($stat['size']);
    echo '</td><td>'.$it->format($stat['mtime']);
    printf(
        '</td><td class="oper"><button class="toClipboard btn btn-primary btn-sm" type="button" value="%s" title="Nome del file con percorso nella Clipboard"><i class="bi bi-clipboard"></i></button>',
        $full
    );
    printf(
        '<button name="rename" type="submit" class="rename btn btn-primary btn-sm" value="%s" title="Cambia il nome"><i class="bi bi-pencil-square"></i></button>',
        $full
    );
    printf(
        '<a href="edit.php?file=%s" class="btn btn-primary btn-sm" target="_blank" title="Apre nell\'editor"><i class="bi bi-pen"></i></a>',
        urlencode($full)
    );
    printf(
        '<button id="unzip" name="unzip" type="submit" class="btn btn-primary btn-sm" value="%s" title="Unzip in questa dir."><i class="bi bi-file-zip"></i></button>',
        $full
    );
}
    echo "</td></tr>\n";
?>
            </table>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>