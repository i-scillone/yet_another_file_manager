<?php
session_set_cookie_params(3600,'/yafm');
session_start();
session_regenerate_id(true);
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
    echo <<<HTML
    <table class='table table-hover'>
        <tr>
            <th class="sort" data-side="{$side}" data-by="name">Nome</th>
            <th>Permessi</th>
            <th class="sort" data-side="{$side}" data-by="size">Dimensione</th>
            <th class="sort" data-side="{$side}" data-by="time">Data ed ora</th>
        </tr>\n
    HTML;
    $d=scandir($path);
    natcasesort($d);
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

$dbg=new MyClasses\Debug();
$dbg->log($_SESSION);
dirContents($_POST['data'],$_POST['side']);
$_SESSION[$_POST['side']]['path']=$_POST['data'];
