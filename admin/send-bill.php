<?php
// Deprecated: This endpoint now simply redirects to the new downloader.
// Use invoice-download.php for all invoice PDFs (single or multiple).

// Build ids list from id/ids params
$idsParam = isset($_GET['ids']) ? trim((string)$_GET['ids']) : '';
$singleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$ids = [];
if ($idsParam !== '') {
    foreach (explode(',', $idsParam) as $v) {
        $n = (int)trim($v);
        if ($n > 0) $ids[] = $n;
    }
} elseif ($singleId > 0) {
    $ids = [$singleId];
}

if (empty($ids)) {
    http_response_code(400);
    echo 'Deprecated. Use invoice-download.php?ids=ID1,ID2';
    exit;
}

$qs = http_build_query(['ids' => implode(',', $ids)]);
header('Location: invoice-download.php?' . $qs, true, 302);
exit;