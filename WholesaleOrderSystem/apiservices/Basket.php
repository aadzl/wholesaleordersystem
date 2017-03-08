<?php

class Basket {

    protected $id;
    protected $merchantId;
    protected $basketStatus;
    protected $orderMessage;
    protected $startDate;
    protected $endDate;
    protected $totalCount;

    function __construct($id) {
        if ($id == "0") {
            
        } else {
            $db = new DbConnection();
            $query = "select * from nola_basket where id='" . $id . "'";
            $rows = $db->select($query);
            if ($rows != null) {
                $this->setId($rows[0]["id"]);
                $this->setBasketStatus($rows[0]["basketStatus"]);
                $this->setOrderMessage($rows[0]["orderMessage"]);
                $this->setStartDate($rows[0]["startDate"]);
                $this->setEndDate($rows[0]["endDate"]);
                $this->setTotalCount($rows[0]["totalCount"]);
                $this->setMerchantId($rows[0]["merchantId"]);
            } else {
                $this->setBasketStatus("N");
            }
        }
    }

    function save() {
        $query = "INSERT INTO nola_basket(id,merchantId,basketStatus,startDate,endDate,totalCount)VALUES (";
        $query = $query . "'" . $this->id . "','" . $this->merchantId . "','" . $this->basketStatus . "','" . $this->startDate . "','" . $this->endDate . "'," . $this->totalCount . ")";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("sepete olusturulurken hata meydana geldi", 1);
        }
    }

    function updateStatus() {
        $query = "update nola_basket set basketStatus='" . $this->getBasketStatus() . "',endDate='" . date_create()->format('Y-m-d H:i:s') . "' where id='" . $this->getId() . "'";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("sepete statusu guncellenirken hata meydana geldi", 1);
        }
    }

    function updateOrderMessage(){
        $query = "update nola_basket set orderMessage='" . $this->getOrderMessage() . "' where id='" . $this->getId() . "'";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("Order Message eklenirken hata meydana geldi", 1);
        }
    }

    function updateBasketItemStatus() {
        $query = "update nola_basketitem set status='" . $this->getBasketStatus() . "' where basketId='" . $this->getId() . "'";
        $db = new DbConnection();
        $db->query("SET NAMES UTF8");
        if ($db->query($query)) {
            return true;
        } else {
            throw new Exception("sepete urun statusu guncellenirken hata meydana geldi", 1);
        }
    }

    function getId() {
        return $this->id;
    }

    function getMerchantId() {
        return $this->merchantId;
    }

    function getBasketStatus() {
        return $this->basketStatus;
    }

    function getOrderMessage() {
        return $this->orderMessage;
    }

    function getStartDate() {
        return $this->startDate;
    }

    function getEndDate() {
        return $this->endDate;
    }

    function getTotalCount() {
        return $this->totalCount;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
    }

    function setBasketStatus($basketStatus) {
        $this->basketStatus = $basketStatus;
    }

    function setOrderMessage($orderMessage) {
        $this->orderMessage = $orderMessage;
    }

    function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    function setTotalCount($totalCount) {
        $this->totalCount = $totalCount;
    }

}

?>
