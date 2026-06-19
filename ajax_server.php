<?php
header('Content-Type: application/json');
require_once './vendor/autoload.php';
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
        if (file_exists($_GET['to'])) {
            $r->data='<div class="alert alert-warning">Il file esiste già!</div>';
        } else {
            $r->ok=copy($_GET['from'],$_GET['to']);
        }
        break;
    default:
        $r->data='<div class="alert alert-danger">Azione non implementata!</div>';
}
echo $r;
