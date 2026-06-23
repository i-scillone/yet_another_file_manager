<?php
session_set_cookie_params(3600,'/yafm');
session_start();
require_once './vendor/autoload.php';
const TEMPLATE=<<<HTML
<div class="input-group">
    <input id="path-%s" type="text" value="%s" class="form-control">
    <button type="button" class="goto btn btn-outline-secondary" data-side="%s">
        <i class="bi bi-arrow-right"></i>
    </button>
</div>
HTML;

function dirContents(string $path,string $side): void
{
    printf(
        TEMPLATE,
        $side,htmlspecialchars(realpath($path)),$side
    );
    echo "<table class='table table-hover'>\n";
    $d=scandir($path);
    if (!in_array('..',$d)) {
        array_unshift($d,'..');
    }
    foreach($d as $f) {
        if ($f=='.') continue;
        echo '<tr>';
        $full=realpath($path.DIRECTORY_SEPARATOR.$f);
        try {
            $inf=new MyClasses\DirEntry($full);
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Errore alla riga {$e->getLine()}: «{$e->getMessage()}»</div>\n";
            break;
        }
        echo '<td>';
        printf(
            '<input type="checkbox" class="sel form-check-input me-2" value="%s" data-side="%s">',
            htmlspecialchars($full),$side
        );
        if (is_dir($full)) {
            printf(
                '<a class="dir" data-file="%s" data-side="%s" href="#">%s</a>',
                htmlspecialchars($full),$side,htmlspecialchars($f)
            );
        } else {
            printf(
                '<span class="file" data-file="%s" data-side="%s">%s</span>',
                htmlspecialchars($full),$side,htmlspecialchars($f)
            );
        }
        echo '</td><td>'.$inf->getMode().'</td>';
        echo "<td>{$inf->getSize()}</td>";
        echo "<td>{$inf->getTime()}</td>";
        echo "</tr>\n";
        $inf=null;
    }
    echo "</table>\n";
}

dirContents($_POST['data'],$_POST['side']);
$_SESSION[$_POST['side']]=$_POST['data'];
