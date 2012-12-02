<?php
/*
Fruity - A Nagios Configuration Tool
Copyright (C) 2005 Groundwork Open Source Solutions

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
define ("LEVEL_CRITICAL",0);
define ("LEVEL_WARNING",1);
define ("LEVEL_INFORMATION",2);

function log_error($caller, $error_text="", $optional_info = "", $error_level = LEVEL_INFORMATION)
{
	$filename = "error.txt";
	if ($error_level == LEVEL_INFORMATION)
		$text = "INFO: ";
	else if ($error_level == LEVEL_WARNING)
		$text = "WARN: ";
	else 
		$text = "\n!!CRIT:";
	$text .= date("[j-F-Y(H:i:s)] ") . " In $caller ";
	if ($error_text != "")
		$text .= " : $error_text ";
	if ($optional_info != "")
		$text .= " ($optional_info)";
	
	$text .= "\n";
	// Let's make sure the file exists and is writable first.
	if (is_writable($filename)) 
	{
//		$buff = file_get_contents($filename);
		$handle = fopen($filename,"w");
    	fwrite($handle, $text.$buff);
		fclose($handle);
    }
  
    else print "cannot open logfile";
  
	
}

?>
