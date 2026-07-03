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

function getDirectory(string $path): array|bool
{
    $d=scandir($path);
    if (!in_array('..',$d)) {
        array_unshift($d,'..');
    }
    $buf=[];
    foreach($d as $f) {
        if ($f=='.') continue;
        $full=realpath($path.DIRECTORY_SEPARATOR.$f);
        try {
            $inf=new MyClasses\DirEntry($full);
            if ($f=='..') $inf->name='..';
            $buf[]=$inf;
        } catch (Exception $e) {
            return false;
        }
    }
    return $buf;
}

$dbg=new MyClasses\Debug();
$dbg->log($_REQUEST);
$dbg->log($_SESSION);
$d=getDirectory($_POST['data']);
usort($d,function ($a, $b) {
    switch ($_SESSION[$_POST['side']]['sortBy']) {
        case 'size':
            return $a->size <=> $b->size;
        case 'time':
            return $a->time <=> $b->time;
        default:
            return strcasecmp($a->name,$b->name);
    }
});
printf(
    TEMPLATE,
    $_POST['side'],htmlspecialchars(realpath($_POST['data'])),$_POST['side']
);
echo <<<HTML
<table class='table table-hover'>
    <tr>
        <th class="sort" data-side="{$_POST['side']}" data-by="name">Nome</th>
        <th>Permessi</th>
        <th class="sort" data-side="{$_POST['side']}" data-by="size">Dimensione</th>
        <th class="sort" data-side="{$_POST['side']}" data-by="time">Data ed ora</th>
    </tr>\n
HTML;
foreach($d as $f) {
    echo '<tr>';
    echo '<td>';
    printf(
        '<input type="checkbox" class="sel form-check-input me-2" value="%s" data-side="%s">',
        htmlspecialchars($f->path),$_POST['side']
    );
    if (is_dir($f->path)) {
        printf(
            '<a class="dir" data-file="%s" data-side="%s" href="#">%s</a>',
            htmlspecialchars($f->path),$_POST['side'],htmlspecialchars($f->name)
        );
    } else {
        printf(
            '<span class="file" data-file="%s" data-side="%s">%s</span>',
            htmlspecialchars($f->path),$_POST['side'],htmlspecialchars($f->name)
        );
    }
    echo '</td><td>'.$f->getMode().'</td>';
    echo "<td>{$f->getSize()}</td>";
    echo "<td>{$f->getTime()}</td>";
    echo "</tr>\n";
}
echo "</table>\n";
$_SESSION[$_POST['side']]['path']=$_POST['data'];
