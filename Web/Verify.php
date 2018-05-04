<?php
// Include config file
require_once "core.php";
$password_updated = false;

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
	header("location: login.php");
	exit;
}
elseif (isset($_SESSION['verified']) && $_SESSION['verified'] == true) {
	header("location: index.php");
	exit;
}
else
{
	// Get user's key
	$key = getUserKey($_SESSION['username']);

	// Processing form data when form is submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		verifyUser($_SESSION['username']);
		$_SESSION['verified'] = 1;
		header("location: index.php");
		exit;
	}
}
?>
 
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->
    <title><?php echo WEBSITE_TITLE; ?></title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" />
    <link href="style.php" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Ruluko' rel='stylesheet' type='text/css' />
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
			<!-- Left Section -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand " style="color: #2ecc71 !important">
					<img src="favicon.ico" style="width: 30px; margin-top: -5px; margin-right: 5px" />
					<?php echo WEBSITE_TITLE; ?> Verify
				</a>
            </div>
			<!-- End Left Section -->

			<!-- Right Section -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav navbar-right">
					<li><a class="welcome-msg"><?php echo strtoupper($_SESSION["username"]); ?></a>
                    <li><a class="nav-option" href="logout.php">LOGOUT</a></li>
                </ul>
            </div>
            <!-- End Right Section -->
        </div>
    </nav>
    <!--End Navigation -->

    <!-- General Section-->
    <section class="for-full-back color-dark" id="generalsettings">
        <div class="container">

			<div class="row space-pad"></div>

            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>IMPORTANT</h1>
                    <h2 class="description-blob">
                        Please write this key down somewhere safe - if you ever need to reset your password for any reason, you will NOT be able to do so without entering this key.
                    </h2>
                </div>
            </div>

			<div class="row space-pad"></div>
			
            <div class="row space-pad text-center">
                <h2 class="verifykey"><?php echo $key; ?></h2>
            </div>

            <div class="row text-center" style="margin-top: -20px;">
				<h3 style="color: #2ecc71">
					I saved this key somewhere safe
				</h3>
				<label class="switch" style="margin-left: 20px;">
					<input type="checkbox" id="verifyCheckbox" />
					<span class="slider round"></span>
				</label>
        </div>

			<div class="row text-center alert-message" id="verifyStatus"></div>

            <div class="row text-center">
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="verifyform"></form>
                <a id="verifyButton" class="btn btn-primary" style="width: 250px"><h5>Verify Me</h5></a>
            </div>

        </div>
    </section>
    <!-- End General Section -->

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/jquery-ui.min.js"></script>
    <script src="assets/plugins/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
		function checkStatus() {
            $.getJSON("sessionstatus.php", function (data) {
                if (data.sessionStatus == false) location.reload();
            });
        }

		$("#verifyButton").click(function () {
			if ($("#verifyCheckbox").is(':checked'))
				$("#verifyform").submit();
			else
			{
				$("#verifyStatus").html("You must verify that you understand this key's importance before continuing");
				$("#verifyStatus").css("color", "red");
				if (!$("#verifyStatus").is(":visible"))
					$("#verifyStatus").slideDown("slow");
			}
        });

		$(document).ready(function () {
			//$("label").tooltip();
			//$("input").tooltip();
			$("html, body, document, documentElement").animate({
				scrollTop: 0
			}, 500);
			checkStatus();
            setInterval(function () {
                checkStatus();
            }, 5000);
        });
    </script>
</body>
</html>
