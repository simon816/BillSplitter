<?php

namespace App;

class ErrorHandler {

    const DEBUG = true;

    public static final function exitNow($statusCode, $message = "") {
        http_response_code($statusCode);
        if (self::DEBUG) {
            throw new \Exception($message);
        }
        $tplName = "error/{$statusCode}";
        if (!Template::templateExists($tplName)) {
            $tplName = "error/error";
        }
        echo Template::renderTemplate($tplName, array('code' => $statusCode, 'message' => $message));
        exit();
    }

}
