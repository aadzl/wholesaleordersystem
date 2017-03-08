<?php include 'includes/_head.php'; ?>
<body>
    <div id="wrapper" class="page-wrap">
        <?php include 'includes/_header.php'; ?>
        <div id="content" class="page-main">
            <div class="container">                
                <?php include 'includes/_new-additions.php'; ?>
                
            </div>
        </div>
    </div> 
    <script>
        $(document).ready(function () {
            var prodid = window.location.hash.substr(1);
            console.log(prodid);
            if (prodid != null && prodid != '') {
                $(document).ready(function () {
                    $('html, body').animate({
                        scrollTop: $("#prod_" + prodid).offset().top - 130
                    }, 500);
                })
            }
        });
    </script>
</body>
</html>