<?php include 'includes/_head.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();

$serviceConfig = parse_ini_file('serviceConfig.ini');

$service_url = $serviceConfig['serviceAddress'] . 'getCustomerOrderList/';

$curl = curl_init($service_url);
$userId = $_SESSION['UserId'];
$curl_post_data = array(
    "userId" => $userId
);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
//print_r($curl_post_data);
$curl_response = curl_exec($curl);
$rr = json_decode($curl_response, true);
?>
<body>
    <div id="wrapper" class="page-wrap">
        <?php include 'includes/_product-header.php'; ?>
        <div id="content" class="page-main">
            <div class="container">
                <?php
                if ($rr["ResponseCode"] != "70") {
                    for ($x = 0; $x <= count($rr["BasketItem"]) - 1; $x++) {
                        ?>
                        <div class="boutique-actions">
                            <div class="ratings">
                                <span class="rating">
                                    <span class="rating-rate"><?php echo $rr["BasketItem"][$x]["totalCount"]; ?></span>
                                    <span class="rating-max">piece</span>
                                </span>
                            </div>
                            <div class="options">
                                <?php if ($rr["BasketItem"][$x]["basketStatus"] == "P") { ?>
                                    <span class="rating-max"><?php echo $rr["BasketItem"][$x]["endDate"]; ?><br></span>
                                    <span class="rating-max">Order is processing<br></span>
                                    <a href="orderdetail.php?BasketId=<?php echo $rr["BasketItem"][$x]["id"]; ?>" class="secondary-action">Detail</a>                                <?php } else if ($rr["BasketItem"][$x]["basketStatus"] == "A") { ?>
                                    <span class="rating-max">Order has not sent yet<br>Please submit your basket</span>
                                    <a href="basket.php" class="secondary-action">Go to basket</a>
                                <?php } else if ($rr["BasketItem"][$x]["basketStatus"] == "C") { ?>
                                    <span class="rating-max"><?php echo $rr["BasketItem"][$x]["endDate"]; ?><br></span>
                                    <span class="rating-max">Order completed<br></span>
                                    <a href="orderdetail.php?BasketId=<?php echo $rr["BasketItem"][$x]["id"]; ?>" class="secondary-action">Detail</a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>

                <?php
            }
            ?>
        </div>
    </div>
    <div id="LoadingImage"
         style=" display: none; position: fixed; top: 80%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);background-color: #FFFFFF">
        <center>Sepete Ekleniyor...</center><img src="assets/images/ajax-loader.gif" /><center>Lütfen Bekleyiniz...</center>
    </div>

</body>
<script>
    function removeAll(id) {
        $.ajax({
            type: 'GET',
            url: "http://order.com/apiservices/removeItemFromBasket"
                    + "&basketItemId=" + id,
            dataType: "json", // data type of response
            async: false,
            beforeSend: function () {
                $("#LoadingImage").show();
            },
            complete: function () {
                $("#LoadingImage").hide();
            },
            success: function (data) {
                if (data.ResponseCode == "0") {
                    location.reload();
                } else {
                    alert(data.ResponseDesc);
                }
            }
        });
    }
    function completeOrder(id) {
        $.ajax({
            type: 'GET',
            url: "http://order.com/apiservices/completeOrderByBasketId"
                    + "&basketId=" + id,
            dataType: "json", // data type of response
            async: false,
            beforeSend: function () {
                $("#LoadingImage").show();
            },
            complete: function () {
                $("#LoadingImage").hide();
            },
            success: function (data) {
                if (data.ResponseCode == "0") {
                    $.ajax({
                        url: "setusercookie.php?BasketId=0",
                        async: false,
                        success: function () {
                            alert("Order has been sent!");
                        }
                    });

                } else {
                    alert(data.ResponseDesc);
                }
            }
        });
    }
</script>
</html>