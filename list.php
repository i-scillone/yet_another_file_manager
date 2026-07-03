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
$d=getDirectory($_POST['data']);
usort($d,function ($a, $b) {
    if ($a->isDir() && !$b->isDir()) return -1;
    if (!$a->isDir() && $b->isDir()) return 1;
    $r=0;
    switch ($_SESSION[$_POST['side']]['sortBy']) {
        case 'size':
            $r=$a->size <=> $b->size;
            break;
        case 'time':
            $r=$a->time <=> $b->time;
            break;
        default:
            $r=strcasecmp($a->name,$b->name);
    }
    if ($_SESSION[$_POST['side']]['desc']) return -$r;
    else return $r;
});
printf(
    TEMPLATE,
    $_POST['side'],htmlspecialchars(realpath($_POST['data'])),$_POST['side']
);
$headers=['name'=>'Nome','perm'=>'Permessi','size'=>'Dimensione','time'=>'Data ed ora'];
echo "<table class='table table-hover'>\n<tr>";
foreach ($headers as $k=>$v) {
    if ($_SESSION[$_POST['side']]['sortBy']==$k) {
        if ($_SESSION[$_POST['side']]['desc']) $label=$v.'↓';
        else $label=$v.'↑';
    } else {
        $label=$v;
    }
    printf(
        '<th class="%s" data-side="%s" data-by="%s">%s</th>',
        $k!='perm'?'sort':'',$_POST['side'],$k,$label
    );
}
echo "</tr>\n";
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
