<?php include 'includes/_head.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();

$serviceConfig = parse_ini_file('serviceConfig.ini');

$service_url = $serviceConfig['serviceAddress'] . 'getProductsByBasketId/';

$curl = curl_init($service_url);
$basketId = $_GET['BasketId'];
$curl_post_data = array(
    "basketId" => $basketId
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
        <?php include 'includes/_productlist-header.php'; ?>
        <div id="content" class="page-main">
            <div class="container">
                <?php
                if ($rr["ResponseCode"] != "70") {
                    for ($x = 0; $x <= count($rr["BasketItem"]) - 1; $x++) {
                        ?>
                        <div class="boutique-actions">
                            <a href="./product.php?id=<?php echo $rr["BasketItem"][$x]["pid"]; ?>" class="new-addition">
                                <img src="./products/<?php echo $rr["BasketItem"][$x]["pictureUrl"]; ?>" class="boutique-logo" alt="Boutique Velo" />
                            </a>
                            <div class="ratings">
                                <span class="rating">
                                    <span class="rating-rate"><?php echo $rr["BasketItem"][$x]["quantity"]; ?></span>
                                    <span class="rating-max">piece</span>
                                </span>

                                
                            </div>
                            <div class="options">
                                <span class="rating-max"><?php echo $rr["BasketItem"][$x]["pmname"]; ?><br></span>
                                <span class="rating-max"><?php echo $rr["BasketItem"][$x]["wtname"]; ?><br></span>
                                <span class="rating-max"><?php echo $rr["BasketItem"][$x]["cname"]; ?></span>
                            </div>
                        </div>
                        <?php
                        echo $rr["BasketItem"][$x]["pname"];
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
                            location.href = './products.php';
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