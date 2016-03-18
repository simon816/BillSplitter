<?php

namespace App;

class Template {

    public static function renderTemplate($templateFile, array $subsititions = array()) {
        $template = new Template($templateFile, $subsititions);
        return $template->render();
    }

    public static function templateExists($name) {
        return file_exists(TEMPLATE_DIR . "/{$name}.html");
    }

    private $file;
    private $vars;
    private $output;

    private function __construct($file, $vars) {
        $this->file = TEMPLATE_DIR . "/{$file}.html";
        if (!file_exists($this->file)) {
            throw new \Exception("Template file doesn't exist! {$this->file}");
        }
        $this->vars = $vars;
    }

    public function render() {
        if ($this->output !== null) {
            return $this->output;
        }
        $content = file_get_contents($this->file);
        return $this->output = $this->replaceTags($content);
    }

    private function replaceTags($content) {
        $content = preg_replace_callback('/[\r\n]?\{#\s*(.+?)\s*#\}[\r\n]?/s', array($this, 'handleReplace'), $content);
        $content = preg_replace_callback('/[\r\n]?\{%\s*(.+?)\s*%\}[\r\n]?/s', array($this, 'handleReplace'), $content);
        return $content;
    }

    private function handleReplace(array $match) {
        $instruction = $match[1];
        if (preg_match('/^url (.+)/', $instruction, $urlMatch)) {
            $url = $urlMatch[1];
            if ($url{0} === '/') {
                $url = substr($url, 1);
            }
            return ROOT . $url;
        }
        if (preg_match('/^include (.+)/', $instruction, $incMatch)) {
            return self::renderTemplate($incMatch[1], $this->vars);
        }
        if (preg_match('/^=(\S+)/', $instruction, $varMatch)) {
            return $this->getVar($varMatch[1]);
        }
        if (preg_match('/^(\S+)=(.*)/s', $instruction, $varMatch)) {
            $this->setVar($varMatch[1], $this->replaceTags($varMatch[2]));
            return '';
        }
    }

    private function sanitize($value, $modifiers) {
        if (!$modifiers) {
            return htmlspecialchars($value); // default
        }
        if ($modifiers == 'html') {
            return $value;
        }
        throw new \Execption("Unknown modifier {$modifiers}");
    }

    private function getVar($name) {
        $parts = explode('|', $name, 2);
        if (count($parts) == 1) {
            $parts[1] = '';
        }
        $modifiers = $parts[1];
        $parts = explode('.', $parts[0]);
        $obj = $this->vars;
        foreach ($parts as $part) {
            if (is_array($obj)) {
                if (array_key_exists($part, $obj)) {
                    $obj = $obj[$part];
                } else {
                    $obj = null;
                }
            } elseif (is_object($obj)) {
                if (isset($obj->$part)) {
                    $obj = $obj->$part;
                } else {
                    $obj = null;
                }
            } else {
                throw new \Execption();
            }
            if ($obj === null || (!is_object($obj) && !is_array($obj))) {
                return $this->sanitize($obj, $modifiers);
            }
        }
        return null;
    }

    private function setVar($name, $value) {
        $parts = explode('.', $name);
        $obj = &$this->vars;
        foreach ($parts as $i => $part) {
            if (is_array($obj)) {
                if (array_key_exists($part, $obj) && (is_object($obj[$part]) || is_array($obj[$part]))) {
                    $obj = &$obj[$part];
                } else {
                    if ($i == count($parts) - 1) {
                        $obj[$part] = $value;
                        break;
                    } else {
                        $obj[$part] = array();
                        $obj = &$obj[$part];
                    }
                }
            } elseif (is_object($obj)) {
                if (isset($obj->$part) && (is_object($obj->$part) || is_array($obj->$part))) {
                    $obj = &$obj->$part;
                } else {
                    if ($i == count($parts) - 1) {
                        $obj->$part = $value;
                        break;
                    } else {
                        $obj->$part = new StdClass();
                        $obj = &$obj->$part;
                    }
                }
            } else {
                throw new \Execption();
            }
        }
    }

}
