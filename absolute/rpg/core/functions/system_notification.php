<?php
    /**
     * Send a system notification via database
     *
     * @param string $message The message to send
     * @param array $metadata Additional metadata
     *
     * @return bool Success
     */
    function SendSystemNotification($message, $metadata = []) {
        global $PDO;

        try {
            $stmt = $PDO->prepare("
                INSERT INTO system_notifications (
                    message,
                    metadata,
                    created_at,
                    processed
                ) VALUES (?, ?, ?, 0)
            ");

            return $stmt->execute([
                $message,
                json_encode($metadata),
                time()
            ]);
        } catch (Exception $e) {
            HandleError($e);

            return false;
        }
    }
