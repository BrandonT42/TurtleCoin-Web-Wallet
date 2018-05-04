<?php
// Include config file
require_once "core.php";
$password_updated = false;

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
	header("location: login.php");
	exit;
}

elseif (!isset($_SESSION['verified']) || $_SESSION['verified'] != true) {
	header("location: verify.php");
	exit;
}

else
{
	// Define variables and initialize with empty values
	$username = $_SESSION['username'];
	$password = $new_password = $confirm_password = "";
	$password_err = $new_password_err = $confirm_password_err = "";
 
	// Processing form data when form is submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Make sure current password isn't blank
		if (empty(trim($_POST["password"]))) $password_err = "Please enter your current password.";
		else $password = $_POST["password"];
		
		// Check if password is correct
		if (verifyPassword($username, $password))
		{
			// Verify that other fields aren't blank
			if (empty(trim($_POST["newpassword"]))) $new_password_err = "Please enter a password.";
			else $new_password = $_POST["newpassword"];
			if (empty(trim($_POST["confirmpassword"]))) $onfirm_password_err = "Please confirm your new password.";
			else $confirm_password = $_POST["confirmpassword"];

			// Check password length
			if (!empty(trim($new_password)) && strlen(trim($new_password)) < 8) $new_password_err = "Password must be at least 8 characters long.";
			elseif (!empty(trim($new_password)) && strlen(trim($new_password)) > 200) $new_password_err = "Password is too long.";

			// Check that passwords match
			elseif (trim($new_password) != trim($confirm_password)) $confirm_password_err = "Passwords do not match.";

			// Check that new password isn't the same as old one
			elseif (trim($password) == trim($new_password)) $new_password_err = "New password can't be the same as your old one.";

			// Update password
			elseif (empty($password_err) && empty($new_password_err) && empty($confirm_password_err))
			{
				updatePassword($_SESSION["username"], trim($new_password));
				$password_updated = true;
			}
		}
		else $password_err = "Password is incorrect.";
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
					<?php echo WEBSITE_TITLE; ?> Settings
				</a>
            </div>
			<!-- End Left Section -->

			<!-- Right Section -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav navbar-right">
					<li><a class="welcome-msg"><?php echo strtoupper($_SESSION["username"]); ?></a>
                    <li><a class="nav-option" href="index.php">HOME</a></li>
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
                    <h1>GENERAL</h1>
                    <h2 class="description-blob">
                        Change the look and feel of the website
                    </h2>
                </div>
            </div>

            <div class="row space-pad text-center" style="padding: 12px">
				<h3>
					Night Mode
					<label class="switch" title="Doesn't do anything yet">
						<input type="checkbox" checked="true" disabled="true" name="nightmode" id="nightmode" />
						<span class="slider round"></span>
					</label>
				</h3>
            </div>

        </div>
    </section>
    <!-- End General Section -->

    <!-- Optimization Section-->
    <section class="for-full-back color-light" id="optimizationsettings">
        <div class="container">

			<div class="row space-pad"></div>

            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>OPTIMIZATION</h1>
                    <h2 class="description-blob">
                        Optimizing your wallet through fusion cleans it up internally, allowing you to send more TRTL at once, and have an overall smoother experience.
                    </h2><br>
					<h5 style="color:red;">
						
						Please note that the fusion process can take anywhere between a few minutes to a few hours to complete, depending on how long it's been since you last optimized, as well as how many transactions you have received since then.
					</h5>
                </div>
            </div>

			<div class="row text-center alert-message" id="optimizationStatus"></div>

            <div class="row  text-center">
                <a id="quickOptimizeButton" class="btn btn-primary" style="width: 250px"><h5>Quick Optimize</h5></a>
                <a id="fullOptimizeButton" class="btn btn-primary" style="width: 250px"><h5>Full Optimize</h5></a>
            </div>

        </div>
    </section>
    <!-- End Optimization Section -->

    <!-- Password Section-->
    <section class="for-full-back color-dark" id="passwordsettings">
        <div class="container">

			<div class="row space-pad"></div>

            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>PASSWORD</h1>
                    <h2 class="description-blob">
                        Change your password
                    </h2>
                </div>
            </div>

			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="passwordform">
            <div class="row space-pad" style="padding: 12px">
                <h3>Current Password:</h3>
                <input class="btn text-input " id="password" name="password" title="Plese enter your current password" value="<?php if(!$password_updated) echo $password; ?>" type="password" />
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px">
                <h3>Password:</h3>
                <input class="btn text-input " id="newpassword" name="newpassword" title="Please enter a new password" value="<?php if(!$password_updated) echo $new_password; ?>" type="password" />
                <span class="help-block"><?php echo $new_password_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px">
                <h3>Confirm Password:</h3>
                <input class="btn text-input " id="confirmpassword" name="confirmpassword" title="Please confirm your new password" value="<?php if(!$password_updated) echo $confirm_password; ?>" type="password" />
                <span class="help-block confirm-password"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="row " style="margin-bottom: 24px"></div>
			<div class="row text-center alert-message" id="passwordAlert"></div>
            <div class="row  text-center">
                <a id="saveButton" class="btn btn-primary" style="width: 250px"><h5>Change Password</h5></a>
            </div>
			<input type="submit" style="display: none;" />
			</form>

        </div>
    </section>
    <!-- End Password Section -->

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

		$("#saveButton").click(function () {
			if ($("#newpassword").val() != $("#confirmpassword").val())
				$(".confirm-password").html("Passwords don't match.");
            else $("#passwordform").submit();
        });

		$("#quickOptimizeButton").click(function () {
			$("#optimizationStatus").html("Attempting quick optimization...");
			$("#optimizationStatus").css("color", "<?php echo $_SESSION['websitecolor']['textcolor']; ?>");
			if (!$("#optimizationStatus").is(":visible"))
				$("#optimizationStatus").slideDown("slow");
			$.post(
                "sendrequest.php",
                {
                    method: 'quickOptimize'
                },
                function (data) {
					console.log(data);
					data = JSON.parse(data);
					if (data.success)
					{
						$("#optimizationStatus").html("Wallet has been fused one time");
						$("#optimizationStatus").css("color", "<?php echo $_SESSION['websitecolor']['highlight']; ?>");
						if (!$("#optimizationStatus").is(":visible"))
							$("#optimizationStatus").slideDown("slow");
					}
					else
					{
						$("#optimizationStatus").html("Wallet is already optimized");
						$("#optimizationStatus").css("color", "red");
						if (!$("#optimizationStatus").is(":visible"))
							$("#optimizationStatus").slideDown("slow");
					}
				}
			);
        });

		$("#fullOptimizeButton").click(function () {
			$.post(
                "sendrequest.php",
                {
                    method: 'fullOptimize'
                },
				function (data) { }
			);
			$("#optimizationStatus").html("Full wallet optimization has begun");
			$("#optimizationStatus").css("color", "<?php echo $_SESSION['websitecolor']['highlight']; ?>");
			if (!$("#optimizationStatus").is(":visible"))
				$("#optimizationStatus").slideDown("slow");
        });

		$(document).ready(function () {
			$("label").tooltip();
			$("input").tooltip();
			<?php if($password_updated) { ?>
			$("#passwordAlert").html("Password updated");
			$("#passwordAlert").css("color", "<?php echo $_SESSION['websitecolor']['highlight']; ?>");
			if (!$("#passwordAlert").is(":visible"))
				$("#passwordAlert").slideDown("slow");
			<?php } ?>
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
