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
 * host_templates.php
 * Author:	Taylor Dondich (tdondich at gmail.com)
 * Description:
 * 	Provides interface to maintain host templates
 *
*/
 


include_once('includes/config.inc');

// Data preparation
// SF BUG# 1445803
// templating error with fruity 1.0rc
$tempInheritedValues = array();
$tempInheritedValuesSources = array();

if (isset($_REQUEST['delete_msg']))
	$status_msg = $_REQUEST['delete_msg'];

if(!isset($_GET['section']))
	$_GET['section'] = 'general';

// Get rid of initial data
unset($_SESSION['tempData']['host_manage']);

// If we're going to modify host data
if(isset($_GET['host_template_id']) && 
		($_GET['section'] == "general" ||
		$_GET['section'] == "checks" ||
		$_GET['section'] == "flapping" || 
		$_GET['section'] == "logging" || 
		$_GET['section'] == "notifications") &&
		$_GET['edit']) {
	$fruity->get_host_template_info($_GET['host_template_id'], $_SESSION['tempData']['host_manage']);
	$_SESSION['tempData']['host_manage']['old_name'] = $_SESSION['tempData']['host_manage']['template_name'];
	
}
	
// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'groups') {
			$fruity->get_host_template_membership_list($_GET['host_template_id'], $hostgroup_list);			
			$fruity->delete_hostgroup_template_member($_GET['hostgroup_id'], $_GET['host_template_id']);
			$status_msg = "Membership Deleted";
			unset($_SESSION['tempData']['host_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'services') {
			
			// Remove inherited service from reports
			$host_array = array();
			$fruity->get_hosts_affected_by_host_template( $_GET['host_template_id'], $host_array );
		
			if (count($host_array)) {	
				foreach( $host_array as $host_id ) {
					$host_name = $fruity->return_host_name($host_id);
//					removeReportsByObject( $host_name, $fruity->return_service_description($_GET['service_id']) );
				}
			}
						
			// Remove service
			$fruity->restart();
			$fruity->delete_service($_GET['service_id']);
			$status_msg = "Service Deleted";
			unset($_SESSION['tempData']['host_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'general') {

			// Remove inherited services from reports
			$services_list = array();
			$fruity->get_host_template_services_list( $_GET['host_template_id'], $services_list );
		
			$host_array = array();
			$fruity->get_hosts_affected_by_host_template( $_GET['host_template_id'], $host_array );
		
			if (count($host_array)) {	
				foreach( $host_array as $host_id ) {
					$host_name = $fruity->return_host_name($host_id);
					foreach ( $services_list as $service ) {
//						removeReportsByObject( $host_name, $service['service_description'] );
					}
				}
			}

			// Remove template
			$fruity->restart();
			$fruity->delete_host_template($_GET['host_template_id']);
			$status_msg = "Host template deleted and attributes propagated.";
			unset($_SESSION['tempData']['host_manage']);
			unset($_GET['host_template_id']);
			unset($_GET['request']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_host_template_contactgroup($_GET['host_template_id'], $_GET['contactgroup_id']);
			$status_msg = "Contact Group Deleted";
			unset($_SESSION['tempData']['host_manage']);
			unset($_GET['request']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'dependencies') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_dependency($_GET['dependency_id']);
			$status_msg = "Dependency Deleted";
			unset($_SESSION['tempData']['host_manage']);
			unset($_GET['request']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'escalations') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_escalation($_GET['escalation_id']);
			$status_msg = "Escalation Deleted";
			unset($_SESSION['tempData']['host_manage']);
			unset($_GET['request']);
		}	
		if($_GET['request'] == "delete" && $_GET['section'] == 'checkcommand') {
			$fruity->delete_host_template_checkcommand_parameter($_GET['checkcommandparameter_id']);
			$status_msg = "Check Command Parameter Deleted.";
		}
}

if(isset($_POST['request'])) {
	if(count($_POST['host_manage'])) {
		foreach( $_POST['host_manage'] as $key=>$value) {
			if( is_array( $value)) {
				$_SESSION['tempData']['host_manage'][$key] = $value;
			} else {
				$_SESSION['tempData']['host_manage'][$key] = (string)$value;
			}
		}
	}
	
	// Enabler checks
	if(count($_POST['host_manage_enablers'])) {
		foreach($_POST['host_manage_enablers'] as $key=>$value) {
			if($value == 0) {
				$_SESSION['tempData']['host_manage'][$key] = NULL;
			}
		}
	}
	if($_POST['request'] == 'add_host_template') {
		// Check for pre-existing host template with same name
		if($fruity->host_template_exists($_SESSION['tempData']['host_manage']['template_name'])) {
			$status_msg = "A host template with that name already exists!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['host_manage'])) {
				foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['host_manage']['template_name'] == '' || $_SESSION['tempData']['host_manage']['template_description'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				if($_SESSION['tempData']['host_manage']['use_template_id'] == '')
					unset($_SESSION['tempData']['host_manage']['use_template_id']);
				// All is well for error checking, add the host into the db.
				if($fruity->add_host_template( $_SESSION['tempData']['host_manage'])) {
					// Check to see if using version 2, and add contact_groups if
					$tempHostTemplateID = $fruity->return_host_template_id_by_name($_SESSION['tempData']['host_manage']['template_name']);
					// Remove session data
					unset($_SESSION['tempData']['host_manage']);
					unset($_GET['child_host_add']);
					$status_msg = "Host Template Added.";
				}
				else {
					$addError = 1;
					$status_msg = "Error: add_host_template failed.";
				}
			}
		}
	}
	else if($_POST['request'] == 'host_template_modify_general') {
		if($_SESSION['tempData']['host_manage']['template_name'] != $_SESSION['tempData']['host_manage']['old_name'] && $fruity->host_template_exists($_SESSION['tempData']['host_manage']['template_name'])) {
			$status_msg = "A host with that name already exists!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['host_manage'])) {
				foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['host_manage']['template_name'] == '' || $_SESSION['tempData']['host_manage']['template_description'] == '') {
				$addError = 1;
				$status_msg = "Incorrect values for fields.  Please verify.";
			}
			// All is well for error checking, modify the command.
			else if($fruity->modify_host_template($_SESSION['tempData']['host_manage'])) {
				// Remove session data
				unset($_SESSION['tempData']['host_manage']);
				$status_msg = "Host template modified.";
				unset($_GET['edit']);
			}
			else {
				$status_msg = "Error: modify_host_template failed.";
			}
		}
	}
	else if($_POST['request'] == 'host_template_modify_checks') {
		if(count($_SESSION['tempData']['host_manage'])) {
			foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		if(($_POST['max_check_attempts_include'] && !is_numeric($_SESSION['tempData']['host_manage']['max_check_attempts'])) || ($_POST['max_check_attempts_include'] && !($_SESSION['tempData']['host_manage']['max_check_attempts'] >= 1)) || ($_POST['freshness_threshold_include'] && !($_SESSION['tempData']['host_manage']['freshness_threshold'] >= 0))) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the template.
		
		else if($fruity->modify_host_template($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host template modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host_template failed.";
		}
	}
	else if($_POST['request'] == 'host_template_modify_flapping') {
		// Field Error Checking
		if(count($_SESSION['tempData']['host_manage'])) {
			foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		if((isset($_SESSION['tempData']['host_manage']['low_flap_threshold']) && $_SESSION['tempData']['host_manage']['low_flap_threshold'] < 0) || (isset($_SESSION['tempData']['host_manage']['high_flap_threshold']) && $_SESSION['tempData']['host_manage']['high_flap_threshold'] < 0)) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the command.
		else if($fruity->modify_host_template($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host template modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host_template failed.";
		}
	}
	else if($_POST['request'] == 'host_template_modify_logging') {
		// Field Error Checking
		// None required for this process
		// All is well for error checking, modify the command.
		if($fruity->modify_host_template($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host template modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host_template failed.";
		}
	}
	else if($_POST['request'] == 'host_template_modify_notifications') {
		// Field Error Checking
		if(count($_SESSION['tempData']['host_manage'])) {
			foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
			
		if(!$_POST['host_manage_enablers']['notification_options']) {
			$_SESSION['tempData']['host_manage']['notification_options_down'] = NULL;
			$_SESSION['tempData']['host_manage']['notification_options_unreachable'] = NULL;
			$_SESSION['tempData']['host_manage']['notification_options_recovery'] = NULL;
			$_SESSION['tempData']['host_manage']['notification_options_flapping'] = NULL;
		}
		else {
			if(!isset($_POST['host_manage']['notification_options_down']))
				$_SESSION['tempData']['host_manage']['notification_options_down'] = '0';
			if(!isset($_POST['host_manage']['notification_options_unreachable']))
				$_SESSION['tempData']['host_manage']['notification_options_unreachable'] = '0';
			if(!isset($_POST['host_manage']['notification_options_recovery']))
				$_SESSION['tempData']['host_manage']['notification_options_recovery'] = '0';
			if(!isset($_POST['host_manage']['notification_options_flapping']))
				$_SESSION['tempData']['host_manage']['notification_options_flapping'] = '0';
		}
		
		if(!$_POST['host_manage_enablers']['stalking_options']) {
			$_SESSION['tempData']['host_manage']['stalking_options_up'] = NULL;
			$_SESSION['tempData']['host_manage']['stalking_options_down'] = NULL;
			$_SESSION['tempData']['host_manage']['stalking_options_unreachable'] = NULL;
		}
		else {
			if(!isset($_POST['host_manage']['stalking_options_up']))
				$_SESSION['tempData']['host_manage']['stalking_options_up'] = '0';
			if(!isset($_POST['host_manage']['stalking_options_down']))
				$_SESSION['tempData']['host_manage']['stalking_options_down'] = '0';
			if(!isset($_POST['host_manage']['stalking_options_unreachable']))
				$_SESSION['tempData']['host_manage']['stalking_options_unreachable'] = '0';
		}
		
		if($_POST['host_manage_enablers']['notification_interval'] && 
			($_SESSION['tempData']['host_manage']['notification_interval'] == '' || $_SESSION['tempData']['host_manage']['notification_interval'] < 0 || !is_numeric($_SESSION['tempData']['host_manage']['notification_interval']))) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the command.
		else if($fruity->modify_host_template($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host template modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host_template failed.";
		}
	}	
	else if($_POST['request'] == 'add_host_service') {
		if(host_has_service($_GET['host_template_id'], $_SESSION['tempData']['host_manage']['service_id'])) {
			$status_msg = "That host already has that service linked.";
		}
		else {
			// All is well, link the service definition.
			link_host_service($_GET['host_template_id'], $_SESSION['tempData']['host_manage']['service_id']);
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Service linked to this host.";
		}
	}
	else if($_POST['request'] == 'add_member_command') {
		if($fruity->host_template_has_hostgroup($_GET['host_template_id'], $_SESSION['tempData']['host_manage']['group_add']['hostgroup_id'])) {
			$status_msg = "That host group already exists in that list!";
		}
		else {
			$fruity->add_hostgroup_template_member($_SESSION['tempData']['host_manage']['group_add']['hostgroup_id'], $_SESSION['tempData']['host_manage']['group_add']['host_id']);
			$status_msg = "Host Added To Host Group.";
			unset($_SESSION['tempData']['host_manage']);
		}
	}
	else if($_POST['request'] == 'command_parameter_add') {
		// All is well for error checking, modify the command.
		$fruity->add_host_template_command_parameter($_GET['host_template_id'], $_SESSION['tempData']['host_manage']);
		// Remove session data
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Command Parameter added.";
	}
	else if($_POST['request'] == 'command_parameter_modify') {
		
		$fruity->delete_host_template_checkcommand_parameter( "", $_GET['host_template_id']);
		
		$temp = array();
		$temp['host_template_id'] = $_GET['host_template_id'];
		for ( $i = 1; $i <= $_POST['numCheckCommandParameters']; $i++ ) {
			$temp['parameter'] = $_POST["ARG$i"];
			$fruity->add_host_template_command_parameter( $_GET['host_template_id'], $temp );
		}
		// Remove session data
		unset($temp);
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Command Parameter modified.";
	}
	if($_POST['request'] == 'update_host_extended') {
		$fruity->modify_host_template_extended($_GET['host_template_id'], $_SESSION['tempData']['host_manage']);
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Updated Host Extended Information";
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->host_template_has_contactgroup($_GET['host_template_id'], $_SESSION['tempData']['host_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['host_manage']);
		}
		else {
			$fruity->add_host_template_contactgroup($_GET['host_template_id'], $_SESSION['tempData']['host_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Host Contact Group Link added.";
			unset($_SESSION['tempData']['host_manage']);
		}
	}
	
	
}


if(isset($_GET['host_template_id'])) {
	
	
	if(!$fruity->get_host_template_info($_GET['host_template_id'], $tempHostTemplateInfo)) {
		$invalidHost = 1;
		$status_msg = "That host template is not valid in the database.";
		unset($_GET['host_template_id']);
	}
	else {
		
	
		// Check to see if we inherit from another template
		if(isset($tempHostTemplateInfo['use_template_id'])) {
			// Then we actually use a template
			// We now need to obtain the inherited values
			$result = $fruity->get_inherited_host_template_values($tempHostTemplateInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
			
			// We need to load up $_SESSION with our inherited values.
			if(count($tempInheritedValues))
			foreach($tempInheritedValues as $key=>$value) {
				if(isset($tempInheritedValues[$key]) && !isset($tempHostTemplateInfo[$key])) {
					$_SESSION['tempData']['host_manage'][$key] = $value;
				}
			}
		}
		
		
		// quick interation to enable values explicitly defined in this template, NOT inherited values
		if(is_array($tempHostTemplateInfo)) {
			foreach(array_keys($tempHostTemplateInfo) as $key) {
				if(isset($tempHostTemplateInfo[$key]))
					$_POST['host_manage_enablers'][$key] = '1';
			}
		}
		// special cases
		if(isset($tempHostTemplateInfo['notification_options_down']) || isset($tempHostTemplateInfo['notification_options_unreachable']) || isset($tempHostTemplateInfo['notification_options_recovery']) || isset($tempHostTemplateInfo['notification_options_flapping']))
			$_POST['host_manage_enablers']['notification_options'] = 1;
		if(isset($tempHostTemplateInfo['stalking_options_up']) || isset($tempHostTemplateInfo['stalking_options_down']) || isset($tempHostTemplateInfo['stalking_options_unreachable']))
			$_POST['host_manage_enablers']['stalking_options'] = 1;
	}
}

// Extended info help
if(isset($_GET['host_template_id']) && $_GET['section'] == "extended") {
	if($fruity->get_host_template_extended_info($_GET['host_template_id'], $tempHostTemplateExtendedInfo)) {
		if($tempHostTemplateExtendedInfo != NULL) {
			foreach(array_keys($tempHostTemplateExtendedInfo) as $key) {
				if(isset($tempHostTemplateExtendedInfo[$key]))
					$_POST['host_manage_enablers'][$key] = '1';
			}
		}
		
		if(isset($tempHostTemplateInfo['use_template_id'])) {
				$result = $fruity->get_inherited_host_template_extended_values($tempHostTemplateInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
				// Let's load up $_SESSION
				if(count($tempInheritedValues))
				foreach($tempInheritedValues as $key=>$value) {
					if(isset($tempInheritedValues[$key]) && !isset($tempHostTemplateExtendedInfo[$key])) {
						$_SESSION['tempData']['host_manage'][$key] = $value;
					}
				}
		}
		
		
		// Okay, now let's overwrite those values with the ones from tempHostTemplateExtendedInfo
		if(isset($tempHostTemplateExtendedInfo)) {
			foreach($tempHostTemplateExtendedInfo as $key=>$value) {
				if(isset($tempHostTemplateExtendedInfo[$key]))
					$_SESSION['tempData']['host_manage'][$key] = $value;
			}
		}
	}
}

// Get list of host templates
$fruity->get_host_template_list($template_list);
$numOfTemplates = count($template_list);


// To create a "default" command
$command_list[] = array("command_id" => 0, "command_name" => "None");
$fruity->return_command_list($command_list);

$fruity->return_period_list($period_list);



print_header("Host Template Editor");
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($_GET['host_template_id'])) {
	print_window_header("Template Info for " . $tempHostTemplateInfo['template_name'], "100%");	
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=checks">Checks</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=flapping">Flapping</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=logging">Logging</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=notifications">Notifications</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=services">Services</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=groups">Group Membership</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=contactgroups">Contact Groups</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=extended">Extended Information</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=dependencies">Dependencies</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=escalations">Escalations</a><?php if(isset($tempHostTemplateInfo['check_command']) || isset($tempInheritedValuesSources['check_command'])) { ?> | <a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=checkcommand">Check Command Parameters</a><?php } ?>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			if($fruity->get_host_template_icon_image($_GET['host_template_id'], $host_icon_image)) {
				$host_template_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_template_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_template_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general&edit=1">
					<input type="hidden" name="request" value="host_template_modify_general" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
					<b>Template Name:</b><br />
					<input type="text" size="40" name="host_manage[template_name]" value="<?=$_SESSION['tempData']['host_manage']['template_name'];?>" onblur="this.value=changeCharCode(this.value);"><br />
					<?=$fruity->element_desc("template_name", "nagios_host_template_desc"); ?><br />
					<br />		
					<b>Description:</b><br />
					<input type="text" size="80" name="host_manage[template_description]" value="<?=$_SESSION['tempData']['host_manage']['template_description'];?>"><br />
					<?=$fruity->element_desc("template_description", "nagios_host_template_desc"); ?><br />
					<br />

					<!-- Begin Template Changer -->
					<?php
						$add_template_list[] = array("host_template_id" => '', "template_name" => "None");
						$fruity->get_host_template_list( $template_list);
						
						if(count($template_list)) {
							foreach($template_list as $tempTemplate) {
								$add_template_list[] = $tempTemplate;
							}
						}
				
					?>
					<b>Uses Host Template:</b><br />
					<?php print_select("host_manage[use_template_id]", $add_template_list, "host_template_id", "template_name", $_SESSION['tempData']['host_manage']['use_template_id']);?><br />
					If this template is to inherit from another template, specify that template's name now.
					<br /><br />
					<!-- End Template Changer -->


					<br />
					<input type="submit" value="Update General" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Template Name:</b> <?=$tempHostTemplateInfo['template_name'];?><br />
					<b>Description:</b> <?=$tempHostTemplateInfo['template_description'];?><br />
					<?php
					if(isset($tempHostTemplateInfo['use_template_id'])) {
						?>
						<b>Inherits From:</b> <?=$fruity->return_host_template_name($tempHostTemplateInfo['use_template_id']);?><br />
						<?php
					}
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			
			<?php
			
				$reps = array();
				$reports = false;
				
				$services_list = array();
				$fruity->get_host_template_services_list( $_GET['host_template_id'], $services_list );
			
				$host_array = array();
				$fruity->get_hosts_affected_by_host_template( $_GET['host_template_id'], $host_array );
			
				if (count($host_array)) {	
					foreach( $host_array as $host_id ) {
						$host_name = $fruity->return_host_name($host_id);
						foreach ( $services_list as $service ) {
							/*$rep = getReportsByObjcet( $host_name, $service['service_description']);*/
							$rep = false;
							if ($rep !== false)
								$reps[] = $rep;
						}
					}
				
					sort($reps);
					$reps = array_unique($reps);
					$reports = implode( null, $reps);

				} else {

					$reports = false;
	
				}
				
				if ($reports !== false)
					$reports = "'$reports'";

					
			?>
			
			[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&request=delete" onClick="javascript:return confirmDelete(<?=$reports;?>);">Delete This Host Template</a> ]
			| [ <a href="<?=$path_config['doc_root'];?>templates_export.php?host_template_id=<?=$_GET['host_template_id'];?>">Export This Host Template</a> ] 
			<?php
		}
		if($_GET['section'] == 'checks') {
			if($fruity->get_host_icon_image($_GET['host_template_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing checks information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=checks&edit=1">
					<input type="hidden" name="request" value="host_template_modify_checks" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
					<?php 
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[active_checks_enabled]", "Active Checks", $fruity->element_desc("active_checks_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['active_checks_enabled'], "active_checks_enabled", (isset($tempInheritedValuesSources['active_checks_enabled']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[passive_checks_enabled]", "Passive Checks", $fruity->element_desc("passive_checks_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['passive_checks_enabled'], "passive_checks_enabled", (isset($tempInheritedValuesSources['passive_checks_enabled']) ? "Override Inherited Value" : "Include In Template"));			
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[check_period]", "Check Period", $fruity->element_desc("check_period", "nagios_hosts_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['host_manage']['check_period'], "check_period", (isset($tempInheritedValuesSources['check_period']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_command]", "Check Command", $fruity->element_desc("check_command", "nagios_hosts_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['host_manage']['check_command'], "check_command", (isset($tempInheritedValuesSources['check_command']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[max_check_attempts]", "Maximum Check Attempts", $fruity->element_desc("max_check_attempts", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['max_check_attempts'], "max_check_attempts", (isset($tempInheritedValuesSources['max_check_attempts']) ? "Override Inherited Value" : "Include In Template")); 
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_interval]", "Check Interval In Time-Units", $fruity->element_desc("check_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['check_interval'], "check_interval", (isset($tempInheritedValuesSources['check_interval']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[retry_interval]", "Retry Interval In Time-Units", $fruity->element_desc("retry_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['retry_interval'], "retry_interval", (isset($tempInheritedValuesSources['retry_interval']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[obsess_over_host]", "Obsess Over Host", $fruity->element_desc("obsess_over_host", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['obsess_over_host'], "obsess_over_host", (isset($tempInheritedValuesSources['obsess_over_host']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_freshness]", "Check Freshness", $fruity->element_desc("check_freshness", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['check_freshness'], "check_freshness", (isset($tempInheritedValuesSources['check_freshness']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[freshness_threshold]", "Freshness Threshold in Seconds", $fruity->element_desc("freshness_threshold", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['freshness_threshold'], "freshness_threshold", (isset($tempInheritedValuesSources['freshness_threshold']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[event_handler]", "Event Handler", $fruity->element_desc("event_handler", "nagios_hosts_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['host_manage']['event_handler'], "event_handler", (isset($tempInheritedValuesSources['event_handler']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[event_handler_enabled]", "Event Handler Enabled", $fruity->element_desc("event_handler_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['event_handler_enabled'], "event_handler_enabled", (isset($tempInheritedValuesSources['event_handler_enabled']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[failure_prediction_enabled]", "Failure Prediction", $fruity->element_desc("failure_prediction_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['failure_prediction_enabled'], "failure_prediction_enabled", (isset($tempInheritedValuesSources['failure_prediction_enabled']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_form_window_finish();
					?>					
					<br />
					<input type="submit" value="Update Checks" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Template:</b><br />
					<?php
					if(isset($tempHostTemplateInfo['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($tempHostTemplateInfo['active_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($tempInheritedValues['active_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['active_checks_enabled'];?></i><br />
						<?php
					}
					if(isset($tempHostTemplateInfo['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($tempHostTemplateInfo['passive_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($tempInheritedValues['passive_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['passive_checks_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($tempHostTemplateInfo['check_period']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($tempInheritedValues['check_period']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_period'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_host_template_command($tempHostTemplateInfo['host_template_id'])); ?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_host_template_command( $tempHostTemplateInfo['use_template_id']));?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_command'];?></i><br />
						<?php
					}						
					if(isset($tempHostTemplateInfo['max_check_attempts'])) {
						?>
						<b>Maximum Check Attempts:</b> <?=$tempHostTemplateInfo['max_check_attempts'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['max_check_attempts'])) {
						?>
						<b>Maximum check Attempts:</b> <?=$tempInheritedValues['max_check_attempts'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['max_check_attempts'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['check_interval'])) {
						?>
						<b>Check Interval:</b> <?=$tempHostTemplateInfo['check_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_interval'])) {
						?>
						<b>Check Interval:</b> <?=$tempInheritedValues['check_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_interval'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['retry_interval'])) {
						?>
						<b>Retry Interval:</b> <?=$tempHostTemplateInfo['retry_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retry_interval'])) {
						?>
						<b>Retry Interval:</b> <?=$tempInheritedValues['retry_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retry_interval'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['obsess_over_host'])) {
						?>
						<b>Obsess Over Host:</b> <? if($tempHostTemplateInfo['obsess_over_host']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['obsess_over_host'])) {
						?>
						<b>Obsess Over Host:</b> <? if($tempInheritedValues['obsess_over_host']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['obsess_over_host'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($tempHostTemplateInfo['check_freshness']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($tempInheritedValues['check_freshness']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_freshness'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$tempHostTemplateInfo['freshness_threshold'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$tempInheritedValues['freshness_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['freshness_threshold'];?></i><br />
						<?php
					}						
					if(isset($tempHostTemplateInfo['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($tempHostTemplateInfo['event_handler']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($tempInheritedValues['event_handler']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['event_handler'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($tempHostTemplateInfo['event_handler_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($tempInheritedValues['event_handler_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['event_handler_enabled'];?></i><br />
						<?php
					}		
					if(isset($tempHostTemplateInfo['failure_prediction_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($tempHostTemplateInfo['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['event_handler_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($tempInheritedValues['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['failure_prediction_enabled'];?></i><br />
						<?php
					}				
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=checks&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'flapping') {
			if($fruity->get_host_icon_image($_GET['host_template_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=flapping&edit=1">
					<input type="hidden" name="request" value="host_template_modify_flapping" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[flap_detection_enabled]", "Flap Detection", $fruity->element_desc("flap_detection_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['flap_detection_enabled'], "flap_detection_enabled", (isset($tempInheritedValuesSources['flap_detection_enabled']) ? "Override Inherited Value" : "Include In Template"));					
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[low_flap_threshold]", "Low Flap Threshold", $fruity->element_desc("low_flap_threshold", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['low_flap_threshold'], "low_flap_threshold", (isset($tempInheritedValuesSources['low_flap_threshold']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[high_flap_threshold]", "High Flap Threshold", $fruity->element_desc("high_flap_threshold", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['high_flap_threshold'], "high_flap_threshold", (isset($tempInheritedValuesSources['high_flap_threshold']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Flapping" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Template:</b><br />
					<?php
					if(isset($tempHostTemplateInfo['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($tempHostTemplateInfo['flap_detection_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($tempInheritedValues['flap_detection_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['flap_detection_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$tempHostTemplateInfo['low_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$tempInheritedValues['low_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['low_flap_threshold'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$tempHostTemplateInfo['high_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$tempInheritedValues['high_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['high_flap_threshold'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=flapping&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'logging') {
			if($fruity->get_host_icon_image($_GET['host_template_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=logging&edit=1">
					<input type="hidden" name="request" value="host_template_modify_logging" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[process_perf_data]", "Process Performance Data", $fruity->element_desc("process_perf_data", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['process_perf_data'], "process_perf_data", (isset($tempInheritedValuesSources['process_perf_data']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[retain_status_information]", "Retain Status Information", $fruity->element_desc("retain_status_information", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['retain_status_information'], "retain_status_information", (isset($tempInheritedValuesSources['retain_status_information']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[retain_nonstatus_information]", "Retain Non-Status Information", $fruity->element_desc("retain_nonstatus_information", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['retain_nonstatus_information'], "retain_nonstatus_information", (isset($tempInheritedValuesSources['retain_nonstatus_information']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Logging" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Template:</b><br />
					<?php
					if(isset($tempHostTemplateInfo['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($tempHostTemplateInfo['process_perf_data']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($tempInheritedValues['process_perf_data']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['process_perf_data'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($tempHostTemplateInfo['retain_status_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($tempInheritedValues['retain_status_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retain_status_information'];?></i><br />
						<?php
					}
					if(isset($tempHostTemplateInfo['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($tempHostTemplateInfo['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($tempInheritedValues['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retain_nonstatus_information'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=logging&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'notifications') {
			if($fruity->get_host_icon_image($_GET['host_template_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information					
					$notification_options_checkbox_group[0]['element_name'] = 'host_manage[notification_options_down]';
					$notification_options_checkbox_group[0]['value'] = '1';
					$notification_options_checkbox_group[0]['element_title'] = 'Down';
					$notification_options_checkbox_group[1]['element_name'] = 'host_manage[notification_options_unreachable]';
					$notification_options_checkbox_group[1]['value'] = '1';
					$notification_options_checkbox_group[1]['element_title'] = 'Unreachable';
					$notification_options_checkbox_group[2]['element_name'] = 'host_manage[notification_options_recovery]';
					$notification_options_checkbox_group[2]['value'] = '1';
					$notification_options_checkbox_group[2]['element_title'] = 'Recovery';
					$notification_options_checkbox_group[3]['element_name'] = 'host_manage[notification_options_flapping]';
					$notification_options_checkbox_group[3]['value'] = '1';
					$notification_options_checkbox_group[3]['element_title'] = 'Flapping';
					
					if($_SESSION['tempData']['host_manage']['notification_options_down'])
						$notification_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_unreachable'])
						$notification_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_recovery']) 
						$notification_options_checkbox_group[2]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_flapping'])
						$notification_options_checkbox_group[3]['checked'] = 1;
					$stalking_options_checkbox_group[0]['element_name'] = 'host_manage[stalking_options_up]';
					$stalking_options_checkbox_group[0]['value'] = '1';
					$stalking_options_checkbox_group[0]['element_title'] = 'Up';
					$stalking_options_checkbox_group[1]['element_name'] = 'host_manage[stalking_options_down]';
					$stalking_options_checkbox_group[1]['value'] = '1';
					$stalking_options_checkbox_group[1]['element_title'] = 'Down';
					$stalking_options_checkbox_group[2]['element_name'] = 'host_manage[stalking_options_unreachable]';
					$stalking_options_checkbox_group[2]['value'] = '1';
					$stalking_options_checkbox_group[2]['element_title'] = 'Unreachable';
					if($_SESSION['tempData']['host_manage']['stalking_options_up'])
						$stalking_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['stalking_options_down'])
						$stalking_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['stalking_options_unreachable'])
						$stalking_options_checkbox_group[2]['checked'] = 1;
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=notifications&edit=1">
					<input type="hidden" name="request" value="host_template_modify_notifications" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notifications_enabled]", "Notifications", $fruity->element_desc("notifications_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['notifications_enabled'], "notifications_enabled", (isset($tempInheritedValuesSources['notifications_enabled']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[notification_interval]", "Notification Interval in Time-Units", $fruity->element_desc("notification_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['notification_interval'], "notification_interval", (isset($tempInheritedValuesSources['notification_interval']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notification_period]", "Notification Period", $fruity->element_desc("notification_period", "nagios_hosts_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['host_manage']['notification_period'], "notification_period", (isset($tempInheritedValuesSources['notification_period']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_checkbox_group_form_element_with_enabler("#eeeeee", "host_manage", $notification_options_checkbox_group, "Notification Options", $fruity->element_desc("notification_options", "nagios_hosts_desc"), "notification_options", (isset($tempInheritedValuesSources['notification_options_down']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_checkbox_group_form_element_with_enabler("#f0f0f0", "host_manage", $stalking_options_checkbox_group, "Stalking Options", $fruity->element_desc("stalking_options", "nagios_hosts_desc"), "stalking_options", (isset($tempInheritedValuesSources['stalking_options_up']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_form_window_finish();
					?>
					<br />
					<input type="submit" value="Update Notifications" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Template:</b><br />
					<?php
					if(isset($tempHostTemplateInfo['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($tempHostTemplateInfo['notifications_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($tempInheritedValues['notifications_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notifications_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$tempHostTemplateInfo['notification_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$tempInheritedValues['notification_interval'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notification_interval'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($tempHostTemplateInfo['notification_period']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($tempInheritedValues['notification_period']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notification_period'];?></i><br />
						<?php
					}					
					if(isset($tempHostTemplateInfo['notification_options_down']) || isset($tempHostTemplateInfo['notification_options_unreachable']) || isset($tempHostTemplateInfo['notification_options_recovery']) || isset($tempHostTemplateInfo['notification_options_flapping'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$tempHostTemplateInfo['notification_options_down'] && !$tempHostTemplateInfo['notification_options_unreachable'] && !$tempHostTemplateInfo['notification_options_recovery'] && !$tempHostTemplateInfo['notification_options_flapping']) {
							print("None");
						}
						else {
							if($tempHostTemplateInfo['notification_options_down']) {
								print("Down");
								if($tempHostTemplateInfo['notification_options_unreachable'] || $tempHostTemplateInfo['notification_options_recovery'] || $tempHostTemplateInfo['notification_options_flapping'])
									print(",");
							}
							if($tempHostTemplateInfo['notification_options_unreachable']) {
								print("Unreachable");
								if($tempHostTemplateInfo['notification_options_recovery'] || $tempHostTemplateInfo['notification_options_flapping'])
									print(",");
							}
							if($tempHostTemplateInfo['notification_options_recovery']) {
								print("Recovery");
									if($tempHostTemplateInfo['notification_options_flapping'])
										print(",");
							}
							if($tempHostTemplateInfo['notification_options_flapping']) {
								print("Flapping");
							}
						}
						print("<br />");
					}
					elseif(isset($tempInheritedValues['notification_options_down'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$tempInheritedValues['notification_options_down'] && !$tempInheritedValues['notification_options_unreachable'] && !$tempInheritedValues['notification_options_recovery'] && !$tempInheritedValues['notification_options_flapping']) {
							print("None");
						}
						else {
							if($tempInheritedValues['notification_options_down']) {
								print("Down");
								if($tempInheritedValues['notification_options_unreachable'] || $tempInheritedValues['notification_options_recovery'] || $tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($tempInheritedValues['notification_options_unreachable']) {
								print("Unreachable");
								if($tempInheritedValues['notification_options_recovery'] || $tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($tempInheritedValues['notification_options_recovery']) {
								print("Recovery");
									if($tempInheritedValues['notification_options_flapping'])
										print(",");
							}
							if($tempInheritedValues['notification_options_flapping']) {
								print("Flapping");
							}
						}
						print("<b> - Inherited From: </b><i>".$tempInheritedValuesSources['notification_options_down']."</i>");
						print("<br />");
					}
					if(isset($tempHostTemplateInfo['stalking_options_up']) || isset($tempHostTemplateInfo['stalking_options_down']) || isset($tempHostTemplateInfo['stalking_options_unreachable'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($tempHostTemplateInfo['stalking_options_up'] || $tempHostTemplateInfo['stalking_options_down'] || $tempHostTemplateInfo['stalking_options_unreachable']) {
								if($tempHostTemplateInfo['stalking_options_up']) {
									print("Up");
									if($tempHostTemplateInfo['stalking_options_down'] || $tempHostTemplateInfo['stalking_options_unreachable'])
										print(",");
								}
								if($tempHostTemplateInfo['stalking_options_down']) {
									print("Down");
									if($tempHostTemplateInfo['stalking_options_unreachable'])
										print(",");
								}
								if($tempHostTemplateInfo['stalking_options_unreachable']) {
									print("Unreachable");
								}
						}
						else {
							print("None");
						}
						print("<br />");
					}
					elseif(isset($tempInheritedValues['stalking_options_up'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($tempInheritedValues['stalking_options_up'] || $tempInheritedValues['stalking_options_down'] || $tempInheritedValues['stalking_options_unreachable']) {
								if($tempInheritedValues['stalking_options_up']) {
									print("Up");
									if($tempInheritedValues['stalking_options_down'] || $tempInheritedValues['stalking_options_unreachable'])
										print(",");
								}
								if($tempInheritedValues['stalking_options_down']) {
									print("Down");
									if($tempInheritedValues['stalking_options_unreachable'])
										print(",");
								}
								if($tempInheritedValues['stalking_options_unreachable']) {
									print("Unreachable");
								}
						}
						else {
							print("None");
						}
						print("<b> - Inherited From: </b><i>".$tempInheritedValuesSources['stalking_options_up']."</i>");
						print("<br />");
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=notifications&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'groups') {
			if(isset($tempHostTemplateInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_hostgroups_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->get_host_template_membership_list($_GET['host_template_id'], $group_list);
			$numOfGroups = count($group_list);
			// Get list of host groups
			$fruity->get_hostgroup_list($hostgroups_list);
			$numOfHostGroups = count($hostgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>servergroup.gif" />
				</td>
				<td valign="top">
				<?php
				if(isset($tempHostTemplateInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Host Groups Inherited By Parent Template:</td>
						</tr>
						<?php
						sort( $inherited_list);
						for($counter = 0; $counter < $numOfInheritedGroups; $counter++) {
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
							<td height="20" width="60" class="altLeft">&nbsp;</td>
							<td height="20" class="altRight"><b><?=$fruity->return_hostgroup_name($inherited_list[$counter]);?>:</b> <?=$fruity->return_hostgroup_alias($inherited_list[$counter]);?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<br />
					<?php
				}
				?>
				
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Host Group Membership:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfGroups; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=groups&request=delete&hostgroup_id=<?=$group_list[$counter]['hostgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_hostgroup_name($group_list[$counter]['hostgroup_id']);?>:</b> <?=$fruity->return_hostgroup_alias($group_list[$counter]['hostgroup_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="hostgroup_member_add" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=groups">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="host_manage[group_add][host_id]" value="<?=$_GET['host_template_id'];?>" />
				<b>Add New Host Group Membership:</b> <?php print_select("host_manage[group_add][hostgroup_id]", $hostgroups_list, "hostgroup_id", "hostgroup_name", "0");?> <input type="submit" value="Add Group"><br />
				<?=$fruity->element_desc("members", "nagios_contactgroups_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'services') {
			if(isset($tempHostTemplateInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_services_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedServices = count($inherited_list);
			}
			$fruity->get_host_template_services_list($_GET['host_template_id'], $hostTemplateServiceList);
			
			$command_list[] = array("value" => 0, "text" => "None");
			
			$numOfServices = count($hostTemplateServiceList);
			
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>services.gif" />
				</td>
				<td valign="top">
				<?php
				if(isset($tempHostTemplateInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Services Inherited By Parent Template:</td>
						</tr>
						<?php
						$counter = 0;
						if($numOfInheritedServices) {
							foreach($inherited_list as $service) {
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
								<td height="20" width="60" class="altLeft">&nbsp;</td>
								<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$service['service_id'];?>"><?=$service['service_description'];?></a></b> from <b><?=$fruity->return_host_template_name($service['host_template_id']);?></b></td>
								</tr>
								<?php
								$counter++;
							}
						}
						?>
					</table>
					<br />
					<?php
				}
				?>

				<form name="formSelected" action="<?=$path_config['doc_root'];?>services.php" method="get">
				<input type="hidden" name="host_template_id" value="<?=$_GET['host_template_id'];?>">
				<input type="hidden" name="section" value="clone">
				<input type="hidden" name="selected" value="1">				
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Services Explicitly Linked to This Host Template:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfServices; $counter++) {
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
						
						$reps = array();
						$reports = false;
						
						$host_array = array();
						$fruity->get_hosts_affected_by_host_template( $_GET['host_template_id'], $host_array );
					
						if (count($host_array)) {
							foreach( $host_array as $host_id ) {
								$host_name = $fruity->return_host_name($host_id);
//								$rep = getReportsByObjcet( $host_name, $fruity->return_service_description($hostTemplateServiceList[$counter]['service_id']) );
								$rep = false;
						
								if ($rep !== false)
									$reps[] = $rep;
							}
							
							sort($reps);
							$reps = array_unique($reps);
							$reports = implode( null, $reps);

						} else {

							$reports = false;

						}
						
						if ($reports !== false)
							$reports = "'$reports'";
							
						?>
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=services&request=delete&service_id=<?=$hostTemplateServiceList[$counter]['service_id'];?>" onClick="javascript:return confirmDelete(<?=$reports;?>);">Delete</a> ]</td>
						<td height="20" class="altRight"><input type='checkbox' name='service_id_<?=$hostTemplateServiceList[$counter]['service_id'];?>'> &nbsp;&nbsp;<b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$hostTemplateServiceList[$counter]['service_id'];?>"><?=$fruity->return_service_description($hostTemplateServiceList[$counter]['service_id']);?></a></b></td>
						</tr>
						<?php
					}
					?>
				</table>
				</form>
				<br />
				[ <a href="<?=$path_config['doc_root'];?>services.php?service_add=1&host_template_id=<?=$_GET['host_template_id'];?>">Create A New Service</a> ] |
			[ <a href="javascript:document.formSelected.section.value='delete';javascript:document.formSelected.submit();">Delete Selected Services</a> ] |
			[ <a href="javascript:document.formSelected.section.value='clone';javascript:document.formSelected.submit();">Clone Selected Services</a> ] |
			[ <a href="<?=$path_config['doc_root'];?>services.php?host_template_id=<?=$_GET['host_template_id'];?>&section=clone">Clone All Services</a> ]
				<br />
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "checkcommand") {
			if(isset($tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_commandparameter_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			// Get List Of Parameters for this service and check
			$fruity->get_host_template_check_command_parameters($_GET['host_template_id'], $checkCommandParameters);
			$numOfCheckCommandParameters = count($checkCommandParameters);
			
			$parameterCounter = 0;
			?>
			<table width="90%" align="center" border="0">
			<tr>
			<td>
			
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
				<td colspan="2">Command Syntax:</td>
			</tr>
			<tr class="altRow2">
			<?php
				$command_line = $fruity->return_host_template_command_line($_GET['host_template_id']);
				
				print("<td class='altLeft'>" . $command_line . "</td>\n");
	
				if (preg_match('/^(\S*)\/(\S*) .*$/', $command_line, $matches))
					print("<td class='altRigth'><input type=button name=command value='View Help'onClick=\"javascript:popUp('" . $path_config['doc_root'] . "get_help.php?command=$matches[2]')\"></td>\n");
			?>
			</tr>
			</table>
			<br>						
			
				<?php
				if(isset($tempHostTemplateInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Parameters Inherited By Parent Template:</td>
						</tr>
						<?php
						if(count($inherited_list)) {
							$counter = 1;
							foreach($inherited_list as $parameter) {
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
								<td height="20" width="60" class="altLeft">&nbsp;</td>
								<td height="20" class="altRight"><b>$ARG<?=++$parameterCounter;?>$:</b> <?=$parameter['parameter'];?></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
					<br />
					<?php
				}
				?>
				<form name="modify_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?section=checkcommand&host_template_id=<?=$_GET['host_template_id'];?>">
				<input type="hidden" name="request" value="command_parameter_modify" />
				<input type="hidden" name="numCheckCommandParameters" value="<? echo $numOfCheckCommandParameters ?>" />
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Check Command Parameters:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfCheckCommandParameters; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=checkcommand&request=delete&checkcommandparameter_id=<?=$checkCommandParameters[$counter]['checkcommandparameter_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"> <b>$ARG<?=++$parameterCounter;?>$:</b> 
						<input type='text' name="ARG<?echo $parameterCounter;?>" value="<?=$checkCommandParameters[$counter]['parameter'];?>">
						</tr>
						<?php
					}
					?>
				</table>
				<br>
				<?php
				if ($numOfCheckCommandParameters)
					print ('<input type="submit" value="Modify Parameter">' . "\n");
				?>
				</form>
			<br />
			<form name="add_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?section=checkcommand&host_template_id=<?=$_GET['host_template_id'];?>">
			<input type="hidden" name="request" value="command_parameter_add" />
			<input type="hidden" name="host_manage[host_template_id]" value="<?=$_GET['host_template_id'];?>" />
			Value for $ARG<?=($counter+1);?>$: <input type="text" name="host_manage[parameter]" /> <input type="submit" value="Add Parameter" />
			</form>
			</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'extended') {
			$directory_list[] = array("value" => '', "text" => 'None');
			$tempDir = array();
			if ($handle = @opendir($sys_config['logos_path'])) {
			   while (false !== ($file = readdir($handle))) {
			       if ($file != "." && $file != "..") {
			           $tempDir[] = $file;
			       }
			   }
			   closedir($handle);
			   asort($tempDir);
				foreach($tempDir as $value) {
					if(!is_dir($sys_config['logos_path'] ."/". $value))
						$directory_list[] = array("value" => $value, "text" => $value);
				}
			}
			$numOfImages = count($directory_list) - 1;
			
			?>
			<table width="100%" border="0">
			<tr>
			<td width="100" align="center" valign="top">
			<img src="<?=$path_config['image_root'];?>info.gif" />
			</td>
			<td valign="top">
			<?php
			if( $_GET['edit']) {
				?>
				<form name="host_manage" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=extended" method="post">
				<input type="hidden" name="request" value="update_host_extended" />
				<input type="hidden" name="host_manage[host_template_id]" value="<?=$_GET['host_template_id'];?>">
				<?php
				double_pane_form_window_start();
				double_pane_textarea_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notes]", "Notes", $fruity->element_desc("notes", "nagios_host_templates_extended_info_desc"), "3", "80", $_SESSION['tempData']['host_manage']['notes'], "notes", (isset($tempInheritedValuesSources['notes']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[notes_url]", "Notes URL", $fruity->element_desc("notes_url", "nagios_host_templates_extended_info_desc"), "60","255", $_SESSION['tempData']['host_manage']['notes_url'], "notes_url", (isset($tempInheritedValuesSources['notes_url']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[action_url]", "Action URL", $fruity->element_desc("action_url", "nagios_host_templates_extended_info_desc"), "60","255", $_SESSION['tempData']['host_manage']['action_url'], "action_url", (isset($tempInheritedValuesSources['action_url']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_select_form_element_with_enabler_and_viewer("#eeeeee", "host_manage", "host_manage[icon_image]", "Icon Image", $fruity->element_desc("icon_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['icon_image'], "icon_image", (isset($tempInheritedValuesSources['icon_image']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[icon_image_alt]", "Icon Image Alt Text", $fruity->element_desc("icon_image_alt", "nagios_host_templates_extended_info_desc"), "60","60", $_SESSION['tempData']['host_manage']['icon_image_alt'], "icon_image_alt", (isset($tempInheritedValuesSources['icon_image_alt']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_select_form_element_with_enabler_and_viewer("#eeeeee", "host_manage", "host_manage[vrml_image]", "VRML Image", $fruity->element_desc("vrml_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['vrml_image'], "vrml_image", (isset($tempInheritedValuesSources['vrml_image']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_select_form_element_with_enabler_and_viewer("#f0f0f0", "host_manage", "host_manage[statusmap_image]", "Statusmap Image", $fruity->element_desc("statusmap_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['statusmap_image'], "statusmap_image", (isset($tempInheritedValuesSources['statusmap_image']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[two_d_coords]", "2D Coordinates", $fruity->element_desc("two_d_coords", "nagios_host_templates_extended_info_desc"), "30","30", $_SESSION['tempData']['host_manage']['two_d_coords'], "two_d_coords", (isset($tempInheritedValuesSources['two_d_coords']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[three_d_coords]", "3D Coordinates", $fruity->element_desc("three_d_coords", "nagios_host_templates_extended_info_desc"), "30","30", $_SESSION['tempData']['host_manage']['three_d_coords'], "three_d_coords", (isset($tempInheritedValuesSources['three_d_coords']) ? "Override Inherited Value" : "Include In Template"));
				double_pane_form_window_finish();
				?>
				<br />
				<input type="submit" value="Update Extended Information" /> [ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=extended">Cancel</a> ]
				</form>
				<?php
			} else {
				print "<b>Included in definition:</b><br />\n";
				if(isset($tempHostTemplateExtendedInfo['notes'])) {
					?>
					<b>Notes:</b> <?=$tempHostTemplateExtendedInfo['notes'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['notes'])) {
					?>
					<b>Notes:</b> <?=$_SESSION['tempData']['host_manage']['notes'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notes'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$tempHostTemplateExtendedInfo['notes_url'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$_SESSION['tempData']['host_manage']['notes_url'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notes_url'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['action_url'])) {
					?>
					<b>Action URL:</b> <?=$tempHostTemplateExtendedInfo['action_url'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['action_url'])) {
					?>
					<b>Action URL:</b> <?=$_SESSION['tempData']['host_manage']['action_url'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['action_url'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$tempHostTemplateExtendedInfo['icon_image'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$_SESSION['tempData']['host_manage']['icon_image'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['icon_image'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$tempHostTemplateExtendedInfo['icon_image_alt'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$_SESSION['tempData']['host_manage']['icon_image_alt'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['icon_image_alt'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['vrml_image'])) {
					?>
					<b>VRML Image:</b> <?=$tempHostTemplateExtendedInfo['vrml_image'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['vrml_image'])) {
					?>
					<b>VRML Image:</b> <?=$_SESSION['tempData']['host_manage']['vrml_image'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['vrml_image'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['statusmap_image'])) {
					?>
					<b>Statusmap Image:</b> <?=$tempHostTemplateExtendedInfo['statusmap_image'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['statusmap_image'])) {
					?>
					<b>Status Image:</b> <?=$_SESSION['tempData']['host_manage']['statusmap_image'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['statusmap_image'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['two_d_coords'])) {
					?>
					<b>2D Coordinates:</b> <?=$tempHostTemplateExtendedInfo['two_d_coords'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['two_d_coords'])) {
					?>
					<b>2D Coordinates:</b> <?=$_SESSION['tempData']['host_manage']['two_d_coords'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['two_d_coords'];?></i><br />
					<?php
				}
				if(isset($tempHostTemplateExtendedInfo['three_d_coords'])) {
					?>
					<b>3D Coordinates:</b> <?=$tempHostTemplateExtendedInfo['three_d_coords'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['three_d_coords'])) {
					?>
					<b>3D Coordinates:</b> <?=$_SESSION['tempData']['host_manage']['three_d_coords'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['three_d_coords'];?></i><br />
					<?php
				}
				?>
				<br />
				[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=extended&edit=1">Edit</a> ]
				<?php
			}
			?>
			</td>
			</tr>
			</table>
			<?php
		}		
		else if($_GET['section'] == 'contactgroups') {
			if(isset($tempHostTemplateInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_contactgroups_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->return_host_template_contactgroups_list($_GET['host_template_id'], $contactgroups_list);			
			$numOfContactGroups = count($contactgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempHostTemplateInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Contact Groups Inherited By Parent Template:</td>
								</tr>
								<?php
								$inherited_list = array_values( $inherited_list);
								for($counter = 0; $counter < $numOfInheritedGroups; $counter++) {
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
									<td height="20" width="60" class="altLeft">&nbsp;</td>
									<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($inherited_list[$counter]);?>:</b> <?=$fruity->return_contactgroup_alias($inherited_list[$counter]);?></td>
									</tr>
									<?php
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Contact Groups Explicitly Linked to This Host Template:</td>
							</tr>
							<?php
							for($counter = 0; $counter < $numOfContactGroups; $counter++) {
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
								<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=contactgroups&request=delete&contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
								<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroups_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroups_list[$counter]['contactgroup_id']);?></td>
								</tr>
								<?php
							}
							?>
						</table>
				<?php	$fruity->get_contactgroup_list($contactgroups_list); ?>
				<br />
				<br />
				<form name="host_template_contactgroup_add" method="post" action="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=contactgroups">
				<input type="hidden" name="request" value="add_contactgroup_command" />
				<input type="hidden" name="host_manage[contactgroup_add][host_id]" value="<?=$_GET['host_template_id'];?>" />
				<b>Add New Contact Group:</b> <?php print_select("host_manage[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Contact Group"><br />
				<?=$fruity->element_desc("contact_groups", "nagios_hosts_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'dependencies') {
			if(isset($tempHostTemplateInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_dependencies_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedDepdendencies = count($inherited_list);
			}
			$fruity->return_host_template_dependencies_list($_GET['host_template_id'], $dependencies_list);
			$numOfDependencies = count($dependencies_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempHostTemplateInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Depdendencies Inherited By Parent Template:</td>
								</tr>
								<?php
								$counter = 0;
								if($numOfInheritedDepdendencies) {
									foreach($inherited_list as $dependency) {
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
										<td height="20" width="60" class="altLeft">&nbsp;</td>
										<td height="20" class="altRight"><b><?=$fruity->return_host_name($dependency['target_host_id']);?></td>
										</tr>
										<?php
										$counter++;
									}
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Depdendencies Explicitly Linked to This Host Template:</td>
							</tr>
							<?php
							$counter = 0;
							if($numOfDependencies) {
								foreach($dependencies_list as $dependency) {
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=dependencies&request=delete&dependency_id=<?=$dependency['dependency_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
									<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$dependency['dependency_id'];?>"><?=$fruity->return_host_name($dependency['target_host_id']);?></a></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						[ <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_add=1&host_template_id=<?=$_GET['host_template_id'];?>">Create A New Host Dependency For This Template</a> ]
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'escalations') {
			if(isset($tempHostTemplateInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_escalations_list($tempHostTemplateInfo['use_template_id'], $inherited_list);
				$numOfInheritedEscalations = count($inherited_list);
			}
			$fruity->return_host_template_escalations_list($_GET['host_template_id'], $escalations_list);
			$numOfEscalations = count($escalations_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempHostTemplateInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Escalations Inherited By Parent Template:</td>
								</tr>
								<?php
								$counter = 0;
								if($numOfInheritedEscalations) {
									foreach($inherited_list as $escalation) {
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
										<td height="20" width="60" class="altLeft">&nbsp;</td>
										<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$escalation['escalation_id'];?>"><?=$fruity->return_escalation_description($escalation['escalation_id']);?></a></b></td>
										</tr>
										<?php
										$counter++;
									}
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Escalations Explicitly Linked to This Host Template</td>
							</tr>
							<?php
							$counter = 0;
							if($numOfEscalations) {
								foreach($escalations_list as $escalation) {
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$_GET['host_template_id'];?>&section=escalations&request=delete&escalation_id=<?=$escalation['escalation_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
									<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$escalation['escalation_id'];?>"><?=$escalation['escalation_description'];?></a></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						[ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_add=1&host_template_id=<?=$_GET['host_template_id'];?>">Create A New Escalation For This Template</a> ]
				</td>
			</tr>
			</table>
			<?php
		}
		print_window_footer();
		?>
		<br />
		<br />
		<?php
	}

	if($_GET['section'] == "general" && !isset($_GET['host_template_add'])) {
		
		print_window_header("Host Templates", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_add=1">Add A New Host Template</a>
		| <a class="sublink" href="<?=$path_config['doc_root'];?>templates_import.php">Import A New Host Template</a><br />
		<br />
		<?php
		if($numOfTemplates) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Host Name</td>
			<td>Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfTemplates; $counter++) {
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
				<td height="20">&nbsp;<a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$template_list[$counter]['host_template_id'];?>"><?=$template_list[$counter]['template_name'];?></a></td>
				<td height="20">&nbsp;<?=$template_list[$counter]['template_description'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Host Templates Exists</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}
	if($_GET['host_template_add']) {
		$add_template_list[] = array("host_template_id" => '', "template_name" => "None");
		if(count($template_list))
		foreach($template_list as $tempTemplate)
			$add_template_list[] = $tempTemplate;
	
		print_window_header("Add Host Template", "100%");
		?>
		<form name="host_template_add_form" method="post" action="<?=$path_config['doc_root'];?>host_templates.php">
		<input type="hidden" name="request" value="add_host_template" />
		<?php double_pane_form_window_start(); ?>
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Template Name:</b><br />
			<input type="text" size="40" name="host_manage[template_name]" value="<?=$_SESSION['tempData']['host_manage']['template_name'];?>" onblur="this.value=changeCharCode(this.value);"><br />
			<?=$fruity->element_desc("template_name", "nagios_hosts_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr bgcolor="eeeeee">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Template Description:</b><br />
			<input type="text" size="40" name="host_manage[template_description]" value="<?=$_SESSION['tempData']['host_manage']['template_description'];?>"><br />
			<?=$fruity->element_desc("template_description", "nagios_hosts_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Uses Host Template:</b> <?php print_select("host_manage[use_template_id]", $add_template_list, "host_template_id", "template_name");?><br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			If this template is to inherit from another template, specify that template's name now.
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<?php double_pane_form_window_finish(); ?>
		<input type="submit" value="Add Host Template" />&nbsp;[ <a href="<?=$path_config['doc_root'];?>templates.php">Cancel</a> ]
		<br /><br />
		</form>
		<?php
		print_window_footer();
		?>
		<br />
		<?php
	}
	?>
	<br />
	<?php
print_footer();
?>
