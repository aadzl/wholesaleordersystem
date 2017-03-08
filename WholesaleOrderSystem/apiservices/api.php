<?php

ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');
ob_start();
header('Access-Control-Allow-Origin: *');
require_once("Rest.inc.php");
include 'DbConnection.php';
include ('MerchantUser.php');
include ('./BasketItem.php');
include ('./Basket.php');
include ('../mailer/class.phpmailer.php');
include ('../mailer/class.smtp.php');

class API extends REST {

    public $data = "";

    const DB_SERVER = "localhost";

    const DB_USER = "wholesaledbuser";
    const DB_PASSWORD = "wholesaledbpassword";
    const DB = "wholesaledb";

    private $db = NULL;

    public function __construct() {
        parent::__construct();    // Init parent contructor
        $this->dbConnect();     // Initiate Database connection
    }

    private function dbConnect() {
        $this->db = mysql_connect(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD);
        if ($this->db) {
            mysql_select_db(self::DB, $this->db);
        }
    }

    public function processApi() {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404);    // If the method not exist with in this class, response would be "Page not found".
    }

    private function createAccountForMerchant() {
        $email = $this->_request['email'];
        $namesurname = $this->_request['namesurname'];
        $password = $this->_request['password'];

        $merchant = new MerchantUser("", "");
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select * from nola_merchantuser where email='" . $email . "'", $this->db)or ( die(mysql_error()));

        if (mysql_num_rows($sql) > 0) {
            $result = mysql_fetch_array($sql, MYSQL_ASSOC);
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Email address is already in use"
            );
            $this->response($this->json($responseArray), 200);
        }

        $merchant->setEmail($email);
        $merchant->setId($this->getGUID());
        $merchant->setNameSurname($namesurname);
        $merchant->setPassword($password);
        $merchant->setStatus("P");
        $merchant->setType("S");
        $merchant->save();
        $responseArray = array(
            "ResponseCode" => 0,
            "ResponseDesc" => "Your account is created and it will be in use after approved by Nola Case."
        );
        $this->response($this->json($responseArray), 200);
    }

    private function login() {
        $email = $this->_request['username'];
        $password = $this->_request['password'];
        $backOfficeUser = new MerchantUser($email, $password);
        $responseArray = array();
        $basketId = 0;
        if ($backOfficeUser->getPassword() == $password) {
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query("SELECT * FROM nola_basket where basketStatus='A' and merchantId='" . $backOfficeUser->getId() . "' ", $this->db)or ( die(mysql_error()));

            if (mysql_num_rows($sql) > 0) {
                $result = mysql_fetch_array($sql, MYSQL_ASSOC);
                $basketId = $result["id"];
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Onaylandı",
                "UserId" => $backOfficeUser->getId(),
                "NameSurname" => $backOfficeUser->getNameSurname(),
                "Email" => $backOfficeUser->getEmail(),
                "BasketId" => $basketId,
                "UserType" => $backOfficeUser->getType()
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "User is not approved or active status"
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function sendpassword() {
        $email = $this->_request['username'];
        $responseArray = array();

        $sql2 = mysql_query("select * from nola_merchantuser where email='" . $email . "'", $this->db)or ( die(mysql_error()));
        $result2 = mysql_fetch_array($sql2, MYSQL_ASSOC);
        $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem"
            );
        
        
        $article = '<PRE>Password reset<br> Your password is :'.$result2["password"]."<br><br>";
            $this->sendPasswordToCustomer($article . "</PRE>",$result2["email"],$result2["namesurname"]);
        
        $this->response($this->json($responseArray), 200);
    }

    private function loginAdmin() {
        $email = $this->_request['username'];
        $password = $this->_request['password'];
        $backOfficeUser = new MerchantUser($email, $password);
        $responseArray = array();
        $basketId = 0;
        if ($backOfficeUser->getPassword() == $password && $backOfficeUser->getType() == 'A') {
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Onaylandı",
                "UserId" => $backOfficeUser->getId(),
                "NameSurname" => $backOfficeUser->getNameSurname(),
                "Email" => $backOfficeUser->getEmail()
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "User is not approved or active status"
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function changePassword() {
        $email = $this->_request['email'];
        $password = $this->_request['password'];

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("update nola_merchantuser set password='" . $password . "' where email='" . $email . "' ", $this->db)or ( die(mysql_error()));

        if ($sql) {
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function changeUserType() {
        $id = $this->_request['id'];
        $type = $this->_request['type'];

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("update nola_merchantuser set type='" . $type . "' where id='" . $id . "' ", $this->db)or ( die(mysql_error()));

        if ($sql) {
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

    private function getGUID() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            return strtoupper(md5(uniqid(rand(), true)));
        }
    }

    private function getGUIDforOrder() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = //chr(123)// "{"
                    //substr($charid, 0, 8)
                    //. substr($charid, 8, 4)
                    //. substr($charid, 12, 4)
                    //. substr($charid, 16, 4)
                    substr($charid, 20, 12);
            //. chr(125); // "}"
            return $uuid;
        }
    }

    private function getApproveCode() {
        $digits = 4;
        $uuid = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        return $uuid;
    }

    private function checkServices() {
        $fail = array(
            'ResponseCode' => 963,
            'ResponseDesc' => "Servisler ayakta"
        );
        $this->response($this->json($fail), 200);
    }

    private function getAllProducts() {
        $userType = $this->_request['UserType'];
        mysql_query("SET NAMES UTF8");
        if ($userType == "V") {
            $sql = mysql_query("SELECT * FROM nola_product where status='A' order by processDate desc", $this->db)or ( die(mysql_error()));
        }
        if ($userType == "S") {
            $sql = mysql_query("SELECT * FROM nola_product where status='A' and isvip='N' order by processDate desc", $this->db)or ( die(mysql_error()));
        }
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "Product" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getAllProductsAdmin() {
        $orderBy=$this->_request['orderBy'];
        $orderStr="";
        if($orderBy==""){
            $orderStr=" order by processDate desc ";
        }
        if($orderBy=="pname"){
            $orderStr=" order by name asc";
        }
        
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT * FROM nola_product ".$orderStr, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "Product" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getAllColors() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT * FROM nola_color", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "Color" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getAllPhoneModels() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT * FROM nola_phonemodel", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "PhoneModel" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function removeItemFromBasket() {
        $basketItemId = $this->_request['basketItemId'];
        $basketId = $this->_request['basketId'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("delete from nola_basketitem where id='" . $basketItemId . "'", $this->db)or ( die(mysql_error()));
        $sql = mysql_query("Select * from nola_basketitem where basketId = '" . $basketId . "'", $this->db)or ( die(mysql_error()));
        if (mysql_num_rows($sql) > 0) {
            $sql = mysql_query("update nola_basket set totalCount = (Select sum(quantity) from nola_basketitem where basketId = '" . $basketId . "') where id = '" . $basketId . "'", $this->db)or ( die(mysql_error()));
        } else {
            $sql = mysql_query("update nola_basket set totalCount = 0 where id = '" . $basketId . "'", $this->db)or ( die(mysql_error()));
        }
        $responseArray = array(
            "ResponseCode" => 0,
            "ResponseDesc" => "Başarılı işlem"
        );
        $this->response($this->json($responseArray), 200);
    }

    private function getCustomerOrderList() {
        $userId = $this->_request['userId'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select * from nola_basket where merchantId=" . $userId . " order by endDate asc", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "No record"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "BasketItem" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getProductsInBasketByBasketId() {
        $basketId = $this->_request['basketId'];
        mysql_query("SET NAMES UTF8");
        //$sql = mysql_query("select b.id biid,p.id pid,p.name pname,p.pictureUrl,b.quantity,pm.name pmname,c.name cname,wt.name wtname from nola_basketitem b,nola_product p,nola_wood_type wt,nola_color c,nola_phonemodel pm where b.basketId='" . $basketId . "' and b.productId=p.id and b.woodtypeId=wt.id and b.colorId=c.id and b.phoneModelId=pm.id", $this->db)or ( die(mysql_error()));
        $sql = mysql_query("SELECT bi.id biid,p.id pid,p.name pname,p.pictureUrl,bi.quantity,pm.name pmname,c.name cname,wt.name wtname FROM nola_basketitem bi,nola_product p,nola_color c,nola_wood_type wt,nola_product_phone_model ppm,nola_phonemodel pm where bi.productId=p.id and bi.colorId=c.id and bi.woodtypeId=wt.id and bi.phoneModelId=ppm.id and ppm.phonemodel_id=pm.id and bi.basketId='" . $basketId . "' order by bi.addingDate", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "BasketItem" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getProductsByBasketId() {
        $basketId = $this->_request['basketId'];
        $orderBy=$this->_request['orderBy'];
        $orderStr="";
        if($orderBy==""){
            $orderStr="";
        }
        if($orderBy=="pname"){
            $orderStr=" order by pname asc";
        }
        if($orderBy=="pmodel"){
            $orderStr=" order by pmname asc";
        }
        if($orderBy=="color"){
            $orderStr=" order by cname asc";
        }
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT p.nolaid,bi.id biid,p.id pid,p.name pname,p.pictureUrl,bi.quantity,pm.name pmname,c.name cname,wt.name wtname FROM nola_basketitem bi,nola_product p,nola_color c,nola_wood_type wt,nola_product_phone_model ppm,nola_phonemodel pm where bi.productId=p.id and bi.colorId=c.id and bi.woodtypeId=wt.id and bi.phoneModelId=ppm.id and ppm.phonemodel_id=pm.id and bi.basketId='" . $basketId . "' ".$orderStr, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $sql2 = mysql_query("select * from nola_basket where id='" . $basketId . "'", $this->db)or ( die(mysql_error()));
            $result2 = mysql_fetch_array($sql2, MYSQL_ASSOC);
            $orderDate = $result2["endDate"];
            $sql2 = mysql_query("select * from nola_merchantuser where id='" . $result2["merchantId"] . "'", $this->db)or ( die(mysql_error()));
            $result2 = mysql_fetch_array($sql2, MYSQL_ASSOC);
            $merchant = $result2["namesurname"];
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "BasketItem" => $result,
                "Merchant" => $merchant,
                "OrderDate" => $orderDate
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getWoodTypeByProductId() {
        $productId = $this->_request['productId'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select wt.id,wt.name from nola_wood_type wt, nola_product_wood_type pwt,nola_product p where pwt.product_id=p.id and wt.id=pwt.wood_type_id and p.id='" . $productId . "'", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "WoodTypes" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getColorsByPhoneModelId() {
        $Id = $this->_request['Id'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select c.id,c.name cName from nola_product_phone_model ppm,nola_phonemodel pm,nola_product_phone_model_color pmc,nola_color c where ppm.id='" . $Id . "' and ppm.phonemodel_id=pm.id and pmc.color_id=c.id and pmc.product_phone_model_id=ppm.id", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "Colors" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getColorsByProdId() {
        $pid = $this->_request['Id'];
        mysql_query("SET NAMES UTF8");
        //SELECT pm.name, p.name, c.name

//FROM nola_product_phone_model_color ppmc

//INNER JOIN nola_color c ON c.id = ppmc.color_id

//INNER JOIN nola_product_phone_model ppm ON ppm.id = ppmc.product_phone_model_id

//INNER JOIN nola_phonemodel pm ON pm.id = ppm.phonemodel_id

//INNER JOIN nola_product p ON p.id = ppm.product_id

//WHERE p.id =  'E82B2D6006F3CFD70217529B076990FE'
$query = "SELECT pm.name as pmname,pm.id as pmid, c.name as cname,c.id as cid,ppm.id as ppmid FROM nola_product_phone_model_color ppmc

INNER JOIN nola_color c ON c.id = ppmc.color_id

INNER JOIN nola_product_phone_model ppm ON ppm.id = ppmc.product_phone_model_id

INNER JOIN nola_phonemodel pm ON pm.id = ppm.phonemodel_id

INNER JOIN nola_product p ON p.id = ppm.product_id

WHERE p.id =  '".$pid."'";
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "PPMS" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getPhoneModelsByProductId() {
        $productId = $this->_request['productId'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select ppm.id,name from nola_product_phone_model ppm,nola_phonemodel pm where ppm.phonemodel_id=pm.id and ppm.product_id='" . $productId . "' order by pm.sortorder", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "PhoneModels" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getDateTime() {
        return date_create()->format('Y-m-d H:i:s');
    }

    private function addToCart() {
        try {
        $basketId = $this->_request['basketId'];
        $merchantId = $this->_request['merchantId'];
        $basket = new Basket($basketId);
        $basketItemId = 0;
        $currentQuantity = 0;
        $result = true;
        if ($basket->getId() == null) {
            $basketId = $this->getGUID();
            $basket->setId($basketId);
            $basket->setBasketStatus("A");
            $basket->setMerchantId($merchantId);
            $basket->setStartDate(date_create()->format('Y-m-d H:i:s'));
            $basket->setTotalCount(0);
            //$basket->save();
            $query = "INSERT INTO nola_basket(id,merchantId,basketStatus,startDate,totalCount)VALUES (";
            $query = $query . "'" . $basketId . "','" . $merchantId . "','A','" . date_create()->format('Y-m-d H:i:s') . "',0)";
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        }

        //$basketId = $this->_request['basketId'];
        $quantity = $this->_request['quantity'];
        $productId = $this->_request['productId'];
        $woodtypeId = $this->_request['woodtypeId'];
        $colorId = $this->_request['colorId'];
        $phoneModelId = $this->_request['phoneModelId'];
        $Id = $this->getGUID();

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select * from nola_basketitem where basketId='" . $basketId . "' and productId='" . $productId . "' and woodtypeId=" . $woodtypeId . " and colorId=" . $colorId . " and phoneModelId='" . $phoneModelId . "'", $this->db)or ( die(mysql_error()));

        if (mysql_num_rows($sql) > 0) {
            $result = mysql_fetch_array($sql, MYSQL_ASSOC);
            $basketItemId = $result["id"];
            $currentQuantity = $result["quantity"];
        }

        $basketItem = new BasketItem($basketItemId);
        if ($basketItemId == 0) {
            $basketItem->setAddingDate(date_create()->format('Y-m-d H:i:s'));
            $basketItem->setBasketId($basketId);
            $basketItem->setColorId($colorId);
            $basketItem->setId($Id);
            $basketItem->setPhoneModelId($phoneModelId);
            $basketItem->setProductId($productId);
            $basketItem->setQuantity($quantity);
            $basketItem->setRemoveDate("");
            $basketItem->setStatus("A");
            $basketItem->setWoodTypeId($woodtypeId);
            //$result = $basketItem->save();
            $query = "INSERT INTO nola_basketitem(id,status,basketId,quantity,productId,woodtypeId,colorId,phoneModelId,addingDate)VALUES (";
            $query = $query . "'" . $Id . "','A','" . $basketId . "'," . $quantity . ",'" . $productId . "'," . $woodtypeId . "," . $colorId;
            $query = $query . ",'" . $phoneModelId . "','" . date_create()->format('Y-m-d H:i:s') . "')";
            mysql_query("SET NAMES UTF8");
            $result = mysql_query($query, $this->db)or ( die(mysql_error()));
        } else {
            $basketItem->setQuantity($quantity + $currentQuantity);
            //$result = $basketItem->updateQuantity();
            $query = "update nola_basketitem set quantity=" . ($quantity + $currentQuantity) . " where id = '" . $basketItem->getId() . "'";
            mysql_query("SET NAMES UTF8");
            $result = mysql_query($query, $this->db)or ( die(mysql_error()));
            $Id = $basketItem->getId();
        }
        if ($result) {
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query("update nola_basket set totalCount = (Select sum(quantity) from nola_basketitem where basketId = '" . $basketId . "') where id = '" . $basketId . "'", $this->db)or ( die(mysql_error()));
            $responseArray = array(
                'ResponseCode' => 85,
                'ResponseDesc' => "Added to cart",
                'CustomerId' => $Id,
                'BasketId' => $basket->getId());
        } else {
            $responseArray = array(
                'ResponseCode' => 30,
                'ResponseDesc' => "An error occured!",
                'CustomerId' => '');
        }
        $this->response($this->json($responseArray), 200);

        } 

        catch (Exception $e) {

            $responseArray = array(
                'ResponseCode' => 500,
                'ResponseDesc' => "An error occured!".$e->getMessage(),
                'CustomerId' => '');

        }
    }

    private function getProductInfoById() {
        $productId = $this->_request['productId'];
        if (!empty($productId)) {
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query("SELECT * FROM nola_product where id='" . $productId . "' ", $this->db)or ( die(mysql_error()));
            $responseArray = array(
                "ResponseCode" => 70,
                "ResponseDesc" => "Kayıt yok"
            );
            if (mysql_num_rows($sql) > 0) {
                $result = mysql_fetch_array($sql, MYSQL_ASSOC);
                $responseArray = array(
                    "ResponseCode" => 0,
                    "ResponseDesc" => "Kayıtlı aktif müşteri",
                    "id" => $result["id"],
                    "status" => $result["status"],
                    "isvip" => $result["isvip"],
                    "name" => $result["name"],
                    "description" => $result["description"],
                    "url" => $result["url"],
                    "pictureUrl" => $result["pictureUrl"],
                );
            }
        }
        $this->response($this->json($responseArray), 200);
    }

    private function completeOrderByBasketId() {
        $basketId = $this->_request['basketId'];
        $orderMessage = $this->_request['ordermessage'];

        $basket = new Basket($basketId);
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT bi.id biid,p.id pid,p.name pname,p.pictureUrl,bi.quantity,pm.name pmname,c.name cname,wt.name wtname FROM nola_basketitem bi,nola_product p,nola_color c,nola_wood_type wt,nola_product_phone_model ppm,nola_phonemodel pm where bi.productId=p.id and bi.colorId=c.id and bi.woodtypeId=wt.id and bi.phoneModelId=ppm.id and ppm.phonemodel_id=pm.id and bi.basketId='" . $basketId . "'", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {

            $basket->setOrderMessage($orderMessage);
            $basket->updateOrderMessage();

            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $article = '<PRE>Order Detail.<br> You can see order detail below :<br>-----------------------------------------------------------------<br>';
            foreach ($result as &$value) {
                //$basketItem = new BasketItem($value["biid"]);
                //$basketItem->updateStatus();
                $article = $article . "Product Name : <b>" . $value["pname"] . "</b><br>";
                $article = $article . "Quantity     : <b>" . $value["quantity"] . "</b><br>";
                $article = $article . "Wood Type    : <b>" . $value["wtname"] . "</b><br>";
                $article = $article . "Color        : <b>" . $value["cname"] . "</b><br>";
                $article = $article . "Phone Model  : <b>" . $value["pmname"] . "</b><br>";
                $article = $article . "--------------------------------------------------<br>";
            }
            $basket->setBasketStatus('P');
            $basket->updateStatus();
            $basket->updateBasketItemStatus();
            $this->sendEmail($article . "</PRE>");

            mysql_query("SET NAMES UTF8");
            $sql = mysql_query("SELECT * FROM nola_merchantuser where id=" . $basket->getMerchantId(), $this->db)or ( die(mysql_error()));

            $resultCustomer = mysql_fetch_array($sql, MYSQL_ASSOC);
            $customerEmail = $resultCustomer["email"];
            $customerName = $resultCustomer["namesurname"];

            $this->sendEmailToCustomer($article . "</PRE>", $customerEmail, $customerName);
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "BasketItem" => $article
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function sendEmail($article) {
        $query = "insert into nola_email(id,email_to,email_to_name,email_body,email_subject,email_status,create_date)values('" . $this->getGUID() . "','order@wholesale.com','Nola','".$article."','New Order Delivered','W','" . date_create()->format('Y-m-d H:i:s') . "')";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        return true;
    }

    private function sendEmailToCustomer($article, $customerEmail, $customerName) {
        $query = "insert into nola_email(id,email_to,email_to_name,email_body,email_subject,email_status,create_date)values('" . $this->getGUID() . "','".$customerEmail."','".$customerName."','".$article."','NolaCase Order Detail','W','" . date_create()->format('Y-m-d H:i:s') . "')";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        return true;
    }

    private function sendPasswordToCustomer($article, $customerEmail, $customerName) {

        $mail = new PHPMailer(); //nesneyi oluşturuyoruz
        $mail->SetLanguage("tr", "phpmailer/language");
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";
        $mail->IsHTML(true);
        $mail->IsSMTP(); //smtp kullanmak için
        $mail->Host = "127.0.0.1"; //mail sunucunuz
        $mail->Port = 587; //
        $mail->SMTPAuth = true; //onayı aktive ediyoruz
        $mail->Username = "order@order.com"; //kullanılacak eposta adresi
        $mail->Password = "order"; //şifre
        $mail->From = "from@from.com"; //formdan gelen mail adresi
        $mail->Fromname = "FROMNAME"; //formdan gelen isim
        $mail->AddAddress($customerEmail, $customerName);
        $mail->Subject = "Password Reminder"; //formdan gelen konu
        $mail->Body = $article; //formdan gelen mesaj

        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }
    
    private function addNewProduct() {
        $productName = $this->_request['productName'];
        $pictureName = $this->_request['pictureName'];
		
		$getQuery=mysql_query("SELECT max(nolaid) nolaid FROM nola_product", $this->db)or ( die(mysql_error()));
        $result = mysql_fetch_array($getQuery, MYSQL_ASSOC);
        $nolaid = $result["nolaid"]+1;
		
        $Id = $this->getGUID();
        $query = "insert into nola_product(id,status,name,description,url,pictureUrl,processDate,nolaid)values('" . $Id . "','A','" . $productName . "','','','" . $pictureName . "','" . date_create()->format('Y-m-d H:i:s') . "',".$nolaid.")";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "Added new product",
            'ProductId' => $Id);
        $this->response($this->json($responseArray), 200);
    }

    private function updateProductStatus() {
        $productId = $this->_request['productId'];
        $status = $this->_request['status'];
        $query = "update nola_product set status='" . $status . "' where id='" . $productId . "'";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "product updated",
            'ProductId' => $productId);
        $this->response($this->json($responseArray), 200);
    }

    private function updateVipStatus() {
        $productId = $this->_request['productId'];
        $status = $this->_request['status'];
        $query = "update nola_product set isvip='" . $status . "' where id='" . $productId . "'";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "product updated",
            'ProductId' => $productId);
        $this->response($this->json($responseArray), 200);
    }

    private function updateProduct() {
        $productId = $this->_request['productId'];
        $productName = $this->_request['productName'];
        $pictureName = $this->_request['pictureName'];
        $Id = $this->getGUID();
        $query = "update nola_product set name='" . $productName . "',pictureUrl='" . $pictureName . "' where id='" . $productId . "'";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "updated",
            'ProductId' => $Id);
        $this->response($this->json($responseArray), 200);
    }

    private function deleteProductPhoneModolColor() {
        $colorId = $this->_request['colorId'];
        $phoneModelId = $this->_request['phoneModelId'];
        $productId = $this->_request['productId'];
        $query = "delete FROM nola_product_phone_model_color where product_phone_model_id=(SELECT id FROM nola_product_phone_model WHERE product_id LIKE '" . $productId . "' AND phonemodel_id =" . $phoneModelId . ") and color_id=" . $colorId;
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "deleted");
        $this->response($this->json($responseArray), 200);
    }

    private function addNewProductModel() {
        $productId = $this->_request['productId'];
        $phoneModelId = $this->_request['phoneModelId'];
        $colorId = $this->_request['colorId'];

        $sql = mysql_query("SELECT * FROM nola_product_phone_model where product_id='" . $productId . "' and phonemodel_id='" . $phoneModelId . "'", $this->db)or ( die(mysql_error()));

        if (mysql_num_rows($sql) > 0) {
            $result = mysql_fetch_array($sql, MYSQL_ASSOC);
            $Id = $result["id"];

            $sql = mysql_query("SELECT * FROM nola_product_phone_model_color where product_phone_model_id='" . $Id . "' and color_id=" . $colorId, $this->db)or ( die(mysql_error()));
            if (mysql_num_rows($sql) > 0) {
                $responseArray = array(
                    'ResponseCode' => 0,
                    'ResponseDesc' => "Already added...",
                    'ProductId' => $Id);
                $this->response($this->json($responseArray), 200);
            }
        } else {
            $Id = $this->getGUID();
            $query = "insert into nola_product_phone_model(id,product_id,phonemodel_id)values('" . $Id . "','" . $productId . "'," . $phoneModelId . ")";
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query($query, $this->db)or ( die(mysql_error()));
        }
        $query = "insert into nola_product_phone_model_color(product_phone_model_id,color_id)values('" . $Id . "'," . $colorId . ")";
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query($query, $this->db)or ( die(mysql_error()));

        $responseArray = array(
            'ResponseCode' => 0,
            'ResponseDesc' => "Added new product model",
            'ProductId' => $Id);
        $this->response($this->json($responseArray), 200);
    }

    private function getPhoneColorByProductId() {
        $productId = $this->_request['productId'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select pmc.id,pm.name pName,c.name cName from nola_product_phone_model ppm,nola_phonemodel pm,nola_product_phone_model_color pmc,nola_color c where ppm.product_id='" . $productId . "' and ppm.phonemodel_id=pm.id and pmc.color_id=c.id and pmc.product_phone_model_id=ppm.id", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 51,
                "ResponseDesc" => "Başarılı işlem",
                "PhoneColors" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getWaitingOrders() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT b.id,m.namesurname,b.endDate,b.totalCount FROM nola_basket b, nola_merchantuser m where b.merchantId=m.id and b.basketStatus='P' order by b.endDate asc", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "Orders" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function getCompletedOrders() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT b.id,m.namesurname,b.endDate,b.totalCount FROM nola_basket b, nola_merchantuser m where b.merchantId=m.id and b.basketStatus='C' order by b.endDate desc", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "Orders" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function approveOrder() {
        $basketId = $this->_request['basketId'];

        $basket = new Basket($basketId);

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("update nola_basket set basketStatus='C' where id='" . $basketId . "' ", $this->db)or ( die(mysql_error()));
        $sql = mysql_query("update nola_basketitem set status='C' where basketId='" . $basketId . "' ", $this->db)or ( die(mysql_error()));
        if ($sql) {

            mysql_query("SET NAMES UTF8");
            $sql = mysql_query("SELECT * FROM nola_merchantuser where id=" . $basket->getMerchantId(), $this->db)or ( die(mysql_error()));

            $resultCustomer = mysql_fetch_array($sql, MYSQL_ASSOC);
            $customerEmail = $resultCustomer["email"];
            $customerName = $resultCustomer["namesurname"];

            $article = '<PRE>Your order is approved by NolaCase<br>-----------------------------------------------------------------<br>';
            $article = $article . "--------------------------------------------------<br>";
            $article = $article . "Order Id : " . $basketId . "<br>";
            $this->sendEmailToCustomer($article . "</PRE>", $customerEmail, $customerName);

            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function deleteOrder() {
        $basketId = $this->_request['basketId'];

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("delete from nola_basket where id='" . $basketId . "' ", $this->db)or ( die(mysql_error()));
        $sql = mysql_query("delete from nola_basketitem where basketId='" . $basketId . "' ", $this->db)or ( die(mysql_error()));
        if ($sql) {
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function getWaitingMerchants() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT * FROM nola_merchantuser where status='P' and type!='A'", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "Merchants" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

    private function approveMerchant() {
        $Id = $this->_request['id'];

        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("update nola_merchantuser set status='A' where id=" . $Id, $this->db)or ( die(mysql_error()));

        if ($sql) {

            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function deleteMerchant() {
        $Id = $this->_request['id'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("delete from nola_merchantuser where id=" . $Id, $this->db)or ( die(mysql_error()));
        if ($sql) {
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Succes"
            );
        } else {
            $responseArray = array(
                "ResponseCode" => 99,
                "ResponseDesc" => "An error occured"
            );
        }

        $this->response($this->json($responseArray), 200);
    }

    private function getActiveMerchants() {
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("SELECT * FROM nola_merchantuser where status='A' and type!='A'", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "Merchants" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }
    
    private function getGroupOrders() {
        $basketIds = $this->_request['basketIds'];
        mysql_query("SET NAMES UTF8");
        $sql = mysql_query("select np.name productname,wt.name woodtypename,nc.name colorname,pm.name phonemodelname,count(1) orderCount,sum(bi.quantity) totalCount from nola_basketitem bi,nola_product_phone_model ppm,nola_product np,nola_wood_type wt,nola_color nc,nola_phonemodel pm where bi.status='P' and bi.phoneModelId=ppm.id and ppm.product_id=np.id and bi.woodtypeId=wt.id and bi.colorId=nc.id and ppm.phonemodel_id=pm.id and bi.basketId in(".$basketIds.") group by productId,woodtypeId,colorId,phoneModelId order by productname asc;", $this->db)or ( die(mysql_error()));
        $responseArray = array(
            "ResponseCode" => 70,
            "ResponseDesc" => "Kayıt yok"
        );
        if (mysql_num_rows($sql) > 0) {
            $result = array();
            while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                $result[] = $rlt;
            }
            $responseArray = array(
                "ResponseCode" => 0,
                "ResponseDesc" => "Başarılı işlem",
                "Orders" => $result
            );
        }
        $this->response($this->json($responseArray), 200);
    }

}

$api = new API;
$api->processApi();
?>