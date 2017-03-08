<?php

class MerchantUser {

    protected $id;
    protected $status;
    protected $namesurname;
    protected $email;
    protected $password;
    protected $type;

    function __construct($email, $password) {
        if ($email == "") {
            
        } else {
            $db = new DbConnection();
            $query = "select * from nola_merchantuser where status='A' and email='" . $email . "' and password='" . $password . "'";
            $db->query("SET NAMES UTF8");
            $rows = $db->select($query);
            if ($rows != null) {
                $this->setId($rows[0]["id"]);
                $this->setEmail($rows[0]["email"]);
                $this->setNameSurname($rows[0]["namesurname"]);
                $this->setPassword($rows[0]["password"]);
                $this->setStatus($rows[0]["status"]);
                $this->setType($rows[0]["type"]);
            } else {
                
            }
        }
    }

    function save() {
        $query = "INSERT INTO nola_merchantuser(status,namesurname,email,password,type)VALUES(";
        $query = $query . "'" . $this->status . "','" . $this->namesurname . "','" . $this->email . "','" . $this->password . "','" . $this->type . "')";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("kullanıcı oluşurken hata meydana geldi", 1);
        }
    }

    function getId() {
        return $this->id;
    }

    function getStatus() {
        return $this->status;
    }

    function getNameSurname() {
        return $this->namesurname;
    }

    function getEmail() {
        return $this->email;
    }

    function getPassword() {
        return $this->password;
    }

    function getType() {
        return $this->type;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setNameSurname($namesurname) {
        $this->namesurname = $namesurname;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setType($type) {
        $this->type = $type;
    }

}
