<?php

class BasketItem {

    protected $id;
    protected $status;
    protected $basketId;
    protected $quantity;
    protected $productId;
    protected $woodtypeId;
    protected $colorId;
    protected $phoneModelId;
    protected $addingDate;
    protected $removeDate;

    function __construct($id) {
        if ($id == 0) {
            
        } else {
            $db = new DbConnection();
            $query = "select * from nola_basketitem where id='" . $id . "'";
            $rows = $db->select($query);
            if ($rows != null) {
                $this->setId($rows[0]["id"]);
                $this->setStatus($rows[0]["status"]);
                $this->setBasketId($rows[0]["basketId"]);
                $this->setQuantity($rows[0]["quantity"]);
                $this->setProductId($rows[0]["productId"]);
                $this->setWoodTypeId($rows[0]["woodtypeId"]);
                $this->setColorId($rows[0]["colorId"]);
                $this->setPhoneModelId($rows[0]["phoneModelId"]);
                $this->setAddingDate($rows[0]["addingDate"]);
                $this->setRemoveDate($rows[0]["removeDate"]);
            } else {
                $this->setStatus("N");
            }
        }
    }

    function save() {
        $query = "INSERT INTO nola_basketitem(id,status,basketId,quantity,productId,woodtypeId,colorId,phoneModelId,addingDate,removeDate)VALUES (";
        $query = $query . "'" . $this->id . "','" . $this->status . "','" . $this->basketId . "'," . $this->quantity . ",'" . $this->productId . "'," . $this->woodtypeId . "," . $this->colorId;
        $query = $query . ",'" . $this->phoneModelId . "','" . $this->addingDate . "','')";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("sepete eklenirken hata meydana geldi", 1);
        }
    }

    function updateQuantity() {
        $query = "update nola_basketitem set quantity=" . $this->getQuantity() . " where id = '" . $this->getId() . "'";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("Sepet kaydı güncellenirken hata meydana geldi", 1);
        }
    }

    function updateStatus() {
        $query = "update nola_basketitem set status='P' where id = '" . $this->getId() . "'";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("Sepet kaydı güncellenirken hata meydana geldi", 1);
        }
    }

    function getId() {
        return $this->id;
    }

    function getStatus() {
        return $this->status;
    }

    function getBasketId() {
        return $this->basketId;
    }

    function getQuantity() {
        return $this->quantity;
    }

    function getProductId() {
        return $this->productId;
    }

    function getWoodTypeId() {
        return $this->woodtypeId;
    }

    function getColorId() {
        return $this->colorId;
    }

    function getPhoneModelId() {
        return $this->phoneModelId;
    }

    function getAddingDate() {
        return $this->addingDate;
    }

    function getRemoveDate() {
        return $this->removeDate;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setBasketId($basketId) {
        $this->basketId = $basketId;
    }

    function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    function setProductId($productId) {
        $this->productId = $productId;
    }

    function setWoodTypeId($woodtypeId) {
        $this->woodtypeId = $woodtypeId;
    }

    function setColorId($colorId) {
        $this->colorId = $colorId;
    }

    function setPhoneModelId($phoneModelId) {
        $this->phoneModelId = $phoneModelId;
    }

    function setAddingDate($addingDate) {
        $this->addingDate = $addingDate;
    }

    function setRemoveDate($removeDate) {
        $this->removeDate = $removeDate;
    }

}

?>
