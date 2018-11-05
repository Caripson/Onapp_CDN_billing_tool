<?php
// fix for crontab
set_include_path("/tmp/Onapp_CDN_billing_tool/");

$production_state;
 //Show consumption for all CP user,
$customerXML;

$privateConf="billingSettingsIPO.xml";
if (file_exists($privateConf)) {
    global $customerXML;
    $production_state="IPO";
    $customerXML="billingSettingsIPO.xml";
    include "settings_dev.php";

} else {
    $customerXML="billingSettings.xml";
    include "settings.php";
}



//Include settings
include "mail_report.php";
include "writefile.php";
include "csvexport.php";

// set HTTP header
$headers = array(
    'Content-Type: application/json',
);
// set url to onapp CP
$service_url = $my_onappURL;
// set date range
$data = array("period" => array("startdate"=>getStartAndEndDate("start"),"enddate"=>getStartAndEndDate("end")));
// encode to json
$data_string = json_encode($data);
//do some magic and request your CP API
$curl= curl_init($my_onappURL);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_USERPWD, $my_email.":".$my_apiKey);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
  );
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
// save webservice response to a vaibale
$result = curl_exec($curl);
//decode the response to json
$raw_data = json_decode($result);
// setup a array where we will store users id and GB transfer
$userdata = array();

// setup a vaibale for user id's
$user_id = 0;


function traverse(&$objOrArray)
{
  // setup global variable so that we can access the values outside the function
  global $user_id;
  global $userdata;

// start the loop
  foreach ($objOrArray as $key => &$value)
	{
    // if value is a array or object. Send it to this fuction again
	if (is_array($value) || is_object($value))
		{
			traverse($value);
		}
	else
  {
    //colled the user id
    if ($key == "user_id")
    {
      $user_id = $value;
      // check if user id exist in userdata array
      if (userExistInArray($value))
      {
        //echo "user is allreday in array";
      }
       else
      {
        // if not, add the user to the array
        array_push($userdata, $value);
      }


    }
    // add cached data to users
    if ($key == "cached")
    {
          $userdata[$user_id] = calc($userdata[$user_id],$value,$user_id);
    }
    // add none cached data to user
    if ($key == "non_cached")
    {
        $userdata[$user_id] = calc($userdata[$user_id],$value,$user_id);
    }

    }
  }
}
// sum data for user
function calc(&$nowGB,&$moreGB,&$userId)
{
  $calcMe = $nowGB + $moreGB;
  // echo "We have now add ". $calcMe . "to user " . $userId;
  return $calcMe;
}
// check if user exist in Array
function  userExistInArray(&$userId)
{
  global $userdata;
  $user_exist=false;
  foreach ($userdata  as &$value) {
    if ($value == $userId)
    {
      $user_exist=true;
    }
  }
  return $user_exist;
}
// Start creating the CSV
function create_CVS(&$objOrArray)
{
  global $XMLarray;
  global $currentUserID;
  global $cvsString;
  global $traceOutput;
  global $production_state;
  global $csvURL;

if ($production_state=="IPO")
{
  $txt = "FromDate;ToDate;NBS_ID;Customer;ProductID;Product;InvoiceText;Contract;Empty1;Empty2;OverUsage;UnitPrice;AmountUsage;Currency";
  echo $txt . "\n";
  writeFile($csvURL,$txt . "\n");
}else{
  $txt = "FromDate;ToDate;NBS_ID;Customer;ProductID;Product;InvoiceText;Contract;Empty1;Empty2;OverUsage;UnitPrice;AmountUsage;Currency";
  echo $txt . "\n";
  writeFile($csvURL,$txt . "\n");
}
  foreach ($objOrArray as $key => &$value)
  {
      // from CP v6 Onapp changed value from GB to bytes   
    $bytesToGb=$value/1000000000;
  $avrunda=intval($bytesToGb);

    buildCVS($XMLarray,$key,$avrunda);
    $currentUserID=false;
  }
  echo "\n\n";
  if ($traceOutput){
  foreach ($objOrArray as $key => &$value)
  {
  // from CP v6 Onapp changed value from GB to bytes
  $bytesToGb=$value/1000000000;
  $avrunda=intval($bytesToGb);

    echo "user : " . $key . " GB usage : " . $avrunda."\n" ;
  }
  }
echo "\n\n";
}

traverse($raw_data);// access webservice, build a user obkect with data
create_CVS($userdata); // create cvs
mailIt();
?>
