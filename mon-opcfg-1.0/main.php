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
Fruity Index Page, Displays Menu, and Statistics
*/
include_once('includes/config.inc');


if(isset($_GET['request'])) {
	if($_GET['request'] == "delete" && $_GET['section'] == "broker") {
		// We want to delete an event broker module
		$fruity->delete_broker_module($_GET['module_id']);
		$status_msg = "Deleted event broker module.";
	}
}

if(isset($_POST['request'])) {
	if(count($_POST['main_config'])) {
		foreach($_POST['main_config'] as $key=>$value)
			$_SESSION['tempData']['main_config'][$key] = $value;
	}
	if($_POST['request'] == 'update') {
		if(!$_POST['main_global_config_checkboxes']['global_host_event_handler']){
			$_SESSION['tempData']['main_config']['global_host_event_handler'] = NULL;
		}
		if(!$_POST['main_global_config_checkboxes']['global_service_event_handler']){
			$_SESSION['tempData']['main_config']['global_service_event_handler'] = NULL;
		}
		if($fruity->update_main_conf($_SESSION['tempData']['main_config'])) {
			unset($_SESSION['tempData']);
			$status_msg = "Updated Main Configuration.";
		}
		else {
			unset($_SESSION['tempData']);
			$status_msg = "Failed To Update Main Configuration.";
		}
	}
	else if($_POST['request'] == "module_add") {
		if(!strlen(trim($_REQUEST['module_line']))) {
			$status_msg = "Broker module line cannot be blank.";
		}
		else {
			// We want to add an event broker module
			$fruity->add_broker_module($_POST['module_line']);
			$status_msg = "Added Broker Module";
		}
	}
}

// Get Existing CGI Configuration
$fruity->get_main_conf($_SESSION['tempData']['main_config']);
	

// To create a "default" command
$fruity->return_command_list($command_list);
$command_list[] = array("command_id" => 0, "command_name" => "None");

// Let's create the date format select list
$date_format_list[] = array("values" => "us", "text" => "us - MM/DD/YYYY HH:MM:SS");
$date_format_list[] = array("values" => "euro", "text" => "euro - DD/MM/YYYY HH:MM:SS");
$date_format_list[] = array("values" => "iso8601", "text" => "iso8601 - YYYY-MM-DD HH:MM:SS");
$date_format_list[] = array("values" => "strict-iso8601", "text" => "strict-iso8601 - YYYY-MM-DDTHH:MM:SS");

// Let's make the log rotation select list
$log_rotate_list[] = array("values" => "n", "text" => "None");
$log_rotate_list[] = array("values" => "h", "text" => "Hourly");
$log_rotate_list[] = array("values" => "d", "text" => "Daily");
$log_rotate_list[] = array("values" => "w", "text" => "Weekly");
$log_rotate_list[] = array("values" => "m", "text" => "Monthly");

// Let's make the Service Check Timeout State select list - [R. Irujo - added 04.12.2012]
$service_check_timeout_state_list[] = array("values" => "c", "text" => "Critical");
$service_check_timeout_state_list[] = array("values" => "u", "text" => "Unknown (default)");
$service_check_timeout_state_list[] = array("values" => "w", "text" => "Warning");
$service_check_timeout_state_list[] = array("values" => "o", "text" => "Ok");

// Let's make the Notifications For Stalked Hosts select list - [R. Irujo - added 11.02.2012]
$stalking_notifications_for_hosts_list[] = array("values" => "0", "text" => "Notifications Disabled (Default)");
$stalking_notifications_for_hosts_list[] = array("values" => "1", "text" => "Notifications Enabled");
$stalking_notifications_for_hosts_list[] = array("values" => "2", "text" => "Variable Disabled (Incompatible)");

// Let's make the Notifications For Stalked Services select list - [R. Irujo - added 11.02.2012]
$stalking_notifications_for_services_list[] = array("values" => "0", "text" => "Notifications Disabled (Default)");
$stalking_notifications_for_services_list[] = array("values" => "1", "text" => "Notifications Enabled");
$stalking_notifications_for_services_list[] = array("values" => "2", "text" => "Variable Disabled (Incompatible)");

// Let's make the Keep Unknown Macros select list - [R. Irujo - added 11.02.2012]
$keep_unknown_macros_list[] = array("values" => "0", "text" => "Remove Macros From Output");
$keep_unknown_macros_list[] = array("values" => "1", "text" => "Keep Old Macro Behavior (Legacy)");
$keep_unknown_macros_list[] = array("values" => "2", "text" => "Variable Disabled (Incompatible)");

// Let's make the Host Performance Data File Mode select list - [R. Irujo - added 11.03.2012]
$host_perfdata_file_mode_list[] = array("values" => "a", "text" => "Append");
$host_perfdata_file_mode_list[] = array("values" => "w", "text" => "Write");
$host_perfdata_file_mode_list[] = array("values" => "p", "text" => "Non-Blocking");
$host_perfdata_file_mode_list[] = array("values" =>  "", "text" => "None");

// Let's make the Service Performance Data File Mode select list - [R. Irujo - added 11.03.2012]
$service_perfdata_file_mode_list[] = array("values" => "a", "text" => "Append");
$service_perfdata_file_mode_list[] = array("values" => "w", "text" => "Write");
$service_perfdata_file_mode_list[] = array("values" => "p", "text" => "Non-Blocking");
$service_perfdata_file_mode_list[] = array("values" =>  "", "text" => "None");

// Let's make the Debug Verbosity select list - [R. Irujo - added 11.04.2012]
$debug_verbosity_list[] = array("values" => "0", "text" => "Basic Information");
$debug_verbosity_list[] = array("values" => "1", "text" => "More Detailed Information (Default)");
$debug_verbosity_list[] = array("values" => "2", "text" => "Highly Detailed Information");
$debug_verbosity_list[] = array("values" => "" , "text" => "None");




if(!isset($_GET['section']))
	$_GET['section'] = 'paths';



print_header("Main Configuration File Editor");
?>
&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=paths">Paths</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=status">Status</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=performance">Performance</a> |<a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=security">Security</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=restart">Restart Actions</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=logging">Logging</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=external">External Commands</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=retention">Retention</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=global">Global Handlers</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=intervals">Intervals</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=flap">Flap</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=timeouts">Timeouts</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=obsess">Obsess</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=freshness">Freshness</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=broker">Event Broker</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>main.php?section=other">Other</a><br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	?>
	<br />
	<?php
	if($_GET['section'] == 'paths') {
		print_window_header("Paths", "100%", "center");
		?>
		<form name="main_path_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=paths">
		<input type="hidden" name="request" value="update" />

			<label for="config_dir" style="width:150px; float:left"><b>Configuration Directory:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[config_dir]" value="<?=$_SESSION['tempData']['main_config']['config_dir'];?>">
			<?=$fruity->element_desc("config_dir", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_file" style="width:150px; float:left"><b>Log File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[log_file]" value="<?=$_SESSION['tempData']['main_config']['log_file'];?>">
			<?=$fruity->element_desc("log_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="object_cache_file" style="width:150px; float:left"><b>Object Cache File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[object_cache_file]" value="<?=$_SESSION['tempData']['main_config']['object_cache_file'];?>">
			<?=$fruity->element_desc("object_cache_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="temp_file" style="width:150px; float:left"><b>Temporary File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[temp_file]" value="<?=$_SESSION['tempData']['main_config']['temp_file'];?>">
			<?=$fruity->element_desc("temp_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="status_file" style="width:150px; float:left"><b>Status File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[status_file]" value="<?=$_SESSION['tempData']['main_config']['status_file'];?>">
			<?=$fruity->element_desc("status_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_archive_path" style="width:150px; float:left"><b>Log Archive Path:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[log_archive_path]" value="<?=$_SESSION['tempData']['main_config']['log_archive_path'];?>">
			<?=$fruity->element_desc("log_archive_path", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="command_file" style="width:150px; float:left"><b>Command File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[command_file]" value="<?=$_SESSION['tempData']['main_config']['command_file'];?>">
			<?=$fruity->element_desc("command_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="lock_file" style="width:150px; float:left"><b>Lock File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[lock_file]" value="<?=$_SESSION['tempData']['main_config']['lock_file'];?>">
			<?=$fruity->element_desc("lock_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="state_retention_file" style="width:150px; float:left"><b>State Retention File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[state_retention_file]" value="<?=$_SESSION['tempData']['main_config']['state_retention_file'];?>">
			<?=$fruity->element_desc("state_retention_file", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="sync_retention_file" style="width:150px; float:left"><b>Sync Retention File:</b></label>
                        <input type="text" size="80" maxlength="255" name="main_config[sync_retention_file]" value="<?=$_SESSION['tempData']['main_config']['sync_retention_file'];?>">
                        <?=$fruity->element_desc("sync_retention_file", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="check_result_path" style="width:150px; float:left"><b>Check Result Path:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[check_result_path]" value="<?=$_SESSION['tempData']['main_config']['check_result_path'];?>">
			<?=$fruity->element_desc("check_result_path", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="temp_path" style="width:150px; float:left"><b>Temp Path:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[temp_path]" value="<?=$_SESSION['tempData']['main_config']['temp_path'];?>">
			<?=$fruity->element_desc("temp_path", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="p1_file" style="width:150px; float:left"><b>p1 File:</b></label>
			<input type="text" size="80" maxlength="255" name="main_config[p1_file]" value="<?=$_SESSION['tempData']['main_config']['p1_file'];?>">
			<?=$fruity->element_desc("p1_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
			<input type="submit" value="Update Path Configuration" />
			</form>
			<?php
	}
	else if($_GET['section'] == 'status') {
		print_window_header("Status", "100%", "center");
		?>
		<form name="main_status_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=status">
		<input type="hidden" name="request" value="update" />
			<b>Aggregated Status Update Interval: </b><input type="text" size="10" maxlength="10" name="main_config[status_update_interval]"  value="<?=$_SESSION['tempData']['main_config']['status_update_interval'];?>"><b> Seconds</b><br />
			<?=$fruity->element_desc("status_update_interval", "nagios_main_desc"); ?><br />
			<br />
		<input type="submit" value="Update Status Configuration" />
		</form>
		<?php
		
	}
	else if($_GET['section'] == 'performance') {
		print_window_header("Performance", "100%", "center");
		?>
		<form name="main_status_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=performance">
		<input type="hidden" name="request" value="update" />
                        <label for="max_check_result_list_items" style="width:300px; float:left"><b>Limit Number Of Items In Check Result List:</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[max_check_result_list_items]"  value="<?=$_SESSION['tempData']['main_config']['max_check_result_list_items'];?>"> - Available Starting in Icinga 1.8 - ( <b>0</b> = Disables Feature, <b>-1</b> = Disables Variable )
                        <?=$fruity->element_desc("max_check_result_list_items", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="child_processes_fork_twice" style="width:300px; float:left"><b>Child Processes Fork Twice:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[child_processes_fork_twice]"  value="<?=$_SESSION['tempData']['main_config']['child_processes_fork_twice'];?>">
                        <?=$fruity->element_desc("child_processes_fork_twice", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="check_result_reaper_frequency" style="width:300px; float:left"><b>Check Result Reaper Frequency (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[check_result_reaper_frequency]"  value="<?=$_SESSION['tempData']['main_config']['check_result_reaper_frequency'];?>">
                        <?=$fruity->element_desc("check_result_reaper_frequency", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="max_check_result_reaper_time" style="width:300px; float:left"><b>Maximum Check Result Reaper Time (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[max_check_result_reaper_time]"  value="<?=$_SESSION['tempData']['main_config']['max_check_result_reaper_time'];?>">
                        <?=$fruity->element_desc("max_check_result_reaper_time", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="max_check_result_file_age" style="width:300px; float:left"><b>Maximum Check Result File Age (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[max_check_result_file_age]"  value="<?=$_SESSION['tempData']['main_config']['max_check_result_file_age'];?>">
                        <?=$fruity->element_desc("max_check_result_file_age", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="cached_host_check_horizon" style="width:300px; float:left"><b>Cached Host Check Horizon (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[cached_host_check_horizon]"  value="<?=$_SESSION['tempData']['main_config']['cached_host_check_horizon'];?>">
                        <?=$fruity->element_desc("cached_host_check_horizon", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="cached_service_check_horizon" style="width:300px; float:left"><b>Cached Service Check Horizon (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[cached_service_check_horizon]"  value="<?=$_SESSION['tempData']['main_config']['cached_service_check_horizon'];?>">
                        <?=$fruity->element_desc("cached_service_check_horizon", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="time_change_threshold" style="width:300px; float:left"><b>Time Change Threshold (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[time_change_threshold]"  value="<?=$_SESSION['tempData']['main_config']['time_change_threshold'];?>">
                        <?=$fruity->element_desc("time_change_threshold", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="event_profiling_enabled" style="width:300px; float:left"><b>Event Profiling:</b></label>
                        <?php print_select("main_config[event_profiling_enabled]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['event_profiling_enabled']);?>
                        <?=$fruity->element_desc("event_profiling_enabled", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="enable_environment_macros" style="width:300px; float:left"><b>Environment Macros:</b></label>
			<?php print_select("main_config[enable_environment_macros]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_environment_macros']);?>
			<?=$fruity->element_desc("enable_environment_macros", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="free_child_process_memory" style="width:300px; float:left"><b>Free Child Process Memory:</b></label>
			<?php print_select("main_config[free_child_process_memory]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['free_child_process_memory']);?>
			<?=$fruity->element_desc("free_child_process_memory", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_large_installation_tweaks" style="width:300px; float:left"><b>Large Installation Tweaks:</b></label>
                        <?php print_select("main_config[use_large_installation_tweaks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_large_installation_tweaks']);?>
                        <?=$fruity->element_desc("use_large_installation_tweaks", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="enable_predictive_host_dependency_checks" style="width:300px; float:left"><b>Predictive Host Dependency Checks:</b></label>
			<?php print_select("main_config[enable_predictive_host_dependency_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_predictive_host_dependency_checks']);?>
			<?=$fruity->element_desc("enable_predictive_host_dependency_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="enable_predictive_service_dependency_checks" style="width:300px; float:left"><b>Predictive Service Dependency Checks:</b></label>
                        <?php print_select("main_config[enable_predictive_service_dependency_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_predictive_service_dependency_checks']);?>
                        <?=$fruity->element_desc("enable_predictive_service_dependency_checks", "nagios_main_desc"); ?>
                        <br />
			<br />
			<label for="host_perfdata_process_empty_results" style="width:300px; float:left"><b>Process Empty Host Perfomance Results:</b></label>
                        <?php print_select("main_config[host_perfdata_process_empty_results]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['host_perfdata_process_empty_results']);?>
                        <?=$fruity->element_desc("host_perfdata_process_empty_results", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="service_perfdata_process_empty_results" style="width:300px; float:left"><b>Process Empty Service Perfomance Results:</b></label>
                        <?php print_select("main_config[service_perfdata_process_empty_results]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['service_perfdata_process_empty_results']);?>
                        <?=$fruity->element_desc("service_perfdata_process_empty_results", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="check_for_orphaned_hosts" style="width:300px; float:left"><b>Orphaned Host Check:</b></label>
			<?php print_select("main_config[check_for_orphaned_hosts]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['check_for_orphaned_hosts']);?>
			<?=$fruity->element_desc("check_for_orphaned_hosts", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="enable_embedded_perl" style="width:300px; float:left"><b>Embedded Perl Interpreter:</b></label>
			<?php print_select("main_config[enable_embedded_perl]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_embedded_perl']);?>
			<?=$fruity->element_desc("enable_embedded_perl", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_embedded_perl_implicitly" style="width:300px; float:left"><b>Embedded Perl Implicit Use:</b></label>
			<?php print_select("main_config[use_embedded_perl_implicitly]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_embedded_perl_implicitly']);?>
			<?=$fruity->element_desc("use_embedded_perl_implicitly", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Performance Configuration" />
		</form>
		<?php	
	}
	else if($_GET['section'] == 'security') {
		print_window_header("Security", "100%", "center");
		?>
		<form name="main_security_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=security">
		<input type="hidden" name="request" value="update" />
                        <label for="nagios_user" style="width:100px; float:left"><b>Nagios User:</b></label>
			<input type="text" name="main_config[nagios_user]" size="50" value="<?=$_SESSION['tempData']['main_config']['nagios_user'];?>">
			<?=$fruity->element_desc("nagios_user", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="nagios_group" style="width:100px; float:left"><b>Nagios Group:</b></label>
			<input type="text" name="main_config[nagios_group]" size="50" value="<?=$_SESSION['tempData']['main_config']['nagios_group'];?>">
			<?=$fruity->element_desc("nagios_group", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="icinga_user" style="width:100px; float:left"><b>Icinga User:</b></label>
			<input type="text" name="main_config[icinga_user]" size="50" value="<?=$_SESSION['tempData']['main_config']['icinga_user'];?>">
                        <?=$fruity->element_desc("icinga_user", "icinga_main_desc"); ?>
			<br />
                        <br />
                        <label for="icinga_group" style="width:100px; float:left"><b>Icinga Group:</b></label>
                        <input type="text" name="main_config[icinga_group]" size="50" value="<?=$_SESSION['tempData']['main_config']['icinga_group'];?>">
                        <?=$fruity->element_desc("icinga_group", "icinga_main_desc"); ?>
			<br />
			<br />
                        <br />
		<input type="submit" value="Update Security Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'restart') {
		print_window_header("Restart", "100%", "center");
		?>
		<form name="main_restart_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=restart">
		<input type="hidden" name="request" value="update" />
			<label for="enable_notifications" style="width:200px; float:left"><b>Notifications:</b></label>
			<?php print_select("main_config[enable_notifications]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_notifications']);?>
			<?=$fruity->element_desc("enable_notifications", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="execute_host_checks" style="width:200px; float:left"><b>Execute Host Checks:</b></label>
                        <?php print_select("main_config[execute_host_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['execute_host_checks']);?>
                        <?=$fruity->element_desc("execute_host_checks", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="execute_service_checks" style="width:200px; float:left"><b>Execute Service Checks:</b></label>
			<?php print_select("main_config[execute_service_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['execute_service_checks']);?>
			<?=$fruity->element_desc("execute_service_checks", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="accept_passive_host_checks" style="width:200px; float:left"><b>Accept Passive Host Checks:</b></label>
                        <?php print_select("main_config[accept_passive_host_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['accept_passive_host_checks']);?>
                        <?=$fruity->element_desc("accept_passive_host_checks", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="accept_passive_service_checks" style="width:200px; float:left"><b>Accept Passive Service Checks:</b></label>
			<?php print_select("main_config[accept_passive_service_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['accept_passive_service_checks']);?>
			<?=$fruity->element_desc("accept_passive_service_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="translate_passive_host_checks" style="width:200px; float:left"><b>Translate Passive Host Checks:</b></label>
			<?php print_select("main_config[translate_passive_host_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['translate_passive_host_checks']);?>
			<?=$fruity->element_desc("translate_passive_host_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="passive_host_checks_are_soft" style="width:200px; float:left"><b>Passive Host Checks Are SOFT:</b></label>
			<?php print_select("main_config[passive_host_checks_are_soft]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['passive_host_checks_are_soft']);?>
			<?=$fruity->element_desc("passive_host_checks_are_soft", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="enable_event_handlers" style="width:200px; float:left"><b>Event Handlers:</b></label>
			<?php print_select("main_config[enable_event_handlers]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_event_handlers']);?>
			<?=$fruity->element_desc("enable_event_handlers", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Restart Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'logging') {
		print_window_header("Logging", "100%", "center");
		?>
		<form name="main_logging_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=logging">
		<input type="hidden" name="request" value="update" />
			<label for="log_rotation_method" style="width:200px; float:left"><b>Log Rotation Method:</b></label>
			<?php print_select("main_config[log_rotation_method]", $log_rotate_list, "values", "text", $_SESSION['tempData']['main_config']['log_rotation_method']);?>
			<?=$fruity->element_desc("log_rotation_method", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_daemon_log" style="width:200px; float:left"><b>Use Daemon Log:</b></label>
			<?php print_select("main_config[use_daemon_log]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_daemon_log']);?>
                        <?=$fruity->element_desc("use_daemon_log", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="use_syslog" style="width:200px; float:left"><b>Syslog Logging:</b></label>
			<?php print_select("main_config[use_syslog]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_syslog']);?>
			<?=$fruity->element_desc("use_syslog", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_syslog_local_facility" style="width:200px; float:left"><b>Local Syslog Facility:</b></label>
                        <?php print_select("main_config[use_syslog_local_facility]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_syslog_local_facility']);?>
                        <?=$fruity->element_desc("use_syslog_local_facility", "nagios_main_desc"); ?>
                        <br />
			<br />
			<label for="syslog_local_facility" style="width:200px; float:left"><b>Syslog Local Facility Value (1 - 7):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[syslog_local_facility]"  value="<?=$_SESSION['tempData']['main_config']['syslog_local_facility'];?>">
                        <?=$fruity->element_desc("syslog_local_facility", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="log_current_states" style="width:200px; float:left"><b>Current State Logging:</b></label>
                        <?php print_select("main_config[log_current_states]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_current_states']);?>
                        <?=$fruity->element_desc("log_current_states", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="log_external_commands_user" style="width:200px; float:left"><b>External Commands User Logging:</b></label>
                        <?php print_select("main_config[log_external_commands_user]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_external_commands_user']);?>
                        <?=$fruity->element_desc("log_external_commands_user", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="log_long_plugin_output" style="width:200px; float:left"><b>Long Plugin Output Logging:</b></label>
                        <?php print_select("main_config[log_long_plugin_output]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_long_plugin_output']);?>
                        <?=$fruity->element_desc("log_long_plugin_output", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="log_notifications" style="width:200px; float:left"><b>Notification Logging:</b></label>
			<?php print_select("main_config[log_notifications]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_notifications']);?>
			<?=$fruity->element_desc("log_notifications", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="log_host_retries" style="width:200px; float:left"><b>Host Check Retry Logging:</b></label>
                        <?php print_select("main_config[log_host_retries]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_host_retries']);?>
                        <?=$fruity->element_desc("log_host_retries", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="log_service_states" style="width:200px; float:left"><b>Service Check Retry Logging:</b></label>		
			<?php print_select("main_config[log_service_retries]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_service_retries']);?>
			<?=$fruity->element_desc("log_service_retries", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_event_handlers" style="width:200px; float:left"><b>Event Handler Logging:</b></label>	
			<?php print_select("main_config[log_event_handlers]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_event_handlers']);?>
			<?=$fruity->element_desc("log_event_handlers", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_initial_states" style="width:200px; float:left"><b>Initial States Logging:</b></label>	
			<?php print_select("main_config[log_initial_states]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_initial_states']);?>
			<?=$fruity->element_desc("log_initial_states", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_current_states" style="width:200px; float:left"><b>External Command Logging:</b></label>	
			<?php print_select("main_config[log_external_commands]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_external_commands']);?>
			<?=$fruity->element_desc("log_external_commands", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="log_passive_checks" style="width:200px; float:left"><b>Passive Check Logging:</b></label>
			<?php print_select("main_config[log_passive_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['log_passive_checks']);?>
			<?=$fruity->element_desc("log_passive_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />				
		<input type="submit" value="Update Logging Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'external') {
		print_window_header("External", "100%", "center");
		?>
		<form name="main_external_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=external">
		<input type="hidden" name="request" value="update" />
			<label for="check_external_commands" style="width:200px; float:left"><b>Check External Commands:</b></label>
			<?php print_select("main_config[check_external_commands]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['check_external_commands']);?>
			<?=$fruity->element_desc("check_external_commands", "nagios_main_desc"); ?>
			<br />		
			<br />
			<label for="command_check_interval" style="width:200px; float:left"><b>Command Check Interval:</b></label>
			<input type="text" size="2" maxlength="2" name="main_config[command_check_interval]"  value="<?=$_SESSION['tempData']['main_config']['command_check_interval'];?>">
			<?=$fruity->element_desc("command_check_interval", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="external_command_buffer_slots" style="width:200px; float:left"><b>External Command Buffer Slots:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[external_command_buffer_slots]"  value="<?=$_SESSION['tempData']['main_config']['external_command_buffer_slots'];?>">
			<?=$fruity->element_desc("external_command_buffer_slots", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update External Command Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'retention') {
		print_window_header("Retention", "100%", "center");
		?>
		<form name="main_retention_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=retention">
		<input type="hidden" name="request" value="update" />
			<label for="dump_retained_host_service_states_to_neb" style="width:300px; float:left"><b>Dump Retained Host and Service States to NEB:</b></label>
                        <?php print_select("main_config[dump_retained_host_service_states_to_neb]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['dump_retained_host_service_states_to_neb']);?>
                        <?=$fruity->element_desc("dump_retained_host_service_states_to_neb", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="precached_object_file" style="width:300px; float:left"><b>Precached Object File:</b></label>
			<?php print_select("main_config[precached_object_file]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['precached_object_file']);?>
			<?=$fruity->element_desc("precached_object_file", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retain_state_information" style="width:300px; float:left"><b>Retain State Information:</b></label>
			<?php print_select("main_config[retain_state_information]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['retain_state_information']);?>
			<?=$fruity->element_desc("retain_state_information", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_retained_program_state" style="width:300px; float:left"><b>Use Retained Program State:</b></label>
			<?php print_select("main_config[use_retained_program_state]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_retained_program_state']);?>
			<?=$fruity->element_desc("use_retained_program_state", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_retained_scheduling_info" style="width:300px; float:left"><b>Use Retained Scheduling Info:</b></label>
			<?php print_select("main_config[use_retained_scheduling_info]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_retained_scheduling_info']);?>
			<?=$fruity->element_desc("use_retained_scheduling_info", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="retention_update_interval" style="width:300px; float:left"><b>Retention Update Interval (Minutes):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[retention_update_interval]" value="<?=$_SESSION['tempData']['main_config']['retention_update_interval'];?>">
                        <?=$fruity->element_desc("retention_update_interval", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="retained_host_attribute_mask" style="width:300px; float:left"><b>Retained Host Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_host_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_host_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_host_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retained_service_attribute_mask" style="width:300px; float:left"><b>Retained Service Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_service_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_service_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_service_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retained_process_host_attribute_mask" style="width:300px; float:left"><b>Retained Process Host Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_process_host_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_process_host_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_process_host_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retained_process_service_attribute_mask" style="width:300px; float:left"><b>Retained Process Service Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_process_service_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_process_service_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_process_service_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retained_contact_host_attribute_mask" style="width:300px; float:left"><b>Retained Contact Host Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_contact_host_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_contact_host_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_contact_host_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="retained_contact_service_attribute_mask" style="width:300px; float:left"><b>Retained Contact Service Atrribute Mask:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[retained_contact_service_attribute_mask]" value="<?=$_SESSION['tempData']['main_config']['retained_contact_service_attribute_mask'];?>">
			<?=$fruity->element_desc("retained_contact_service_attribute_mask", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Retention Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'global') {
		print_window_header("Global Handlers", "100%", "center");
		?>
		<form name="main_global_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=global">
		<input type="hidden" name="request" value="update" />
		<br />
		<?php
		double_pane_form_window_start();
		double_pane_select_form_element_with_enabler("#f0f0f0", "main_global_config", "main_config[global_host_event_handler]", "Global Host Event Handler", $fruity->element_desc("global_host_event_handler", "nagios_main_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['global_host_event_handler'], "global_host_event_handler", "Enabled");
		double_pane_select_form_element_with_enabler("#f0f0f0", "main_global_config", "main_config[global_service_event_handler]", "Global Service Event Handler", $fruity->element_desc("global_service_event_handler", "nagios_main_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['global_service_event_handler'], "global_service_event_handler", "Enabled");
		double_pane_form_window_finish();
		?>
		<br />
		<input type="submit" value="Update Global Handlers Configuration" />
		<br />
		</form>
		<?php
	}
	else if($_GET['section'] == 'intervals') {
		print_window_header("Intervals", "100%", "center");
		?>
		<form name="main_interval_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=intervals">
		<input type="hidden" name="request" value="update" />
			<label for="sleep_time" style="width:300px; float:left"><b>Sleep Time (Seconds):</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[sleep_time]" value="<?=$_SESSION['tempData']['main_config']['sleep_time'];?>">
			<?=$fruity->element_desc("sleep_time", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="host_inter_check_delay_method" style="width:300px; float:left"><b>Host Inter-Check Delay Method:</b></label>
                        <input type="text" name="main_config[host_inter_check_delay_method]" value="<?=$_SESSION['tempData']['main_config']['host_inter_check_delay_method'];?>">
                        <?=$fruity->element_desc("host_inter_check_delay_method", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="service_inter_check_delay_method" style="width:300px; float:left"><b>Service Inter-Check Delay Method:</b></label>
			<input type="text" name="main_config[service_inter_check_delay_method]" value="<?=$_SESSION['tempData']['main_config']['service_inter_check_delay_method'];?>">
			<?=$fruity->element_desc("service_inter_check_delay_method", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="max_host_check_spread" style="width:300px; float:left"><b>Max Host Check Spread (Minutes):</b></label>
                        <input type="text" name="main_config[max_host_check_spread]" value="<?=$_SESSION['tempData']['main_config']['max_host_check_spread'];?>">
                        <?=$fruity->element_desc("max_host_check_spread", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="max_service_check_spread" style="width:300px; float:left"><b>Max Service Check Spread (Minutes):</b></label>
			<input type="text" name="main_config[max_service_check_spread]" value="<?=$_SESSION['tempData']['main_config']['max_service_check_spread'];?>">
			<?=$fruity->element_desc("max_service_check_spread", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="service_interleave_factor" style="width:300px; float:left"><b>Service Interleave Factor:</b></label>
			<input type="text" name="main_config[service_interleave_factor]" value="<?=$_SESSION['tempData']['main_config']['service_interleave_factor'];?>">
			<?=$fruity->element_desc("service_interleave_factor", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="max_concurrent_checks" style="width:300px; float:left"><b>Max Concurrent Service Checks:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[max_concurrent_checks]" value="<?=$_SESSION['tempData']['main_config']['max_concurrent_checks'];?>">
			<?=$fruity->element_desc("max_concurrent_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="interval_length" style="width:300px; float:left"><b>Interval Length (Seconds):</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[interval_length]" value="<?=$_SESSION['tempData']['main_config']['interval_length'];?>">
			<?=$fruity->element_desc("interval_length", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="auto_reschedule_checks" style="width:300px; float:left"><b>Auto Reschedule Checks:</b></label>
			<?php print_select("main_config[auto_reschedule_checks]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['auto_reschedule_checks']);?>
			<?=$fruity->element_desc("auto_reschedule_checks", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="auto_rescheduling_interval" style="width:300px; float:left"><b>Auto Rescheduling Interval (Seconds):</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[auto_rescheduling_interval]" value="<?=$_SESSION['tempData']['main_config']['auto_rescheduling_interval'];?>">
			<?=$fruity->element_desc("auto_rescheduling_interval", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="auto_rescheduling_window" style="width:300px; float:left"><b>Auto Rescheduling Window (Seconds):</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[auto_rescheduling_window]" value="<?=$_SESSION['tempData']['main_config']['auto_rescheduling_window'];?>">
			<?=$fruity->element_desc("auto_rescheduling_window", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_aggressive_host_checking" style="width:300px; float:left"><b>Use Aggressive Host Checking:</b></label>		
			<?php print_select("main_config[use_aggressive_host_checking]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_aggressive_host_checking']);?>
			<?=$fruity->element_desc("use_aggressive_host_checking", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Interval Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'flap') {
		print_window_header("Flapping", "100%", "center");
		?>
		<form name="main_flap_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=flap">
		<input type="hidden" name="request" value="update" />
			<label for="enable_flap_detection" style="width:300px; float:left"><b>Enable Flap Detection:</b></label>
			<?php print_select("main_config[enable_flap_detection]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['enable_flap_detection']);?>
			<?=$fruity->element_desc("enable_flap_detection", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="low_host_flap_threshold" style="width:300px; float:left"><b>Low Host Flap Threshold (Percent):</b></label>
                        <input type="text" size="5" maxlength="5" name="main_config[low_host_flap_threshold]" value="<?=$_SESSION['tempData']['main_config']['low_host_flap_threshold'];?>">
                        <?=$fruity->element_desc("low_host_flap_threshold", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="low_service_flap_threshold" style="width:300px; float:left"><b>Low Service Flap Threshold (Percent):</b></label>
			<input type="text" size="5" maxlength="5" name="main_config[low_service_flap_threshold]" value="<?=$_SESSION['tempData']['main_config']['low_service_flap_threshold'];?>">
			<?=$fruity->element_desc("low_service_flap_threshold", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="high_host_flap_threshold" style="width:300px; float:left"><b>High Host Flap Threshold (Percent):</b></label>
                        <input type="text" size="5" maxlength="5" name="main_config[high_host_flap_threshold]" value="<?=$_SESSION['tempData']['main_config']['high_host_flap_threshold'];?>">
                        <?=$fruity->element_desc("high_host_flap_threshold", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="high_service_flap_threshold" style="width:300px; float:left"><b>High Service Flap Threshold (Percent):</b></label>
			<input type="text" size="5" maxlength="5" name="main_config[high_service_flap_threshold]" value="<?=$_SESSION['tempData']['main_config']['high_service_flap_threshold'];?>">
			<?=$fruity->element_desc("high_service_flap_threshold", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Interval Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'timeouts') {
		print_window_header("Timeouts", "100%", "center");
		?>
		<form name="main_timeouts_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=timeouts">
		<input type="hidden" name="request" value="update" />
			<label for="service_check_timeout_state" style="width:400px; float:left"><b>Service Check Timeout State:</b></label>
                        <?php print_select("main_config[service_check_timeout_state]", $service_check_timeout_state_list, "values", "text", $_SESSION['tempData']['main_config']['service_check_timeout_state']);?>
                        <?=$fruity->element_desc("service_check_timeout_state", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="host_check_timeout" style="width:400px; float:left"><b>Host Check Timeout (Seconds):</b></label>
			<input type="text" size="8" maxlength="8" name="main_config[host_check_timeout]" value="<?=$_SESSION['tempData']['main_config']['host_check_timeout'];?>">
			<?=$fruity->element_desc("host_check_timeout", "nagios_main_desc"); ?>
			<br />
			<br />
                <input type="hidden" name="request" value="update" />
                        <label for="service_check_timeout" style="width:400px; float:left"><b>Service Check Timeout (Seconds):</b></label>
                        <input type="text" size="8" maxlength="8" name="main_config[service_check_timeout]" value="<?=$_SESSION['tempData']['main_config']['service_check_timeout'];?>">
                        <?=$fruity->element_desc("service_check_timeout", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="event_handler_timeout" style="width:400px; float:left"><b>Event Handler Timeout (Seconds):</b></label>
			<input type="text" size="8" maxlength="8" name="main_config[event_handler_timeout]" value="<?=$_SESSION['tempData']['main_config']['event_handler_timeout'];?>">
			<?=$fruity->element_desc("event_handler_timeout", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="notification_timeout" style="width:400px; float:left"><b>Notification Timeout (Seconds):</b></label>
			<input type="text" size="8" maxlength="8" name="main_config[notification_timeout]" value="<?=$_SESSION['tempData']['main_config']['notification_timeout'];?>">
			<?=$fruity->element_desc("notification_timeout", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="ochp_timeout" style="width:400px; float:left"><b>Obsessive Compulsive Host Processor Timeout (Seconds):</b></label>
                        <input type="text" size="8" maxlength="8" name="main_config[ochp_timeout]" value="<?=$_SESSION['tempData']['main_config']['ochp_timeout'];?>">
                        <?=$fruity->element_desc("ochp_timeout", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="ocsp_timeout" style="width:400px; float:left"><b>Obsessive Compulsive Service Processor Timeout (Seconds):</b></label>
			<input type="text" size="8" maxlength="8" name="main_config[ocsp_timeout]" value="<?=$_SESSION['tempData']['main_config']['ocsp_timeout'];?>">
			<?=$fruity->element_desc("ocsp_timeout", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="perfdata_timeout" style="width:400px; float:left"><b>Performance Data Processor Command Timeout (Seconds):</b></label>
			<input type="text" size="8" maxlength="8" name="main_config[perfdata_timeout]"  value="<?=$_SESSION['tempData']['main_config']['perfdata_timeout'];?>">
			<?=$fruity->element_desc("perfdata_timeout", "nagios_main_desc"); ?>
			<br />
			<br />
			<br />
		<input type="submit" value="Update Timeout Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'obsess') {
		print_window_header("Obsession", "100%", "center");
		?>
		<form name="main_obsess_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=obsess">
		<input type="hidden" name="request" value="update" />

                        <label for="obsess_over_hosts" style="width:300px; float:left"><b>Obsess Over Hosts:</b></label>
                        <?php print_select("main_config[obsess_over_hosts]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['obsess_over_hosts']);?>
                        <?=$fruity->element_desc("obsess_over_hosts", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="obsess_over_services" style="width:300px; float:left"><b>Obsess Over Services:</b></label>
			<?php print_select("main_config[obsess_over_services]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['obsess_over_services']);?>
			<?=$fruity->element_desc("obsess_over_services", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="ochp_command" style="width:300px; float:left"><b>Obsessive Compulsive Host Processor Command:</b></label>
                        <?php print_select("main_config[ochp_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['ochp_command']);?>
                        <?=$fruity->element_desc("ochp_command", "nagios_main_desc"); ?>
                        <br />
			<br />
			<label for="ocsp_command" style="width:300px; float:left"><b>Obsessive Compulsive Service Processor Command:</b></label>
			<?php print_select("main_config[ocsp_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['ocsp_command']);?>
			<?=$fruity->element_desc("ocsp_command", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="stalking_even_handlers_for_hosts" style="width:300px; float:left"><b>Stalking Event Handlers For Hosts:</b></label>
                        <?php print_select("main_config[stalking_event_handlers_for_hosts]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['stalking_event_handlers_for_hosts']);?>
                        <?=$fruity->element_desc("stalking_event_handlers_for_hosts", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="stalking_event_handlers_for_services" style="width:300px; float:left"><b>Stalking Event Handlers For Services:</b></label>
                        <?php print_select("main_config[stalking_event_handlers_for_services]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['stalking_event_handlers_for_services']);?>
                        <?=$fruity->element_desc("stalking_event_handlers_for_services", "nagios_main_desc"); ?>
			<br />
                        <br />
                        <label for="stalking_notifications_for_hosts" style="width:300px; float:left"><b>Stalking Notifications For Hosts:</b></label>
                        <?php print_select("main_config[stalking_notifications_for_hosts]", $stalking_notifications_for_hosts_list, "values", "text", $_SESSION['tempData']['main_config']['stalking_notifications_for_hosts']);?> - Available starting in Icinga 1.6
                        <?=$fruity->element_desc("stalking_notifications_for_hosts", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="stalking_notifications_for_services" style="width:300px; float:left"><b>Stalking Notifications For Services:</b></label>
                        <?php print_select("main_config[stalking_notifications_for_services]", $stalking_notifications_for_services_list, "values", "text", $_SESSION['tempData']['main_config']['stalking_notifications_for_services']);?> - Available starting in Icinga 1.6
                        <?=$fruity->element_desc("stalking_notifications_for_services", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<br />				
		<input type="submit" value="Update Obsession Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'freshness') {
		print_window_header("Freshness", "100%", "center");
		?>
		<form name="main_freshness_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=freshness">
		<input type="hidden" name="request" value="update" />
                        <label for="check_host_freshness" style="width:300px; float:left"><b>Check Host Freshness:</b></label>
                        <?php print_select("main_config[check_host_freshness]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['check_host_freshness']);?>
                        <?=$fruity->element_desc("check_host_freshness", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="check_service_freshness" style="width:300px; float:left"><b>Check Service Freshness:</b></label>
			<?php print_select("main_config[check_service_freshness]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['check_service_freshness']);?>
			<?=$fruity->element_desc("check_service_freshness", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="host_freshness_check_interval" style="width:300px; float:left"><b>Host Freshness Check Interval (Seconds):</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[host_freshness_check_interval]"  value="<?=$_SESSION['tempData']['main_config']['host_freshness_check_interval'];?>">
			<?=$fruity->element_desc("host_freshness_check_interval", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="service_freshness_check_interval" style="width:300px; float:left"><b>Service Freshness Check Interval (Seconds):</b></label>
                        <input type="text" size="10" maxlength="10" name="main_config[service_freshness_check_interval]"  value="<?=$_SESSION['tempData']['main_config']['service_freshness_check_interval'];?>">
                        <?=$fruity->element_desc("service_freshness_check_interval", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="additional_freshness_latency" style="width:300px; float:left"><b>Additional Freshness Latency:</b></label>
			<input type="text" size="10" maxlength="10" name="main_config[additional_freshness_latency]"  value="<?=$_SESSION['tempData']['main_config']['additional_freshness_latency'];?>">
			<?=$fruity->element_desc("additional_freshness_latency", "nagios_main_desc"); ?>
			<br />
			<br />				
		<input type="submit" value="Update Obsession Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'other') {
		print_window_header("Other", "100%", "center");
		?>
		<form name="main_other_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=other">
		<input type="hidden" name="request" value="update" />
			<label for="allow_empty_hostgroup_assignment" style="width:350px; float:left"><b>Allow Empty Hostgroup Assignment For Services:</b></label>
                        <?php print_select("main_config[allow_empty_hostgroup_assignment]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['allow_empty_hostgroup_assignment']);?>
                        <?=$fruity->element_desc("allow_empty_hostgroup_assignment", "nagios_main_desc"); ?>
			<br />
                        <br />
			<label for="soft_state_dependencies" style="width:350px; float:left"><b>Soft State Dependencies:</b></label>
			<?php print_select("main_config[soft_state_dependencies]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['soft_state_dependencies']);?>
			<?=$fruity->element_desc("soft_state_dependencies", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="process_performance_data" style="width:350px; float:left"><b>Process Performance Data:</b></label>
			<?php print_select("main_config[process_performance_data]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['process_performance_data']);?>
			<?=$fruity->element_desc("process_performance_data", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="host_perfdata_command" style="width:350px; float:left"><b>Host Performance Data Command:</b></label>
			<?php print_select("main_config[host_perfdata_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['host_perfdata_command']);?>
			<?=$fruity->element_desc("host_perfdata_command", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="service_perfdata_command" style="width:350px; float:left"><b>Service Performance Data Command:</b></label>
                        <?php print_select("main_config[service_perfdata_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['service_perfdata_command']);?>
                        <?=$fruity->element_desc("service_perfdata_command", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="host_perfdata_file_processing_command" style="width:350px; float:left"><b>Host Performance Data File Processing Command:</b></label>
                        <?php print_select("main_config[host_perfdata_file_processing_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['host_perfdata_file_processing_command']);?>
                        <?=$fruity->element_desc("host_perfdata_file_processing_command", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="service_perfdata_file_processing_command" style="width:350px; float:left"><b>Service Performance Data File Processing Command:</b></label>
                        <?php print_select("main_config[service_perfdata_file_processing_command]", $command_list, "command_id", "command_name", $_SESSION['tempData']['main_config']['service_perfdata_file_processing_command']);?>
                        <?=$fruity->element_desc("service_perfdata_file_processing_command", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="host_perfdata_file_mode" style="width:350px; float:left"><b>Host Performance Data File Mode:</b></label>
                        <?php print_select("main_config[host_perfdata_file_mode]", $host_perfdata_file_mode_list, "values", "text", $_SESSION['tempData']['main_config']['host_perfdata_file_mode']);?>
                        <?=$fruity->element_desc("host_perfdata_file_mode", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="service_perfdata_file_mode" style="width:350px; float:left"><b>Service Performance Data File Mode:</b></label>
                        <?php print_select("main_config[service_perfdata_file_mode]", $service_perfdata_file_mode_list, "values", "text", $_SESSION['tempData']['main_config']['service_perfdata_file_mode']);?>
                        <?=$fruity->element_desc("service_perfdata_file_mode", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="host_perfdata_file" style="width:350px; float:left"><b>Host Performance Data File:</b></label>
			<input type="text" size="60" name="main_config[host_perfdata_file]"  value="<?=$_SESSION['tempData']['main_config']['host_perfdata_file'];?>">
			<?=$fruity->element_desc("host_perfdata_file", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="service_perfdata_file" style="width:350px; float:left"><b>Service Performance Data File:</b></label>
                        <input type="text" size="60" name="main_config[service_perfdata_file]"  value="<?=$_SESSION['tempData']['main_config']['service_perfdata_file'];?>">
                        <?=$fruity->element_desc("service_perfdata_file", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="host_perfdata_template" style="width:350px; float:left"><b>Host Performance Template:</b></label>
			<input type="text" size="60" name="main_config[host_perfdata_template]"  value="<?=$_SESSION['tempData']['main_config']['host_perfdata_template'];?>">
			<?=$fruity->element_desc("host_perfdata_template", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="service_perfdata_template" style="width:350px; float:left"><b>Service Performance Template:</b></label>
                        <input type="text" size="60" name="main_config[service_perfdata_template]"  value="<?=$_SESSION['tempData']['main_config']['service_perfdata_template'];?>">
                        <?=$fruity->element_desc("service_perfdata_template", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="host_perfdata_file_template" style="width:350px; float:left"><b>Host Performance Data File Template:</b></label>
                        <input type="text" size="60" maxlength="255" name="main_config[host_perfdata_file_template]"  value="<?=$_SESSION['tempData']['main_config']['host_perfdata_file_template'];?>">
                        <?=$fruity->element_desc("host_perfdata_file_template", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="service_perfdata_file_template" style="width:350px; float:left"><b>Service Performance Data File Template:</b></label>
                        <input type="text" size="60" maxlength="255" name="main_config[service_perfdata_file_template]"  value="<?=$_SESSION['tempData']['main_config']['service_perfdata_file_template'];?>">
                        <?=$fruity->element_desc("service_perfdata_file_template", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="host_perfdata_file_processing_interval" style="width:350px; float:left"><b>Host Performance Data File Processing Interval (Seconds):</b></label>
			<input type="text" name="main_config[host_perfdata_file_processing_interval]"  value="<?=$_SESSION['tempData']['main_config']['host_perfdata_file_processing_interval'];?>">
			<?=$fruity->element_desc("host_perfdata_file_processing_interval", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="service_perfdata_file_processing_interval" style="width:350px; float:left"><b>Service Performance Data File Processing Interval (Seconds):</b></label>
                        <input type="text" name="main_config[service_perfdata_file_processing_interval]"  value="<?=$_SESSION['tempData']['main_config']['service_perfdata_file_processing_interval'];?>">
                        <?=$fruity->element_desc("service_perfdata_file_processing_interval", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="keep_unknown_macros" style="width:350px; float:left"><b>Keep Unknown Macros Option:</b></label>
                        <?php print_select("main_config[keep_unknown_macros]", $keep_unknown_macros_list, "values", "text", $_SESSION['tempData']['main_config']['keep_unknown_macros']);?> - Available starting in Icinga 1.8
                        <?=$fruity->element_desc("keep_unknown_macros", "nagios_main_desc"); ?>
                        <br />
                        <br />
                        <label for="daemon_dumps_core" style="width:350px; float:left"><b>Daemon Dumps Core:</b></label>
                        <?php print_select("main_config[daemon_dumps_core]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['daemon_dumps_core']);?>
                        <?=$fruity->element_desc("daemon_dumps_core", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="check_for_orphaned_services" style="width:350px; float:left"><b>Check For Orphaned Services:</b></label>
			<?php print_select("main_config[check_for_orphaned_services]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['check_for_orphaned_services']);?>
			<?=$fruity->element_desc("check_for_orphaned_services", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="date_format" style="width:350px; float:left"><b>Date Format:</b></label>
			<?php print_select("main_config[date_format]", $date_format_list, "values", "text", $_SESSION['tempData']['main_config']['date_format']);?>
			<?=$fruity->element_desc("date_format", "nagios_main_desc"); ?>
			<br />
			<br />
                        <label for="use_timezone" style="width:350px; float:left"><b>Timezone Option:</b></label>
                        <input type="text" name="main_config[use_timezone]"  value="<?=$_SESSION['tempData']['main_config']['use_timezone'];?>">
                        <?=$fruity->element_desc("use_timezone", "nagios_main_desc"); ?>
                        <br />
                        <br />
			<label for="illegal_object_name_chars" style="width:350px; float:left"><b>Illegal Object Name Characters:</b></label>
			<input type="text" name="main_config[illegal_object_name_chars]"  value="<?=$_SESSION['tempData']['main_config']['illegal_object_name_chars'];?>">
			<?=$fruity->element_desc("illegal_object_name_chars", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="illegal_macro_output_chars" style="width:350px; float:left"><b>Illegal Macro Output Characters:</b></label>
			<input type="text" name="main_config[illegal_macro_output_chars]"  value="<?=$_SESSION['tempData']['main_config']['illegal_macro_output_chars'];?>">
			<?=$fruity->element_desc("illegal_macro_output_chars", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_regexp_matching" style="width:350px; float:left"><b>Use Regular Expression Matching:</b></label>
			<?php print_select("main_config[use_regexp_matching]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_regexp_matching']);?>
			<?=$fruity->element_desc("use_regexp_matching", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="use_true_regexp_matching" style="width:350px; float:left"><b>Use True Regular Expression Matching:</b></label>
			<?php print_select("main_config[use_true_regexp_matching]", $enable_list, "values", "text", $_SESSION['tempData']['main_config']['use_true_regexp_matching']);?>
			<?=$fruity->element_desc("use_true_regexp_matching", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="admin_email" style="width:350px; float:left"><b>Admin Email:</b></label>
			<input type="text" name="main_config[admin_email]" size=60 value="<?=$_SESSION['tempData']['main_config']['admin_email'];?>">
			<?=$fruity->element_desc("admin_email", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="admin_pager" style="width:350px; float:left"><b>Admin Pager:</b></label>
			<input type="text" name="main_config[admin_pager]" size=60 value="<?=$_SESSION['tempData']['main_config']['admin_pager'];?>">
			<?=$fruity->element_desc("admin_pager", "nagios_main_desc"); ?>
			<br />
			<br />
			<label for="debug_file" style="width:350px; float:left"><b>Debug File:</b></label>
         		<input type="text" name="main_config[debug_file]" size=60 value="<?=$_SESSION['tempData']['main_config']['debug_file'];?>">
         		<?=$fruity->element_desc("debug_file", "nagios_main_desc"); ?>
			<br />
		        <br />
			<label for="debug_level" style="width:350px; float:left"><b>Debug Level:</b></label>
         		<input type="text" name="main_config[debug_level]" value="<?=$_SESSION['tempData']['main_config']['debug_level'];?>">
         		<?=$fruity->element_desc("debug_level", "nagios_main_desc"); ?>
			<br />
         		<br />
			<label for="max_debug_file_size" style="width:350px; float:left"><b>Max Debug File Size:</b></label>
         		<input type="text" name="main_config[max_debug_file_size]" value="<?=$_SESSION['tempData']['main_config']['max_debug_file_size'];?>">
        		 <?=$fruity->element_desc("max_debug_file_size", "nagios_main_desc"); ?>
			<br />
         		<br />
			<label for="debug_verbosity" style="width:350px; float:left"><b>Debug Verbosity:</b></label>
			<?php print_select("main_config[debug_verbosity]", $debug_verbosity_list, "values", "text", $_SESSION['tempData']['main_config']['debug_verbosity']);?>
		         <?=$fruity->element_desc("debug_verbosity", "nagios_main_desc"); ?>
			<br />
         		<br />
                        <br />
		<input type="submit" value="Update Other Configuration" />
		</form>
		<?php
	}
	else if($_GET['section'] == 'broker') {
		$fruity->return_broker_modules($module_list);
		$numOfModules = count($module_list);
		
		print_window_header("Event Broker", "100%", "center");
		
		$broker_list = array();
		$broker_list[] = array("value" => "0", "label" => "Broker nothing");
		$broker_list[] = array("value" => "-1", "label" => "Broker everything");
		
		?>
		<form name="main_broker_config" method="post" action="<?=$path_config['doc_root'];?>main.php?section=broker">
		<input type="hidden" name="request" value="update" />
			<b>Event Broker Options:</b> <?php print_select("main_config[event_broker_options]", $broker_list, "value", "label", $_SESSION['tempData']['main_config']['event_broker_options']);?><br />
			<?=$fruity->element_desc("event_broker_options", "nagios_main_desc"); ?><br />
			<br />
		<input type="submit" value="Update Event Broker Configuration" />
		</form>
		
		<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td colspan="2">Event Broker Modules:</td>
			</tr>
			<?php
			$counter = 0;
			if($numOfModules) {
				foreach($module_list as $module) {
					if($counter % 2) {
						?>
						<tr class="altRow1">
						<?php
					}
					else {
						?>
						<tr class="altRow2">
						<?php
					}
					?>
					<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>main.php?section=broker&request=delete&module_id=<?=$module['module_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
					<td height="20" class="altRight"><b><?=$module['module_line']?></b></td>
					</tr>
					<?php
					$counter++;
				}
			}
			?>
		</table>
		<br />
		<br />
		<b>Add Event Broker Module:</b>
		<form action="<?=$_SERVER['PHP_SELF'];?>?&section=broker" method="post">
		<input type="hidden" name="request" value="module_add" />
		Module Path And Any Arguments:<input type="text" size="50" maxsize="255" name="module_line" /> <input type="submit" value="Add Module" /><br />
		<i>Example:</i> /usr/lib/module.so arg1 arg2 arg3
		</form>
		<?php
	}
	
	print_window_footer();
	?>
	<br />
	<?php
print_footer();
?>
