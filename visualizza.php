<?php
if (file_exists($_GET['file'])) {
    // 1. IMPORTANTE: Forza l'apertura INLINE (nel browser)
    header('Content-Disposition: inline; filename="'.basename($_GET['file']).'"');
    // 2. IMPORTANTE: Specifica il tipo di file corretto
    header('Content-Type: ' . mime_content_type($_GET['file']));
    header('Content-Length: ' . filesize($_GET['file']));
    readfile($_GET['file']);
    exit;
} else {
    echo "File non trovato.";
}