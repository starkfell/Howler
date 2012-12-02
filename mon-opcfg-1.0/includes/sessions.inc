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

/*
	Filename: sessions.inc
	Description:
	Entry point to begin sessions
*/

function updateStatus($status) {
	$buffer = ob_get_clean();
	ob_flush();
	flush();
	$buffer .= "<script type=\"text/JavaScript\">" . PHP_EOL;
	$buffer .= "document.getElementById('progress').firstChild.nodeValue = '$status';" . PHP_EOL;
	$buffer .= "</script>" . PHP_EOL;
	echo $buffer . PHP_EOL;
	ob_start();
}

function begin_session($notvalid = 0) {
	global $fruity;
	session_start();
	print_blank_header("Configuration Utility Loading...");
	?>
	<div align="center">
	<br />
	<br />
	<h1>Initializing Configuration Utility...</h1>
	<br />
	</div>
	<div align="center" id="progress">
	Reticulating Splines...
	</div>
	<?php
	updateStatus("Setting Session Variables...");
	session_register("session_secret");
	$_SESSION['session_secret'] = SESSION_SECRET;
	session_register("username");
	$_SESSION['username'] = '';
	session_register("logged_in");
	$_SESSION['logged_in'] = 0;
	// Create Fruity Session Object
	updateStatus("Creating Configuration Utility Object...");
	session_register("fruity");
	$_SESSION['fruity'] = new Fruity();
	$fruity = $_SESSION['fruity'];
	updateStatus("Calling init...");
	$_SESSION['fruity']->init();
	updateStatus("Entering Application...");
	?>
	<SCRIPT LANGUAGE="JavaScript1.2">
	window.location.href="index.php";
	</SCRIPT>
	<?php
	die();
}


function verify_session() {
	global $fruity;
	session_start();	// Restart Session

	if(!isset($_SESSION["session_secret"]) || $_SESSION["session_secret"] <> SESSION_SECRET) {
		session_destroy();
		begin_session();	// Start New Session
	}
	// If we got here, the session has been verified, let's make our global variable aliases
	$fruity = $_SESSION['fruity'];	
	if( $fruity && method_exists( $fruity, "restart")) {
		$fruity->restart();	// Call our restart() routine which restarts after every session start
	} else {
		// There was a session problem!
		session_destroy();
		begin_session();
	}
	
	// Pre-Processing Formatting
	// Let's first get rid of slashes if magic_quotes is on
	if( get_magic_quotes_gpc()) {
		$fruity->prepare_for_use($_GET);
		$fruity->prepare_for_use($_POST);
	}
	
	return;
}

// Prevent erros on export
if (!isset($argv[1]))
	verify_session();