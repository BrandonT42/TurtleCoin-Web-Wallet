<?php
require_once "core.php";

if (!isset($_SESSION['username']) || empty($_SESSION['username']))
	echo "{\"sessionStatus\": false, \"availableBalance\": 0, \"lockedAmount\": 0 }";

else {
	$balance = getBalance($_SESSION["address"]);
	$_SESSION["availablebalance"] = $balance["availableBalance"] / 100;
	$_SESSION["lockedamount"] = $balance["lockedAmount"] / 100;
?>
{ "sessionStatus": true, "availableBalance": "<?php echo number_format($_SESSION['availablebalance'], 2); ?>", "lockedAmount": "<?php echo number_format($_SESSION['lockedamount'], 2); ?>" }
<?php } ?>