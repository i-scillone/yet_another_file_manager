<?php
function varDump(...$x): void
{
    $trace=debug_backtrace();
    echo "<pre class='text-info'>Riga {$trace[0]['line']}:\n";
    foreach ($x as $item) {
        echo '‹'.gettype($item).'› '.json_encode($item,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
    }
    echo "</pre>\n";
}
function dumpToLog(...$x): void
{
    $now=new DateTimeImmutable();
    $trace=debug_backtrace();
    $f=fopen(__DIR__.'/debug.log','a');
    fwrite($f,$now->format('[d M, H:i:s.u] '));
    fwrite($f,'['.basename($trace[0]['file']).':'.$trace[0]['line'].']'.PHP_EOL);
    foreach ($x as $v) {
        fwrite($f,'['.gettype($v).'] ');
        fwrite($f,json_encode($v,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL);
    }
    fclose($f);
}
function toLog(string $x): void
{
    $now=new DateTimeImmutable();
    $trace=debug_backtrace();
    $f=fopen(__DIR__.'/debug.log','a');
    fwrite($f,$now->format('[d M, H:i:s.u] '));
    fwrite($f,'['.basename($trace[0]['file']).':'.$trace[0]['line'].'] ');
    fwrite($f,$x.PHP_EOL);
    fclose($f);
}
function zipErr(int $x): string
{
    switch ($x) {
        case ZipArchive::ER_EXISTS:
            $r='File already exists.';
            break;
        case ZipArchive::ER_INCONS:
            $r='Zip archive inconsistent.';
            break;
        case ZipArchive::ER_INVAL:
            $r='Invalid argument.';
            break;
        case ZipArchive::ER_MEMORY:
            $r='Malloc failure.';
            break;
        case ZipArchive::ER_NOENT:
            $r='No such file.';
            break;
        case ZipArchive::ER_NOZIP:
            $r='Not a zip archive.';
            break;
        case ZipArchive::ER_OPEN:
            $r="Can't open file.";
            break;
        case ZipArchive::ER_READ:
            $r='Read error.';
            break;
        case ZipArchive::ER_SEEK:
            $r='Seek error.';
            break;
    }
    return $r;
}
function deleteAll(string $path): bool
{
    $d=scandir($path);
    foreach ($d as $f) {
        if ($f=='.' || $f=='..') continue;
        $full=realpath($path.'/'.$f);
        if (is_dir($full)) {
            $r1=deleteAll($full);
            $r2=@rmdir($full);
            if (!$r1 && !$r2) return false;
        } else {
            $r1=chmod($full,0666);
            $r2=unlink($full);
            if (!$r1 && !$r2) return false;
        }
    }
    $r1=rmdir($path);
    return $r1;
}
class dirEntry
{
    public $path;
    public $mode;
    public $size;
    public $time;

    public function __construct(string $path)
    {
        $this->path=$path;
        $stat=stat($path);
        $this->mode=$stat['mode'];
        $this->size=$stat['size'];
        $this->time=$stat['mtime'];
    }
    public function getName(): string
    {
        return basename($this->path);
    }
    public function getMode(): string
    {
        $r='----------';
        if ($this->mode & 040000) $r[0]='d';
        if ($this->mode & 0400) $r[1]='r';
        if ($this->mode & 0200) $r[2]='w';
        if ($this->mode & 0100) $r[3]='x';
        if ($this->mode & 040) $r[4]='r';
        if ($this->mode & 020) $r[5]='w';
        if ($this->mode & 010) $r[6]='x';
        if ($this->mode & 04) $r[7]='r';
        if ($this->mode & 02) $r[8]='w';
        if ($this->mode & 01) $r[9]='x';
        return $r;
    }
    public function getExt(): string
    {
        return pathinfo($this->path,PATHINFO_EXTENSION);
    }
    public function getSize(): string
    {
        if ($this->size/1000000000) {
            $u='GB';
            $s=$this->size/1000000000;
        }
        if ($this->size>1000000) {
            $u='MB';
            $s=$this->size/1000000;
        } elseif ($this->size>1000) {
            $u='kB';
            $s=$this->size/1000;
        } else {
            $u='B';
            $s=$this->size;
        }
        return sprintf('%6.2f %s',$s,$u);
    }
    public function getTime(): string
    {
        $ita=new IntlDateFormatter('it_IT',IntlDateFormatter::MEDIUM,IntlDateFormatter::MEDIUM,'Europe/Rome');
        return $ita->format($this->time);
    }
}
class dirStruct implements Iterator
{
    private $struct;
    private $index;

    public function __construct(string $path)
    {
        $d=scandir($path);
        $this->struct=[];
        foreach ($d as $f) {
            if ($f=='.' || $f=='..') continue;
            $this->struct[]=new dirEntry($path.'/'.$f);
        }
        $this->index=0;
    }
    public function sortBy(string $order,bool $desc=false)
    {
        usort($this->struct,function($a,$b) use ($order,$desc){
            switch ($order) {
                case 'n':
                    $r=mb_strtolower($a->getName()) <=> mb_strtolower($b->getName());
                    break;
                case 'e':
                    $r=$a->getExt() <=> $b->getExt();
                    break;
                case 's':
                    $r=$a->size <=> $b->size;
                    break;
                case 'd':
                    $r=$a->time <=> $b->time;
                    break;
                default:
                    $r=0;
            }
            if ($desc) return -$r;
            else return $r;
        });
    }
    public function current(): mixed
    {
        return $this->struct[$this->index];
    }
    public function key(): mixed
    {
        return $this->index;
    }
    public function next(): void
    {
        $this->index++;
    }
    public function rewind(): void
    {
        $this->index=0;
    }
    public function valid(): bool
    {
        return isset($this->struct[$this->index]);
    }
}
class MyZip extends ZipArchive
{
    public function fromList(array $list)
    {
        foreach ($list as $item) {
            if ($item=='.' || $item=='..') continue;
            if (is_dir($item)) {
                echo "\e[31m$item è una dir.\e[0m\n";
                $d=scandir($item);
                $this->fromList($d);
            }
            else {
                echo "$item\n";
                $this->addFile($item);
            }
        }
    }
}