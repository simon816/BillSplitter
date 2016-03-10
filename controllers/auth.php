<?php

use App\AuthManager;

class AuthController extends App\Controller {

    public function __construct() {
        parent::__construct();
    }

    protected function handleAction($action, $args) {
        if ($args !== '') {
            return false;
        }
        switch ($action) {
            case 'login':
                $this->handleLogin();
                return true;
            case 'register':
                $this->handleRegister();
                return true;
            case 'logout':
                $this->handleLogout();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleLogin() {
        if (AuthManager::isLoggedIn()) {
            $this->redirect('/');
        }
        $session = App\SessionManager::getInstance();

        if ($this->isGet()) {
            $form = array('email' => $session->get('login.email'));
            $this->output($this->renderTemplate('auth/login', array('error' => $session->get('login.error'), 'form' => $form)));
            $session->remove('login.email');
            $session->remove('login.error');
            return;
        }

        $email = $this->validatePost('email');
        $password = $this->validatePost('password', 6);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $session->get('login.error', array());
            $error['email'] = 'Invalid email address';
            $session->put('login.error', $error);
            $session->put('login.email', $email);
            $this->redirect('/auth/login', 303);
        }

        $resp = AuthManager::login($email, $password);
        if ($resp['success']) {
            $this->redirect('/', 303);
        }
        $session->put('login.error', $resp['error']);
        $session->put('login.email', $email);
        $this->redirect('/auth/login', 303);
    }

    private function handleRegister() {
        if (AuthManager::isLoggedIn()) {
            $this->redirect('/');
        }
        $session = App\SessionManager::getInstance();

        if ($this->isGet()) {
            $form = array('email' => $session->get('reg.email'), 'name' => $session->get('reg.name'));
            $this->output($this->renderTemplate('auth/register', array('error' => $session->get('reg.error'), 'form' => $form)));
            $session->remove('reg.email');
            $session->remove('reg.name');
            $session->remove('reg.error');
            return;
        }

        $name = $this->validatePost('name', 2);
        $email = $this->validatePost('email');
        $password = $this->validatePost('password', 6);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $session->get('reg.error', array());
            $error['email'] = 'Invalid email address';
            $session->put('reg.error', $error);
            $session->put('reg.email', $email);
            $session->put('reg.name', $name);
            $this->redirect('/auth/register', 303);
        }

        $resp = AuthManager::register($name, $email, $password);
        if ($resp['success']) {
            $this->redirect('/', 303);
        }
        $session->put('reg.email', $email);
        $session->put('reg.name', $name);
        $session->put('reg.error', $resp['error']);
        $this->redirect('/auth/register', 303);
    }

    private function handleLogout() {
        AuthManager::logout();
        $this->redirect('/');
    }

}
