<?php
require_once "core.php";
$name = "Name";
$balance = "0.00";
$key = "";

if (isset($_SESSION["username"]) || !empty($_SESSION["username"]))
{
	header("location: index.php");
	exit;
}

else
{
	$create_username = "Username";
	$create_password = $confirm_password = "";
	$create_username_err = $create_password_err = $confirm_password_err = "";

	// GET request received
	if ($_SERVER["REQUEST_METHOD"] == "GET")
	{
		if(!isset($_GET['key']) || empty($_GET['key']))
		{
			header("location: login.php");
			exit;
		}
		$key = $_GET['key'];
		$redeemable = verifyRedeemKey($key);
		if ($redeemable == "")
		{
			header("location: login.php");
			exit;
		}
		$name = $redeemable['name'];
		$address = $redeemable['address'];

		// Get address balance
		$balance = getBalance($address);
		$balance = ($balance["availableBalance"] + $balance["lockedAmount"]) / 100;
	}

	// POST request received
	elseif ($_SERVER['REQUEST_METHOD'] == "POST")
	{
		// Get key
		if (!isset($_POST['key']) || empty($_POST['key']))
		{
			header("location: login.php");
			exit;
		}
		$key = $_POST['key'];
		$redeemable = verifyRedeemKey($key);
		if ($redeemable == "")
		{
			header("location: login.php");
			exit;
		}
		$name = $redeemable['name'];
		$address = $redeemable['address'];

		// Make sure username and password aren't blank
		if (empty(trim($_POST["username"])) || $_POST["username"] == "Username") $create_username_err = "Please enter a username.";
		else $create_username = $_POST["username"];
		if (empty(trim($_POST["password"]))) $create_password_err = "Please enter a Password.";
		else $create_password = $_POST["password"];
		if (empty(trim($_POST["confirm_password"]))) $confirm_password_err = "Please confirm your password.";
		else $confirm_password = $_POST["confirm_password"];

		// Check username and password length
		if (!empty(trim($create_username)) && strlen(trim($create_username)) > 30) $create_username_err = "Username is too long.";
		elseif (!empty(trim($create_password)) && strlen(trim($create_password)) < 8) $create_password_err = "Password must be at least 8 characters long.";
		elseif (!empty(trim($create_password)) && strlen(trim($create_password)) > 200) $create_password_err = "Password is too long.";

		// Check that passwords match
		elseif ($create_password != $confirm_password) $confirm_password_err = "Passwords do not match.";

		// Attempt to create a new user
		elseif (empty($create_username_err) && empty($create_password_err) && empty($confirm_password_err))
			attemptRedeem(trim($key), trim($create_username), trim($create_password), $create_username_err, $create_password_err, $confirm_password_err);
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
            <div class="navbar-header">
                <a class="navbar-brand " style="color: #2ecc71 !important">
                    <img src="favicon.ico" style="width: 30px; margin-top: -5px; margin-right: 5px" />
                    <?php echo WEBSITE_TITLE; ?> Redeem
                </a>
            </div>
        </div>
    </nav>
    <!--End Navigation -->

    <!-- Create Section -->
    <section class="for-full-back color-dark" id="create">
        <div class="container">
			<div class="row space-pad"></div>
            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>REDEEM</h1>
                    <h2 class="description-blob">
                        Hello <a class="color-green"><?php echo $name; ?></a>, you have <a class="color-green"><?php echo $balance; ?></a> TRTL waiting for you, create an account to redeem it.
                    </h2>
                </div>
            </div>

			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="createform">
            <div class="row space-pad" style="padding: 12px">
                <h3>Username:</h3>
                <input class="btn text-input " id="createusername" name="username" value="<?php echo $create_username; ?>" title="This is what you will use to login" />
                <span class="help-block"><?php echo $create_username_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px">
                <h3>Password:</h3>
                <input class="btn text-input " id="createpassword" name="password" value="<?php echo $create_password; ?>" title="Your password must be at least 8 characters long" type="password" />
                <span class="help-block"><?php echo $create_password_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px">
                <h3>Confirm Password:</h3>
                <input class="btn text-input " id="confirmpassword" name="confirm_password" value="<?php echo $confirm_password; ?>" title="You must confirm your password to create a new account" type="password" />
                <span class="help-block confirm-password"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="row " style="margin-bottom: 24px"></div>
			<div class="row text-center alert-message"></div>
            <div class="row  text-center">
                <a id="createButton" class="btn btn-primary" style="width: 250px"><h5>Create</h5></a>
            </div>
			<input type="password" name="key" value="<?php echo $key; ?>" style="display: none;" />
			<input type="submit" style="display: none;" />
			</form>

        </div>
    </section>
    <!--End Create Section-->

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/jquery-ui.min.js"></script>
    <script src="assets/plugins/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        $("#createButton").click(function () {
			if ($("#createpassword").val() != $("#confirmpassword").val())
				$(".confirm-password").html("Passwords don't match.");
            else $("#createform").submit();
        });

        $("input").focusin(function () {
            if ($(this).val() == "Username" || $(this).val() == "Password")
                $(this).val("");
        });

        $("#createusername").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Username");
        });

        $("#createpassword").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Password");
        });

		$(document).ready(function () {
			$("input").tooltip();
			$("html, body, document, documentElement").animate({
				scrollTop: 0
			}, 500);
        });
    </script>

</body>
</html>
