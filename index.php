<?php
session_set_cookie_params(3600,'/yafm');
session_start();
require_once 'vendor/autoload.php';
$dbg=new MyClasses\Debug();
if (!isset($_SESSION['left']) || !isset($_SESSION['right'])) {
    $_SESSION=['left'=>'.','right'=>'.'];
}
?>
<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yet Another File Manager</title>
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: Inter;
            src: url(InterVariable.woff2) format(woff2);
        }
        body { font-family: Inter; font-variant-numeric: slashed-zero; }
        table { color: inherit; background-color: inherit; }
        table a { text-decoration: none; }
        /* Forza le colonne ad occupare SOLO l'altezza del loro contenitore e attiva lo scroll */
        .scroll-column {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            overflow-y: auto; /* Attiva la barra solo se serve */
            padding: 1rem;    /* Ripristina il padding p-3 di bootstrap che viene gestito meglio qui */
        }
        /* Serve a dare un posizionamento relativo ai due blocchi principali */
    </style>
</head>
<body class="vh-100 d-flex flex-column m-0 overflow-hidden">
    <div id="context-menu" class="dropdown-menu" style="position: absolute; display: none;">
        <a class="dropdown-item" href="#" id="copy"><i class="bi bi-copy me-2"></i>Copia</a>
        <a class="dropdown-item" href="#" id="move"><i class="bi bi-arrows-move me-2"></i>Sposta</a>
        <a class="dropdown-item" href="#" id="delete"><i class="bi bi-trash"></i>Cancella</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" id="action-3"><i class="bi bi-share me-2"></i>Cambia permessi</a>
    </div>
    <div id="confirm-dialog" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Sei sicuro?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button id="confirm-yes" type="button" class="btn btn-primary">Sì</button>
                </div>
            </div>
        </div>
    </div>
    <div id="copy-dialog" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="copy-name">Nome</label>
                    <input id="copy-name" type="text" class="form-control">
                    <label for="copy-ext">Estensione</label>
                    <input id="copy-ext" type="text" class="form-control">
                    <input id="copy-overwrite" type="checkbox" class="from-check-input">
                    <label for="copy-overwite" class="from-check-label">Sovrascrivi</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button id="copy-engage" type="button" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid flex-grow-1 position-relative p-0" style="min-height: 0;">
        <div class="row g-0 h-100 w-100 position-absolute top-0 start-0">
            <div class="col-6 leftBox position-relative h-100">
                <div class="scroll-column">..</div>
            </div>
            <div class="col-6 rightBox position-relative h-100">
                <div class="scroll-column">..</div>
            </div>
        </div>
    </div>
    <div class="container-fluid bottomBox py-2 flex-shrink-0 border-top">Status bar</div>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/npm-asset/jquery/dist/jquery.min.js"></script>
    <script>
        <?php 
        printf("const DIR_SEP='%s';\n",addslashes(DIRECTORY_SEPARATOR));
        printf("const SESSION=%s;\n",json_encode($_SESSION));
        ?>
        function pathInfo(x)
        {
            let found=x.match(/^(.*[\/\\])?([^\/\\]+?)(?:\.([^.]+))?$/);
            let fullName;
            if (typeof found[3]=='undefined') fullName=found[2];
            else fullName=found[2]+'.'+found[3];
            if (found) return { path: found[1], fullName: fullName, name: found[2], ext: found[3] };
            else return false;
        }
        const state={
            action: null,
            selectedFile: null,
            otherSide: null,
            dialog: null,
            setSelectedFile(x) {
                this.selectedFile={
                    inf: pathInfo(x.data('path')),
                    file: x.data('path'),
                    side: x.data('side')
                };
            },
            setOtherSide() {
                let side = (this.selectedFile.side == 'left') ? 'right' : 'left';
                this.otherSide={
                    path: $('#path-'+side).val(),
                    side: side,
                    class: `.${side}Box`
                };
            }
        };

        $( '.leftBox .scroll-column').load('list.php',{ data:SESSION.left, side:'left' });
        $('.rightBox .scroll-column').load('list.php',{ data:SESSION.right, side:'right' });
        $(document).on('click','.dir',function(ev){
            ev.preventDefault();
            let encapsed=$(this);
            let side=encapsed.data('side');
            $(`.${side}Box .scroll-column`).load('list.php',{
                data: encapsed.data('path'),
                side: side
            });
        });
        // Menù contestuale
        const contextMenu = $("#context-menu");
        $(document).on("contextmenu",".dir, .file",function(e) {
            e.preventDefault(); // Blocca il menù del browser
            e.stopPropagation(); // Evita che l'evento si propaghi a elementi genitori
            // Ora 'this' è esattamente l'elemento .dir cliccato
            state.setSelectedFile($(this));
            // Ottieni le coordinate e mostra il menù
            const mouseX = e.pageX;
            const mouseY = e.pageY;
            contextMenu.css({
                top: mouseY + "px",
                left: mouseX + "px"
            }).show();
        });
        $(document).on("click", function(e) {
            if (!$(e.target).closest("#context-menu").length) {
                contextMenu.hide();
            }
        });
        contextMenu.on("click", ".dropdown-item", function(e) {
            e.preventDefault();
            state.setOtherSide();
            state.action=this.id;
            switch (state.action) {
                case 'copy':
                    $('#copy-dialog .modal-title').text('Copia su '+state.otherSide.path);
                    $('#copy-dialog #copy-name').val(state.selectedFile.inf.name);
                    $('#copy-dialog #copy-ext').val(state.selectedFile.inf.ext);
                    state.dialog = new bootstrap.Modal('#copy-dialog');
                    state.dialog.show();
                    break;
                case 'delete':
                    $('#confirm-dialog .modal-body').html('Sei sicuro di voler cancellare <mark>'+state.selectedFile.file+'</mark>?');
                    state.dialog=new bootstrap.Modal('#confirm-dialog');
                    state.dialog.show();
                    break;
                case 'move':
                    $('#copy-dialog .modal-title').text('Sposta su '+state.otherSide.path);
                    $('#copy-dialog #copy-name').val(state.selectedFile.inf.name);
                    $('#copy-dialog #copy-ext').val(state.selectedFile.inf.ext);
                    state.dialog = new bootstrap.Modal('#copy-dialog');
                    state.dialog.show();
                    break;
            }
            contextMenu.hide();
        });
        $(document).on('click','#copy-engage',(ev)=>{
            $.getJSON(
                'ajax_server.php',
                {
                    action: state.action,
                    from: state.selectedFile.file,
                    to: state.otherSide.path+DIR_SEP+$('#copy-name').val()+'.'+$('#copy-ext').val(),
                    overwrite: $('#copy-overwrite').prop('checked')
                },
                function(x){
                    if (!x.ok) {
                        $('.bottomBox').html(x.data);
                    } else {
                        $('.'+state.otherSide.side+'Box .scroll-column').load(
                            'list.php',{data:state.otherSide.path,side:state.otherSide.side}
                        );
                        if (state.action=='move') {
                            $('.'+state.selectedFile.side+'Box .scroll-column').load(
                                'list.php',{data:state.selectedFile.inf.path,side:state.selectedFile.side}
                            );
                        }
                    }
                }
            );
            state.dialog.hide();
        });
        $(document).on('click','#confirm-yes',(ev)=>{
            $.getJSON(
                'ajax_server.php',
                {
                    action: 'delete',
                    file: state.selectedFile.file
                },
                function(x){
                    if (!x.ok) {
                        $('.bottomBox').html(x.data);
                    } else {
                        console.log(state);
                        $('.'+state.selectedFile.side+'Box .scroll-column').load(
                            'list.php',{data:state.selectedFile.inf.path,side:state.selectedFile.side}
                        );
                    }
                }
            );
            state.dialog.hide();
        });
    </script> 
</body>
</html>
