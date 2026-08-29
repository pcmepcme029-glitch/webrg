<?php
/**
 * MTARG License API
 * - ?key=XXXX-XXXX-XXXX-XXXX  → validate a license (used by the game server)
 * - ?action=list               → return all licensed servers (used by the client)
 *
 * Supabase credentials live in private/config.php (outside the web root).
 */

require_once __DIR__ . '/../private/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$key    = isset($_GET['key']) ? strtoupper(trim($_GET['key'])) : '';

// ------------------------------------------------------------
// Mode 1: list all licensed servers (for the client server browser)
// ------------------------------------------------------------
if ($action === 'list') {
    $url = $SUPABASE_URL . '/rest/v1/' . $TABLE
         . '?active=eq.true&select=server_name,server_ip,logo_url,banner_url,tags';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $SUPABASE_KEY,
        'Authorization: Bearer ' . $SUPABASE_KEY
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        http_response_code(502);
        echo json_encode(['error' => 'Could not fetch server list']);
        exit;
    }

    $rows = json_decode($response, true);
    if (!is_array($rows)) $rows = [];

    // Filter to servers that have a name + IP (fully registered)
    $servers = array_values(array_filter($rows, function ($r) {
        return !empty($r['server_name']) && !empty($r['server_ip']);
    }));

    echo json_encode(['servers' => $servers]);
    exit;
}

// ------------------------------------------------------------
// Mode 2: validate a license key
// ------------------------------------------------------------
if ($key === '') {
    http_response_code(400);
    echo json_encode(['valid' => false, 'message' => 'No license key provided']);
    exit;
}

$url = $SUPABASE_URL . '/rest/v1/' . $TABLE
     . '?' . $COL_KEY . '=eq.' . urlencode($key)
     . '&active=eq.true&select=id';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $SUPABASE_KEY,
    'Authorization: Bearer ' . $SUPABASE_KEY
]);
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$rows  = json_decode($response, true);
$valid = ($status === 200 && is_array($rows) && count($rows) > 0);

echo json_encode([
    'valid'   => $valid,
    'message' => $valid ? 'License accepted' : 'Invalid license'
]);