<?php
session_set_cookie_params(3600,dirname($_SERVER['REQUEST_URI']));
session_start();
$theme=$_SESSION['theme'];
if ($theme=='dark') {
    $editorTheme='shadowfox';
    $ruler='"#404000"';
} else {
    $editorTheme='eclipse';
    $ruler='"#ffff00"';
}
?>
<!doctype html>
<html lang="it" data-bs-theme="<?= $theme ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= basename($_REQUEST['file']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="codemirror/theme/shadowfox.css">
    <link rel="stylesheet" href="codemirror/theme/eclipse.css">
    <link rel="stylesheet" href="codemirror/addon/dialog/dialog.css">
    <script src="codemirror/lib/codemirror.js"></script>
    <script src="codemirror/addon/edit/matchbrackets.js"></script>
    <script src="codemirror/addon/edit/matchtags.js"></script>
    <script src="codemirror/addon/fold/xml-fold.js"></script>
    <script src="codemirror/addon/search/search.js"></script>
    <script src="codemirror/addon/search/searchcursor.js"></script>
    <script src="codemirror/addon/dialog/dialog.js"></script>
    <script src="codemirror/addon/edit/closetag.js"></script>
    <script src="codemirror/mode/xml/xml.js"></script>
    <script src="codemirror/mode/javascript/javascript.js"></script>
    <script src="codemirror/mode/css/css.js"></script>
    <script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="codemirror/mode/clike/clike.js"></script>
    <script src="codemirror/mode/php/php.js"></script>
    <script src="codemirror/mode/shell/shell.js"></script>
    <script src="codemirror/mode/diff/diff.js"></script>
    <script src="codemirror/addon/display/rulers.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        .CodeMirror { height: 75vh; font-size: 12px; }
    </style>
  </head>
  <body>
<?php
const MODES=[
    '...'=>'',
    'CSS'=>'css',
    'Diff'=>'diff',
    'HTML'=>'text/html',
    'JavaScript'=>'javascript',
    'PHP'=>'application/x-httpd-php',
    'Shell'=>'shell'
];
if (isset($_REQUEST['mode'])) {
    $mode=$_REQUEST['mode'];
} else {
    $e=pathinfo($_REQUEST['file'],PATHINFO_EXTENSION);
    switch ($e) {
        case 'css':
            $mode=MODES['CSS'];
            break;
        case 'html':
            $mode=MODES['HTML'];
            break;
        case 'js':
            $mode=MODES['JavaScript'];
            break;
        case 'php':
            $mode=MODES['PHP'];
            break;
        default:
            $mode='';
    }
}
$options='';
foreach(MODES as $k=>$v) {
    if ($v==$mode) $sel=' selected';
    else $sel='';
    $options.="<option value='$v'$sel>$k</option>";
}
if (isset($_REQUEST['doIt'])) {
    switch ($_REQUEST['doIt']) {
        case 'save':
            file_put_contents($_REQUEST['file'],$_REQUEST['source']);
            break;
    }
}
?>
    <div class="container">
    	<h1><?= $_REQUEST['file'] ?></h1>
        <form name="mainForm" action="edit.php" method="post">
          <div class="row">
            <div class="col input-group mb-2">
                <select id="mode" name="mode" class="form-select">
                <?= $options ?>
                </select>
            </div>
            <div class="col">
            	<button name="doIt" type="submit" value="save" class="btn btn-primary"><i class="bi bi-floppy"></i></button>
                <a id="search" href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#info"><i class="bi bi-question"></i></a>
            </div>
          </div>
            <?= "<input name='file' type='hidden' value='$_REQUEST[file]'>"; ?>
            <textarea name="source"><?php echo htmlspecialchars(file_get_contents($_REQUEST['file'])); ?></textarea>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        var ed;
        $(function(){
            ed=CodeMirror.fromTextArea(document.mainForm.source,{
                theme: '<?= $editorTheme ?>',
                lineNumbers: true,
                indentUnit: 4,
                matchBrackets: true,
                matchTags: true,
                autoCloseTags: true,
                extraKeys: {
                    'Ctrl-I': function(cm){
                        cm.execCommand('indentMore');
                    },
                    'Shift-Ctrl-I': function(cm){
                        cm.execCommand('indentLess');
                    }
                },
                rulers: [{ 
                    column: 80,
                    color: <?= $ruler ?>,
                    lineStyle: 'dashed'
                }],
                mode: '<?= $mode ?>'
            });
            for (k in CodeMirror.keyMap.default) {
                var v=CodeMirror.keyMap.default[k];
                if (k!='fallthrough') $('#mappings').append('<tr><td>'+k+'</td><td>'+v+'</td></tr>');
            }
            $('#mode').on('change',function(ev){
                ed.setOption('mode',this.value);
            });
        });
    </script>
    <div class="modal fade" id="info">
      <div class="modal-dialog">
        <div class="modal-content text-dark bg-info">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Tasti dell'Editor</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <table id="mappings" class="table"></table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>