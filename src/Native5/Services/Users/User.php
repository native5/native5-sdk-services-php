<?php
namespace Native5\Services\Users;

class User {
    private $_username;
    private $_password;
    private $_name;
    private $_aliases;
    private $_roles;

    public function __construct(\Native5\Services\Users\UserBuilder $builder = null) {
        $this->_username = $builder->getUsername();
        $this->_password = $builder->getPassword();
        $this->_name = $builder->getName();
        $this->_aliases = $builder->getAliases();
        $this->_roles = $builder->getRoles();
    }

    public static function createBuilder($username) {
        return new \Native5\Services\Users\UserBuilder($username);
    }

    public function getUsername() {
        return $this->_username;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function getName() {
        return $this->_name;
    }

    public function getAliases() {
        return $this->_aliases;
    }

    public function getRoles() {
        return $this->_roles;
    }
}

