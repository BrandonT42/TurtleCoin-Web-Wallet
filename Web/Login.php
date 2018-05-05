<?php
// Include config file
require_once "core.php";

if (isset($_SESSION["username"]) || !empty($_SESSION["username"]))
{
	header("location: index.php");
	exit;
}

else
{
	// Define variables and initialize with empty values
	$login_username = $create_username = "Username";
	$login_password = "Password";
	$create_password = $confirm_password = $confirm_login_password = $confirm_login_password_err = $recovery_key = $recovery_key_err = "";
	$login_username_err = $login_password_err = $create_username_err = $create_password_err = $confirm_password_err = "";
	$check = "";
 
	// Processing form data when form is submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Logging in
		if ($_POST["type"] == "login")
		{
			// Make sure username and password aren't blank
			if (empty(trim($_POST["username"])) || trim($_POST["username"]) == "Username") $login_username_err = "Please enter a username.";
			else $login_username = trim($_POST["username"]);
			if (empty(trim($_POST["password"]))) $login_password_err = "Please enter your password.";
			else $login_password = trim($_POST["password"]);

			// Check username and password length
			if (!empty($login_username) && strlen($login_username) > 30) $login_username_err = "Username is too long.";
			elseif (!empty($login_password) && strlen($login_password) > 200) $login_password_err = "Password is too long.";

			// Attempt a login
			elseif (empty($login_username_err) && empty($login_password_err))
				attemptLogin($login_username, $login_password, $login_username_err, $login_password_err);
			if ($login_password_err == "Password is incorrect.") $check = "badpassword";
		}
		
		// Creating a new account
		elseif ($_POST["type"] == "create")
		{
			// Make sure username and password aren't blank
			if (empty(trim($_POST["username"])) || trim($_POST["username"]) == "Username") $create_username_err = "Please enter a username.";
			else $create_username = trim($_POST["username"]);
			if (empty(trim($_POST["password"]))) $create_password_err = "Please enter a Password.";
			else $create_password = trim($_POST["password"]);
			if (empty(trim($_POST["confirm_password"]))) $confirm_password_err = "Please confirm your password.";
			else $confirm_password = trim($_POST["confirm_password"]);

			// Check username and password length
			if (!empty($create_username) && strlen($create_username) > 30) $create_username_err = "Username is too long.";
			elseif (!empty($create_password) && strlen($create_password) < 8) $create_password_err = "Password must be at least 8 characters long.";
			elseif (!empty($create_password) && strlen($create_password) > 200) $create_password_err = "Password is too long.";

			// Check that passwords match
			elseif ($create_password != $confirm_password) $confirm_password_err = "Passwords do not match.";

			// Attempt to create a new user
			elseif (empty($create_username_err) && empty($create_password_err) && empty($confirm_password_err))
				attemptCreate($create_username, $create_password, $create_username_err, $create_password_err, $confirm_password_err);
		}

		// Reseting a password
		elseif ($_POST['type'] == 'recovery')
		{
			$check = "recovery";

			// Make sure username and password aren't blank
			if (empty(trim($_POST["username"])) || trim($_POST["username"]) == "Username") $login_username_err = "Please enter a username.";
			else $login_username = trim($_POST["username"]);
			if (empty(trim($_POST["recoverykey"]))) $recovery_key_err = "Please enter your recovery key.";
			else $recovery_key = trim($_POST["recoverykey"]);
			if (empty(trim($_POST["password"]))) $login_password_err = "Please enter a Password.";
			else $login_password = trim($_POST["password"]);
			if (empty(trim($_POST["confirm_password"]))) $confirm_login_password_err = "Please confirm your password.";
			else $confirm_login_password = trim($_POST["confirm_password"]);

			// Check username and password length
			if (!empty($login_username) && strlen($login_username) > 30) $login_username_err = "Username is too long.";
			elseif (!empty($login_password) && strlen($login_password) < 8) $login_password_err = "Password must be at least 8 characters long.";
			elseif (!empty($login_password) && strlen($login_password) > 200) $login_password_err = "Password is too long.";

			// Check that passwords match
			elseif ($login_password != $confirm_login_password) $confirm_login_password_err = "Passwords do not match.";

			// Attempt to update password and login
			elseif (empty($login_username_err) && empty($login_password_err) && empty($confirm_login_password_err))
			{
				// Check if user exists
				if (verifyRecoveryKey($login_username, $recovery_key))
				{
					// Update password
					updatePassword($login_username, $login_password);

					// Login
					attemptLogin($login_username, $login_password, $errone, $errtwo);
				}
				else $recovery_key_err = "Invalid recovery key.";
			}
		}
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
                    <?php echo WEBSITE_TITLE; ?> Login
                </a>
            </div>
        </div>
    </nav>
    !-- End Navigation -->

    <!-- Login Section-->
    <section class="for-full-back color-dark" id="login">
        <div class="container">
			<div class="row space-pad"></div>
            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>LOGIN</h1>
                    <h2 class="description-blob">
                        You must login before you are able to access your TRTL.
                    </h2>
                </div>
            </div>

			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="loginform">
			<input name="type" value="login" style="display: none;" id="logintype" />
            <div class="row space-pad" style="padding: 12px">
                <h3>Username:</h3>
                <input class="btn text-input " id="loginusername" name="username" value="<?php echo $login_username; ?>" title="Please enter your username" />
                <span class="help-block"><?php echo $login_username_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px; display: none;" id="recoverykeyprompt">
                <h3>Recovery Key:</h3>
                <input class="btn text-input " id="recoverykey" name="recoverykey" value="<?php echo $recovery_key; ?>" title="Please enter your password recovery key" type="password" />
                <span class="help-block" id="recoveryerror"><?php echo $recovery_key_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px">
                <h3 id="passwordlabel">Password:</h3>
                <input class="btn text-input " id="loginpassword" name="password" value="<?php echo $login_password; ?>" title="Please enter your password" type="password" />
                <span class="help-block" id="loginpassworderror"><?php echo $login_password_err; ?></span>
            </div>
            <div class="row space-pad" style="padding: 12px; display: none;" id="loginpasswordprompt">
                <h3>Confirm Password:</h3>
                <input class="btn text-input " id="confirmloginpassword" name="confirm_password" value="<?php echo $confirm_login_password; ?>" title="You must confirm your new password" type="password" />
                <span class="help-block confirm-login-password"><?php echo $confirm_login_password_err; ?></span>
            </div>
            <div class="row " style="margin-bottom: 24px"></div>
			<div class="row text-center alert-message" id="loginStatus"></div>
            <div class="row  text-center">
                <a id="loginButton" class="btn btn-primary" style="width: 250px"><h5>Login</h5></a>
            </div>
			<input type="submit" style="display: none;" />
			</form>
        </div>
    </section>
    <!-- End Login Section -->

    <!-- Create Section -->
    <section class="for-full-back color-light" id="create">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>CREATE</h1>
                    <h2 class="description-blob">
                        Create a new wallet account.
                    </h2>
                </div>
            </div>

			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="createform">
			<input name="type" value="create" style="display: none;" />
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
		var check = "<?php echo $check; ?>";

        $("#loginButton").click(function () {
			if (check != "recovery") $("#loginform").submit();
			else
			{
				if ($("#recoverykey").val() == "")
					$("#recoveryerror").html("Please enter your password recovery key.");
				else if ($("#loginpassword").val() != $("#confirmloginpassword").val())
					$(".confirm-login-password").html("Passwords don't match.");
				else $("#loginform").submit();
			}
        });

        $("#createButton").click(function () {
			if ($("#createpassword").val() != $("#confirmpassword").val())
				$(".confirm-password").html("Passwords don't match.");
            else $("#createform").submit();
        });

        $("input").focusin(function () {
            if ($(this).val() == "Username" || $(this).val() == "Password")
                $(this).val("");
        });

        $("#loginusername").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Username");
        });

        $("#loginpassword").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Password");
        });

        $("#createusername").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Username");
        });

        $("#createpassword").focusout(function () {
            if ($(this).val() == "")
                $(this).val("Password");
        });

		function showRecovery()
		{
			$("#logintype").val("recovery");
			$("#loginpassword").val("");
			$("#loginpassword").attr("title", "Please enter a new password");
			$("#loginpassword").attr("data-original-title", "Please enter a new password");
			$("#passwordlabel").text("New Password:");
			<?php if ($check != "recovery") { ?>$("#loginpassworderror").text("");<?php } ?>
			$("#loginButton").html("<h5>Reset Password</h5>");
			if (!$("#recoverykeyprompt").is(":visible"))
				$("#recoverykeyprompt").slideDown("slow");
			if (!$("#loginpasswordprompt").is(":visible"))
				$("#loginpasswordprompt").slideDown("slow");
			if ($("#loginStatus").is(":visible"))
				$("#loginStatus").slideUp("slow");
		}

		$(document).ready(function () {
			if (check == "badpassword")
			{
				$("#loginStatus").html("<a style='color: white;' href='javascript:showRecovery()'>Forgot your password? Click here</a>");
				$("#loginStatus").css("color", "<?php echo $_SESSION['websitecolor']['textcolor']; ?>");
				if (!$("#loginStatus").is(":visible"))
					$("#loginStatus").slideDown("slow");
			}
			else if (check == "recovery")
			{
				$("#logintype").val("recovery");
				$("#loginpassword").attr("title", "Please enter a new password");
				$("#loginpassword").attr("data-original-title", "Please enter a new password");
				$("#passwordlabel").text("New Password:");
				<?php if ($check != "recovery") { ?>$("#loginpassworderror").text("");<?php } ?>
				$("#loginButton").html("<h5>Reset Password</h5>");
				if (!$("#recoverykeyprompt").is(":visible"))
					$("#recoverykeyprompt").slideDown(0);
				if (!$("#loginpasswordprompt").is(":visible"))
					$("#loginpasswordprompt").slideDown(0);
				if ($("#loginStatus").is(":visible"))
					$("#loginStatus").slideUp(0);
			}
			$("input").tooltip();
			$("html, body, document, documentElement").animate({
				scrollTop: 0
			}, 500);
        });
    </script>

</body>
</html>
