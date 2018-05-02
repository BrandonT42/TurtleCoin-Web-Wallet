<?php
// Begin a session
session_start();

/*************************************/
/*** DEFINED VARIABLES ***************/
/*************************************/

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'WebWallet');
define('DB_PASSWORD', 'WebWalletPassword');
define('DB_NAME', 'shellwalletweb');

// Wallet variables
require_once "walletd.php";

// Website variables
define("WEBSITE_TITLE", "Shell Web");
$footermessage = array(
	"Turtles are nature's mobile homes.",
	"1 TRTL = 1 TRTL",
	"RockSteady's AI is coming along nicely."
);
$websitecolors = array(
	"dark" => array(
		"highlight"      => "#2ecc71",
		"textcolor"      => "#ffffff",
		"bodycolor"      => "#a2a2a2",
		"headercolor"    => "#2f2f2f",
		"navcolor"       => "#ffffff",
		"darkcolor"      => "#1d1d1d",
		"darkcolortext"  => "#a2a2a2",
		"lightcolor"     => "#212121",
		"lightcolortext" => "#a2a2a2",
		"footercolor"    => "#000000",
		"bordercolor"    => "transparent",
		"disabled"       => "#a2a2a2"
	),
	"light" => array(
		"highlight"      => "#2ecc71",
		"textcolor"      => "#000000",
		"bodycolor"      => "#000000",
		"headercolor"    => "#2f2f2f",
		"navcolor"       => "#ffffff",
		"darkcolor"      => "#1d1d1d",
		"darkcolortext"  => "#a2a2a2",
		"lightcolor"     => "#fafafa",
		"lightcolortext" => "#000000",
		"footercolor"    => "#000000",
		"bordercolor"    => "#e0e0e0",
		"disabled"       => "#c1c1c1"
	)
);
if (empty($_SESSION['websitecolor']))
	$_SESSION['websitecolor'] = $websitecolors["dark"];

/*************************************/
/*** DATABASE FUNCTIONS **************/
/*************************************/

// Creates a user with a new address
function attemptCreate($username, $password, &$username_err, &$password_err)
{
	// Create a connection
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
	// Prepare a select statement
	if ($statement = mysqli_prepare($mysql, "SELECT id FROM users WHERE username = ?"))
	{
		// Bind parameters
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = trim($username);
            
		// Check if username already exists in database
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1) $username_err = "This username is already taken.";
		}

		// Failed to reach database
		else
		{
			$username_err = "Something went wrong, please try again later.";
			$password_err = "";
		}
	}
         
	// Close statement
	mysqli_stmt_close($statement);
    
	// Check input errors before inserting in database
	if (empty($username_err) && empty($password_err) && empty($confirm_password_err))
	{
		// Prepare an insert statement
		if ($statement = mysqli_prepare($mysql, "INSERT INTO users (username, password, address, createdat, lastlogin) VALUES (?, ?, ?, ?, ?)"))
		{
			// Bind parameters
			mysqli_stmt_bind_param($statement, "sssss", $param_username, $param_password, $param_address, $param_createdat, $param_lastlogin);
			$param_username = $username;
			$param_password = password_hash($password, PASSWORD_DEFAULT);
			$param_address = createAddress();
			$param_createdat = date("m/d/Y h:i:s a");
			$param_lastlogin = date("m/d/Y h:i:s a");
            
			// Add user to database
			if ($param_address != "" && mysqli_stmt_execute($statement))
			{
				$_SESSION["username"] = $username; 
				$_SESSION["address"] = $param_address;
				$_SESSION["availablebalance"] = 0.0;
				$_SESSION["lockedamount"] = 0.0; 
				header("location: index.php");
			}

			// Failed to reach database or failed to create address
			else
			{
				$username_err = "Something went wrong, please try again later.";
				$password_err = "";
			}
		}
         
		// Close statement
		mysqli_stmt_close($statement);
	}
    
	// Close connection
	mysqli_close($mysql);
}

// Logs into user profile
function attemptLogin($username, $password, &$username_err, &$password_err)
{
	// Create a connection
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
	// Prepare a select statement
	if ($statement = mysqli_prepare($mysql, "SELECT username, password, address FROM users WHERE username = ?"))
	{
		// Bind parameters
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
            
		// Attempt to execute the prepared statement
		if (mysqli_stmt_execute($statement))
		{
			// Store result
			mysqli_stmt_store_result($statement);
                
			// Check if username exists, if yes then verify password
			if (mysqli_stmt_num_rows($statement) == 1)
			{                    
				// Bind result variables
				mysqli_stmt_bind_result($statement, $username, $hashed_password, $address);
				if (mysqli_stmt_fetch($statement))
				{
					if (password_verify($password, $hashed_password))
					{
						$_SESSION['username'] = $username; 
						$_SESSION['address'] = $address;
						$balance = getBalance($address);
						$_SESSION['availablebalance'] = $balance["availableBalance"] / 100;
						$_SESSION['lockedamount'] = $balance["lockedAmount"] / 100;
						updateLastLogin($username);
						header("location: index.php");
					}

					// Failed to reach database
					else
					{
						// Display an error message if password is not valid
						$password_err = 'Password is incorrect.';
					}
				}
			}
			else
			{
				// Display an error message if username doesn't exist
				$username_err = 'No account found with that username.';
			}
		}

		// Failed to reach database
		else
		{
			$username_err = "Something went wrong, please try again later.";
			$password_err = "";
		}
	}
        
	// Close statement
	mysqli_stmt_close($statement);
    
	// Close connection
	mysqli_close($mysql);
}

// Updates last login timestamp
function updateLastLogin($username)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "UPDATE users SET lastlogin = ? WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "ss", date('m/d/Y h:i:s a'), $username);
		mysqli_stmt_execute($statement);
	}
	mysqli_stmt_close($statement);
	mysqli_close($mysql);
}

// Updates a user password
function updatePassword($username, $password)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "UPDATE users SET password = ? WHERE username = ?"))
	{
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		mysqli_stmt_bind_param($statement, "ss", $hashed_password, $username);
		mysqli_stmt_execute($statement);
	}
	mysqli_stmt_close($statement);
	mysqli_close($mysql);
}

// Verifies a user password
function verifyPassword($username, $password)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "SELECT username, password, address FROM users WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $username, $hashed_password, $address);
				if (mysqli_stmt_fetch($statement))
				{
					if (password_verify($password, $hashed_password))
					{
						mysqli_stmt_close($statement);
						mysqli_close($mysql);
						return true;
					}
				}
			}
		}
	}
	mysqli_stmt_close($statement);
	mysqli_close($mysql);
	return false;
}

/*************************************/
/*** WALLET FUNCTIONS ****************/
/*************************************/

// Sends an RPC request
function SendRequest($Request)
{
	$Request["jsonrpc"] = "2.0";
	$Request["id"] = "10";
	$Request["password"] = WALLET_PASSWORD;
	$content = json_encode($Request, JSON_NUMERIC_CHECK);
	$curl = curl_init(WALLET_URL);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
		array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	if (!empty($json_response))
	{
		return json_decode($json_response, true);
	}
	else {
		return json_decode("{\"error\":\"No wallet connection.\"}", true);
	}
}

// Sends a transaction
function sendTransaction($sendtoaddress, $sendfromaddress, $amount, $fee, $paymentid)
{
	$options = array(
		'method'   => 'sendTransaction',
		'params'   => [
			'anonymity' => '4',
			'fee'       => $fee,
			'paymentId' => $paymentid,
			'addresses' => [
				$sendfromaddress
			],
			'transfers' => [
				[
					'address' => $sendtoaddress,
					'amount' => $amount
				]
			]
		]
	);
	return SendRequest($options);
}

// Creates a new address, returns blank if it fails
function createAddress()
{
	$options = array(
		'method'   => 'createAddress'
	);
	$response = SendRequest($options);
	if (!empty($response["result"]))
		return SendRequest($options)["result"]["address"];
	else return "";
}

// Gets balances belonging to an address
function getBalance($address)
{
	$options = array(
		'method'   => 'getBalance',
		'params'   => [
			'address' => $address
		]
	);
	$result = SendRequest($options);
	if (!empty($result["result"]))
		return $result["result"];
	else return array(
		'availableBalance' => 0,
		'lockedAmount' => 0
	);
}

/*************************************/
/*** UTILITY FUNCTIONS ***************/
/*************************************/

// Generates a random string
function randomString($length = 255, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $pieces = [];
    $max = mb_strlen($characters, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $characters[random_int(0, $max)];
    }
    return implode('', $pieces);
}

?>