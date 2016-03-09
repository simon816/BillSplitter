<?php

class HomeController extends App\Controller {

    public function __construct() {
        parent::__construct('home/index');
    }

    protected function handleDefaultGet() {
        if (App\AuthManager::isLoggedIn()) {
            $template = 'home/dashboard';
        } else {
            $template = 'home/welcome';
        }
        $this->output($this->renderTemplate($template));
    }

}
