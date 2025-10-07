<?php
session_set_cookie_params(3600,dirname($_SERVER['REQUEST_URI']));
session_start();
?>
<!doctype html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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
            navigator.clipboard.writeText(p)
                .then(function(){
                    alert('"'+p+'" copiato nella Clipboard');
            	})
                .catch(function(e){
                    alert("Errore:\n"+e);
                });                          
        });
        $('#delete').on('click',function(ev){
            var tot=$('.fileSel:checked').length;
            if (!confirm(`Hai selezionato ${tot} file.\nVuoi veramente cancellarlo/i?`)) {
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
        $('.chmod').on('click',function(ev){
            var mode=prompt('Nuovi permessi?');
            if (mode) {
                $('#mainForm').append(`<input name="newMode" type="hidden" value="${mode}">`);
            }
            else ev.preventDefault();
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
    <div class="container">
<?php
require 'global.php';
$path = strtr($_REQUEST['path'] ?? __DIR__,'\\','/');
$url= substr($path,strlen($_SERVER['DOCUMENT_ROOT']));
$it=new IntlDateFormatter(
    'it_IT',IntlDateFormatter::MEDIUM,IntlDateFormatter::MEDIUM,'Europe/Rome'
);
echo "<h2>$path</h2>\n";
if (isset($_REQUEST['delete'])) {
    foreach ($_REQUEST['sel'] as $f) {
        $full=$path.'/'.$f;
        if (is_dir($full)) $r=deleteAll($full);
        else $r=unlink($full);
        if (!$r) echo "<div class='alert alert-warning'>$f non cancellato</div>\n";
    }
} elseif (isset($_REQUEST['newFile'])) {
    $full=$path.'/'.$_REQUEST['newFile'];
    file_put_contents($full,'');
} elseif (isset($_REQUEST['newDir'])) {
    $full=$path.'/'.$_REQUEST['newDir'];
    mkdir($full);
} elseif (isset($_REQUEST['copy']) || isset($_REQUEST['move'])) {
    if (isset($_REQUEST['copy'])) $dest=$_REQUEST['copy'];
    else $dest=$_REQUEST['move'];
    if (file_exists($dest)) {
        foreach ($_REQUEST['sel'] as $f) {
            $from=$path.'/'.$f;
            $to=$dest.'/'.$f;
            if (isset($_REQUEST['copy'])) copy($from,$to);
            else rename($from,$to);
        }
    } else {
        echo "<div class='alert alert-danger'>$dest non esiste!</div>\n";
    }
} elseif (isset($_REQUEST['zip'])) {
    $z=new MyZip();
    $r=$z->open($path.'/'.$_REQUEST['zip'],ZipArchive::CREATE);
    if ($r===true) {
        $z->recursive($_REQUEST['sel'],$path);
        $closed=$z->close();
        if (!$closed) echo '<div class="alert alert-danger">Non chiuso!</div>';
    } else {
        echo '<div class="alert alert-danger">'.zipErr($r).'</div>';
    }
} elseif (isset($_REQUEST['unzip'])) {
    $z=new ZipArchive();
    $r=$z->open($_REQUEST['unzip']);
    if ($r===true) {
        $z->extractTo($path);
        $z->close();
    } else {
        echo '<div class="alert alert-danger">'.zipErr($r).'</div>';
    }
} elseif (isset($_REQUEST['rename'])) {
    $r=rename($_REQUEST['rename'],$_REQUEST['path'].'/'.$_REQUEST['newName']);
    if ($r==false) {
        echo "<div class='alert alert-danger'>Impossibile cancellare $_REQUEST[newName]</div>";
    }
} elseif (isset($_REQUEST['chmod'])) {
    $r=chmod($_REQUEST['chmod'],$_REQUEST['newMode']);
    if ($r==false) {
        echo "<div class='alert alert-danger'>Permessi di $_REQUEST[chmod] intatti</div>";
    }
}
if (isset($_REQUEST['sort'])) {
    $_SESSION['sort']=$_REQUEST['sort'];
    $_SESSION['desc']=isset($_REQUEST['desc']);
} else {
    $_SESSION['sort']='n';
    $_SESSION['desc']=false;
}
?>
        <form id="mainForm" name="mainForm" method="post">
            <?= "<input name='path' type='hidden' value='$path'>" ?>
            <?php printf("<a href='index.php?path=%s' class='btn btn-primary' title='Dir. superiore'><i class='bi bi-arrow-up'></i></a>",@realpath($path.'/..')); ?>
            <button class='btn btn-primary' title="Rileggi la dir."><i class='bi bi-arrow-clockwise'></i></button>
            <span class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-sort-up"></i></button>
                <ul class="dropdown-menu">
                    <li><button name="sort" class="dropdown-item" type="submit" value="n">Nome</button></li>
                    <li><button name="sort" class="dropdown-item" type="submit" value="e">Estensione</button></li>
                    <li><button name="sort" class="dropdown-item" type="submit" value="s">Dimensione</button></li>
                    <li><button name="sort" class="dropdown-item" type="submit" value="d">Data</button></li>
                    <li>
                        <div class="form-check dropdown-item">
                            <input id="desc" name="desc" class="form-check-input" type="checkbox">
                            <label for="desc" class="form-check-label">Discendente</label>
                        </div>
                    </li>
                </ul>
            </span>
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
$d=new dirStruct($path);
$d->sortBy($_SESSION['sort'],$_SESSION['desc']);
foreach ($d as $f) {
    printf(
        '<tr><td><input class="fileSel" name="sel[]" type="checkbox" value="%s"></td><td>',
        $f->getName()
    );
    if ($f->mode & 040000) {
        echo '<i class="bi bi-folder"></i> ';
        printf('<a href="index.php?path=%s">%s</a>',urlencode($f->path),htmlspecialchars($f->getName()));
    } else {
        echo '<i class="bi bi-file-earmark"></i> ';
        printf('<a href="%s" class="cleanLink" target="_blank">%s</a>',$url.'/'.$f->getName(),htmlspecialchars($f->getName()));
    }
    echo '</td><td class="font-monospace">'.$f->getMode();
    echo '</td><td>'.$f->getSize();
    echo '</td><td>'.$f->getTime();
    printf(
        '</td><td class="oper"><button class="toClipboard btn btn-primary btn-sm" type="button" value="%s" title="Nome del file con percorso nella Clipboard"><i class="bi bi-clipboard"></i></button>',
        $f->path
    );
    printf(
        '<button name="rename" type="submit" class="rename btn btn-primary btn-sm" value="%s" title="Cambia il nome"><i class="bi bi-pencil-square"></i></button>',
        $f->path
    );
    printf(
        '<button name="chmod" type="submit" class="chmod btn btn-primary btn-sm" value="%s" title="Cambia i permessi"><i class="bi bi-ui-checks-grid"></i></button>',
        $f->path
    );
    printf(
        '<a href="edit.php?file=%s" class="btn btn-primary btn-sm" target="_blank" title="Apre nell\'editor"><i class="bi bi-pen"></i></a>',
        urlencode($f->path)
    );
    printf(
        '<button id="unzip" name="unzip" type="submit" class="btn btn-primary btn-sm" value="%s" title="Unzip in questa dir."><i class="bi bi-file-zip"></i></button>',
        $f->path
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