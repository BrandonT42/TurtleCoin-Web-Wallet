<?php
require_once 'core.php';

if (!isset($_SESSION['username']) || empty($_SESSION['username']))
	echo '{"error":{"message":"bad request"}}';

elseif ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST["method"] == "sendTransaction")
	{
		$address = trim($_POST["address"]);
		$amount = trim($_POST["amount"]);
		$fee = trim($_POST["fee"]);
		$paymentid = "";
		if (isset($_POST["paymentid"]))
			$paymentid = trim($_POST["paymentid"]);
		echo json_encode(sendTransaction($address, $_SESSION["address"], $amount, $fee, $paymentid));
	}
	elseif ($_POST["method"] == "getKey")
	{
		$name = trim($_POST["address"]);
		$amount = trim($_POST["amount"]);
		$fee = trim($_POST["fee"]);
		$result = array(
			'key' => createRedeem($name, $amount, $fee)
		);
		echo json_encode($result);
	}
	elseif ($_POST["method"] == "quickOptimize")
	{
		$result = array(
			'success' => quickOptimizeAddress($_SESSION['address'])
		);
		echo json_encode($result);
	}
	elseif ($_POST["method"] == "fullOptimize")
	{
		fullOptimizeAddress($_SESSION['address']);
		echo "{}";
	}
	else echo '{"error":{"message":"bad request"}}';
}
else echo '{"error":{"message":"bad request"}}';
?>