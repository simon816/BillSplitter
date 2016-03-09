<?php

namespace App;

class AuthController extends Controller {

    protected function authorizeRequest($request) {
        return \UserManager::getInstance()->isLoggedIn();
    }

}
