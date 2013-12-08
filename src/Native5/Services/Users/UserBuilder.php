<?php
namespace Native5\Services\Users;

class UserBuilder {
    private $_username;
    private $_password;
    private $_name;
    private $_aliases;
    private $_roles;

    public function __construct($username) {
        $this->_username = $username;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setPassword($password) {
        $this->_password = $password;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    public function getAliases() {
        return $this->_aliases;
    }

    public function setAliases($aliases) {
        $this->_aliases = $aliases;
        return $this;
    }

    public function getRoles() {
        return $this->_roles;
    }

    public function setRoles($roles) {
        $this->_roles = $roles;
        return $this;
    }

    public function build() {
        return new \Native5\Services\Users\User($this);
    }
}

