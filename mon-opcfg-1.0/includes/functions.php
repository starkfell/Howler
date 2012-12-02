<?php

/**
 * Get the path to the given command
 * 
 * @return string
 */

function get_path( $command ) {

        $path = false;
        exec( "which $command" , $path );

        if (isset($path))
                return $path[0];
        else
                return false;

}

/**
 * This just fixes that stupid microtime() function
 * that returns seconds and microseconds as 2 strings
 *
 * @return string
 */

function getmicrotime() {

	$mtime=microtime();
	$mtime=explode(" ", $mtime);
	$mtime=$mtime[1] + $mtime[0];
	return $mtime;
}

/**
 * This function... well, it should be obvious.
 *
 * @return string
 */

function formattime($secs) {
  if ($secs < 1) {
    $out=number_format($secs*1000, 4) . " milliseconds";
  } else {
    $tmp=$secs;
    $years = floor($tmp/31557600);
    $tmp -= ($years*31557600);
    $days = floor($tmp/86400);
    $tmp -= ($days*86400);
    $hours = floor($tmp/3600);
    $tmp -= ($hours*3600);
    $minutes = floor($tmp/60);
    $tmp -= ($minutes*60);
    $seconds = $tmp;
    $out = number_format($seconds, 3) . " secs";
    if ($minutes>0) { $out = "$minutes mins, " . $out; }
    if ($hours>0)   { $out = "$hours hrs, " . $out; }
    if ($days>0)    { $out = "$days days, " . $out; }
    if ($years>0)   { $out = number_format($years) . " years, " . $out; }
  }
  return $out;
}

/**
 * This function replaces all backslashes ('\') founded on a string,
 * into double-backslashes ('\\')
 *
 * @return string
 */

function replace_backslashes( $string_to_replace ) {

   $new_string = "";

   for ($i=0; $i<strlen($string_to_replace); $i++) {

      if (substr($string_to_replace, $i, 1)=="\\") {
         $new_string=$new_string."\\\\";
      }else {
         $new_string=$new_string.substr($string_to_replace, $i, 1);
      }
   }

   return $new_string;

}
/**
 * Empty null values in the 
 * given array
 *
 * @return array
 */

function unset_empty_array_values( &$array ) {

        foreach($array as $key => $value) {
                if ($value == "")
                        unset($array["$key"]);
        }

}



?>