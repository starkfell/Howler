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


if(isset($_POST['request'])) {
	if(count($_POST['cgi_config'])) {
		foreach($_POST['cgi_config'] as $key=>$value) {
			$_SESSION['tempData']['cgi_config'][$key] = $value;
		}
	}
	if($_POST['request'] == 'update') {
		$fruity->update_cgi_conf($_SESSION['tempData']['cgi_config']);
		unset($_SESSION['tempData']);
		$status_msg = "Updated CGI Configuration.";
	}
}

// Get Existing CGI Configuration
$fruity->get_cgi_conf($_SESSION['tempData']['cgi_config']);
	
// Let's make the status map layout select list
$statusmap_layout_list[] = array("values" => "0", "text" => "User-Defined Coordinates");
$statusmap_layout_list[] = array("values" => "1", "text" => "Depth Layers");
$statusmap_layout_list[] = array("values" => "2", "text" => "Collapsed Tree");
$statusmap_layout_list[] = array("values" => "3", "text" => "Balanced Tree");
$statusmap_layout_list[] = array("values" => "4", "text" => "Circular");
$statusmap_layout_list[] = array("values" => "5", "text" => "Circular (Marked Up)");
$statusmap_layout_list[] = array("values" => "6", "text" => "Circular (Marked Down)");

// Let's make the status wrl layout select list
$statuswrl_layout_list[] = array("values" => "0", "text" => "User-Defined Coordinates");
$statuswrl_layout_list[] = array("values" => "1", "text" => "Depth Layers");
$statuswrl_layout_list[] = array("values" => "2", "text" => "Collapsed Tree");
$statuswrl_layout_list[] = array("values" => "3", "text" => "Balanced Tree");
$statuswrl_layout_list[] = array("values" => "4", "text" => "Circular");

// Refresh Type select list - [Added 11.01.2012 - R. Irujo]
$refresh_type_list[] = array("values" => "0", "text" => "HTTP Header");
$refresh_type_list[] = array("values" => "1", "text" => "JavaScript");

// CGI Log Rotation Method select list - [Added 11.02.2012 - R. Irujo]
$cgi_log_rotation_method_list[] = array("values" => "n", "text" => "None");
$cgi_log_rotation_method_list[] = array("values" => "h", "text" => "Hourly");
$cgi_log_rotation_method_list[] = array("values" => "d", "text" => "Daily");
$cgi_log_rotation_method_list[] = array("values" => "w", "text" => "Weekly");
$cgi_log_rotation_method_list[] = array("values" => "m", "text" => "Monthly");

// Show Child Hosts select list - [Added 11.02.2012 - R. Irujo]
$extinfo_show_child_hosts_list[] = array("values" => "0", "text" => "disabled(default)");
$extinfo_show_child_hosts_list[] = array("values" => "1", "text" => "only show immediate child host(s)");
$extinfo_show_child_hosts_list[] = array("values" => "2", "text" => "show immediate and subsequent child host(s)");


if(!isset($_GET['section']))
	$_GET['section'] = 'paths';


print_header("CGI Configuration File Editor");
?>
&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>cgi.php?section=paths">Paths</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>cgi.php?section=authentication">Authentication</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>cgi.php?section=status">Status</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>cgi.php?section=sounds">Sounds</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>cgi.php?section=other">Other</a><br />
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
		<form name="cgi_path_config" method="post" action="<?=$path_config['doc_root'];?>cgi.php?section=paths">
		<input type="hidden" name="request" value="update" />
		<br />
                <label for="physical_html_path" style="width:180px; float:left"><b>Physical HTML Path</b>:</label>
		<input type="text" size="80" name="cgi_config[physical_html_path]" VALUE="<?=$_SESSION['tempData']['cgi_config']['physical_html_path'];?>"><br />
		<?=$fruity->element_desc("physical_html_path", "nagios_cgi_desc"); ?>
		<br />
                <label for="url_html_path" style="width:180px; float:left"><b>URL HTML Path:</b></label>
		<input type="text" size="80" name="cgi_config[url_html_path]" VALUE="<?=$_SESSION['tempData']['cgi_config']['url_html_path'];?>"><br />
		<?=$fruity->element_desc("url_html_path", "nagios_cgi_desc"); ?>
		<br />
                <label for="cgi_log_file" style="width:180px; float:left"><b>CGI Log File:</b></label>
                <input type="text" size="80" name="cgi_config[cgi_log_file]" VALUE="<?=$_SESSION['tempData']['cgi_config']['cgi_log_file'];?>"><br />
                <?=$fruity->element_desc("cgi_log_file", "nagios_cgi_desc"); ?>
		<br />
                <label for="cgi_log_archive_path" style="width:180px; float:left"><b>CGI Log Archive Path:</b></label>
                <input type="text" size="80" name="cgi_config[cgi_log_archive_path]" VALUE="<?=$_SESSION['tempData']['cgi_config']['cgi_log_archive_path'];?>"><br />
                <?=$fruity->element_desc("cgi_log_archive_path", "nagios_cgi_desc"); ?>
                <br />
                <label for="cgi_log_rotation_method" style="width:180px; float:left"><b>CGI Log Rotation Method:</b></label>
                <?php print_select("cgi_config[cgi_log_rotation_method]", $cgi_log_rotation_method_list, "values", "text", $_SESSION['tempData']['cgi_config']['cgi_log_rotation_method']);?><br />
                <?=$fruity->element_desc("cgi_log_rotation_method", "nagios_cgi_desc"); ?>
                <br />
		<br />
		<br />
		<input type="submit" value="Update Path Configuration" />
		<?php
	}
	else if($_GET['section'] == 'authentication') {
		print_window_header("Authentication", "100%", "center");
		?>
		<form name="cgi_authentication_config" method="post" action="<?=$path_config['doc_root'];?>cgi.php?section=authentication">		
		<input type="hidden" name="request" value="update" />
                <label for="use_authentication" style="width:360px; float:left"><b>Use Authentication:</b></label>
		<?php print_select("cgi_config[use_authentication]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['use_authentication']);?><br />
		<?=$fruity->element_desc("use_authentication", "nagios_cgi_desc"); ?>
		<br />
		<label for="use_ssl_authentication" style="width:360px; float:left"><b>Use SSL Authentication:</b></label>
                <?php print_select("cgi_config[use_ssl_authentication]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['use_ssl_authentication']);?><br />
                <?=$fruity->element_desc("use_ssl_authentication", "nagios_cgi_desc"); ?>
                <br />
		<label for="show_all_services_host_is_authorized_for" style="width:360px; float:left"><b>Show All Services A Host Is Authorized For:</b></label>
                <?php print_select("cgi_config[show_all_services_host_is_authorized_for]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['show_all_services_host_is_authorized_for']);?><br />
                <?=$fruity->element_desc("show_all_services_host_is_authorized_for", "nagios_cgi_desc"); ?>
                <br />
                <label for="default_user_name" style="width:360px; float:left"><b>Default Username:</b></label>
                <input type="text" name="cgi_config[default_user_name]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['default_user_name'];?>"><br />
                <?=$fruity->element_desc("default_user_name", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_for_system_information" style="width:360px; float:left"><b>Authorized For System Information:</b></label>
		<input type="text" name="cgi_config[authorized_for_system_information]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_system_information'];?>"><br />
		<?=$fruity->element_desc("authorized_for_system_information", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_system_commands" style="width:360px; float:left"><b>Authorized For System Commands:</b></label>
		<input type="text" name="cgi_config[authorized_for_system_commands]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_system_commands'];?>"><br />
		<?=$fruity->element_desc("authorized_for_system_commands", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_configuration_information" style="width:360px; float:left"><b>Authorized For Configuration Information:</b></label>
		<input type="text" name="cgi_config[authorized_for_configuration_information]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_configuration_information'];?>"><br />
		<?=$fruity->element_desc("authorized_for_configuration_information", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_all_hosts" style="width:360px; float:left"><b>Authorized For All Hosts:</b></label>
		<input type="text" name="cgi_config[authorized_for_all_hosts]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_all_hosts'];?>"><br />
		<?=$fruity->element_desc("authorized_for_all_hosts", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_all_host_commands" style="width:360px; float:left"><b>Authorized For All Host Commands:</b></label>
		<input type="text" name="cgi_config[authorized_for_all_host_commands]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_all_host_commands'];?>"><br />
		<?=$fruity->element_desc("authorized_for_all_host_commands", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_all_services" style="width:360px; float:left"><b>Authorized For All Services:</b></label>
		<input type="text" name="cgi_config[authorized_for_all_services]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_all_services'];?>"><br />
		<?=$fruity->element_desc("authorized_for_all_services", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_all_service_commands" style="width:360px; float:left"><b>Authorized For All Service Commands:</b></label>
		<input type="text" name="cgi_config[authorized_for_all_service_commands]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_all_service_commands'];?>"><br />
		<?=$fruity->element_desc("authorized_for_all_service_commands", "nagios_cgi_desc"); ?>
		<br />
                <label for="authorized_for_full_command_resolution" style="width:360px; float:left"><b>Allow User(s) Full Command Line View</b></label>
                <input type="text" name="cgi_config[authorized_for_full_command_resolution]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_full_command_resolution'];?>"> - Available starting in Icinga 1.6 <br />
                <?=$fruity->element_desc("authorized_for_full_command_resolution", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_for_read_only" style="width:360px; float:left"><b>Deny User(s) Access To Commands And Comments:</b></label>
                <input type="text" name="cgi_config[authorized_for_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_read_only'];?>"> - Available starting in Icinga 1.5<br />
                <?=$fruity->element_desc("authorized_for_read_only", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_contactgroup_for_read_only" style="width:360px; float:left"><b>Deny ContactGroup(s) Access To Commands And Comments:</b></label>
                <input type="text" name="cgi_config[authorized_contactgroup_for_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_contactgroup_for_read_only'];?>"> - Available starting in Icinga 1.5 <br />
                <?=$fruity->element_desc("authorized_contactgroup_for_read_only", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_for_comments_read_only" style="width:360px; float:left"><b>User(s) Read-Only For Comments:</b></label>
                <input type="text" name="cgi_config[authorized_for_comments_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_comments_read_only'];?>"> - Available starting in Icinga 1.8 <br />
                <?=$fruity->element_desc("authorized_for_comments_read_only", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_contactgroup_for_read_only" style="width:360px; float:left"><b>ContactGroup(s) Read-Only For Comments:</b></label>
                <input type="text" name="cgi_config[authorized_contactgroup_for_comments_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_contactgroup_for_comments_read_only'];?>"> - Available starting in Icinga 1.8 <br />
                <?=$fruity->element_desc("authorized_contactgroup_for_comments_read_only", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_for_downtimes_read_only" style="width:360px; float:left"><b>Deny User(s) Read-Only For Downtimes:</b></label>
                <input type="text" name="cgi_config[authorized_for_downtimes_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_for_downtimes_read_only'];?>"> - Available starting in Icinga 1.8 <br />
                <?=$fruity->element_desc("authorized_for_downtimes_read_only", "nagios_cgi_desc"); ?>
                <br />
                <label for="authorized_contactgroup_for_downtime_read_only" style="width:360px; float:left"><b>Contactgroup(s) Read-Only For Downtimes:</b></label>
                <input type="text" name="cgi_config[authorized_contactgroup_for_downtimes_read_only]" size="120" VALUE="<?=$_SESSION['tempData']['cgi_config']['authorized_contactgroup_for_downtimes_read_only'];?>"> - Available starting in Icinga 1.8 <br />
                <?=$fruity->element_desc("authorized_contactgroup_for_downtimes_read_only", "nagios_cgi_desc"); ?>
                <br />
		<br />
		<br />
		<input type="submit" value="Update Authentication Configuration" />
		<?php
	}
	else if($_GET['section'] == 'status') {
		print_window_header("Status Parameters", "100%", "center");
		?>
		<form name="cgi_authentication_config" method="post" action="<?=$path_config['doc_root'];?>cgi.php?section=status">
		<input type="hidden" name="request" value="update" />
		<br />
		<b>Statusmap Transparency Index Color:</b> - Default Value is White: (R,G,B) = (255,255,255)<br />
		<br />
		<label for="color_transparency_index_r" style="width:300px; float:left">Color Transparency Index (Red):</label>
		<input type="text" name="cgi_config[color_transparency_index_r]" VALUE="<?=$_SESSION['tempData']['cgi_config']['color_transparency_index_r'];?>">
                <?=$fruity->element_desc("color_transparency_index_r", "nagios_cgi_desc"); ?>
		<br />
                <label for="color_transparency_index_g" style="width:300px; float:left">Color Transparency Index (Green):</label>
		<input type="text" name="cgi_config[color_transparency_index_g]" VALUE="<?=$_SESSION['tempData']['cgi_config']['color_transparency_index_g'];?>">
                <?=$fruity->element_desc("color_transparency_index_g", "nagios_cgi_desc"); ?>
		<br />
                <label for="color_transparency_index_b" style="width:300px; float:left">Color Transparency Index (Blue):</label>
                <input type="text" name="cgi_config[color_transparency_index_b]" VALUE="<?=$_SESSION['tempData']['cgi_config']['color_transparency_index_b'];?>">
                <?=$fruity->element_desc("color_transparency_index_b", "nagios_cgi_desc"); ?>
		<br />
		<br />
                <label for="statusmap_background_image" style="width:300px; float:left"><b>Status Map Background Image:</b></label>
                <input type="text" name="cgi_config[statusmap_background_image]" VALUE="<?=$_SESSION['tempData']['cgi_config']['statusmap_background_image'];?>">
                <?=$fruity->element_desc("statusmap_background_image", "nagios_cgi_desc"); ?><br />
                <br />
                <label for="default_statusmap_layout" style="width:300px; float:left"><b>Default Status Map Layout:</b></label>
		<?php print_select("cgi_config[default_statusmap_layout]", $statusmap_layout_list, "values", "text", $_SESSION['tempData']['cgi_config']['default_statusmap_layout']);?>
		<?=$fruity->element_desc("default_statusmap_layout", "nagios_cgi_desc"); ?><br />
		<br />
		<label for="statuswrl_include" style="width:300px; float:left"><b>Statuswrl Include:</b></label>
		<input type="text" name="cgi_config[statuswrl_include]" VALUE="<?=$_SESSION['tempData']['cgi_config']['statuswrl_include'];?>">
		<?=$fruity->element_desc("statuswrl_include", "nagios_cgi_desc"); ?><br />
		<br />
                <label for="default_statuswrl_layout" style="width:300px; float:left"><b>Default Statuswrl Layout:</b></label>
		<?php print_select("cgi_config[default_statuswrl_layout]", $statuswrl_layout_list, "values", "text", $_SESSION['tempData']['cgi_config']['default_statuswrl_layout']);?>
		<?=$fruity->element_desc("default_statuswrl_layout", "nagios_cgi_desc"); ?><br />
		<br />
		<br />
		<br />
                 <label for="http_charset" style="width:300px; float:left"><b>HTTP Character Set:</b></label>
                 <input type="text" name="cgi_config[http_charset]" size="10" VALUE="<?=$_SESSION['tempData']['cgi_config']['http_charset'];?>"><br />
                <?=$fruity->element_desc("http_charset", "nagios_cgi_desc"); ?>
		<br />
		<label for="refresh_rate" style="width:300px; float:left"><b>Refresh Rate:</b></label>
		<input type="text" name="cgi_config[refresh_rate]" size="10" VALUE="<?=$_SESSION['tempData']['cgi_config']['refresh_rate'];?>"><br />
		<?=$fruity->element_desc("refresh_rate", "nagios_cgi_desc"); ?>
		<br />
                <label for="default_downtime_duration" style="width:300px; float:left"><b>Default Downtime Duration:</b></label>
                <input type="text" name="cgi_config[default_downtime_duration]" VALUE="<?=$_SESSION['tempData']['cgi_config']['default_downtime_duration'];?>"> - Available starting in Icinga 1.5<br />
                <?=$fruity->element_desc("default_downtime_duration", "nagios_cgi_desc"); ?>
		<br />
                <label for="default_expiring_disabled_notifications_duration" style="width:300px; float:left"><b>Default Expiring Disabled Notifications Duration:</b></label>
                <input type="text" name="cgi_config[default_expiring_disabled_notifications_duration]" VALUE="<?=$_SESSION['tempData']['cgi_config']['default_expiring_disabled_notifications_duration'];?>"> - Available starting in Icinga 1.8 <br />
                <?=$fruity->element_desc("default_expiring_disabled_notifications_duration", "nagios_cgi_desc"); ?>
		<br />
                <label for="default_expiring_acknowledgement_duration" style="width:300px; float:left"><b>Default Expiring Acknowledgement Duration:</b></label>
                <input type="text" name="cgi_config[default_expiring_acknowledgement_duration]" VALUE="<?=$_SESSION['tempData']['cgi_config']['default_expiring_acknowledgement_duration'];?>"> - Available starting in Icinga 1.6 <br />
                <?=$fruity->element_desc("default_expiring_acknowledgement_duration", "nagios_cgi_desc"); ?>
                <br />
                <label for="add_notif_num_hard" style="width:300px; float:left"><b>Show Service State (Hard) And Notification Number:</b></label>
		 <input type="text" name="cgi_config[add_notif_num_hard]" VALUE="<?=$_SESSION['tempData']['cgi_config']['add_notif_num_hard'];?>"><br />
                <?=$fruity->element_desc("add_notif_num_hard", "nagios_cgi_desc"); ?>
		<br />
                <label for="add_notif_num_soft" style="width:300px; float:left"><b>Show Service State (Soft) And Notification Number:</b></label>
		<input type="text" name="cgi_config[add_notif_num_soft]" VALUE="<?=$_SESSION['tempData']['cgi_config']['add_notif_num_soft'];?>"><br />
                <?=$fruity->element_desc("add_notif_num_soft", "nagios_cgi_desc"); ?>
		<br />
                <label for="result_limit" style="width:300px; float:left"><b>Limit Number Of Page Entries Displayed:</b></label>
                <input type="text" name="cgi_config[result_limit]" VALUE="<?=$_SESSION['tempData']['cgi_config']['result_limit'];?>"> - Available starting in Icinga 1.8<br />
                <?=$fruity->element_desc("result_limit", "nagios_cgi_desc"); ?>
                <br />
                <label for="extinfo_show_child_hosts" style="width:300px; float:left"><b>Show Child Hosts:</b></label>
                <?php print_select("cgi_config[extinfo_show_child_hosts]", $extinfo_show_child_hosts_list, "values", "text", $_SESSION['tempData']['cgi_config']['extinfo_show_child_hosts']);?> - Available starting in Icinga 1.6<br />
                <?=$fruity->element_desc("extinfo_show_child_hosts", "nagios_cgi_desc"); ?>
                <br />
                <label for="refresh_type" style="width:300px; float:left"><b>CGI Refresh Type:</b></label>
                <?php print_select("cgi_config[refresh_type]", $refresh_type_list, "values", "text", $_SESSION['tempData']['cgi_config']['refresh_type']);?> - Available starting in Icinga 1.7<br />
                <?=$fruity->element_desc("refresh_type", "nagios_cgi_desc"); ?>
                <br />
                <label for="suppress_maintenance_downtime" style="width:300px; float:left"><b>Suppress Maintenance Downtime:</b></label>
                <?php print_select("cgi_config[suppress_maintenance_downtime]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['suppress_maintenance_downtime']);?><br />
                <?=$fruity->element_desc("suppress_maintenance_downtime", "nagios_cgi_desc"); ?>
                <br />
		<label for="persistent_ack_comments" style="width:300px; float:left"><b>Persistent Acknowledge Comments:</b></label>
                <?php print_select("cgi_config[persistent_ack_comments]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['persistent_ack_comments']);?><br />
                <?=$fruity->element_desc("persistent_ack_comments", "nagios_cgi_desc"); ?>
                <br />
		<label for="enforce_comments_on_actions" style="width:300px; float:left"><b>Enforce Comments On Actions:</b></label>
                <?php print_select("cgi_config[enforce_comments_on_actions]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['enforce_comments_on_actions']);?><br />
                <?=$fruity->element_desc("enforce_comments_on_actions", "nagios_cgi_desc"); ?>
                <br />
		<label for="lock_author_names" style="width:300px; float:left"><b>Lock Author Names:</b></label>
		<?php print_select("cgi_config[lock_author_names]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['lock_author_names']);?><br />
		<?=$fruity->element_desc("lock_author_names", "nagios_cgi_desc"); ?>
		<br />
		<label for="status_show_long_plugin_output" style="width:300px; float:left"><b>Status Show Long Plugin Output:</b></label>
                <?php print_select("cgi_config[status_show_long_plugin_output]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['status_show_long_plugin_output']);?><br />
                <?=$fruity->element_desc("status_show_long_plugin_output", "nagios_cgi_desc"); ?>
                <br />
                <label for="tab_friendly_titles" style="width:300px; float:left"><b>Show Object Type in Tab Title:</b></label>
                <?php print_select("cgi_config[tab_friendly_titles]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['tab_friendly_titles']);?><br />
                <?=$fruity->element_desc("tab_friendly_titles", "nagios_cgi_desc"); ?>
                <br />
                <label for="show_partial_hostgroups" style="width:300px; float:left"><b>Show Partial Hostgroups:</b></label>
                <?php print_select("cgi_config[show_partial_hostgroups]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['show_partial_hostgroups']);?><br />
                <?=$fruity->element_desc("show_partial_hostgroups", "nagios_cgi_desc"); ?>
                <br />
                <label for="display_status_totals" style="width:300px; float:left"><b>Display Host/Service Status Totals:</b></label>
                <?php print_select("cgi_config[display_status_totals]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['display_status_totals']);?> - Available starting in Icinga 1.7<br />
                <?=$fruity->element_desc("display_status_totals", "nagios_cgi_desc"); ?>
                <br />
                <label for="lowercase_user_name" style="width:300px; float:left"><b>Convert Username To Lowercase:</b></label>
                <?php print_select("cgi_config[lowercase_user_name]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['lowercase_user_name']);?> - Available starting in Icinga 1.8<br />
                <?=$fruity->element_desc("lowercase_user_name", "nagios_cgi_desc"); ?>
                <br />
		<label for="show_tac_header" style="width:300px; float:left"><b>Show Tactical Header (TAC):</b></label>
                <?php print_select("cgi_config[show_tac_header]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['show_tac_header']);?><br />
                <?=$fruity->element_desc("show_tac_header", "nagios_cgi_desc"); ?>
                <br />
		<label for="show_tac_header_pending" style="width:300px; float:left"><b>TAC Show Pending Counts:</b></label>
                <?php print_select("cgi_config[show_tac_header_pending]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['show_tac_header_pending']);?><br />
                <?=$fruity->element_desc("show_tac_header_pending", "nagios_cgi_desc"); ?>
                <br />
		<label for="tac_show_only_hard_state" style="width:300px; float:left"><b>TAC Show Only Hard State</b></label>
                <?php print_select("cgi_config[tac_show_only_hard_state]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['tac_show_only_hard_state']);?><br />
                <?=$fruity->element_desc("tac_show_only_hard_state", "nagios_cgi_desc"); ?>
                <br />
                <label for="use_logging" style="width:300px; float:left"><b>Log CGI Commands:</b></label>
                <?php print_select("cgi_config[use_logging]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['use_logging']);?><br />
                <?=$fruity->element_desc("use_logging", "nagios_cgi_desc"); ?>
                <br />
                <label for="showlog_initial_states" style="width:300px; float:left"><b>CGI Log - Show Initial States:</b></label>
                <?php print_select("cgi_config[showlog_initial_states]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['showlog_initial_states']);?><br />
                <?=$fruity->element_desc("showlog_initial_states", "nagios_cgi_desc"); ?>
                <br />
                <label for="showlog_current_states" style="width:300px; float:left"><b>CGI Log - Show Current States:</b></label>
                <?php print_select("cgi_config[showlog_current_states]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['showlog_current_states']);?><br />
                <?=$fruity->element_desc("showlog_current_states", "nagios_cgi_desc"); ?>
		<br />
                <label for="use_pending_states" style="width:300px; float:left"><b>Use Pending States:</b></label>
                <?php print_select("cgi_config[use_pending_states]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['use_pending_states']);?><br />
                <?=$fruity->element_desc("use_pending_states", "nagios_cgi_desc"); ?>
                <br />
                <label for="action_url_target" style="width:300px; float:left"><b>Action URL Target:</b></label>
                <input type="text" size=120 name="cgi_config[action_url_target]" VALUE="<?=$_SESSION['tempData']['cgi_config']['action_url_target'];?>"><br />
                <?=$fruity->element_desc("action_url_target", "nagios_cgi_desc"); ?>
                <br />
                <label for="notes_url_target" style="width:300px; float:left"><b>Notes URL Target:</b></label>
                <input type="text" size=120 name="cgi_config[notes_url_target]" VALUE="<?=$_SESSION['tempData']['cgi_config']['notes_url_target'];?>"><br />
                <?=$fruity->element_desc("notes_url_target", "nagios_cgi_desc"); ?>
                <br />
		<br />
		<br />
		<input type="submit" value="Update Status Configuration" />
		<?php
	}
	else if($_GET['section'] == 'sounds') {
		print_window_header("Status Parameters", "100%", "center");
		?>
		<form name="cgi_authentication_config" method="post" action="<?=$path_config['doc_root'];?>cgi.php?section=sounds">
		<input type="hidden" name="request" value="update" />
		<br />
		<b>Audio Files</b> are assumed to be in the <b>media/</b> subdirectory in your HTML directory (i.e. - <i>/usr/local/icinga/share/media</i> )
		<br />
		<br />
		<br />
                <label for="normal_sound" style="width:180px; float:left"><b>Normal Sound:</label>
                <input type="text" name="cgi_config[normal_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['normal_sound'];?>"><br />
                <?=$fruity->element_desc("normal_sound", "nagios_cgi_desc"); ?>
		<br />
                <label for="host_unreachable_sound" style="width:180px; float:left"><b>Host Unreachable Sound:</label>
		<input type="text" name="cgi_config[host_unreachable_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['host_unreachable_sound'];?>"><br />
		<?=$fruity->element_desc("host_unreachable_sound", "nagios_cgi_desc"); ?>
		<br />
                <label for="host_down_sound" style="width:180px; float:left"><b>Host Down Sound:</label>
		<input type="text" name="cgi_config[host_down_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['host_down_sound'];?>"><br />
		<?=$fruity->element_desc("host_down_sound", "nagios_cgi_desc"); ?>
		<br />
                <label for="service_critical_sound" style="width:180px; float:left"><b>Service Critical Sound:</label>
		<input type="text" name="cgi_config[service_critical_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['service_critical_sound'];?>"><br />
		<?=$fruity->element_desc("service_critical_sound", "nagios_cgi_desc"); ?>
		<br />
                <label for="service_warning_sound" style="width:180px; float:left"><b>Service Warning Sound:</label>
		<input type="text" name="cgi_config[service_warning_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['service_warning_sound'];?>"><br />
		<?=$fruity->element_desc("service_warning_sound", "nagios_cgi_desc"); ?>
		<br />
                <label for="service_unknown_sound" style="width:180px; float:left"><b>Service Unknown Sound:</label>
		<input type="text" name="cgi_config[service_unknown_sound]" VALUE="<?=$_SESSION['tempData']['cgi_config']['service_unknown_sound'];?>"><br />
		<?=$fruity->element_desc("service_unknown_sound", "nagios_cgi_desc"); ?>
		<br />
		<br />
		<br />
		<input type="submit" value="Update Sound Configuration" />
		<?php
	}
	else if($_GET['section'] == 'other') {
		print_window_header("Other", "100%", "center");
		?>
		<form name="cgi_authentication_config" method="post" action="<?=$path_config['doc_root'];?>cgi.php?section=other">
		<input type="hidden" name="request" value="update" />                
                <label for="enable_splunk_integration" style="width:240px; float:left"><b>Enable Splunk Integration:</b></label>
		<?php print_select("cgi_config[enable_splunk_integration]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['enable_splunk_integration']);?><br />
                <?=$fruity->element_desc("enable_splunk_integration", "nagios_cgi_desc"); ?>
                <br />
                <label for="splunk_url" style="width:240px; float:left"><b>Splunk URL:</b></label>
                <input type="text" size="120" name="cgi_config[splunk_url]" VALUE="<?=$_SESSION['tempData']['cgi_config']['splunk_url'];?>"><br />
                <?=$fruity->element_desc("splunk_url", "nagios_cgi_desc"); ?>
		<br />
                <label for="show_context_help" style="width:240px; float:left"><b>Context Sensitive Help:</b></label>
		<?php print_select("cgi_config[show_context_help]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['show_context_help']);?><br />
		<?=$fruity->element_desc("show_context_help", "nagios_cgi_desc"); ?>
		<br />
		<label for="escape_html_tags" style="width:240px; float:left"><b>Escape HTML Tags:</b></label>
		<?php print_select("cgi_config[escape_html_tags]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['escape_html_tags']);?><br />
		<?=$fruity->element_desc("escape_html_tags", "nagios_cgi_desc"); ?>
		<br />
                <label for="highlight_table_rows" style="width:240px; float:left"><b>Highlight Table Rows:</b></label>
                <?php print_select("cgi_config[hightlight_table_rows]", $enable_list, "values", "text", $_SESSION['tempData']['cgi_config']['highlight_table_rows']);?><br />
                <?=$fruity->element_desc("highlight_table_rows", "nagios_cgi_desc"); ?>
                <br />
		<label for="csv_delimiter" style="width:240px; float:left"><b>Field Separator For CSV Export:</b></label>
                <input type="text" size="20" name="cgi_config[csv_delimiter]" VALUE="<?=$_SESSION['tempData']['cgi_config']['csv_delimiter'];?>"><br />
                <?=$fruity->element_desc("csv_delimiter", "nagios_cgi_desc"); ?>
                <br />
                <label for="csv_data_enclosure" style="width:240px; float:left"><b>Field Enclosure Character For CSV Export:</b></label>
                <input type="text" size="20" name="cgi_config[csv_data_enclosure]" VALUE="<?=$_SESSION['tempData']['cgi_config']['csv_data_enclosure'];?>"><br />
                <?=$fruity->element_desc("csv_data_enclosure", "nagios_cgi_desc"); ?>
		<br />
                <label for="first_day_of_week" style="width:240px; float:left"><b>Set First Day Of Week:</b></label>
                <input type="text" size="20" name="cgi_config[first_day_of_week]" VALUE="<?=$_SESSION['tempData']['cgi_config']['first_day_of_week'];?>"><br />
                <?=$fruity->element_desc("first_day_of_week", "nagios_cgi_desc"); ?>
                <br />
		<label for="nagios_check_command" style="width:240px; float:left"><b>OpMon Check Command:</b></label>
                <input type="text" size="120" name="cgi_config[nagios_check_command]" VALUE="<?=$_SESSION['tempData']['cgi_config']['nagios_check_command'];?>"><br />
                <?=$fruity->element_desc("nagios_check_command", "nagios_cgi_desc"); ?>
                <br />
		<label for="ping_syntax" style="width:240px; float:left"><b>Ping Syntax:</b></label>
		<input type="text" size="120" name="cgi_config[ping_syntax]" VALUE="<?=$_SESSION['tempData']['cgi_config']['ping_syntax'];?>"><br />
		<?=$fruity->element_desc("ping_syntax", "nagios_cgi_desc"); ?>
		<br />
		<br />
		<br />
		<input type="submit" value="Update Other Configuration" />
		<?php
	}		
	print_window_footer();

print_footer();
?>
