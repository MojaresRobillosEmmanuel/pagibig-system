<?php
class Response {
    public static function json($data, $status = 200) {
        // Clean output buffer if anything was output before
        if (ob_get_level()) {
            ob_clean();
        }

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        
        // Ensure proper JSON encoding with error handling
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($json === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            $json = json_encode([
                'status' => 'error',
                'message' => 'Internal server error: JSON encoding failed'
            ]);
        }
        
        echo $json;
        exit();
    }

    public static function success($data = null, $message = 'Success') {
        return self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'An error occurred', $status = 400) {
        return self::json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }

    public static function unauthorized($message = 'Unauthorized access') {
        return self::json([
            'status' => 'error',
            'message' => $message
        ], 401);
    }
}
