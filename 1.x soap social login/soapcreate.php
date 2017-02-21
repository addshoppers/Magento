<?php

header("Access-Control-Allow-Origin: HTTP://WWW.EXAMPLE.COM");
//ini_set("log_errors", 1);
//ini_set("error_log", "/tmp/php-error.log");
//ini_set('soap.wsdl_cache_enabled',0);
//ini_set('soap.wsdl_cache_ttl',0);
//error_reporting(E_ALL);


//-------Start Update This Info
//addshoppers api secret
$AddShoppersSecret = "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";

//Magento api user and key and store url
$apiUser = "USER";
$apiKey = "PASS";
$url = "HTTP://WWW.EXAMPLE.COM";
$apiUrl = $url . "/index.php/api/soap/?wsdl"; //use v1 of soap api url


//secret codes for password hashes (you make these up)
$mySecret1 = "1111111111111111111111111111";
$mySecret2 = "2222222222222222222222222222";
//-------END Update This Info



try{
	//get id info and set to vars
	$siteID = $_GET["siteid"];
	$storeID = $_GET["storeid"];
	$groupID = $_GET["groupid"];

	//if ids are not numeric kill the script
	if(!is_numeric ($siteID) || !is_numeric ($storeID) || !is_numeric ($groupID) ){
		die("id not numeric");
	}

	// validate signature
	$params = json_decode($_GET["data"],true);
	
	$signature = null;
	$p = array();

	foreach($params as $key => $value)
	{

    		if($key == "signature")
        		$signature = $value;
    		else
    			$p[] = $key . "=" . $value;

	    	$epos = strpos($key, "_email");
    		if($epos){$email = $value;}

                $fpos = strpos($key, "_firstname");
                if($fpos){$fname = $value;}

                $lpos = strpos($key, "_lastname");
                if($lpos){$lname = $value;}
	}

	asort($p);
	$query = $AddShoppersSecret . implode($p);
	//echo $signature;
	//echo "<br>";
	$hashed = hash("md5", $query);
	if($signature !== $hashed){
        	die("false");
	}

	//--first pass
	//IMPORTANT - NEEDS TO BE UPDATED WITH NEW API FOR SALT STRING***
	$hash1 =$email;
	$pass1 = hash_hmac('sha256', $hash1, $mySecret1);
	$pass2 = hash_hmac('sha256', $pass1, $mySecret2);
	$passout = substr($pass2, 0,12);
	$pass = $passout;

	$filters = array(
		array(
			'email' => array(
				'like' => $email.'%'
			)
		)
	);

	$create = array(
		array(
			'email' => $email,
			'firstname' => $fname,
			'lastname' => $lname,
			'password' => $pass,
			'website_id' => $siteID,
			'store_id' => $storeID,
			'group_id' => $groupID
		)
	);

	$client = new SoapClient($apiUrl , $filters);
	$session = $client->login($apiUser, $apiKey); 

	//-------get info if customer exists
	$result = $client->call($session, 'customer.list', $filters);
	//var_dump($result);
	if(count($result)  > 0){
		//echo "Customer Exists";
		$custID = $result[0]["customer_id"];
		//echo $hash = $result[0]["password_hash"] . "<br>\n";
		$update = array(
                		'customerId' => $custID,
                		'customerData' => array(
                                		'firstname' => $fname,
                                		'lastname' => $lname,
                                		'email' => $email,
                                		'password' => $pass
                                		)
        		);

		//------- Update customer info
        $updated = $client->call($session, 'customer.update', $update);
		echo "true|".$email."|".$pass;
	} else {
		//------- Create customer 
        $created = $client->call($session,'customer.create', $create);
		echo "true|".$email."|".$pass;
	}

	//echo email and pass
	$client->endSession($session);

} catch (Exception $e) {
    	echo 'Caught exception: ',  $e->getMessage(), "\n";
}


?>
