<?php

namespace App;

class ErrorHandler {

    const DEBUG = true;

    public static final function exitNow($statusCode, $message = "") {
        http_response_code($statusCode);
        if (self::DEBUG) {
            if ($message instanceof \Exception) {
                throw $message;
            }
            throw new \Exception($message);
        }
        if ($message instanceof \Exception) {
            $message = $message->getMessage();
        }
        $tplName = "error/{$statusCode}";
        if (!Template::templateExists($tplName)) {
            $tplName = "error/error";
        }
        echo Template::renderTemplate($tplName, array('code' => $statusCode, 'message' => $message));
        exit();
    }

}
