<?php

$my_email = "your@email.com";
$my_apiKey = "API_KEY_FROM_ONAPP_CP";
$my_onappURL = "https://YOUR.URL.TO.ONAPP.CP";

$GMAILusername = "billingScript@company.com";
$GMAILpassword = "PASSW0RD";
$GMAILfrom ="billingScript@company.com";
$billingMAIL = "billing@company.com";
$csvURL='/tmp/Onapp_CDN_billing_tool/' .  getDateYear(). '-'. fixdate(getDateMonth()-1) . '_ouput.csv';

$MAILrecipient=array("team@home.com","staff@home.com","accounts@home.com");
$MailaddCC=array("noc@home.se");
$MailaddBCC=array("max@home.com");
$Mailreaply= "noreaply@home.com";

$traceOutput=false;

function getStartAndEndDate($startorend)
{

//set default variable
$dateStringEnd=00 ;
$dateStringStart=00;

//get current month and year
$getDateMonth=getDateMonth();
$getDateYear=getDateYear();

// set last month and year
$getLastMonth=fixdate(getDateMonth()-1);
$getLastYear=fixdate(getDateYear()-1);

// handel Jan
if ($getDateMonth=="01"){

        $dateStringEnd = $getDateYear."-01"."-01+00:00:00";
        $dateStringStart = $getLastYear."-12"."-01+00:00:00";
        echo "Last year";
    }else{
        $dateStringEnd = $getDateYear."-".$getDateMonth."-01+00:00:00";
        $dateStringStart = $getDateYear."-".$getLastMonth."-01+00:00:00";
        }


if ($startorend=="start")
{
   $startorend=$dateStringStart;
}
else
{
   $startorend=$dateStringEnd;
}

return $startorend;
//echo $dateStringEnd . "\n";
//echo $dateStringStart . "\n";
}

// get current month
function getDateMonth() {
  $tz_object = new DateTimeZone('Europe/Stockholm');
  date_default_timezone_set('Europe/Stockholm');
           $datetime = new DateTime();
           $datetime->setTimezone($tz_object);
           return $datetime->format('m');
           }
           function getDateYear() {
                         $tz_object = new DateTimeZone('Europe/Stockholm');
                         date_default_timezone_set('Europe/Stockholm');

                         $datetime = new DateTime();
                         $datetime->setTimezone($tz_object);
                         return $datetime->format('Y');
}
//fix dayt format and add "0" if year/day is less then 10
function fixdate($mdate)
   {
   $len= strlen($mdate);
   if ($len >= 2 )
   {
   //echo "do nothing";
   }else
   {
  $mdate="0".$mdate;
   }
   return $mdate;
}

?>
