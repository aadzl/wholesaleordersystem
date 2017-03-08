<?php include 'includes/_head.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();

$userId = $_SESSION['UserId'];
?>
<body>
    <div id="wrapper" class="page-wrap">
        <?php include 'includes/_product-header.php'; ?>
        <div id="content" class="page-main">
            <div class="container">
                <div class="boutique-actions">
                    Akın Garagon
                </div>
                <div class="boutique-actions">
                    <?php echo $_SESSION["Email"] ?>
                </div>
                <div class="boutique-actions">
                    Password : <input type="password" id="password" placeholder="Password">
                    <input type="hidden" id="email" value="<?php echo $_SESSION["Email"] ?>">
                </div>

            </div>
            <li class="buttons">
                <button class="primary-action" onclick="changePasswordEmail()">SAVE</button>
            </li>

        </div>
    </div>
    <div id="LoadingImage"
         style=" display: none; position: fixed; top: 80%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);background-color: #FFFFFF">
        <center>Processing...</center><img src="assets/images/ajax-loader.gif" /><center>Please wait...</center>
    </div>

</body>
<script>
    function changePasswordEmail() {
        $.ajax({
            type: 'GET',
            url: "http://order.com/apiservices/changePassword/?"
                    + "password=" + $("#password").val() + "&email=" + $("#email").val(),
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
                    alert("Password is changed. Please login again with your new creditendals.");
                    location.href='./index.php';
                } else {
                    alert(data.ResponseDesc);
                }
            }
        });
    }

</script>
</html>