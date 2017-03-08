<?php include 'includes/_head.php';
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();
$serviceConfig = parse_ini_file('serviceConfig.ini');
$service_url = $serviceConfig['serviceAddress'] . 'getProductsInBasketByBasketId/';

$curl = curl_init($service_url);
$basketId = $_SESSION['BasketId'];
$curl_post_data = array(
    "basketId" => $basketId
);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
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
                            <a href="./product.php?id=<?php echo $rr["BasketItem"][$x]["pid"]; ?>" class="new-addition">
                                <img src="./products/<?php echo $rr["BasketItem"][$x]["pictureUrl"]; ?>" class="boutique-logo" alt="Boutique Velo" />
                            </a>
                            <div class="ratings">
                                <span class="rating">
                                    <span class="rating-rate"><?php echo $rr["BasketItem"][$x]["quantity"]; ?></span>
                                    <span class="rating-max">piece</span>
                                </span>

                                <a href="#" class="secondary-action" onclick="removeAll('<?php echo $rr["BasketItem"][$x]["biid"]; ?>', '<?php echo $_SESSION["BasketId"]; ?>')">Remove All</a>
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
            <div style="display: block; width: 90%; margin: 10px auto;">
                <span>Leave a message?</span>
                <textarea style="width: 100%; max-width: 500px; resize: vertical; max-height: 300px;" id="ordermessage" name="ordermessage"></textarea>
            </div>
                <div class="buttons">
                    <button id="submitButton" class="primary-action" onclick="first('<?php echo $basketId; ?>')">
                        <span class="ui-button-text">SUBMIT</span>

                    </button>
                </li>
                <?php
            }
            ?>
        </div>
    </div>
    <div id="LoadingImage"
         style=" display: none; position: fixed; top: 80%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);background-color: #FFFFFF">
        <center>Processing...</center><img src="assets/images/ajax-loader.gif" /><center>Please Wait...</center>
    </div>

</body>
<script>
    function removeAll(id, basketId) {
        $.ajax({
            type: 'GET',
            url: "<?php echo $serviceConfig['serviceAddress']; ?>removeItemFromBasket"
                    + "&basketItemId=" + id + "&basketId=" + basketId,
            dataType: "json", // data type of response
            async: false,
            beforeSend: function () {
                console.log("<?php echo $serviceConfig['serviceAddress']; ?>removeItemFromBasket"
                        + "&basketItemId=" + id + "&basketId=" + basketId);
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

    function first(id) {
        $("#LoadingImage").show();
        $("#submitButton span").text("Please Wait");
        document.getElementById("submitButton").disabled = true;
        completeOrder(id);
    }
    function completeOrder(id) {
        $.ajax({
            type: 'GET',
            url: "<?php echo $serviceConfig['serviceAddress']; ?>completeOrderByBasketId?"
                    + "basketId=" + id+"&ordermessage="+$('#ordermessage').val(),
            dataType: "json", // data type of response
            async: true,
            beforeSend: function () {
                console.log("<?php echo $serviceConfig['serviceAddress']; ?>completeOrderByBasketId?basketId=" + id);
            },
            complete: function () {
                //$("#LoadingImage").hide();
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