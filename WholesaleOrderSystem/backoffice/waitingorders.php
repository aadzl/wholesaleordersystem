<?php
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Credentials: true ");
//header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
//header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size,X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
session_start();
if (empty($_SESSION['UserId'])) {
    header('Location: index.php');
}
?>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Nola Administration Panel</title>

        <!-- Bootstrap Core CSS -->
        <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- MetisMenu CSS -->
        <link href="bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

        <!-- Timeline CSS -->
        <link href="dist/css/timeline.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="dist/css/sb-admin-2.css" rel="stylesheet">

        <!-- Morris Charts CSS -->
        <link href="bower_components/morrisjs/morris.css" rel="stylesheet">

        <!-- Custom Fonts -->
        <link href="bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>
        <div id="wrapper">

            <!-- Navigation -->
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.html">Nola Administration Panel</a>
                </div>
                <!-- /.navbar-header -->

                <!-- /.navbar-top-links -->

                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav navbar-collapse">
                        <ul class="nav" id="side-menu">
                            <li class="sidebar-search">
                                <div class="input-group custom-search-form">
                                    <input type="text" class="form-control" placeholder="Search...">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                                <!-- /input-group -->
                            </li>
                            <li>
                                <a href="products.php"><i class="fa fa-tasks"></i> Product List</a>
                            </li>
                            <li>
                                <a href="waitingorders.php"><i class="fa fa-spinner"></i> Waiting Orders</a>
                            </li>
                            <li>
                                <a href="completedorders.php"><i class="fa fa-check-square-o"></i> Completed Orders</a>
                            </li>
                            <li>
                                <a href="waitingmerchants.php"><i class="fa fa-meh-o"></i> Waiting Merchants</a>
                            </li>
                            <li>
                                <a href="allmerchants.php"><i class="fa fa-users"></i> All Merchants</a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.sidebar-collapse -->
                </div>
                <!-- /.navbar-static-side -->
            </nav>

            <div id="page-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">Waiting Order List</h1>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-9">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Waiting Orders
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Merchant</th>
                                                <th>Order Date</th>
                                                <th>Total Count</th>
                                                <th>Approve</th>
                                            </tr>
                                        </thead>
                                        <form action="mergedorderlist.php" method="POST">
                                              
                                        <tbody>
                                            <?php
                                            $serviceConfig = parse_ini_file('../serviceConfig.ini');
                                            $service_url = $serviceConfig['serviceAddress'] . 'getWaitingOrders/';
                                            $curl = curl_init($service_url);
                                            $curl_post_data = array();
                                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($curl, CURLOPT_POST, true);
                                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                                            $curl_response = curl_exec($curl);
                                            $rr = json_decode($curl_response, true);
                                            $totalCount = 0;
                                            if (!empty($rr["Orders"])) {
                                                for ($x = 0; $x < sizeof($rr["Orders"]); $x++) {
                                                    $totalCount = $totalCount + $rr["Orders"][$x]["totalCount"];
                                                    ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="c<?php echo $x+1;?>" value="<?php echo $rr["Orders"][$x]["id"]; ?>"></td>
                                                        <td onclick="directDetail('<?php echo $rr["Orders"][$x]["id"]; ?>')"><?php echo $rr["Orders"][$x]["namesurname"]; ?></td>
                                                        <td onclick="directDetail('<?php echo $rr["Orders"][$x]["id"]; ?>')"><?php echo $rr["Orders"][$x]["endDate"]; ?></td>
                                                        <td onclick="directDetail('<?php echo $rr["Orders"][$x]["id"]; ?>')"><?php echo $rr["Orders"][$x]["totalCount"]; ?></td>
                                                        <td><a href="approveorder.php?basketId=<?php echo $rr["Orders"][$x]["id"]; ?>">Approve</a></td>
                                                    </tr>
                                                <?php }
                                                ?>
                                                <tr>
                                                    <td colspan="5">Totally <?php echo $x; ?> orders and <?php echo $totalCount; ?> pieces</td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        <input name="orderCount" type="hidden" value="<?php echo $x; ?>">
                                                <input type="submit" value="Get Merged List">
                                        </tbody>
                                        
                                        </form>
                                    </table>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>
                    <!-- /.col-lg-6 -->

                    <!-- /.col-lg-6 -->
                </div>
                <!-- /.row -->
                <!-- /.row -->
            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->
        <div id="LoadingImage"
             style=" display: none; position: fixed; top: 80%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);background-color: #FFFFFF">
            <center>Bilgiler Yükleniyor...</center><img src="ajax-loader.gif" /><center>Lütfen Bekleyiniz...</center>
        </div>
        <!-- jQuery -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="bower_components/metisMenu/dist/metisMenu.min.js"></script>

        <!-- Morris Charts JavaScript -->
        <script src="bower_components/raphael/raphael-min.js"></script>



        <!-- Custom Theme JavaScript -->
        <script src="dist/js/sb-admin-2.js"></script>

    </body>
    <script>
                                                        function directDetail(id) {
                                                            document.location.href = 'orderDetail.php?basketId=' + id;
                                                        }
    </script>
</html>
