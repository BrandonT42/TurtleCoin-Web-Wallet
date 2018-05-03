<?php
require_once "core.php";

if (isset($_SESSION["username"]) || !empty($_SESSION["username"]))
{
	header("location: index.php");
	exit;
}

else
{
	// GET request received
	if ($_SERVER["REQUEST_METHOD"] == "GET")
	{
		// TODO : Check database for supplied key

		// TODO : If not found, redirect to login.php

		// TODO : If found, get address for that key, get address balance, prompt for username and password (like create form on login.php), say they have X turtle waiting for them

		// TODO : Change username and password to supplied variables, remove key from database, give password recovery key prompt
	}
}
?>