<ol class="item-list product-list">
    <?php
    header('Content-Type: text/html; charset=utf-8');
    ob_start();
    session_start();

    $serviceConfig = parse_ini_file('serviceConfig.ini');

    $service_url = $serviceConfig['serviceAddress'] . 'getAllProducts/';
    $curl = curl_init($service_url);
    $curl_post_data = array("UserType" => $_SESSION['UserType']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
//print_r($curl_post_data);
    $curl_response = curl_exec($curl);
    $rr = json_decode($curl_response, true);
    if ($rr["ResponseCode"] == "51") {
        for ($x = 0; $x <= count($rr["Product"]) - 1; $x++) {
            echo "<li class='item-list-item' id='prod_".$rr["Product"][$x]["id"]."'>";
            echo "<a href='./product.php?id=" . $rr["Product"][$x]["id"] . "' class='new-addition'>";
            echo "<img src='./products/" . $rr["Product"][$x]["pictureUrl"] . "' alt='' />";
            echo "</a>";
            echo "<h4 class='seller-name'><center>" . $rr["Product"][$x]["name"] . "</center></h4>";
            echo "</li>";
        }
    } else {
        print_r($curl_response);
        $_SESSION['ResponseDescription'] = $rr["ResponseDesc"];
        header('Location: index.php?&result=fail&code=' . $rr["ResponseCode"]);
    }
    ?>
</ol>