<?php
/**
 * check-reset.php
 * Lightweight endpoint polled by the browser after an inline edit to
 * trigger the scheduled demo auto-reset as soon as it's due, instead of
 * waiting for the next unrelated page load.
 */

require_once __DIR__ . '/includes/ContentReset.php';

$before = simplephp_json_read(CONTENT_RESET_SCHEDULE_FILE, null);
$wasDue = is_array($before) && isset($before['reset_at']) && time() >= $before['reset_at'];

simplephp_maybe_auto_reset_content();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'reset' => $wasDue]);
