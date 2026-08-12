<?php
/**
 * ContentReset
 *
 * Shared helpers for the demo auto-reset feature: whenever content is
 * edited, a reset is scheduled for N seconds later. Since there's no
 * guaranteed cron/background worker in this environment, the schedule is
 * checked lazily on normal page loads ("poor man's cron") - the first
 * request that comes in after the scheduled time performs the reset.
 */

declare(strict_types=1);

require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/Security.php';

define('CONTENT_RESET_SCHEDULE_FILE', SIMPLEPHP_DATA_DIR . '/reset_schedule.json');
define('CONTENT_RESET_CURRENT_JSON', SIMPLEPHP_DATA_DIR . '/content.json');
define('CONTENT_RESET_DEFAULT_JSON', SIMPLEPHP_DATA_DIR . '/content_default.json');

/**
 * Schedule (or push back) an automatic content reset.
 * Called whenever new content is saved. Each call resets the countdown,
 * so the reset fires N seconds after the *last* edit.
 */
function simplephp_schedule_content_reset(int $seconds = 30): void
{
    $schedule = ['reset_at' => time() + $seconds];
    simplephp_json_write(CONTENT_RESET_SCHEDULE_FILE, $schedule);
}

/** Cancel any pending scheduled reset. */
function simplephp_cancel_content_reset(): void
{
    if (file_exists(CONTENT_RESET_SCHEDULE_FILE)) {
        @unlink(CONTENT_RESET_SCHEDULE_FILE);
    }
}

/** Copy content_default.json back over content.json. Returns true on success. */
function simplephp_perform_content_reset(): bool
{
    if (!file_exists(CONTENT_RESET_DEFAULT_JSON)) {
        return false;
    }
    return copy(CONTENT_RESET_DEFAULT_JSON, CONTENT_RESET_CURRENT_JSON);
}

/**
 * If a reset is scheduled and due, perform it and clear the schedule.
 * Safe to call on every request - it's a cheap no-op most of the time.
 */
function simplephp_maybe_auto_reset_content(): void
{
    if (!file_exists(CONTENT_RESET_SCHEDULE_FILE)) {
        return;
    }

    $schedule = simplephp_json_read(CONTENT_RESET_SCHEDULE_FILE, []);
    $resetAt = $schedule['reset_at'] ?? null;

    if (!is_int($resetAt) && !is_float($resetAt)) {
        simplephp_cancel_content_reset();
        return;
    }

    if (time() >= $resetAt) {
        simplephp_perform_content_reset();
        simplephp_cancel_content_reset();
    }
}
