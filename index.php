<?php
session_set_cookie_params(21600,dirname($_SERVER['SCRIPT_NAME']));
session_start();
session_regenerate_id(true);
?>
<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Bootstrap con Scroll Indipendente</title>
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: Inter;
            src: url(InterVariable.woff2) format(woff2);
        }
        body { font-family: Inter; font-variant-numeric: slashed-zero; }
        table { color: inherit; background-color: inherit; }
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
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" id="action-3"><i class="bi bi-share me-2"></i>Cambia permessi</a>
    </div>
    <div id="copyDialog" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
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
    <div class="container-fluid bottomBox py-2 flex-shrink-0"><?= $feedback ?></div>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/npm-asset/jquery/dist/jquery.min.js"></script>
    <script>
    function doIt(action,data,side)
    {
        $('#action').val(action);
        $('#data').val(data);
        $('#side').val(side);
        $('#actionForm').submit();
    }
    $(document).on('click','.dir',function(ev){
        console.log('AJAX');
        ev.preventDefault();
        let encapsed=$(this);
        let side=encapsed.data('side');
        $(`.${side}Box .scroll-column`).load('ajax_server.php',{
            action: 'goTo',
            data: encapsed.data('path'),
            side: side
        });
    });
    // Menù contestuale
    const $contextMenu = $("#context-menu");
    let selectedFile = null;
    $(".dir, .file").on("contextmenu", function(e) {
        e.preventDefault(); // Blocca il menù del browser
        e.stopPropagation(); // Evita che l'evento si propaghi a elementi genitori
        // Ora 'this' è esattamente l'elemento .dir cliccato
        selectedFile = $(this);
        // Ottieni le coordinate e mostra il menù
        const mouseX = e.pageX;
        const mouseY = e.pageY;
        $contextMenu.css({
            top: mouseY + "px",
            left: mouseX + "px"
        }).show();
        console.log(selectedFile.data('path'));
    });
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#context-menu").length) {
            $contextMenu.hide();
        }
    });
    $contextMenu.on("click", ".dropdown-item", function(e) {
        e.preventDefault();
        const myModalAlternative = new bootstrap.Modal('#copyDialog');
        myModalAlternative.show();
        doIt($(this).attr("id"),selectedFile.data('path'),selectedFile.data('side'));
        $contextMenu.hide();
    });
    </script> 
</body>
</html>
