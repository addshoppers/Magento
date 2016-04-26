<?php
// AddShoppers Social Shopper Login
error_reporting(E_ALL | E_STRICT);
// **********TODO: Set your AddShoppers API Secret Key below (found in https://www.addshoppers.com/merchants under Settings -> API)
$AddShoppersSecret = "XXXXXXXXXXXXXXXXX";

//-----get info from url
$urluser = $_GET["asusrnm"];
$urlemail = $_GET["aseml"];


// validate signature 
$params = json_decode($_GET["data"]);
$signature = null;
$p = array();
 
foreach($params as $key => $value)
{
    if($key == "signature")
        $signature = $value;
    else
    	$p[] = $key . "=" . $value;
    	$pos = strpos($key, "_email");
    	if($pos){
        	$urlemail = $value;
    	}

}
asort($p);
$query = $AddShoppersSecret . implode($p);
$hashed = hash("md5", $query);
if($signature !== $hashed)
        die("Invalid AddShoppers key or bad signature request.");
        
// signature validated, this is a valid request... continue on



//-----check if	a name and email exist
if(!$urluser){
        //die();
}
if(!$urlemail){
        //die();
}

//-----split name into first name and last name
$arr = explode('_',trim($urluser));
$firstname = $arr[0];
$lastname = array_shift($arr);
$lastname = implode(" ", $arr);

$email = $urlemail;

//-----begin magento create account stuff

$mageFilename = './app/Mage.php';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}

require_once $mageFilename;
Varien_Profiler::enable();
//Mage::setIsDeveloperMode(true);
//ini_set('display_errors', 1);
umask(0);


Mage::app();


$storeAppId = 'NONE';
$currentRequestURL = $_SERVER['HTTP_HOST'];
$currentRequestURL = str_replace("http://","",$currentRequestURL);
$currentRequestURL = str_replace("https://","",$currentRequestURL);
$currentRequestURL = $currentRequestURL . '/';

foreach (Mage::app()->getWebsites() as $website) {
    foreach ($website->getGroups() as $group) {
        $stores = $group->getStores();
        foreach ($stores as $store) {
            $eachStoreUrl = $store->getBaseUrl();
            $eachStoreUrl = str_replace("http://","",$eachStoreUrl);
            $eachStoreUrl = str_replace("https://","",$eachStoreUrl);
            if($currentRequestURL == $eachStoreUrl){
              $storeAppId = $store->getStoreId();
            }
        }
    }
}


if($storeAppId){
  if($storeAppId != 'NONE'){
    Mage::app($storeAppId);
    Mage::app()->setCurrentStore($storeAppId);
  }
}

// //-----check if user is already logged in, if so stop
// Mage::getSingleton('core/session', array('name' => 'frontend'));
// $sesscheck = Mage::getSingleton('customer/session', array('name'=>'frontend'));


// if($sesscheck->isLoggedIn()){   
//   echo "false";
// 	die();
// }



$customer_email = $email;  	// email adress that will pass by the questionaire 
$customer_fname = $firstname;   // first name from api 
$customer_lname = $lastname;    // last name from api 
$passwordLength = 10;           // the lenght of autogenerated password

//-------PASSWORD STUFF
$mySecret1 = "XXXXXXXXXXXXXXXXX";
$mySecret2 = "XXXXXXXXXXXXXXXXX";
//--first pass
$hash1 =$userEmail; //This creates a more secure hash
$pass1 = hash_hmac('sha256', $hash1, $mySecret1);
//second pass
$pass2 = hash_hmac('sha256', $pass1, $mySecret2);
$passout = substr($pass2, 0,12);



$customer = Mage::getModel('customer/customer');
$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
$customer->loadByEmail($customer_email);

$newcust = false;


if(!$customer->getId()) {
	//setting data such as email, firstname, lastname, and password 		
  	$customer->setEmail($customer_email); 
  	$customer->setFirstname($customer_fname);
  	$customer->setLastname($customer_lname);
  	//$pass = $customer->generatePassword($passwordLength);
    $pass = $passout;
  	//$customer->setPassword($customer->generatePassword($passwordLength));
  	$customer->setPassword($pass);
//echo $pass . "|";
	try{
  	//the save the data and send the new account email.    
  		$customer->save();
  		$customer->setConfirmation(null);
  		$customer->save(); 
  		$customer->sendNewAccountEmail();
	}
	catch(Exception $ex){
 		echo "false";
	}
}
else {
//set new password
//$pass = $customer->generatePassword($passwordLength);
  $pass = $passout;
  //echo "NEW|";
$customer->setPassword($pass);
$customer->save();
$customer->setConfirmation(null);
$customer->save(); 
}
echo "true|";
echo $pass;
//echo "|";
//echo "abcd";
//echo Mage::getSingleton('core/session')->getFormKey();
// echo Mage::helper('wishlist')->getAddUrl($product);

 
?>
