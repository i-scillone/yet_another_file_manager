<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Bootstrap con Scroll Indipendente</title>
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Stili temporanei per i colori dei riquadri */
        .box-a { background-color: #f8d7da; border-bottom: 2px solid #f5c2c7; }
        .box-b { background-color: #d1e7dd; }
        .box-c { background-color: #cff4fc; }
        /* Forza le colonne ad occupare il 100% dell'altezza del loro contenitore */
        .scroll-column {
            height: 100%;
            overflow-y: auto; /* Attiva la barra di scorrimento verticale solo se serve */
        }
    </style>
</head>
<body class="vh-100 d-flex flex-column m-0 overflow-hidden">
<?php
require_once './vendor/autoload.php';

function dirContents(string $p)
{
    echo '<div style="font-weight: bold">'.realpath($p)."</div>\n";
    $d=scandir($p);
    foreach($d as $f) {
        if ($f=='.') continue;
        echo '<tr>';
        $full=$p.DIRECTORY_SEPARATOR.$f;
        try {
            $inf=new MyClasses\DirEntry($full);
        } catch (Exception $e) {
            echo "<div>Errore alla riga {$e->getLine()}: «{$e->getMessage()}»</div>\n";
            break;
        }
        echo '<td>'.htmlspecialchars($f).'</td>';
        echo '<td>'.$inf->getMode().'</td>';
        echo "</tr>\n";
        $inf=null;
    }
}
?>
    <div class="container-fluid box-a py-2 flex-shrink-0">
        <strong>Riquadro A</strong><br>
        Questo riquadro è fisso in alto.
    </div>
    <div class="container-fluid flex-grow-1 position-relative p-0" style="min-height: 0;">
        <div class="row g-0 h-100 w-100 position-absolute top-0 start-0">
            <div class="col-6 box-b p-3 scroll-column">
                <table class="table table-hover">
<?php
dirContents('..');
?>
                </table>
            </div>
            <div class="col-6 box-c p-3 scroll-column">
                <table class="table table-hover">
<?php
dirContents('.');
?>
                </table>
            </div>
        </div>
    </div>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>