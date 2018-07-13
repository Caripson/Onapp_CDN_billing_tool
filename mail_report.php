<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mailIt()
{
  global $GMAILusername;
  global $GMAILpassword;
  global $GMAILfrom;
  global $billingMAIL;
  global $traceOutput;
  global $csvURL;
  global $HTMLmessage;
  global $MAILrecipient;
  global $MailaddCC;
  global $MailaddBCC;
  global $Mailreaply;

  $from = $GMAILfrom;
  $to = $billingMAIL;

//Load Composer's autoloader
require 'vendor/autoload.php';

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = $GMAILusername;                   // SMTP username
$mail->Password = $GMAILpassword;               // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
$mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
$mail->setFrom($GMAILfrom, "CDN-monitor");     //Set who the message is to be sent from
$mail->addReplyTo($Mailreaply, $Mailreaply);  //Set an alternative reply-to address
$mail->addAddress($billingMAIL);  // Add a recipient

foreach ($MAILrecipient as &$value) {
    $mail->addAddress($value);
}
foreach ($MailaddCC as &$value) {
    $mail->addCC($value);
}
foreach ($MailaddBCC as &$value) {
  $mail->addBCC($value);
}

//$mail->addAddress('ellen@example.com');               // Name is optional
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
$mail->addAttachment($csvURL, 'CDN billing_' . getDateYear(). "-". fixdate(getDateMonth()-1).".csv" );         // Add attachments
//$mail->addAttachment('/images/image.jpg', 'new.jpg'); // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'CDN billing '. getDateYear(). "-". fixdate(getDateMonth()-1);
$mail->Body    = '';
$mail->AltBody = '';

$signature = "<br><br> Kind Regards CDN Team";
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//file_get_contents('contents.html'), dirname(__FILE__)

$mail->msgHTML(file_get_contents('head.html') . file_get_contents('message.html') . $HTMLmessage . $signature  . file_get_contents('end.html'), dirname(__FILE__));




if(!$mail->send()) {
  if ($traceOutput){
   echo 'Message could not be sent.';
   echo 'Mailer Error: ' . $mail->ErrorInfo;
 }
   exit;
}

}
?>
