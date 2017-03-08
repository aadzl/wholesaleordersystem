<?php
include 'includes/_head_index.php';
if (!empty($_GET["exp"])) {
    ?>
    <script>
        alert("<?php if (!empty($_GET["exp"])) echo $_GET["exp"] ?>");
    </script>
    <?php
}
if (!empty($_GET["result"])) {
    ?>
    <script>
        alert("Password has been sent your email address");
    </script>
    <?php
}
?>
<body>
    <div id="wrapper" class="page-wrap">
<?php include 'includes/_header_index.php'; ?>
        <div id="content" class="page-main">
            <div class="container">                
                <div class="login-page">
                    <div class="form">
                        <form class="login-form" action="sendpassword.php" method="POST">
                            <input type="text" name="username" placeholder="username"/>
                            <button>send by email</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $('.message a').click(function () {
        $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
    });
</script>
</html>
<?php
session_start();
session_unset();
session_destroy();
?>