<?php

// ----------------------------
// Fix IE Caching
// ----------------------------
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );  // disable IE caching
header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" ); 
header( "Cache-Control: no-cache, must-revalidate" ); 
header( "Pragma: no-cache" );


// ----------------------------
// Process Ajax Requests
// ----------------------------

include_once('includes/config.inc');

$opcfg_dir = "{$sys_config['web_dir']}/opcfg";
$log_file = $opcfg_dir . "/export.log";
$lock_file = $opcfg_dir . "/export.lock";

$nohup = get_path("nohup");
$php = get_path("php");
$export_command = "$nohup $php -q $opcfg_dir/export.php '{$_SERVER["PHP_AUTH_USER"]}' '{$_SERVER["REMOTE_ADDR"]}' >$log_file 2>$log_file &";

if (isset($_REQUEST['action'])) {

	$ajax_function = $_REQUEST['action'];
	$ajax_function();

} else { die(); }

function start_export() {

	global $export_command, $log_file, $lock_file;

	if (file_exists($lock_file)) { // Check if another process is running

		print "Another export process is running!";

	} else {

		// Remove old file
		@unlink($log_file);

		// Start new Background Process
		exec("$export_command");

		print "Export process started successfully!";

	}

}

function show_log() {

	global $lock_file,$log_file;

	if (file_exists($lock_file)) {
		print "...";
	} else {
	
		$lines = array();
		$lines = @file( $log_file );

		if ($lines !== false) {
			foreach( $lines as $line ) {
				print "$line\n";
			}
		}

	}

}

function show_run_log() {

	global $lock_file, $log_file;

	if (file_exists($lock_file)) {	
		$lines = array();
		$lines = @file( $log_file );

		if ($lines !== false) {
			foreach( $lines as $line ) {
				print "$line\n";
			}
		}

	} else {
		print "No export process running";
	}

}

?>
