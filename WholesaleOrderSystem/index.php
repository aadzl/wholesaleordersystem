<?php
include 'includes/_head_index.php';
if (!empty($_GET["exp"])) {
    ?>
    <script>
        alert("<?php if (!empty($_GET["exp"])) echo $_GET["exp"] ?>");
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
                        <form class="register-form" action="createaccount.php" method="POST">
                            <input type="text" name="namesurname" placeholder="namesurname"/>
                            <input type="text" name="email" placeholder="email address"/>
                            <input type="password" name="password" placeholder="password"/>
                            <button>create</button>
                            <p class="message">Already registered? <a href="#">Sign In</a></p>
                        </form>
                        <form class="login-form" action="login.php" method="POST">
                            <input type="text" name="username" placeholder="username"/>
                            <input type="password" name="password" placeholder="password"/>
                            <button>login</button>
                            <p class="message">Not registered? <a href="#">Create an account</a></p>
                            <p class="message"><a href="forgotpassword.php">Forgot password</a></p>
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