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
function dbgLog(...$x): void
{
    $now=new DateTimeImmutable();
    $trace=debug_backtrace();
    $f=fopen(__DIR__.'/debug.log','a');
    fwrite($f,$now->format('[d M, H:i:s.u] '));
    fwrite($f,'['.basename($trace[0]['file']).':'.$trace[0]['line'].']'.PHP_EOL);
    foreach ($x as $v) {
        fwrite($f,gettype($v)."\t");
        fwrite($f,json_encode($v,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL);
    }
    fclose($f);
}
function modeToText(int $x): string
{
    $r='----------';
    if ($x & 040000) $r[0]='d';
    if ($x & 0400) $r[1]='r';
    if ($x & 0200) $r[2]='w';
    if ($x & 0100) $r[3]='x';
    if ($x & 040) $r[4]='r';
    if ($x & 020) $r[5]='w';
    if ($x & 010) $r[6]='x';
    if ($x & 04) $r[7]='r';
    if ($x & 02) $r[8]='w';
    if ($x & 01) $r[9]='x';
    return $r;
}
function formatSize(int $x): string
{
    if ($x/1000000000) {
        $u='GB';
        $s=$x/1000000000;
    }
    if ($x>1000000) {
        $u='MB';
        $s=$x/1000000;
    } elseif ($x>1000) {
        $u='kB';
        $s=$x/1000;
    } else {
        $u='B';
        $s=$x;
    }
    return sprintf('%6.2f %s',$s,$u);
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