<?php

date_default_timezone_set('Europe/London');

ob_start('ob_gzhandler');

set_error_handler(function ($severity, $message, $file, $line, $args) {
        throw new ErrorException($message, 1, $severity, $file, $line);
});

spl_autoload_register(function ($class) {
    $file =  $base_dir = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

/// PHP compat

if (!function_exists('http_response_code')) {
    function http_response_code($code) {
        header("{$_SERVER['SERVER_PROTOCOL']} {$code}", true, $code);
    }
}


require 'config.php';

function routeRequest($route) {
    if (strlen($route) > 0 && $route{0} != '/') {
        $route = "/{$route}";
    }
    $file = PUBLIC_DIR . $route;
    // Public file response (in case .htaccess is not used)
    if (is_file($file)) {
        $mTime = filemtime($file);
        $etag = md5_file($file);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mTime) . ' GMT');
        header("Etag: {$etag}");

        // css seems to be detected as text/plain
        $type = strpos($route, '/css/') === 0 ? 'text/css' : mime_content_type($file);
        header("Content-Type: $type");
        if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)
            || (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $mTime)) {
            http_response_code(304);
        } else {
            readfile(PUBLIC_DIR . $route);
        }
        return;
    }
    $routes = json_decode(file_get_contents(ROUTES));

    if ($routes === null) {
        ErrorHandler::exitNow(500, "Failed to read routes.json");
    }

    $mostSpecific = '';
    $controller = null;
    foreach ($routes as $path => $pathController) {
        if (stripos($route, $path) === 0 && strlen($path) > strlen($mostSpecific)) {
            $mostSpecific = $path;
            $controller = $pathController;
        }
    }

    if ($controller === null) {
        ErrorHandler::exitNow(404, "Unknown route \"$route\"");
    }

    $controllerPath = CONTROLLER_DIR . "/{$controller}.php";
    if (!file_exists($controllerPath)) {
        ErrorHandler::exitNow(500, "Controller file does not exist");
    }

    require $controllerPath;

    $className = "{$controller}Controller";

    $ctlr = new $className();
    if (!($ctlr instanceof App\Controller)) {
        ErrorHandler::exitNow(500, "Constructed controller is not actually a controller");
    }
    $ctlr->handleRequest($route, $mostSpecific);
}

if (ROOT === $_SERVER['SCRIPT_NAME'] . '/') {
    $uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
} else {
    $uri = isset($_GET['route']) ? $_GET['route'] : '/';
}
routeRequest($uri);
