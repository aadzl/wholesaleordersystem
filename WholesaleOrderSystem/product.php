			<?php include 'includes/_head.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();

$serviceConfig = parse_ini_file('serviceConfig.ini');

$service_url = $serviceConfig['serviceAddress'] . 'getProductInfoById/';
$curl = curl_init($service_url);
$productId = $_GET['id'];
$curl_post_data = array(
    "productId" => $productId
);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
//print_r($curl_post_data);
$curl_response = curl_exec($curl);
$rr = json_decode($curl_response, true);
if ($rr["ResponseCode"] == "0") {
    $id = $rr["id"];
    $name = $rr["name"];
    $status = $rr["status"];
    $description = $rr["description"];
    $url = $rr["url"];
    $pictureUrl = $rr["pictureUrl"];
}
?>
<body>
    <div id="wrapper" class="page-wrap">
        <?php include 'includes/_product-header.php'; ?>
        <div id="content" class="page-main">
            <div class="container">
                <div class="product-carousel">
                    <div class="swiper-container">
                        <div class="swiper-wrapper" style="">
                            <img src="./products/<?php echo $rr["pictureUrl"]; ?>" style="" >
                            <!--<div class="swiper-slide" style="background-image: url('./products/<?php echo $rr["pictureUrl"]; ?>');"></div>-->
                        </div>

                    </div>


                </div>
                <article class="details product-details" style="max-width: 500px;margin: 0 auto;    position: absolute;
    top: 165px;
    width: 100%; padding-bottom:170px !important; margin-bottom: 100px ">
                    <div>
                        <p><center><?php echo $name; ?></center></p>

                    </div>
                    <?php
                    /* $service_url = $serviceConfig['serviceAddress'] . 'getWoodTypeByProductId/';
                      $curl = curl_init($service_url);
                      $productId = $_GET['id'];
                      $curl_post_data = array(
                      "productId" => $productId
                      );
                      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($curl, CURLOPT_POST, true);
                      curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                      //print_r($curl_post_data);
                      $curl_response = curl_exec($curl);
                      $rr = json_decode($curl_response, true); */
                    if ($status == "A") {
                        ?>
                                <?php
                                $service_url = $serviceConfig['serviceAddress'] . 'getPhoneModelsByProductId/';
                                $curl = curl_init($service_url);
                                $productId = $_GET['id'];
                                $curl_post_data = array(
                                    "productId" => $productId
                                );
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($curl, CURLOPT_POST, true);
                                curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                                //print_r($curl_post_data);
                                $curl_response = curl_exec($curl);
                                $rr = json_decode($curl_response, true);
                                ?>
                        <fieldset style="padding-bottom: 100px">
                            <table class="pmclist">
                            <?php

                                for ($x = 0; $x <= count($rr["PhoneModels"]) - 1; $x++) {
?>
                            <tr  data-pid="<?php echo $rr["PhoneModels"][$x]["id"]; ?>" id="pd_<?php echo $rr["PhoneModels"][$x]["id"];?>">
                                <td>
                                    <span><?php echo $rr["PhoneModels"][$x]["name"]?></span>
                                </td>

                            </tr>
                            <?php

                                }
?>
                                </table>
                            <button id="addToCart2" class="primary-action" onclick="AddtoCart2()">
                                        <svg class="icon-cart"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="./assets/images/sprite.defs.svg#icon-cart"></use></svg>
                                        ADD TO CART
                                    </button>
                        </fieldset>
                    <?php } ?>
                </article>
            </div>
        </div>
    </div>
    <div id="LoadingImage"
         style=" display: none; position: fixed; top: 80%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);background-color: #FFFFFF">
        <center>Adding to cart...</center><img src="assets/images/ajax-loader.gif" /><center>Please Wait...</center>
    </div>
    <input type="hidden" id="merchantId" value="<?php echo $_SESSION['UserId']; ?>">
    <input type="hidden" id="productId" value="<?php echo $productId; ?>">
    <input type="hidden" id="basketId" value="<?php echo $_SESSION['BasketId']; ?>">
</body>

<script>
    $(document).ready(function(){
        $.ajax({
            type: 'GET',
            url: "<?php echo $serviceConfig['serviceAddress'];?>getColorsByProdId"
                    + "?Id=" + $("#productId").val(),
            dataType: "json", // data type of response
            async: false,
            beforeSend: function () {
                $("#LoadingImage").show();

            },
            complete: function () {
                $("#LoadingImage").hide();
            },
            success: function (data) {
                if (data.ResponseCode == "51") {
                    for (x = 0; x < data.PPMS.length; x++)
                    {
    $('#pd_'+data.PPMS[x].ppmid).append('<td><span>'+data.PPMS[x].cname+'</span></br><input class="ppmcq" type="number" id="ppmcq_'+data.PPMS[x].cid+'" data-ppmid="'+data.PPMS[x].ppmid+'" data-cid="'+data.PPMS[x].cid+'"/></td>');
                        //$("#selectedColor").append(new Option(data.PPMS[x].cName, data.PPMS[x].id));
                    }
                    //document.getElementById("selectedCount").disabled = false;
                } else {
                    alert(data.errorDesc);
                }
            }
        });
    });
    function getColors() {

        $.ajax({
            type: 'GET',
            url: "<?php echo $serviceConfig['serviceAddress'];?>getColorsByPhoneModelId"
                    + "?Id=" + $('#selectedPhoneModel option:selected').val(),
            dataType: "json", // data type of response
            async: false,
            beforeSend: function () {
                $("#LoadingImage").show();

            },
            complete: function () {
                $("#LoadingImage").hide();
            },
            success: function (data) {
                var datas = '';
                $("#selectedColor").empty();
                if (data.ResponseCode == "51") {
                    for (x = 0; x < data.Colors.length; x++)
                    {
                        $("#selectedColor").append(new Option(data.Colors[x].cName, data.Colors[x].id));
                    }
                    document.getElementById("selectedCount").disabled = false;
                } else {
                    alert(data.errorDesc);
                }
            }
        });
    }

    function enableAddToCart() {
        if ($('#selectedCount option:selected').val() == 0) {
            document.getElementById("addToCart").disabled = true;
        } else {
            document.getElementById("addToCart").disabled = false;
        }
    }
    function AddtoCart2(){
        var pcnt = 0;
        var padded = 0;
    var paddedAPI=0;
        $('.ppmcq').each(function (){
            pcnt++;
            if ($.trim($(this).val()) == '') {

            }
            else {
                var pcqty=0;
                pcqty = Number($(this).val());
                if(pcqty!=null&&pcqty>0){
                    padded++;
                }
                else{
                    alert('There are some invalid entry.');
                    $(this).focus();
                    return false;
                }
            }
        });
        if(padded==0){
            alert('Please add minimum 1 option!');
            return false;
        }
        var pAPIcnt=0;
        $($('.ppmcq').get()).each(function (i,el){
            if ($.trim($(el).val()) == '') {

            }
            else {
                var pcqty=0;
                pcqty = Number($(el).val());
                if(pcqty!=null&&pcqty>0){
    pAPIcnt++;
    setTimeout(function(){ 
                    $.ajax({
                    type: 'GET',
                    url: "<?php echo $serviceConfig['serviceAddress'];?>addToCart"
                            + "?merchantId=" + $("#merchantId").val() +
                            "&basketId=" + $("#basketId").val() +
                            "&quantity=" + $(el).val() +
                            "&productId=" + $("#productId").val() +
                            "&woodtypeId=1&colorId=" + $(el).attr('data-cid') +
                            "&phoneModelId=" + $(el).attr('data-ppmid'),
                    dataType: "json", // data type of response
                    async: true,
                    beforeSend: function () { 
    console.log("<?php echo $serviceConfig['serviceAddress'];?>addToCart"
                            + "?merchantId=" + $("#merchantId").val() +
                            "&basketId=" + $("#basketId").val() +
                            "&quantity=" + $(el).val() +
                            "&productId=" + $("#productId").val() +
                            "&woodtypeId=1&colorId=" + $(el).attr('data-cid') +
                            "&phoneModelId=" + $(el).attr('data-ppmid'));
                        $("#LoadingImage").show();
                    },
                    complete: function (data) {
                    },
                    success: function (data) {
    console.log('----DATA Response-----');
    console.log(data);
    console.log('---------');
    if (data.ResponseCode == "500") {
    console.log(data.ResponseDesc);
    }
                        if (data.ResponseCode == "85") {
                            $("#basketId").val(data.BasketId);
                            $.ajax({
                                url: "setusercookie.php?BasketId=" + data.BasketId,
                                async: true,
                                complete: function () {
                                   paddedAPI++;
    if(paddedAPI==padded){
        alert('All items added successfully!');
    window.location.replace("http://order.com/products.php#"+$('#productId').val());
    }
                                }
                            });
                        } else {
                            alert(data.errorDesc);
                        }
                        $("#LoadingImage").hide();
                    }
                });
    },150 + ( pAPIcnt * 150 ));
                }
            }
        });
    }
    $('#addToCart').on('click', function (e) {
        if ($('#selectedCount option:selected').val() == 0 || $('#selectedWoodType option:selected').val() == 0 || $('#selectedColor option:selected').val() == 0 || $('#selectedPhoneModel option:selected').val() == 0) {
            alert("Please select all properties before add!");
            return;
        }
        document.getElementById("addToCart").disabled = true;
        $.ajax({
            type: 'GET',
            url: "<?php echo $serviceConfig['serviceAddress'];?>addToCart"
                    + "?merchantId=" + $("#merchantId").val() +
                    "&basketId=" + $("#basketId").val() +
                    "&quantity=" + $('#selectedCount option:selected').val() +
                    "&productId=" + $("#productId").val() +
                    "&woodtypeId=" + $('#selectedWoodType option:selected').val() +
                    "&colorId=" + $('#selectedColor option:selected').val() +
                    "&phoneModelId=" + $('#selectedPhoneModel option:selected').val(),
            dataType: "json", // data type of response
            async: true,
            beforeSend: function () {
                console.log("<?php echo $serviceConfig['serviceAddress'];?>addToCart"
                        + "?merchantId=" + $("#merchantId").val() +
                        "&basketId=" + $("#basketId").val() +
                        "&quantity=" + $('#selectedCount option:selected').val() +
                        "&productId=" + $("#productId").val() +
                        "&woodtypeId=" + $('#selectedWoodType option:selected').val() +
                        "&colorId=" + $('#selectedColor option:selected').val() +
                        "&phoneModelId=" + $('#selectedPhoneModel option:selected').val());
                $("#LoadingImage").show();
            },
            complete: function (data) {
            },
            success: function (data) {
                if (data.ResponseCode == "85") {
                    $("#basketId").val(data.BasketId);
                    $.ajax({
                        url: "setusercookie.php?BasketId=" + data.BasketId,
                        async: true,
                        complete: function () {
                            alert("Added to cart");
                        }
                    });
                } else {
                    alert(data.errorDesc);
                }
                $("#LoadingImage").hide();
                document.getElementById("addToCart").disabled = false;
            }
        });
    })
</script>
<style>
    .pmclist {
        width: 100%;
    }
    .pmclist input[type="number"] {
        width: 50px
    }
    .page-main {
        padding: 50px 0;
    }
    .product-carousel img {
        /*width: 100%; 
        max-width: 300px;*/
        height: 120px;
        margin: 0 auto; 
    }
    .product-carousel {        
        position: fixed;
        padding: 0;
        width: 100%;
        height: 120px;
        z-index: 99;
        background-color: #fff;
    }
</style>
<script>
    //$(document).ready(function(){
    //$(window).scroll(function(){
    //    var scrollTop = $(window).scrollTop();
    //    console.log(scrollTop);
    //    if(scrollTop>150){
    //        $('body').addClass('scrolled');
    //    }
    //    else{
    //        $('body').removeClass('scrolled');
    //    }
    //});
    //});
</script>
<style>
    body {
        font-weight: 600
    }
</style>
</html>