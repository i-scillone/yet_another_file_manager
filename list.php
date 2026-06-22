<?php
session_set_cookie_params(3600,'/yafm');
session_start();
require_once './vendor/autoload.php';

function dirContents(string $path,string $side): void
{
    printf(
        "<input id='path-%s' type='text' value='%s' class='form-control'>",
        $side,htmlspecialchars(realpath($path))
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
        if (is_dir($full)) {
            printf(
                '<a class="dir" data-path="%s" data-side="%s" href="#">%s</a>',
                htmlspecialchars($full),$side,htmlspecialchars($f)
            );
        } else {
            printf(
                '<span class="file" data-path="%s" data-side="%s">%s</span>',
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

$dbg=new MyClasses\Debug();
$dbg->log($_POST);
dirContents($_POST['data'],$_POST['side']);
$_SESSION[$_POST['side']]=$_POST['data'];

