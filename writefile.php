<?php


function endsWith($haystack, $needle)
{
        echo $haystack;
            $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
}



function writeFile($file,$txt)
{
       $myfile = fopen($file, "w") or die("Unable to open file!");
       $file="\xEF\xBB\xBF".$file;
       fputs($myfile, $txt);
       fclose($myfile);
}
function appendtofile($file,$txt)
{
       $myfile = fopen($file, "a") or die("Unable to open file!");
       $file="\xEF\xBB\xBF".$file;
       fputs($myfile, $txt);
       fclose($myfile);
}


?>
