<?php
header('Content-Type: application/json');
require_once './vendor/autoload.php';
define('ALERT_TEMPLATE','<div class="alert alert-warning alert-dismissible fade show">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
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
        $overwrite = filter_var($_GET['overwrite'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (file_exists($_GET['to']) && !$overwrite) {
            $r->data=sprintf(ALERT_TEMPLATE,'Il file esiste già!');
        } elseif ($_GET['action']=='move') {
            $r->ok=rename($_GET['from'],$_GET['to']);
        } else {
            $r->ok=copy($_GET['from'],$_GET['to']);
        }
        break;
    case 'delete':
        $r->ok=unlink($_GET['file']);
        if (!$r->ok) $r->data=sprintf(ALERT_TEMPLATE,'Impossibile cancellare il file!');
        break;
    default:
        $r->data=sprintf(ALERT_TEMPLATE,'Azione non implementata!');
}
echo $r;
