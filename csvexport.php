<?php

//Setup URL for XML file. Can be local or external ex webservice.
$url = $customerXML;
//we set a header for the xml file before do the request
$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
//lets load the xml and put in a array :)
$xml = file_get_contents($url, false, $context);
$xml = simplexml_load_string($xml);
$XMLarray = json_decode(json_encode((array)$xml), TRUE);


$currentUserID=false; //this varibale is used to make sure that we process complete the process of a user data
$committedvolume; //we need to set this varibale to global so that we can calc overage
$cvsString=""; // the csv string we print
$overuse=0; //if overuse from customer. Set put it here. So that we can calc the price in the next loop
$userIdWeWorkWith=0; // just a check that we complete a user before we process next
$HTMLmessage="";
$HTMLmessageUser="";
$HTMLmessageBandwidth="";
$HTMLmessagePrice="";

// this function is called from runme.php
function buildCVS(&$XMLarray,$userID,$gbtransfer)
{
        global $XMLarray; //the user array with data from xml
        getUserContractSettings($XMLarray,$userID,$gbtransfer); // begin the process
}
//get user contract settings and create the $cvsString
function getUserContractSettings(&$objOrArray,$userID,$gbtransfer)
{
        // some global variables
        global $production_state;
        global $currentUserID;
        global $committedvolume;
        global $cvsString;
        global $overuse;
        global $userIdWeWorkWith;
        global $csvURL;

        global $HTMLmessage;
        global $HTMLmessageUser;
        global $HTMLmessageBandwidth;
        global $HTMLmessagePrice;



      // start the foreach,
       foreach ($objOrArray as $key => &$value)
          {
          if (is_array($value) || is_object($value))
          {
                getUserContractSettings($value,$userID,$gbtransfer);
          }
          else
          {
                  // get Onapp user id
                  if ($key=="id")
                  {
                   if ($value==$userID)
                   {
                   //echo "we found settings for this user \n";
                   $currentUserID=true;
                   $userIdWeWorkWith=$value;
                   }
                   else
                   {
                     //echo "we did not find the settings for this user \n";
                   }
                  }

                  if ($currentUserID){
                    if ($userIdWeWorkWith==$userID )
                    {
                          if ($key=="customerId")
                          {
                          $cvsString=$cvsString . getStartAndEndDate("start").";";
                          $cvsString=$cvsString . getStartAndEndDate("stop").";";
                          $cvsString=$cvsString . $value . ";";
                          }
                          if ($key=="customer")
                          {
                           $cvsString=$cvsString . $value . ";";
                           $HTMLmessageUser="<b>" . $value . "</b>&nbsp";
                          }
                          if ($key=="productId")
                          {
                          $cvsString=$cvsString . $value . ";";
                          }
                          if ($key=="service")
                          {
                          $cvsString=$cvsString . $value . ";";
                          }
                          if ($key=="comments")
                          {
                          $cvsString=$cvsString . getDateYear(). "-". fixdate(getDateMonth()-1) . " " . $value . ";";
                          }
                          if ($key=="ordernumber")
                          {
                          $cvsString=$cvsString . $value . ";";
                          }
                          if ($key=="committedvolume")
                          {
                          $committedvolume=$value;

                          if($production_state=="IPO")
                          {
                            $cvsString=$cvsString . "0;";
                            $cvsString=$cvsString . "0;";
                          }else {
                            $cvsString=$cvsString . $value . ";";
                            $cvsString=$cvsString . $gbtransfer . ";";
                          }

                          if ($gbtransfer > $committedvolume)
                          {
                          $overuse=$gbtransfer-$committedvolume;
                          $HTMLmessageBandwidth=$overuse . " GB &nbsp;";
                          } else {
                          $overuse=0;//$committedvolume-$gbtransfer;
                          }
                          $cvsString=$cvsString . $overuse . ";";

                          }
                          if ($key=="price_gb_peak_usage")
                          {
                          $cvsString=$cvsString . str_replace(".",",", $value) . ";";
                          if ($overuse>=1)
                          {
                            $calc=$overuse*$value;
                            $cvsString=$cvsString . str_replace(".",",", $calc)  . ";";
                            $HTMLmessagePrice=$calc . "&nbsp;";

                          }else{
                                  $cvsString=$cvsString .  "0;";
                          }
                          }
                          if ($key=="price_gb_usage")
                          {
                            if($production_state=="IPO")
                            {
                                //$cvsString=$cvsString . $value . ",";
                            }else {
                                $cvsString=$cvsString . str_replace(".",",", $value) . ";";
                            }
                          }
                          if ($key=="currency")
                          {
                          $cvsString=$cvsString . $value ;
                          if ($production_state=="IPO")
                          {
                            if ($overuse >= 1){
                                $HTMLmessage = $HTMLmessage . $HTMLmessageUser . $HTMLmessageBandwidth . $HTMLmessagePrice . $value ."<br>";
                                $txt = $cvsString . "\n";
                                echo $cvsString . "\n";
                                appendtofile($csvURL,$txt);
                            }
                            $cvsString="";
                            $userIdWeWorkWith=0;
                            $currentUserID=false;
                          }else {
                            $HTMLmessage = $HTMLmessage . $HTMLmessageUser . $HTMLmessageBandwidth . $HTMLmessagePrice . $value ."<br>";
                            $txt = $cvsString . "\n";
                            echo $cvsString . "\n";
                            appendtofile($csvURL,$txt);
                            $cvsString="";
                            $userIdWeWorkWith=0;
                            $currentUserID=false;
                          }


                          }
                          }


                  }
          }
}}


?>
