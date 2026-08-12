<?php
/**
 * Read-only content.json accessor for the inline editor.
 * content.json now lives outside the web root, so the browser can no
 * longer fetch it directly - this proxies it for logged-in admins only.
 */

require_once __DIR__ . '/includes/Security.php';
simplephp_secure_session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

echo json_encode(simplephp_json_read(SIMPLEPHP_DATA_DIR . '/content.json', ['menu' => [], 'pages' => [], 'site' => []]));
