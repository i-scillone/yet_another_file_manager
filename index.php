<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Bootstrap con Scroll Indipendente</title>
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: Inter;
            src: url(InterVariable.woff2) format(woff2);
        }
        body { font-family: Inter; font-variant-numeric: slashed-zero; }
        /* Stili temporanei per i colori dei riquadri 
        .box-a, .box-d { background-color: #8899ff; }
        .box-b { color: #cc99ff; #background-color: black; }
        .box-c { background-color: black; }
        table { color: inherit; background-color: inherit; }
        */
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
        $full=realpath($p.DIRECTORY_SEPARATOR.$f);
        try {
            $inf=new MyClasses\DirEntry($full);
        } catch (Exception $e) {
            echo "<div>Errore alla riga {$e->getLine()}: «{$e->getMessage()}»</div>\n";
            break;
        }
        if (is_dir($full)) {
            printf(
                '<td><a class="dir" data-path="%s" href="#">%s</a></td>',
                htmlspecialchars($full),htmlspecialchars($f)
            );
        } else {
            printf('<td>%s</td>',htmlspecialchars($f));
        }
        echo '<td>'.$inf->getMode().'</td>';
        echo "<td>{$inf->getSize()}</td>";
        echo "<td>{$inf->getTime()}</td>";
        echo "</tr>\n";
        $inf=null;
    }
}
$dbg=new MyClasses\Debug();
$dbg->log($_REQUEST);
?>
    <div class="container-fluid box-a py-2 flex-shrink-0 border-bottom">
        <strong>Riquadro A</strong><br>
        Questo riquadro è fisso in alto.
    </div>
    <div class="container-fluid flex-grow-1 position-relative p-0" style="min-height: 0;">
        <div class="row g-0 h-100 w-100 position-absolute top-0 start-0">
            <div class="col-6 box-b p-3 scroll-column">
                <form id="left" action="index.php" method="post">
                    <input id="action" name="action" type="hidden">
                </form>
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
    <div class="container-fluid box-d py-2 flex-shrink-0 border-top">
        <strong>Riquadro D</strong><br>
        Questo riquadro è fisso in basso ed è alto e largo esattatemente come il Riquadro A.
    </div>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/npm-asset/jquery/dist/jquery.min.js"></script>
    <script>
    $('.dir').on('click',function(ev){
        $('#action').attr('name','goTo');
        $('#action').val($(this).data('path'));
        $('#left').submit();
    });
    </script> 
</body>
</html>
