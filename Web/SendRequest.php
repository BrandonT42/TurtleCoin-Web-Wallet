<?php
require_once 'core.php';

if (!isset($_SESSION['username']) || empty($_SESSION['username']))
	echo "{'error':'bad request'}";

elseif ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST["method"] == "sendTransaction")
	{
		$address = trim($_POST["address"]);
		$amount = $_POST["amount"];
		$fee = $_POST["fee"];
		$paymentid = "";
		if (isset($_POST["paymentid"]))
		{
			$paymentid = trim($_POST["paymentid"]);
		}
		echo json_encode(sendTransaction($address, $_SESSION["address"], $amount, $fee, $paymentid));
	}
	else echo "{'error':'bad request'}";
}
else echo "{'error':'bad request'}";
?>