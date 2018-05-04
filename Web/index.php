<?php
require_once "core.php";

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
	header("location: login.php");
	exit;
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
                <?php echo WEBSITE_TITLE; ?> </a>
            </div>
			<!-- End Left Section -->

			<!-- Right Section -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav navbar-right">
					<li><a class="welcome-msg"><?php echo strtoupper($_SESSION["username"]); ?></a>
                    <li><a class="nav-option" href="#balance">BALANCE</a></li>
                    <li><a class="nav-option" href="#send">SEND</a></li>
                    <li><a class="nav-option" href="settings.php">SETTINGS</a></li>
                    <li><a class="nav-option" href="logout.php">LOGOUT</a></li>
                </ul>
            </div>
            <!-- End Right Section -->
        </div>
    </nav>
    <!--End Navigation -->

    <!--Balance Section-->
    <section class="for-full-back color-dark " id="balance">
        <div class="container">
			<div class="row space-pad"></div>

            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>BALANCE</h1>
                </div>
            </div>

            <div class="row text-center">
				<div class="col-md-8 col-md-offset-2">
                <h2 class="description-blob">
                    Your Address:
                </h2>
				</div>
            </div>
            <div class="row space-pad"></div>
            <div class="row text-center">
                <h2 class="address-blob" title="Double-click to copy to clipboard"><?php echo $_SESSION['address']; ?></h2>
            </div>
            <div class="row space-pad"></div>

            <div class="row text-center ">
                <div class="col-md-6 ">
                    <div class="about-div color-light">
                        <h2 title="Your spendable balance"><a id="availableBalance" class="color-green"><?php echo number_format($_SESSION['availablebalance'], 2); ?></a> TRTL</h2>
                        Available Balance
                    </div>
                </div>

                <div class="col-md-6 ">
                    <div class="about-div color-light">
                        <h2 title="This balance is temporarily locked up in transactions"><a id="lockedAmount" class="color-green"><?php echo number_format($_SESSION['lockedamount'], 2); ?></a> TRTL</h2>
                        Locked Amount
                    </div>
                </div>
            </div>

			<div class="row space-pad"></div>

        </div>
    </section>
    <!--End Balance Section-->

    <!-- Send Section-->
    <section class="for-full-back color-light" id="send">
        <div class="container">
			<div class="row space-pad"></div>

            <div class="row text-center">
                <div class="col-md-8 col-md-offset-2">
                    <h1>SEND</h1>
                    <h2 class="description-blob">
                        Send TRTL to anyone in the world, quickly and privately.
                    </h2>
                </div>
            </div>

            <div class="row space-pad" style="padding: 12px">
                <h3>Send To</h3>
                <input class="btn text-input " id="sendToAddress" value="TRTL Address or Name" title="Enter the address you'd like to send TRTL to, or enter someone's name to generate a shareable link they can create a wallet with" />
            </div>

            <div class="row  ">
                <div class="col-md-6 ">
                    <h3>Amount</h3>
                    <input class="btn numberonly text-input " id="sendAmount" value="0.00" title="The amount of TRTL you'd like to send" />
                </div>
                <div class="col-md-6 ">
                    <h3>Fee</h3>
                    <input class="btn numberonly text-input " id="sendFee" value="0.10" title="Network transaction fee" />
                </div>
            </div>

            <div class="row space-pad" style="padding: 12px">
                <h3>Payment ID (Optional)</h3>
                <input class="btn text-input " id="sendPaymentId" title="Optional payment ID" value="" />
            </div>

            <div class="row " style="margin-bottom: 24px"></div>
			<div class="row text-center alert-message" id="sendStatus"></div>
            <div class="row  text-center">
                <a id="sendButton" class="btn btn-primary" style="width: 250px"><h5>Send</h5></a>
            </div>

			<div class="row space-pad"></div>
        </div>
    </section>
    <!--End Send Section-->

    <!--footer Section -->
    <div class="for-full-back" id="footer">
		<div class="text-center">
			<?php
				shuffle($footermessage);
				echo $footermessage[0];
			?>
		</div>
    </div>
    <!--End footer Section -->
	
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/jquery-ui.min.js"></script>
    <script src="assets/plugins/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
		var balance = 0;
        function checkStatus() {
            $.getJSON("sessionstatus.php", function (data) {
                if (data.sessionStatus == false) location.reload();
				balance = parseFloat(data.availableBalance.replace(/,/g, ''));
                $("#availableBalance").html(data.availableBalance);
                $("#lockedAmount").html(data.lockedAmount);
            });
        }
		function round(value) {
		    return Number(Math.round(value+'e0')+'e-0');
		}

        $(document).ready(function () {
			$("input").tooltip();
			$("h2").tooltip();
			$("html, body, document, documentElement").animate({
				scrollTop: 0
			}, 500);
			
			$("#sendStatus").slideToggle();
            checkStatus();
            setInterval(function () {
                checkStatus();
            }, 5000);
        });

		$('#sendAmount').on("cut copy paste", function(e) {
			e.preventDefault();
		});

		$(".address-blob").dblclick(function() {
			$(this).animate({
					color: "#ffffff"
				}, 200);
			document.getSelection().removeAllRanges();
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val($(this).html()).select();
			document.execCommand("copy");
			$temp.remove();
			document.getSelection().removeAllRanges();
			$(this).animate({
					color: "#<?php echo $_SESSION['websitecolor']['highlight']; ?>"
				}, 400);
		});

        $("#sendButton").click(function () {
			var requestmethod = "sendTransaction";
			if ($("#sendToAddress").val().length != 99 || !$("#sendToAddress").val().startsWith("TRTL"))
				requestmethod = "getKey";
			if ($("#sendAmount").val() < 0.01)
			{
				$("#sendStatus").html("Amount must be greated than 0");
				$("#sendStatus").css("color", "red");
				if (!$("#sendStatus").is(":visible"))
					$("#sendStatus").slideDown("slow");
			}
			else if ($("#sendAmount").val() > balance)
			{
				$("#sendStatus").html("Not enough balance");
				$("#sendStatus").css("color", "red");
				if (!$("#sendStatus").is(":visible"))
					$("#sendStatus").slideDown("slow");
			}
			else if ($("#sendFee") < 0.1)
			{
				$("#sendStatus").html("Fee must be at least 0.1 TRTL");
				$("#sendStatus").css("color", "red");
				if (!$("#sendStatus").is(":visible"))
					$("#sendStatus").slideDown("slow");
			}
            else $.post(
                "sendrequest.php",
                {
                    method: requestmethod,
                    address: $("#sendToAddress").val(),
                    amount: round($("#sendAmount").val() * 100),
                    fee: round($("#sendFee").val() * 100),
                    paymentid: $("#sendPaymentId").val()
                },
                function (data) {
					data = JSON.parse(data);
                    if (data.error != null)
					{
						$("#sendStatus").html(data.error.message);
						$("#sendStatus").css("color", "red");
						if (!$("#sendStatus").is(":visible"))
							$("#sendStatus").slideDown("slow");
					}
                    else if (data.result != null)
					{
						$("#sendStatus").html("Success! TX: <a class='tx-link' href='http://turtle-block.com/?hash=" + data.result.transactionHash +
							"#blockchain_transaction'  target='_blank'>" + data.result.transactionHash + "</a>");
						$("#sendStatus").css("color", "<?php echo $_SESSION['websitecolor']['highlight']; ?>");
						if (!$("#sendStatus").is(":visible"))
							$("#sendStatus").slideDown("slow");
						$("#sendAmount").val("0.00");
						$("#sendFee").val("0.10");
					}
                    else if (data.key != null)
					{
						$("#sendStatus").html("Success!<br/><a class='tx-link key-address' title='Click to copy to clipboard'>" +
							document.URL.substr(0,document.URL.lastIndexOf('/')) + "/redeem.php?key=" + data.key + "</a>");
						$("#sendStatus").css("color", "<?php echo $_SESSION['websitecolor']['highlight']; ?>");
						$(".key-address").tooltip();
						$(".key-address").click(function() {
							$(this).animate({
									color: "#ffffff"
								}, 200);
							document.getSelection().removeAllRanges();
							var $temp = $("<input>");
							$("body").append($temp);
							$temp.val($(this).html()).select();
							document.execCommand("copy");
							$temp.remove();
							document.getSelection().removeAllRanges();
							$(this).animate({
									color: "#<?php echo $_SESSION['websitecolor']['highlight']; ?>"
								}, 400);
						});
						if (!$("#sendStatus").is(":visible"))
							$("#sendStatus").slideDown("slow");
						$("#sendAmount").val("0.00");
						$("#sendFee").val("0.10");
					}
                }
            );
        });

        $('.numberonly').keypress(function (event) {
            var $this = $(this);
            if ((event.which != 46 || $this.val().indexOf('.') != -1) &&
                ((event.which < 48 || event.which > 57) &&
                    (event.which != 0 && event.which != 8))) {
                event.preventDefault();
            }

            var text = $(this).val();
            if ((event.which == 46) && (text.indexOf('.') == -1)) {
                setTimeout(function () {
                    if ($this.val().substring($this.val().indexOf('.')).length > 3) {
                        $this.val($this.val().substring(0, $this.val().indexOf('.') + 3));
                    }
                }, 1);
            }

            if ((text.indexOf('.') != -1) &&
                (text.substring(text.indexOf('.')).length > 2) &&
                (event.which != 0 && event.which != 8) &&
                ($(this)[0].selectionStart >= text.length - 2)) {
                event.preventDefault();
            }
        });

        $('.numberonly').bind("paste", function (e) {
            var text = e.originalEvent.clipboardData.getData('Text');
            if ($.isNumeric(text)) {
                if ((text.substring(text.indexOf('.')).length > 3) && (text.indexOf('.') > -1)) {
                    e.preventDefault();
                    $(this).val(text.substring(0, text.indexOf('.') + 3));
                }
            }
            else {
                e.preventDefault();
            }
        });

        $("input").focusin(function() {
            if ($(this).val() == "0" || $(this).val() == "0.00" || $(this).val() == "0.0" || $(this).val() == "0." || $(this).val() == "TRTL Address or Name")
                $(this).val("");
        });

        $("#sendAmount").focusout(function () {
            if ($(this).val() == "")
                $(this).val("0.00");
        });

        $("#sendToAddress").focusout(function () {
            if ($(this).val() == "")
                $(this).val("TRTL Address or Name");
        });
    </script>
</body>
</html>
