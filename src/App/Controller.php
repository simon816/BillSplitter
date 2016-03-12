<?php

namespace App;

abstract class Controller {

    protected $template;

    protected function __construct($template = null) {
        if ($template != null && !is_string($template)) {
            throw new \InvalidArgumentException("Provided template argument is not a string");
        }
        $this->template = $template;
    }

    public function handleRequest($request, $ruleMatched) {
        $url = parse_url($request);
        if ($url === false) {
            $this->handleError(400, "Invalid URI");
        }
        if (!$this->authorizeRequest($request)) {
            $this->handleError(403, "Authorization failed");
        }
        if (!isset($url['path']) || strpos($url['path'], $ruleMatched) !== 0) {
            $this->handleError(404, "Path doesn't match rule");
        }
        $path = substr($url['path'], strlen($ruleMatched)) ?: '';
        if ($path !== '' && $path{0} !== '/') {
            $this->handleError(404, "File not found");
        }
        $parts = explode('/', substr($path, 1) ?: '', 2);
        if (count($parts) < 2) {
            $parts[] = '';
        }
        list($action, $args) = $parts;
        try {
            if (!$this->handleAction($action, $args)) {
                $this->handleError(404, "Unknown action '{$action}'");
            }
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    protected function authorizeRequest($request) {
        return true;
    }

    protected function handleAction($action, $args) {
        if ($action === '') {
            $this->handleDefaultAction();
            return true;
        }
        return false;
    }

    protected function isGet() {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'GET';
    }

    protected function isPost() {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    protected function handleDefaultAction() {
        switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
            case 'GET':
                return $this->handleDefaultGet();
            case 'POST':
                return $this->handleDefaultPost();
            default:
                $this->handleError(405, "HTTP Verb \"{$_SERVER['REQUEST_METHOD']}\" unsupported");
        }
    }

    protected function handleDefaultGet() {
        if ($this->template != null) {
            $this->output($this->renderTemplate($this->template));
        } else {
            $this->ensureGet();
        }
    }

    protected function handleDefaultPost() {
        $this->handleError(405, "POST request is not supported at this endpoint");
    }

    protected function renderTemplate($template, array $vars = array()) {
        $vars = array_merge($this->getGlobalVars(), $vars);
        return Template::renderTemplate($template, $vars);
    }

    protected function getGlobalVars() {
        $userId = AuthManager::getUserId();
        $user = $userId !== null ? \UserManager::getName($userId) : null;
        return array(
            'user' => $user
        );
    }

    protected function handleError($code, $message = "") {
        ErrorHandler::exitNow($code, $message);
    }

    protected function fail(\Exception $e, $code = 500) {
        $this->handleError($code, $e);
    }

    protected function failJson(\Exception $e, $code = 500) {
        http_response_code($code);
        $this->outputJson(array('error' =>
            array('message' => $e->getMessage(), 'code' => $e->getCode(), 'type' => get_class($e))
        ));
        exit;
    }

    protected function output($data) {
        echo $data;
    }

    protected function outputJson($data) {
        header('Content-Type: application/json');
        $this->output(json_encode($data));
    }

    protected function checkSuccessJson($ensureTrue, $errorMessage) {
        if ($ensureTrue !== true) {
            $this->failJson(new \Exception($errorMessage));
        }
    }

    protected function redirect($path, $code = 302) {
        if (!empty($path) && $path{0} === '/') {
            $path = ROOT . substr($path, 1);
        }
        header("Location: {$path}", true, $code);
        exit;
    }

    protected function ensureGet() {
        if (!$this->isGet()) {
            $this->handleError(405, "GET request is not supported at this endpoint");
        }
    }

    protected function validatePost($varName, $opt0 = null, $opt1 = null) {
        if (!$this->isPost()) {
            $this->handleError(405);
        }
        if (!array_key_exists($varName, $_POST)) {
            $this->handleError(400, "Missing post data '{$varName}'");
        }
        $value = $_POST[$varName];
        if ($opt0 !== null) {
            if (is_int($opt0)) {
                $minLen = $opt0;
                if (!is_string($value)) {
                    $this->handleError(400, "Post data '{$varName}' is not a valid string");
                }
                if (strlen($value) < $minLen) {
                    $this->handleError(400, "Post data '{$varName}' is shorter than the required length of {$minLen}");
                }
            } elseif (is_string($opt0)) {
                if ($opt0 === 'int') {
                    if (!ctype_digit($value)) {
                        $this->handleError(400, "Post data '{$varName}' is not a valid integer");
                    }
                    $value = (int) $value;
                    if (is_int($opt1) && $value < $opt1) {
                        $this->handleError(400, "Post data '{$varName}' is smaller than the minimum of {$opt1}");
                    }
                } elseif ($opt0 === 'float') {
                    if (!is_numeric($value) || $value != (string) (float) $value) {
                        $this->handleError(400, "Post data '{$varName}' is not a valid float");
                    }
                    $value = (float) $value;
                    if (is_float($opt1) && $value < $opt1) {
                        $this->handleError(400, "Post data '{$varName}' is smaller than the minimum of {$opt1}");
                    }
                } elseif ($opt0 === 'array') {
                    if (!is_array($value)) {
                        $this->handleError(400, "Post data '{$varName}' is not an array");
                    }
                }
            }
        }
        return $value;
    }

    protected function loadModel($modelName) {
        return new $modelName(\Database::getInstance());
    }

}
