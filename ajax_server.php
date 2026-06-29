<?php
header('Content-Type: application/json');
require_once './vendor/autoload.php';
define('ALERT_TEMPLATE','<div class="alert alert-warning alert-dismissible fade show">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
define('MSG_TEMPLATE','<div class="alert alert-primary alert-dismissible fade show">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
class Result
{
    public bool $ok;
    public string $data;
    
    public function __construct()
    {
        $this->ok=false;
        $this->data='';
    }
    public function __toString()
    {
        return json_encode($this);
    }
}

$dbg=new MyClasses\Debug();
$dbg->log($_GET);
$r=new Result();
switch ($_GET['action'] ?? false) {
    case 'copy':
    case 'move':
    case 'rename':
        $overwrite = filter_var($_GET['overwrite'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (is_array($_GET['from'])) {
            $r->ok=true;
            foreach ($_GET['from'] as $item) {
                $name=basename($item);
                $to=$_GET['to'].DIRECTORY_SEPARATOR.$name;
                if ($_GET['action']=='move') $ok=rename($item,$to);
                else $ok=copy($item,$to);
                if ($ok===false) $r->ok=false;
            }
            if (!$r->ok) $r->data=$r->data=sprintf(ALERT_TEMPLATE,'Errore nella/o copia/spostamento!');
        } else {
            if ($_GET['action']=='rename') {
                $to=dirname($_GET['from']).DIRECTORY_SEPARATOR.basename($_GET['to']);
            } else {
                $to=$_GET['to'];
            }
            if (file_exists($to) && !$overwrite) {
                $r->data=sprintf(ALERT_TEMPLATE,'Il file esiste già!');
            } elseif ($_GET['action']=='copy') {
                $r->ok=copy($_GET['from'],$to);
            } else {
                $r->ok=rename($_GET['from'],$to);
            }
        }
        break;
    case 'delete':
        $r->ok=true;
        foreach ($_GET['file'] as $item) {
            $ok=unlink($item);
            if ($ok===false) $r->ok=false;
        }
        if (!$r->ok) $r->data=sprintf(ALERT_TEMPLATE,'Impossibile cancellare il file!');
        break;
    case 'new':
        $isDir=filter_var($_GET['dir']??false,FILTER_VALIDATE_BOOLEAN);
        if ($isDir) $r->ok=mkdir($_GET['name']);
        else $r->ok=touch($_GET['name']);
        if ($r->ok===false) $r->data=sprintf(ALERT_TEMPLATE,'Impossibile creare il file o la directory!');
        break;
    case 'owner':
        $inf=new MyClasses\DirEntry($_GET['file']);
        $r->data=sprintf(
            MSG_TEMPLATE,
            basename($_GET['file']).' ➔ '.$inf->getOwner()
        );;
        $r->ok=true;
        break;
    default:
        $r->data=sprintf(ALERT_TEMPLATE,'Azione non implementata!');
}
echo $r;
