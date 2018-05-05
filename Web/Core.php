<?php
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
define('MINIMUM_FEE', 0.1);
define('DEFAULT_MIXIN', 4);
define('WALLET_OPTIMIZER_PATH', 'c:/wamp/wallet-optimizer.exe');

// Security variables
define('SESSION_TIMEOUT', 600); // 10 minutes
define('REGENERATION_TIMEOUT', 180); // 3 minutes

// Website variables
define("WEBSITE_TITLE", "Web Wallet (TESTNET)");
define('BLOCK_EXPLORER', 'https://testnet-vico.turtlecoin.ws/blocks/?hash=');
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

/*************************************/
/*** SESSION CONTROL *****************/
/*************************************/

// Begin a session
session_start();

// Regenerate cookie id if regenerate timeout has elapsed
if (!isset($_SESSION['LAST_REGEN'])) $_SESSION['LAST_REGEN'] = time();
elseif (time() - $_SESSION['LAST_REGEN'] > REGENERATION_TIMEOUT)
{
    session_regenerate_id(true);
    $_SESSION['LAST_REGEN'] = time();
}

// Destroy session if session timeout has elapsed
if (!isset($_SESSION['LAST_ACTIVITY'])) $_SESSION['LAST_ACTIVITY'] = time();
elseif (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)
{
	resetSession();
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Set default website colors
if (empty($_SESSION['websitecolor']))
	$_SESSION['websitecolor'] = $websitecolors["dark"];

// Resets and destroys a session
function resetSession()
{
	session_unset();
	setcookie("PHPSESSID", session_id(), time() - 3600);
    session_destroy();
}

/*************************************/
/*** DATABASE FUNCTIONS **************/
/*************************************/

// Creates a user with a new address
function attemptCreate($username, $password, &$username_err, &$password_err, &$confirm_password_err)
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
			if (mysqli_stmt_num_rows($statement) > 0) $username_err = "This username is already taken.";
		}

		// Failed to reach database
		else
		{
			$username_err = "Something went wrong, please try again later.";
			$password_err = "";
		}
		
		// Close statement
		mysqli_stmt_close($statement);
	}
    
	// Check input errors before inserting in database
	if (empty($username_err) && empty($password_err) && empty($confirm_password_err))
	{
		// Prepare an insert statement
		if ($statement = mysqli_prepare($mysql, "INSERT INTO users (name, username, password, uid, address, createdat, lastlogin) VALUES (?, ?, ?, ?, ?, ?, ?)"))
		{
			// Bind parameters
			mysqli_stmt_bind_param($statement, "sssssss", $param_name, $param_username, $param_password, $param_uid, $param_address, $param_createdat, $param_lastlogin);
			$param_name = "";
			$param_username = $username;
			$param_password = password_hash($password, PASSWORD_DEFAULT);
			$param_uid = randomString();
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
				$_SESSION['LAST_ACTIVITY'] = time();
				header("location: index.php");
			}

			// Failed to reach database or failed to create address
			else
			{
				$username_err = mysql_error($statement);//"Something went wrong, please try again later.";
				$password_err = "";
			}
			
			// Close statement
			mysqli_stmt_close($statement);
		}
	}
    
	// Close connection
	mysqli_close($mysql);
}

// Creates a user with a redeemed address
function attemptRedeem($key, $username, $password, &$username_err, &$password_err, &$confirm_password_err)
{
	// Create a connection
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
	// Prepare a select statement
	if (empty($key_err) && $statement = mysqli_prepare($mysql, "SELECT password FROM users WHERE username = ?"))
	{
		// Bind parameters
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = trim($username);
            
		// Check if username already exists in database
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) > 0) $username_err = "This username is already taken.";
		}

		// Failed to reach database
		else
		{
			$username_err = "Something went wrong, please try again later.";
			$password_err = "";
		}
		
		// Close statement
		mysqli_stmt_close($statement);
	}
    
	// Check input errors before inserting in database
	if (empty($username_err) && empty($password_err) && empty($confirm_password_err))
	{
		// Prepare an update statement
		if ($statement = mysqli_prepare($mysql, "UPDATE users SET name=?, username=?, password=?, uid=?, createdat=?, lastlogin=? WHERE uid = ? AND (password = null or password = '')"))
		{
			// Bind parameters
			mysqli_stmt_bind_param($statement, "sssssss", $param_name, $param_username, $param_password, $param_uid, $param_createdat, $param_lastlogin, $param_key);
			$param_name = "";
			$param_username = $username;
			$param_password = password_hash($password, PASSWORD_DEFAULT);
			$param_uid = randomString();
			$param_createdat = date("m/d/Y h:i:s a");
			$param_lastlogin = date("m/d/Y h:i:s a");
			$param_key = $key;
            
			// Add user to database
			if (mysqli_stmt_execute($statement))
			{
				$_SESSION["username"] = $username;
				$_SESSION["address"] = getUserAddress($username);
				$balance = getBalance($address);
				$_SESSION['availablebalance'] = $balance["availableBalance"] / 100;
				$_SESSION['lockedamount'] = $balance["lockedAmount"] / 100;
				$_SESSION['LAST_ACTIVITY'] = time();
				header("location: index.php");
			}

			// Failed to reach database or failed to create address
			else
			{
				$username_err = "Something went wrong, please try again later.";
				$password_err = "";
			}
         
			// Close statement
			mysqli_stmt_close($statement);
		}
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
	if ($statement = mysqli_prepare($mysql, "SELECT username, password, address, verified FROM users WHERE username = ?"))
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
				mysqli_stmt_bind_result($statement, $username, $hashed_password, $address, $verified);
				if (mysqli_stmt_fetch($statement))
				{
					if (password_verify($password, $hashed_password))
					{
						$_SESSION['username'] = $username; 
						$_SESSION['address'] = $address;
						$_SESSION['verified'] = $verified;
						$balance = getBalance($address);
						$_SESSION['availablebalance'] = $balance["availableBalance"] / 100;
						$_SESSION['lockedamount'] = $balance["lockedAmount"] / 100;
						updateLastLogin($username);
						$_SESSION['LAST_ACTIVITY'] = time();
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
        
		// Close statement
		mysqli_stmt_close($statement);
	}
    
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
		mysqli_stmt_close($statement);
	}
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
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
}

// Gets a user's address'
function getUserAddress($username)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "SELECT address FROM users WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $address);
				if (mysqli_stmt_fetch($statement))
				{
					mysqli_stmt_close($statement);
					mysqli_close($mysql);
					return $address;
				}
			}
		}
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
	return "";
}

// Verifies a user password
function verifyPassword($username, $password)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "SELECT password FROM users WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $hashed_password);
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
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
	return false;
}

// Verifies a user password recovery key
function verifyRecoveryKey($username, $key)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	// Check unhashed key
	if ($statement = mysqli_prepare($mysql, "SELECT uid FROM users WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $uid);
				if (mysqli_stmt_fetch($statement))
				{
					if ($uid == $key || $uid == password_verify($key, $uid))
					{
						mysqli_stmt_close($statement);
						mysqli_close($mysql);
						return true;
					}
				}
			}
		}
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
	return false;
}

// Verifies a user password
function verifyRedeemKey($key)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "SELECT name, address FROM users WHERE uid = ? AND (password = null or password = '') AND (username = null or username = '')"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_key);
		$param_key = $key;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $name, $address);
				if (mysqli_stmt_fetch($statement))
				{
					mysqli_stmt_close($statement);
					mysqli_close($mysql);
					return array(
							'name' => $name,
							'address' => $address
						);
				}
			}
		}
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
	return "";
}

// Creates a redeemable key
function createRedeem($name, $amount, $fee)
{
	$_SESSION['LAST_ACTIVITY'] = time();
	// Create a new address
	$sendtoaddress = createAddress();
	if ($sendtoaddress == "") return "Error: Failed to create address";

	// Send transaction
	$result = sendTransaction($sendtoaddress, $_SESSION['address'], $amount, $fee, "");
	if (empty($result["result"])) return "Error sending transaction: " . $result["error"]["message"];

	// Add to database
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "INSERT INTO users (username, password, name, uid, address, createdat) VALUES (?, ?, ?, ?, ?, ?)"))
	{
		// Bind parameters
		mysqli_stmt_bind_param($statement, "ssssss", $param_username, $param_password, $param_name, $param_uid, $param_address, $param_createdat);
		$param_username = "";
		$param_password = "";
		$param_name = $name;
		$param_uid = randomString();
		$param_address = $sendtoaddress;
		$param_createdat = date("m/d/Y h:i:s a");
            
		// Add user to database
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_close($statement);
			mysqli_close($mysql);
			return $param_uid;
		}

		// Failed to reach database or failed to create address
		else
		{
			$result = mysqli_stmt_error($statement);
			mysqli_stmt_close($statement);
			mysqli_close($mysql);
			return $result;
		}
			
		// Close statement
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
}

// Sets a user as verified
function verifyUser($username)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "UPDATE users SET verified = 1, uid = ? WHERE username = ?"))
	{
		$hashed_key = password_hash(getUserKey($username), PASSWORD_DEFAULT);
		mysqli_stmt_bind_param($statement, "ss", $hashed_key, $username);
		mysqli_stmt_execute($statement);
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
}

// Gets a user's key from the database
function getUserKey($username)
{
	$mysql = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($statement = mysqli_prepare($mysql, "SELECT uid FROM users WHERE username = ?"))
	{
		mysqli_stmt_bind_param($statement, "s", $param_username);
		$param_username = $username;
		if (mysqli_stmt_execute($statement))
		{
			mysqli_stmt_store_result($statement);
			if (mysqli_stmt_num_rows($statement) == 1)
			{
				mysqli_stmt_bind_result($statement, $key);
				if (mysqli_stmt_fetch($statement))
				{
					mysqli_stmt_close($statement);
					mysqli_close($mysql);
					return $key;
				}
			}
		}
		mysqli_stmt_close($statement);
	}
	mysqli_close($mysql);
	return "";
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
	$_SESSION['LAST_ACTIVITY'] = time();
	$options = array(
		'method'   => 'sendTransaction',
		'params'   => [
			'anonymity' => DEFAULT_MIXIN,
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

// Performs a fusion transaction with the best possible threshold
function sendFusionTransaction($address)
{
	$_SESSION['LAST_ACTIVITY'] = time();
	// Get balance
	$balance = getBalance($address);
	if ($balance['availableBalance'] > 0)
	{
		// Set initial variables
		$bestThreshold = $threshold = $balance['availableBalance'];
		$optimizable = 0;

		// Loop the estimate while dividing in half to find optimal values
		while ($threshold > (MINIMUM_FEE * 100))
		{
			$request = array(
				'method'   => 'estimateFusion',
				'params'   => [
					'threshold' => $threshold,
					'addresses' => [
						$address
					]
				]
			);
			$estimate = SendRequest($request);
			if (isset($estimate['result']) && $estimate['result']['fusionReadyCount'] > $optimizable)
			{
				$optimizable = $estimate['result']['fusionReadyCount'];
				$bestThreshold = $threshold;
			}
			$threshold /= 2;
		}

		// Check if wallet can be optimized
		if ($optimizable == 0) return "";

		// Send fusion transaction
		$request = array(
			'method' => 'sendFusionTransaction',
			'params' => [
				'anonymity' => DEFAULT_MIXIN,
				'threshold' => $bestThreshold,
				'addresses' => [
					$address
				]
			]
		);
		$result = SendRequest($request);
		if (!empty($result['error'])) return "";
		else return $result['result']['transactionHash'];
	}
}

// Loops through and fully optimizes an address
function fullOptimizeAddress($address)
{
	$_SESSION['LAST_ACTIVITY'] = time();
	// Get balance
	$balance = getBalance($address);
	if ($balance['availableBalance'] > 0)
	{
		// Open wallet optimizer ulitity
		exec(WALLET_OPTIMIZER_PATH . " " . WALLET_URL . " " . WALLET_PASSWORD . " " . $address . " " . $balance['availableBalance']);
	}
}

// Performs a single fusion, returns true if successful, false if not
function quickOptimizeAddress($address)
{
	//if (empty(sendFusionTransaction($address))) return false;
	//else return true;
	return sendFusionTransaction($address);
}

/*************************************/
/*** UTILITY FUNCTIONS ***************/
/*************************************/

// Generates a random string
function randomString($length = 100, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $pieces = [];
    $max = mb_strlen($characters, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $characters[random_int(0, $max)];
    }
    return implode('', $pieces);
}

?>