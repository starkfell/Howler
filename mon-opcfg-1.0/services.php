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
 * services.php
 * Author:	Taylor Dondich (tdondich at gmail.com)
 * Description:
 * 	Provides interface to maintain services
 *
*/
 
include_once('includes/config.inc');

// Data preparation
// SF BUG# 1445803
// templating error with fruity 1.0rc
$tempInheritedValues = array();
$tempInheritedValuesSources = array();

if(!isset($_GET['section']))
	$_GET['section'] = 'general';

// Get rid of initial data
unset($_SESSION['tempData']['service_manage']);

// If we're going to modify service data
if(isset($_GET['service_id']) && 
		($_GET['section'] == "general" ||
		$_GET['section'] == "checks" ||
		$_GET['section'] == "flapping" || 
		$_GET['section'] == "logging" || 
		$_GET['section'] == "notifications") &&
		$_GET['edit']) {
	$fruity->get_service_info($_GET['service_id'], $_SESSION['tempData']['service_manage']);
	$_SESSION['tempData']['service_manage']['old_name'] = $_SESSION['tempData']['service_manage']['service_description'];
	
}

if (isset($_REQUEST['section']) && $_REQUEST['section'] == "delete") {
	
	foreach($_REQUEST as $param => $value) {
		if (preg_match("/service_id_(\d*)/", $param, $matches )) {
			
			if (isset($_REQUEST['host_template_id'])) {
				
				$host_list = array();
				$fruity->get_hosts_affected_by_host_template( $_REQUEST['host_template_id'], $host_list );
				foreach($host_list as $host_id) {
//					removeReportsByObject( $fruity->return_host_name( $host_id ), $fruity->return_service_description( $matches[1] ));
				}
				
			} else {
//				removeReportsByObject( $fruity->return_host_name( $_REQUEST['host_id']) , $fruity->return_service_description( $matches[1] ) );
			}
			
			$fruity->restart();
			$fruity->delete_service( $matches[1] );
				
		}
	}
	
	if (isset($_REQUEST['host_id']))
		header("Location: {$path_config['doc_root']}hosts.php?host_id={$_REQUEST['host_id']}&section=services&delete_msg=" . urlencode("Services Deleted"));
	else
		header("Location: {$path_config['doc_root']}host_templates.php?host_template_id={$_REQUEST['host_template_id']}&section=services&delete_msg=" . urlencode("Services Deleted"));
}
	
	
// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'services' && !$_GET['subsection']) {
			$fruity->delete_service($_GET['service_id']);
			unset($_GET['service_id']);
			$status_msg = "Service deleted.";
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
			$fruity->delete_service_contactgroup($_GET['service_id'], $_GET['contactgroup_id']);
			$status_msg = "Contact Group Deleted";
			unset($_SESSION['tempData']['service_manage']);
			unset($_GET['request']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'servicegroups') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_service_servicegroup($_GET['service_id'], $_GET['servicegroup_id']);
			$status_msg = "Service Group Deleted";
			unset($_SESSION['tempData']['service_manage']);
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
			$fruity->delete_service_checkcommand_parameter($_GET['checkcommandparameter_id']);
			$status_msg = "Check Command Parameter Deleted.";
		}
		
			
}

if(isset($_POST['request'])) {
	if(count($_POST['service_manage'])) {
		foreach($_POST['service_manage'] as $key=>$value) {
			$_SESSION['tempData']['service_manage'][$key] = $value;
		}

	}
	
	// Enabler checks
	if(count($_POST['service_manage_enablers'])) {
		foreach($_POST['service_manage_enablers'] as $key=>$value) {
			if($value == 0) {
				$_SESSION['tempData']['service_manage'][$key] = NULL;
			}
		}
	}
	if($_POST['request'] == 'add_service') {
		// Check for pre-existing service description with host
		if($fruity->service_exists($_SESSION['tempData']['service_manage'])) {
			$status_msg = "A service with that description already exists for that host or host template!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['service_manage'])) {
				foreach($_SESSION['tempData']['service_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['service_manage']['service_description'] == '') {
				$addError = 1;
				$status_msg = "Incorrect values for fields.  Please verify.";
			}
			else {
				if($_SESSION['tempData']['service_manage']['use_template_id'] == '')
					unset($_SESSION['tempData']['service_manage']['use_template_id']);
				// All is well for error checking, add the command into the db.
				if($fruity->add_service($_SESSION['tempData']['service_manage'])) {
					unset($_GET['service_add']);
					
					// Let's get the service id
					if(isset($_GET['host_template_id'])) {
						$serviceID = $fruity->return_service_id_by_host_template_and_description($_GET['host_template_id'], $_SESSION['tempData']['service_manage']['service_description']);
					}
					elseif(isset($_GET['host_id'])) {
						$serviceID = $fruity->return_service_id_by_host_and_description($_GET['host_id'], $_SESSION['tempData']['service_manage']['service_description']);
					}
					else {
						$serviceID = $fruity->return_service_id_by_hostgroup_and_description($_GET['hostgroup_id'], $_SESSION['tempData']['service_manage']['service_description']);
					}
					header("Location: " . $path_config['doc_root'] . "services.php?service_id=".$serviceID);
					die();					
					
				}
				else {
					$addError = 1;
					$status_msg = "Error: add_service failed.";
				}
			}
		}
	}
	else if($_POST['request'] == 'service_modify_general') {
		if($_SESSION['tempData']['service_manage']['service_description'] != $_SESSION['tempData']['service_manage']['old_name'] && $fruity->service_exists($_SESSION['tempData']['service_manage'])) {
			$status_msg = "A service with that name already exists for that host or host template!";
		}
		else {

			// Log change
			if($_SESSION['tempData']['service_manage']['service_description'] != $_SESSION['tempData']['service_manage']['old_name'] && !preg_match("/^_DUPLICATED_/", $_SESSION['tempData']['service_manage']['old_name'])) {
				$type_string = "service";
				$to_change_hosts = $fruity->get_hosts_affected_by_service_change($_SESSION['tempData']['service_manage']['service_id']);
				if (!is_array($to_change_hosts)) {
					$to_change_string = $to_change_hosts . "#" . $_SESSION['tempData']['service_manage']['old_name'] . "#" . $_SESSION['tempData']['service_manage']['service_description'];
					$fruity->set_changes( $type_string, $to_change_string );
				}else{
					foreach($to_change_hosts as $to_change_host) {
						$to_change_string = $to_change_host . "#" . $_SESSION['tempData']['service_manage']['old_name'] . "#" . $_SESSION['tempData']['service_manage']['service_description'];
						$fruity->set_changes( $type_string, $to_change_string );
					}
				}
			}
			
			// Field Error Checking
			if(count($_SESSION['tempData']['service_manage'])) {
				foreach($_SESSION['tempData']['service_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['service_manage']['service_description'] == '') {
				$addError = 1;
				$status_msg = "Incorrect values for fields.  Please verify.";
			}
			// All is well for error checking, modify the command.
			else if($fruity->modify_service($_SESSION['tempData']['service_manage'])) {
				// Remove session data
				unset($_SESSION['tempData']['service_manage']);
				$status_msg = "Service modified.";
				unset($_GET['edit']);
			}
			else {
				$status_msg = "Error: modify_service failed.";
			}
		}
	}
	else if($_POST['request'] == 'service_modify_checks') {
		if(count($_SESSION['tempData']['service_manage'])) {
			foreach($_SESSION['tempData']['service_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		if(($_POST['service_manage_enablers']['max_check_attempts'] && !is_numeric($_SESSION['tempData']['service_manage']['max_check_attempts'])) || 
		($_POST['service_manage_enablers']['max_check_attempts'] && !($_SESSION['tempData']['service_manage']['max_check_attempts'] >= 1)) ||
		($_POST['service_manage_enablers']['normal_check_interval'] && !is_numeric($_SESSION['tempData']['service_manage']['normal_check_interval'])) || 
		($_POST['service_manage_enablers']['normal_check_interval'] && !($_SESSION['tempData']['service_manage']['normal_check_interval'] >= 1)) ||
		($_POST['service_manage_enablers']['retry_check_interval'] && !is_numeric($_SESSION['tempData']['service_manage']['retry_check_interval'])) || 
		($_POST['service_manage_enablers']['retry_check_interval'] && !($_SESSION['tempData']['service_manage']['retry_check_interval'] >= 1)) ||
		($_POST['service_manage_enablers']['retry_check_interval'] && !is_numeric($_SESSION['tempData']['service_manage']['retry_check_interval'])) || 
		($_POST['service_manage_enablers']['freshness_threshold'] && !($_SESSION['tempData']['service_manage']['freshness_threshold'] >= 0))) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the template.
		else if($fruity->modify_service($_SESSION['tempData']['service_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['service_manage']);
			$status_msg = "Service modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_service failed.";
		}
	}
	else if($_POST['request'] == 'service_modify_flapping') {
		// Field Error Checking
		if(count($_SESSION['tempData']['service_manage'])) {
			foreach($_SESSION['tempData']['service_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		if((isset($_SESSION['tempData']['service_manage']['low_flap_threshold']) && $_SESSION['tempData']['service_manage']['low_flap_threshold'] < 0) || (isset($_SESSION['tempData']['service_manage']['high_flap_threshold']) && $_SESSION['tempData']['service_manage']['high_flap_threshold'] < 0)) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the command.
		else if($fruity->modify_service($_SESSION['tempData']['service_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['service_manage']);
			$status_msg = "Service modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_service failed.";
		}
	}
	else if($_POST['request'] == 'service_modify_logging') {
		// Field Error Checking
		// None required for this process
		// All is well for error checking, modify the command.
		if($fruity->modify_service($_SESSION['tempData']['service_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['service_manage']);
			$status_msg = "Service modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_service failed.";
		}
	}
	else if($_POST['request'] == 'service_modify_notifications') {
		// Field Error Checking
		if(count($_SESSION['tempData']['service_manage'])) {
			foreach($_SESSION['tempData']['service_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
			
		if(!$_POST['service_manage_enablers']['notification_options']) {
			$_SESSION['tempData']['service_manage']['notification_options_warning'] = NULL;
			$_SESSION['tempData']['service_manage']['notification_options_unknown'] = NULL;
			$_SESSION['tempData']['service_manage']['notification_options_critical'] = NULL;
			$_SESSION['tempData']['service_manage']['notification_options_recovery'] = NULL;
			$_SESSION['tempData']['service_manage']['notification_options_flapping'] = NULL;
		}
		else {
			if(!isset($_POST['service_manage']['notification_options_warning']))
				$_SESSION['tempData']['service_manage']['notification_options_warning'] = '0';
			if(!isset($_POST['service_manage']['notification_options_unknown']))
				$_SESSION['tempData']['service_manage']['notification_options_unknown'] = '0';
			if(!isset($_POST['service_manage']['notification_options_critical']))
				$_SESSION['tempData']['service_manage']['notification_options_critical'] = '0';
			if(!isset($_POST['service_manage']['notification_options_recovery']))
				$_SESSION['tempData']['service_manage']['notification_options_recovery'] = '0';
			if(!isset($_POST['service_manage']['notification_options_flapping']))
				$_SESSION['tempData']['service_manage']['notification_options_flapping'] = '0';
		}
		
		if(!$_POST['service_manage_enablers']['stalking_options']) {
			$_SESSION['tempData']['service_manage']['stalking_options_ok'] = NULL;
			$_SESSION['tempData']['service_manage']['stalking_options_warning'] = NULL;
			$_SESSION['tempData']['service_manage']['stalking_options_unknown'] = NULL;
			$_SESSION['tempData']['service_manage']['stalking_options_critical'] = NULL;
		}
		else {
			if(!isset($_POST['service_manage']['stalking_options_ok']))
				$_SESSION['tempData']['service_manage']['stalking_options_ok'] = '0';
			if(!isset($_POST['service_manage']['stalking_options_warning']))
				$_SESSION['tempData']['service_manage']['stalking_options_warning'] = '0';
			if(!isset($_POST['service_manage']['stalking_options_unknown']))
				$_SESSION['tempData']['service_manage']['stalking_options_unknown'] = '0';
			if(!isset($_POST['service_manage']['stalking_options_critical']))
				$_SESSION['tempData']['service_manage']['stalking_options_critical'] = '0';
		}
		
		if($_POST['service_manage_enablers']['notification_interval'] && 
			($_SESSION['tempData']['service_manage']['notification_interval'] == '' || $_SESSION['tempData']['service_manage']['notification_interval'] < 0 || !is_numeric($_SESSION['tempData']['service_manage']['notification_interval']))) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the command.
		else if($fruity->modify_service($_SESSION['tempData']['service_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['service_manage']);
			$status_msg = "Service modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_service failed.";
		}
	}
	else if($_POST['request'] == 'add_member_command') {
		add_hostgroup_member($_SESSION['tempData']['service_manage']['group_add']['hostgroup_id'], $_SESSION['tempData']['service_manage']['group_add']['host_id']);
		$status_msg = "Host Added To Host Group.";
		unset($_SESSION['tempData']['service_manage']);
	}
	else if($_POST['request'] == 'command_parameter_add') {
		// All is well for error checking, modify the command.
		$fruity->add_service_command_parameter($_GET['service_id'], $_SESSION['tempData']['service_manage']);
		// Remove session data
		unset($_SESSION['tempData']['service_manage']);
		$status_msg = "Command Parameter added.";
	}
	else if ($_POST['request'] == 'command_parameter_modify') {
		
		$fruity->delete_service_checkcommand_parameter( "", $_GET['service_id']);

		$temp = array();
		$temp['service_id'] = $_GET['service_id'];
		for ( $i = 1; $i <= $_POST['numCheckCommandParameters']; $i++ ) {
			$temp['parameter'] = $_POST["ARG$i"];
			$fruity->add_service_command_parameter( $_GET['service_id'], $temp);
		}
		// Remove session data
		unset($temp);
		unset($_SESSION['tempData']['service_manage']);
		$status_msg = "Command Parameter modified.";		
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->service_has_contactgroup($_GET['service_id'], $_SESSION['tempData']['service_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['service_manage']);
		}
		else {
			$fruity->add_service_contactgroup($_GET['service_id'], $_SESSION['tempData']['service_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Service Contact Group Link added.";
			unset($_SESSION['tempData']['service_manage']);
		}
	}
	else if($_POST['request'] == 'add_servicegroup_command') {
		if($fruity->service_has_servicegroup($_GET['service_id'], $_SESSION['tempData']['service_manage']['servicegroup_add']['servicegroup_id'])) {
			$status_msg = "That service group already exists in that list!";
			unset($_SESSION['tempData']['service_manage']);
		}
		else {
			$fruity->add_service_servicegroup($_GET['service_id'], $_SESSION['tempData']['service_manage']['servicegroup_add']['servicegroup_id']);
			$status_msg = "New Service Service Group Link added.";
			unset($_SESSION['tempData']['service_manage']);
		}
	}
	
	if($_POST['request'] == 'update_service_extended') {
		$fruity->modify_service_extended($_GET['service_id'], $_SESSION['tempData']['service_manage']);
		unset($_SESSION['tempData']['service_manage']);
		$status_msg = "Updated Service Extended Information";
	}
}

if(isset($_GET['service_id'])) {
	if(!$fruity->get_service_info($_GET['service_id'], $tempServiceInfo)) {
		$invalidHost = 1;
		$status_msg = "That service is not valid in the database.";
		unset($_GET['service_id']);
	}
	else {
		// Check to see if we inherit from another template
		if(isset($tempServiceInfo['use_template_id'])) {
			// Then we actually use a template
			// We now need to obtain the inherited values
			$result = $fruity->get_inherited_service_template_values($tempServiceInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
			
			// We need to load up $_SESSION with our inherited values.
			if(count($tempInheritedValues))
			foreach($tempInheritedValues as $key=>$value) {
				if(isset($tempInheritedValues[$key]) && !isset($tempServiceInfo[$key])) {
					$_SESSION['tempData']['service_manage'][$key] = $value;
				}
			}
		}
		
		
		// quick interation to enable values explicitly defined in this template, NOT inherited values
		if(is_array($tempServiceInfo)) {
			foreach(array_keys($tempServiceInfo) as $key) {
				if(isset($tempServiceInfo[$key]))
					$_POST['service_manage_enablers'][$key] = '1';
			}
		}
		// special cases
		if(isset($tempServiceInfo['notification_options_warning']) || isset($tempServiceInfo['notification_options_unknown']) || isset($tempServiceInfo['notification_options_critical']) || isset($tempServiceInfo['notification_options_recovery']) || isset($tempServiceInfo['notification_options_flapping']))
			$_POST['service_manage_enablers']['notification_options'] = 1;
		if(isset($tempServiceInfo['stalking_options_ok']) || isset($tempServiceInfo['stalking_options_warning']) || isset($tempServiceInfo['stalking_options_unknown']) || isset($tempServiceInfo['stalking_options_critical']))
			$_POST['service_manage_enablers']['stalking_options'] = 1;
	}
}

// Extended info help
if(isset($_GET['service_id']) && $_GET['section'] == "extended") {
	if($fruity->get_service_extended_info($_GET['service_id'], $tempServiceExtendedInfo)) {
		if($tempServiceExtendedInfo != NULL) {
			foreach(array_keys($tempServiceExtendedInfo) as $key) {
				if(isset($tempServiceExtendedInfo[$key]))
					$_POST['service_manage_enablers'][$key] = '1';
			}
		}
		
		if(isset($tempServiceInfo['use_template_id'])) {
				$result = $fruity->get_inherited_service_template_extended_values($tempServiceInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
				// Let's load up $_SESSION
				if(count($tempInheritedValues))
				foreach($tempInheritedValues as $key=>$value) {
					if(isset($tempInheritedValues[$key]) && !isset($tempServiceExtendedInfo[$key])) {
						$_SESSION['tempData']['service_manage'][$key] = $value;
					}
				}
		}
		
		
		// Okay, now let's overwrite those values with the ones from tempServiceTemplateExtendedInfo (Do we really need this?)
		if(isset($tempServiceExtendedInfo)) {
			foreach($tempServiceExtendedInfo as $key=>$value) {
				if(isset($tempServiceExtendedInfo[$key]))
					$_SESSION['tempData']['service_manage'][$key] = $value;
			}
		}
	}
}

// Get list of host templates
$fruity->get_service_template_list($template_list);
$numOfTemplates = count($template_list);

// To create a "default" command

$fruity->return_command_list($command_list);
$command_list[] = array("command_id" => 0, "command_name" => "None");

$fruity->return_period_list($period_list);

$volatile_list[] = array("value" => 0, "label" => "Not Volatile");
$volatile_list[] = array("value" => 1, "label" => "Volatile");

// Cute hack
if(isset($_GET['host_template_id']))
	$tempServiceInfo['host_template_id'] = $_GET['host_template_id'];
else if(isset($_GET['host_id']))
	$tempServiceInfo['host_id'] = $_GET['host_id'];
else if(isset($_GET['hostgroup_id']))
	$tempServiceInfo['hostgroup_id'] = $_GET['hostgroup_id'];
	

print_header("Service Editor");
?>
[ <a href="<?=$path_config['doc_root'];?><?php 
	if(isset($tempServiceInfo['host_template_id'])) { 
		print("host_templates.php?host_template_id=".$tempServiceInfo['host_template_id']);
	}
	elseif(!isset($tempServiceInfo['hostgroup_id'])) {
		print("hosts.php?host_id=".$tempServiceInfo['host_id']);
	}
	else	{
		print("hostgroups.php?hostgroup_id=".$tempServiceInfo['hostgroup_id']);
	}	
	?>&section=services">Return To Host<?php if(isset($tempServiceInfo['hostgroup_id'])) print("group");?> <?php if(isset($tempServiceInfo['host_template_id'])) print("Template ");?> Services</a> ]
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}

        // display system list for cloning
        if ($_GET['section'] == 'clone') {
        	
				if(!isset($_GET['host_id']) && !isset($_GET['host_template_id']))
					print("<div align=\"center\" class=\"statusmsg\">Host from missing!  Go back to the host and click Clone agai
n.</div><br />");
				else if (!isset($_GET['to_host'])) {
					
					print("<center>\n");
					print("Select host(s) to assign cloned services:\n");
					
					$host_list = array();
					
					if(isset($_GET['host_id'])) {
						$fruity->get_host_list($host_list);
						print("<form method=\"get\" action=\"services.php?section=clone&host_id=".$_GET['host_id']."\" name=\"to_host\">\n");
					} else if(isset($_GET['host_template_id'])) {
						$fruity->get_host_list($host_list);
						print("<form method=\"get\" action=\"services.php?section=clone&host_template_id=".$_GET['host_template_id']."\" name=\"to_host\">\n");
					}

					print("<select size=50 name=\"to_host[]\" multiple>\n");
					foreach ( $host_list as $host )
						print("<option value=\"".$host['host_id']."\">".$host['host_name']."</option>\n");
						
					if(isset($_GET['host_id']))
						print("<input type=\"hidden\" name=\"host_id\" value=\"".$_GET['host_id']."\">\n");
					else if (isset($_GET['host_template_id']))
						print("<input type=\"hidden\" name=\"host_template_id\" value=\"".$_GET['host_template_id']."\">\n");
					
					print("<input type=\"hidden\" name=\"section\" value=\"clone\">\n");

					if (isset($_GET['selected'])) {
						print("<input type=\"hidden\" name=\"selected\" value=\"1\">\n");
						foreach( $_GET as $param => $value ) {
							$matches = array();
							if (preg_match("/service_id_(\d*)/", $param, $matches ))
								print("<input type=\"hidden\" name=\"service_id_{$matches[1]}\" value=\"".$matches[1]."\">\n");                  		
						}
					}
                  
						print("<br>\n");
                  print("<input type=\"submit\" value=\"Clone\">\n");
                  print("</select>\n");
                  print("</center>\n");
                } else {
                	
                	$host_list = $_GET['to_host'];
                	foreach($host_list as $host_id) {
                		$_GET['to_host'] = $host_id;
                	
	                	// Clone the services
	                  $service_id_list = array();
	                  $to_host_info = array();
	                  $fruity->get_host_info($_GET['to_host'], $to_host_info);      // get host info

	                  printf("<b>Host: %s</b><br>\n", $to_host_info['host_name']);
	                  
	                  if (isset($_GET['selected'])) {
								foreach( $_GET as $param => $value ) {
	                  		$matches = array();
	                  		if (preg_match("/service_id_(\d*)/", $param, $matches )) {
										$temp = array();
										
										if(isset($_GET['host_id']))
											$temp['host_id'] = $_GET['host_id'];
										else if (isset($_GET['host_template_id']))
											$temp['host_template_id'] = $_GET['host_template_id'];
	
										$temp['service_id'] = $matches[1];
										$service_id_list[] = $temp;
	                  			unset($temp);
	                  		}
	                  	}
	                  } else if (isset($_GET['host_id'])) {
		                  $fruity->get_host_services_list($_GET['host_id'], $service_id_list);
	                  } else if (isset($_GET['host_template_id'])) {
	                  	
	                  	$temp_service_list = array();
	                  	$fruity->get_host_template_inherited_services_list( $_GET['host_template_id'], $temp_service_list );
	                  	
								foreach( $temp_service_list as $key => $value ) {
									
									$temp = array();
									$temp['host_template_id'] = $value['host_template_id'];
									$temp['service_id'] = $value['service_id'];
									$service_id_list[] = $temp;
									unset($temp);
	
								}
	                  	
	                  }
	                  
	                  foreach ($service_id_list as $key => $service) {
								$service_info = array();
								$extended_service_info = array();
								$check_cmds = array();
								$contactgroups = array();
								$servicegroups = array();
								
								// handle service check
								$fruity->get_service_info($service['service_id'], $service_info);
								$service_info['host_id'] = $_GET['to_host'];
								unset($service_info['service_id']);
								unset($service_info['host_template_id']);
								
								foreach ($service_info as $key => $val)
								 if ($val == '')
								   unset($service_info[$key]);
	
								printf("Cloning Service \"%s\" to host %s", $service_info['service_description'], $to_host_info['host_name']);
								
								if ($fruity->service_exists($service_info))
									$service_info['service_description'] = "_DUPLICATED_" . $service_info['service_description'];

								if ($fruity->service_exists($service_info)) {
									printf(" - <font color=\"red\">This service was already cloned!</font>");
									print("<br>\n");
									continue;
								}
								
								if ($fruity->add_service($service_info))
								 printf(" - <font color=\"green\">Service cloned</font>");
								else
								 printf(" - <font color=\"red\">Service failed!</font>");
								
								// get new service_id
								$new_service_id = $fruity->return_service_id_by_host_and_description($_GET['to_host'], $service_info['service_description']);
								
								// handle extended info
								$fruity->get_service_extended_info($service['service_id'], $extended_service_info);
								if (count($extended_service_info)) {
									foreach ($extended_service_info as $key => $val)
								 		if (empty($val))
											unset($extended_service_info[$key]);
											
									if (!empty($extended_service_info)) {
								 		$extended_service_info['service_id'] = $new_service_id;
								 		$fruity->modify_service_extended($new_service_id, $extended_service_info);
								 		printf(" - <font color=\"green\">Extended info cloned</font>");
									}
								}
								
								// handle command parameters
								$fruity->get_service_check_command_parameters($service['service_id'],$check_cmds);
								if (!empty($check_cmds)) {
								 foreach ($check_cmds as $check_cmd) {
								   foreach ($check_cmd as $key => $val)
								     if (!isset($val))
								       unset($check_cmd[$key]);
								   unset($check_cmd['checkcommandparameter_id']);
								   unset($check_cmd['last_updated']);
								   unset($check_cmd['service_id']);
								   if ($fruity->add_service_command_parameter($new_service_id, $check_cmd))
								     printf(" - <font color=\"green\">Command parameter cloned</font>");
								   else
								     printf(" - <font color=\"red\">Command parameter failed!</font>");
								 }
								}
								
								// handle Contact Groups
								$fruity->return_service_contactgroups_list($service['service_id'], $contactgroups );
								if (!empty($contactgroups)) {
									foreach($contactgroups as $key => $value) {
										$fruity->add_service_contactgroup( $new_service_id, $value['contactgroup_id']);
									}
									printf(" - <font color=\"green\">Contactgroups cloned</font>");
								}
	
								// handle Group Membership
								$fruity->get_service_servicegroups($service['service_id'], $servicegroups );
								if (!empty($servicegroups)) {
									foreach($servicegroups as $key => $value) {
										$fruity->add_service_servicegroup( $new_service_id, $value['servicegroup_id']);
									}
									printf(" - <font color=\"green\">Serivicegroups cloned</font>");
								}
								
								print("<br>\n");
	                  }
	                  
	                  print("<hr>\n");
						}
					print("<div align=\"center\" class=\"statusmsg\">Cloning Complete</div><br />");
              	}
				}

	// Show service information table if selected
	if($_GET['service_id']) {
		$title = "Service Info for " . $tempServiceInfo['service_description'] . " On ";
		if(isset($tempServiceInfo['host_id']))
			$title .= "Host: " . $fruity->return_host_name($tempServiceInfo['host_id']);
		else if(isset($tempServiceInfo['hostgroup_id']))
			$title .= "Hostgroup: " . $fruity->return_hostgroup_name($tempServiceInfo['hostgroup_id']);
		else
			$title .= "Host Template: " . $fruity->return_host_template_name($tempServiceInfo['host_template_id']);		
		print_window_header($title, "100%");	
		?>
		<a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=checks">Checks</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=flapping">Flapping</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=logging">Logging</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=notifications">Notifications</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=servicegroups">Group Membership</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=contactgroups">Contact Groups</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=extended">Extended Information</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=dependencies">Dependencies</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=escalations">Escalations</a><?php if(isset($tempServiceInfo['check_command']) || isset($tempInheritedValuesSources['check_command'])) {?> | <a class="sublink" href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=checkcommand">Check Command Parameters</a><?php }?>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			if($fruity->get_service_icon_image($_GET['service_id'], $service_icon_image)) {
				$service_icon_image = $path_config['doc_root'] . 'logos/' . $service_icon_image;
			} else {
				$service_icon_image = $path_config['image_root'] . "services.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$service_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="service_manage" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general&edit=1">
					<input type="hidden" name="request" value="service_modify_general" />
					<input type="hidden" name="service_template_id" value="<?=$_GET['service_id'];?>">	
					<b>Service Description:</b><br />
					<input type="text" size="80" name="service_manage[service_description]" value="<?=$_SESSION['tempData']['service_manage']['service_description'];?>" onblur="this.value=changeCharCode(this.value);"><br />
					<?=$fruity->element_desc("service_description", "nagios_services_desc"); ?><br />
<!-- Beginn Change Service Template -->
					<?php
							$add_template_list[] = array("service_template_id" => NULL, "template_name" => "None");
							if(count($template_list))
							foreach($template_list as $tempTemplate)
								$add_template_list[] = $tempTemplate;
					?>
					<br />
					<b>Uses Service Template:</b> <?php print_select("service_manage[use_template_id]", $add_template_list, "service_template_id", "template_name", $tempServiceInfo['use_template_id']);?><br />
					If this template is to inherit from another template, specify that template's name now.
	
<!-- End Change Service Template -->
					<br />
					<br />
					<input type="submit" value="Update General" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Description:</b> <?=$tempServiceInfo['service_description'];?><br />
					<?php
					if(isset($tempServiceInfo['use_template_id'])) {
						?>
						<b>Inherits From:</b> <?=$fruity->return_service_template_name($tempServiceInfo['use_template_id']);?><br />
						<?php
					}
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			<?php
			if(isset($tempServiceInfo['host_template_id'])) {
				?>
				[ <a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$tempServiceInfo['host_template_id'];?>&section=services&service_id=<?=$_GET['service_id'];?>&request=delete">Delete This Service</a> ]
				<?php
			}
			elseif(isset($tempServiceInfo['host_id'])) {
				
//				$reports = getReportsByObjcet( $fruity->return_host_name($tempServiceInfo['host_id']), $fruity->return_service_description($_GET['service_id']) );
				$reports = false;
				if ($reports !== false)
					$reports = "'$reports'";
				
				?>
				[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$tempServiceInfo['host_id'];?>&section=services&service_id=<?=$_GET['service_id'];?>&request=delete" onClick="javascript:return confirmDelete(<?=$reports;?>);">Delete This Service</a> ]
				<?php
			}				
			else {
				?>
				[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$tempServiceInfo['hostgroup_id'];?>&section=services&service_id=<?=$_GET['service_id'];?>&request=delete">Delete This Service</a> ]
				<?php
			}	
		}
		if($_GET['section'] == 'checks') {
			if($fruity->get_service_icon_image($_GET['service_id'], $service_icon_image)) {
				$service_icon_image = $path_config['doc_root'] . 'logos/' . $service_icon_image;
			} else {
				$service_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$service_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing checks information
					$fruity->return_command_list($check_command_list);					
					?>
					<form name="service_manage" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=checks&edit=1">
					<input type="hidden" name="request" value="service_modify_checks" />
					<input type="hidden" name="service_template_id" value="<?=$_GET['service_id'];?>">
					<?php 
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[is_volatile]", "Is Volatile", $fruity->element_desc("is_volatile", "nagios_services_desc"), $volatile_list, "value", "label", $_SESSION['tempData']['service_manage']['is_volatile'], "is_volatile", (isset($tempInheritedValuesSources['is_volatile']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[check_command]", "Check Command", $fruity->element_desc("check_command", "nagios_services_desc"), $check_command_list, "command_id", "command_name", $_SESSION['tempData']['service_manage']['check_command'], "check_command", (isset($tempInheritedValuesSources['check_command']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[max_check_attempts]", "Maximum Check Attempts", $fruity->element_desc("max_check_attempts", "nagios_services_desc"), "4", "4", $_SESSION['tempData']['service_manage']['max_check_attempts'], "max_check_attempts", (isset($tempInheritedValuesSources['max_check_attempts']) ? "Override Inherited Value" : "Include In Definition")); 
					double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[normal_check_interval]", "Normal Check Interval In Time-Units", $fruity->element_desc("normal_check_interval", "nagios_services_desc"), "8", "8", $_SESSION['tempData']['service_manage']['normal_check_interval'], "normal_check_interval", (isset($tempInheritedValuesSources['normal_check_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[retry_check_interval]", "Retry Check Interval In Time-Units", $fruity->element_desc("retry_check_interval", "nagios_services_desc"), "8", "8", $_SESSION['tempData']['service_manage']['retry_check_interval'], "retry_check_interval", (isset($tempInheritedValuesSources['retry_check_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[active_checks_enabled]", "Active Checks", $fruity->element_desc("active_checks_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['active_checks_enabled'], "active_checks_enabled", (isset($tempInheritedValuesSources['active_checks_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[passive_checks_enabled]", "Passive Checks", $fruity->element_desc("passive_checks_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['passive_checks_enabled'], "passive_checks_enabled", (isset($tempInheritedValuesSources['passive_checks_enabled']) ? "Override Inherited Value" : "Include In Definition"));			
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[check_period]", "Check Period", $fruity->element_desc("check_period", "nagios_services_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['service_manage']['check_period'], "check_period", (isset($tempInheritedValuesSources['check_period']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[parallelize_check]", "Parallelize Check", $fruity->element_desc("parallelize_check", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['parallelize_check'], "parallelize_check", (isset($tempInheritedValuesSources['parallelize_check']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[obsess_over_service]", "Obsess Over Service", $fruity->element_desc("obsess_over_service", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['obsess_over_service'], "obsess_over_service", (isset($tempInheritedValuesSources['obsess_over_service']) ? "Override Inherited Value" : "Include In Definition"));			
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[check_freshness]", "Check Freshness", $fruity->element_desc("check_freshness", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['check_freshness'], "check_freshness", (isset($tempInheritedValuesSources['check_freshness']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[freshness_threshold]", "Freshness Threshold", $fruity->element_desc("freshness_threshold", "nagios_services_desc"), "8", "8", $_SESSION['tempData']['service_manage']['freshness_threshold'], "freshness_threshold", (isset($tempInheritedValuesSources['freshness_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[event_handler_enabled]", "Event Handler", $fruity->element_desc("event_handler_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['event_handler_enabled'], "event_handler_enabled", (isset($tempInheritedValuesSources['event_handler_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[event_handler]", "Event Handler Command", $fruity->element_desc("event_handler", "nagios_services_desc"), $check_command_list, "command_id", "command_name", $_SESSION['tempData']['service_manage']['event_handler'], "event_handler", (isset($tempInheritedValuesSources['event_handler']) ? "Override Inherited Value" : "Include In Definition"));					
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[failure_prediction_enabled]", "Failure Prediction", $fruity->element_desc("failure_prediction_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['failure_prediction_enabled'], "failure_prediction_enabled", (isset($tempInheritedValuesSources['failure_prediction_enabled']) ? "Override Inherited Value" : "Include In Template"));					
					double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[action_url]", "Action URL", $fruity->element_desc("action_url", "nagios_services_desc"), "140", "255", $_SESSION['tempData']['service_manage']['action_url'], "action_url", (isset($tempInheritedValuesSources['action_url']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>					
					<br />
					<input type="submit" value="Update Checks" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($tempServiceInfo['is_volatile'])) {
						?>
						<b>Is Volatile:</b> <?=$tempServiceInfo['is_volatile'] ? "Volatile" : "Not Volatile";?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['is_volatile'])) {
						?>
						<b>Is Volatile:</b> <?=$tempServiceInfo['is_volatile'] ? "Volatile" : "Not Volatile";?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['is_volatile'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_service_command($tempServiceInfo['service_id'])); ?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_service_command($tempServiceInfo['service_id'])); ?> <i>(See </i><b>Check Command Parameters</b><i> for parameter inheritance information)</i><br />
						<?php
					}				
					if(isset($tempServiceInfo['max_check_attempts'])) {
						?>
						<b>Maximum Check Attempts:</b> <?=$tempServiceInfo['max_check_attempts'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['max_check_attempts'])) {
						?>
						<b>Maximum Check Attempts:</b> <?=$tempInheritedValues['max_check_attempts'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['max_check_attempts'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['normal_check_interval'])) {
						?>
						<b>Normal Check Interval:</b> <?=$tempServiceInfo['normal_check_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['normal_check_interval'])) {
						?>
						<b>Normal Check Interval:</b> <?=$tempInheritedValues['normal_check_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['normal_check_interval'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['retry_check_interval'])) {
						?>
						<b>Retry Check Interval:</b> <?=$tempServiceInfo['retry_check_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retry_check_interval'])) {
						?>
						<b>Retry Check Interval:</b> <?=$tempInheritedValues['retry_check_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retry_check_interval'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($tempServiceInfo['active_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($tempInheritedValues['active_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['active_checks_enabled'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($tempServiceInfo['passive_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($tempInheritedValues['passive_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['passive_checks_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($tempServiceInfo['check_period']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($tempInheritedValues['check_period']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_period'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['parallelize_check'])) {
						?>
						<b>Parallize Checks:</b> <? if($tempServiceInfo['parallelize_check']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['parallelize_check'])) {
						?>
						<b>Parallize Checks:</b> <? if($tempInheritedValues['parallelize_check']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['parallelize_check'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['obsess_over_service'])) {
						?>
						<b>Obsess Over Service:</b> <? if($tempServiceInfo['obsess_over_service']) print("Yes"); else print("No");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['obsess_over_service'])) {
						?>
						<b>Obsess Over Service:</b> <? if($tempInheritedValues['obsess_over_service']) print("Yes"); else print("No");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['obsess_over_service'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($tempServiceInfo['check_freshness']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($tempInheritedValues['check_freshness']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['check_freshness'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$tempServiceInfo['freshness_threshold'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$tempInheritedValues['freshness_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['freshness_threshold'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($tempServiceInfo['event_handler_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($tempInheritedValues['event_handler_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['event_handler_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($tempServiceInfo['event_handler']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($tempInheritedValues['event_handler']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['event_handler'];?></i><br />
						<?php
					}	
					if(isset($tempServiceInfo['failure_prediction_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($tempServiceInfo['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['failure_prediction_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($tempInheritedValues['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['failure_prediction_enabled'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['action_url'])) {
						?>
						<b>Action URL:</b> <?=$tempServiceInfo['action_url'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['action_url'])) {
						?>
						<b>Action URL:</b> <?=$tempInheritedValues['action_url'];?><b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['action_url'];?></i><br />
						<?php
					}
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=checks&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'flapping') {
			if($fruity->get_service_icon_image($_GET['service_id'], $service_icon_image)) {
				$service_icon_image = $path_config['doc_root'] . 'logos/' . $service_icon_image;
			} else {
				$service_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$service_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="service_manage" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=flapping&edit=1">
					<input type="hidden" name="request" value="service_modify_flapping" />
					<input type="hidden" name="service_template_id" value="<?=$_GET['service_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[flap_detection_enabled]", "Flap Detection", $fruity->element_desc("flap_detection_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['flap_detection_enabled'], "flap_detection_enabled", (isset($tempInheritedValuesSources['flap_detection_enabled']) ? "Override Inherited Value" : "Include In Definition"));					
					double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[low_flap_threshold]", "Low Flap Threshold", $fruity->element_desc("low_flap_threshold", "nagios_services_desc"), "4", "4", $_SESSION['tempData']['service_manage']['low_flap_threshold'], "low_flap_threshold", (isset($tempInheritedValuesSources['low_flap_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[high_flap_threshold]", "High Flap Threshold", $fruity->element_desc("high_flap_threshold", "nagios_services_desc"), "4", "4", $_SESSION['tempData']['service_manage']['high_flap_threshold'], "high_flap_threshold", (isset($tempInheritedValuesSources['high_flap_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Flapping" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($tempServiceInfo['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($tempServiceInfo['flap_detection_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($tempInheritedValues['flap_detection_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['flap_detection_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$tempServiceInfo['low_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$tempInheritedValues['low_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['low_flap_threshold'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$tempServiceInfo['high_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$tempInheritedValues['high_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['high_flap_threshold'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=flapping&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'logging') {
			if($fruity->get_service_icon_image($_GET['service_id'], $service_icon_image)) {
				$service_icon_image = $path_config['doc_root'] . 'logos/' . $service_icon_image;
			} else {
				$service_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$service_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="service_manage" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=logging&edit=1">
					<input type="hidden" name="request" value="service_modify_logging" />
					<input type="hidden" name="service_template_id" value="<?=$_GET['service_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[process_perf_data]", "Process Performance Data", $fruity->element_desc("process_perf_data", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['process_perf_data'], "process_perf_data", (isset($tempInheritedValuesSources['process_perf_data']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[retain_status_information]", "Retain Status Information", $fruity->element_desc("retain_status_information", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['retain_status_information'], "retain_status_information", (isset($tempInheritedValuesSources['retain_status_information']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[retain_nonstatus_information]", "Retain Non-Status Information", $fruity->element_desc("retain_nonstatus_information", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['retain_nonstatus_information'], "retain_nonstatus_information", (isset($tempInheritedValuesSources['retain_nonstatus_information']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Logging" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($tempServiceInfo['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($tempServiceInfo['process_perf_data']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($tempInheritedValues['process_perf_data']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['process_perf_data'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($tempServiceInfo['retain_status_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($tempInheritedValues['retain_status_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retain_status_information'];?></i><br />
						<?php
					}
					if(isset($tempServiceInfo['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($tempServiceInfo['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($tempInheritedValues['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['retain_nonstatus_information'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=logging&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'notifications') {
			if($fruity->get_service_icon_image($_GET['service_id'], $service_icon_image)) {
				$service_icon_image = $path_config['doc_root'] . 'logos/' . $service_icon_image;
			} else {
				$service_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$service_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					$notification_options_checkbox_group[0]['element_name'] = 'service_manage[notification_options_warning]';
					$notification_options_checkbox_group[0]['value'] = '1';
					$notification_options_checkbox_group[0]['element_title'] = 'Warning';
					$notification_options_checkbox_group[1]['element_name'] = 'service_manage[notification_options_unknown]';
					$notification_options_checkbox_group[1]['value'] = '1';
					$notification_options_checkbox_group[1]['element_title'] = 'Unknown';
					$notification_options_checkbox_group[2]['element_name'] = 'service_manage[notification_options_critical]';
					$notification_options_checkbox_group[2]['value'] = '1';
					$notification_options_checkbox_group[2]['element_title'] = 'Critical';
					$notification_options_checkbox_group[3]['element_name'] = 'service_manage[notification_options_recovery]';
					$notification_options_checkbox_group[3]['value'] = '1';
					$notification_options_checkbox_group[3]['element_title'] = 'Recovery';
					$notification_options_checkbox_group[4]['element_name'] = 'service_manage[notification_options_flapping]';
					$notification_options_checkbox_group[4]['value'] = '1';
					$notification_options_checkbox_group[4]['element_title'] = 'Flapping';
					
					if($_SESSION['tempData']['service_manage']['notification_options_warning'])
						$notification_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['notification_options_unknown'])
						$notification_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['notification_options_critical']) 
						$notification_options_checkbox_group[2]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['notification_options_recovery']) 
						$notification_options_checkbox_group[3]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['notification_options_flapping'])
						$notification_options_checkbox_group[4]['checked'] = 1;
					$stalking_options_checkbox_group[0]['element_name'] = 'service_manage[stalking_options_ok]';
					$stalking_options_checkbox_group[0]['value'] = '1';
					$stalking_options_checkbox_group[0]['element_title'] = 'Ok';
					$stalking_options_checkbox_group[1]['element_name'] = 'service_manage[stalking_options_warning]';
					$stalking_options_checkbox_group[1]['value'] = '1';
					$stalking_options_checkbox_group[1]['element_title'] = 'Warning';
					$stalking_options_checkbox_group[2]['element_name'] = 'service_manage[stalking_options_unknown]';
					$stalking_options_checkbox_group[2]['value'] = '1';
					$stalking_options_checkbox_group[2]['element_title'] = 'Unknown';
					$stalking_options_checkbox_group[3]['element_name'] = 'service_manage[stalking_options_critical]';
					$stalking_options_checkbox_group[3]['value'] = '1';
					$stalking_options_checkbox_group[3]['element_title'] = 'Critical';
					if($_SESSION['tempData']['service_manage']['stalking_options_ok'])
						$stalking_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['stalking_options_warning'])
						$stalking_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['stalking_options_unknown'])
						$stalking_options_checkbox_group[2]['checked'] = 1;
					if($_SESSION['tempData']['service_manage']['stalking_options_critical'])
						$stalking_options_checkbox_group[3]['checked'] = 1;
					?>
					<form name="service_manage" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=notifications&edit=1">
					<input type="hidden" name="request" value="service_modify_notifications" />
					<input type="hidden" name="service_template_id" value="<?=$_GET['service_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[notifications_enabled]", "Notifications", $fruity->element_desc("notifications_enabled", "nagios_services_desc"), $enable_list, "values", "text", $_SESSION['tempData']['service_manage']['notifications_enabled'], "notifications_enabled", (isset($tempInheritedValuesSources['notifications_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[notification_interval]", "Notification Interval in Time-Units", $fruity->element_desc("notification_interval", "nagios_services_desc"), "8", "8", $_SESSION['tempData']['service_manage']['notification_interval'], "notification_interval", (isset($tempInheritedValuesSources['notification_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[notification_period]", "Notification Period", $fruity->element_desc("notification_period", "nagios_services_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['service_manage']['notification_period'], "notification_period", (isset($tempInheritedValuesSources['notification_period']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_checkbox_group_form_element_with_enabler("#eeeeee", "service_manage", $notification_options_checkbox_group, "Notification Options", $fruity->element_desc("notification_options", "nagios_services_desc"), "notification_options", (isset($tempInheritedValuesSources['notification_options_warning']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_checkbox_group_form_element_with_enabler("#f0f0f0", "service_manage", $stalking_options_checkbox_group, "Stalking Options", $fruity->element_desc("stalking_options", "nagios_services_desc"), "stalking_options", (isset($tempInheritedValuesSources['stalking_options_ok']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<br />
					<input type="submit" value="Update Notifications" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($tempServiceInfo['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($tempServiceInfo['notifications_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($tempInheritedValues['notifications_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notifications_enabled'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$tempServiceInfo['notification_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$tempInheritedValues['notification_interval'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notification_interval'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($tempServiceInfo['notification_period']);?><br />
						<?php
					}
					elseif(isset($tempInheritedValuesSources['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($tempInheritedValues['notification_period']);?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notification_period'];?></i><br />
						<?php
					}					
					if(isset($tempServiceInfo['notification_options_down']) || isset($tempServiceInfo['notification_options_unreachable']) || isset($tempServiceInfo['notification_options_recovery']) || isset($tempServiceInfo['notification_options_flapping'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$tempServiceInfo['notification_options_warning'] && !$tempServiceInfo['notification_options_unknown'] && !$tempServiceInfo['notification_options_critical'] && !$tempServiceInfo['notification_options_recovery'] && !$tempServiceInfo['notification_options_flapping']) {
							print("None");
						}
						else {
							if($tempServiceInfo['notification_options_warning']) {
								print("Warning");
								if($tempServiceInfo['notification_options_unknown'] || $tempServiceInfo['notification_options_critical'] || $tempServiceInfo['notification_options_recovery'] || $tempServiceInfo['notification_options_flapping'])
									print(",");
							}
							if($tempServiceInfo['notification_options_unknown']) {
								print("Unreachable");
								if($tempServiceInfo['notification_options_critical'] && $tempServiceInfo['notification_options_recovery'] || $tempServiceInfo['notification_options_flapping'])
									print(",");
							}
							if($tempServiceInfo['notification_options_critical']) {
								print("Critical");
								if($tempServiceInfo['notification_options_recovery'] || $tempServiceInfo['notification_options_flapping'])
									print(",");
							}
							if($tempServiceInfo['notification_options_recovery']) {
								print("Recovery");
									if($tempServiceInfo['notification_options_flapping'])
										print(",");
							}
							if($tempServiceInfo['notification_options_flapping']) {
								print("Flapping");
							}
						}
						print("<br />");
					}
					elseif(isset($tempInheritedValues['notification_options_warning'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$tempInheritedValues['notification_options_warning'] && !$tempInheritedValues['notification_options_unknown'] && !$tempInheritedValues['notification_options_critical'] && !$tempInheritedValues['notification_options_recovery'] && !$tempInheritedValues['notification_options_flapping']) {
							print("None");
						}
						else {
							if($tempInheritedValues['notification_options_warning']) {
								print("Warning");
								if($tempInheritedValues['notification_options_unknown'] || $tempInheritedValues['notification_options_critical'] || $tempInheritedValues['notification_options_recovery'] || $tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($tempInheritedValues['notification_options_unknown']) {
								print("Unreachable");
								if($tempInheritedValues['notification_options_critical'] && $tempInheritedValues['notification_options_recovery'] || $tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($tempInheritedValues['notification_options_critical']) {
								print("Critical");
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
						print("<b> - Inherited From: </b><i>".$tempInheritedValuesSources['notification_options_warning']."</i>");
						print("<br />");
					}
					if(isset($tempServiceInfo['stalking_options_ok']) || isset($tempServiceInfo['stalking_options_warning']) || isset($tempServiceInfo['stalking_options_unknown']) || isset($tempServiceInfo['stalking_options_critical'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($tempServiceInfo['stalking_options_ok'] || $tempServiceInfo['stalking_options_warning'] || $tempServiceInfo['stalking_options_unknown'] || $tempServiceInfo['stalking_options_critical']) {
								if($tempServiceInfo['stalking_options_ok']) {
									print("Ok");
									if($tempServiceInfo['stalking_options_warning'] || $tempServiceInfo['stalking_options_unknown'] || $tempServiceInfo['stalking_options_critical'])
										print(",");
								}
								if($tempServiceInfo['stalking_options_warning']) {
									print("Warning");
									if($tempServiceInfo['stalking_options_unknown'] || $tempServiceInfo['stalking_options_critical'])
										print(",");
								}
								if($tempServiceInfo['stalking_options_unknown']) {
									print("Unknown");
									if($tempServiceInfo['stalking_options_critical'])
										print(",");
								}
								if($tempServiceInfo['stalking_options_critical']) {
									print("Critical");
								}
						}
						else {
							print("None");
						}
						print("<br />");
					}
					elseif(isset($tempInheritedValues['stalking_options_ok'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($tempInheritedValues['stalking_options_ok'] || $tempInheritedValues['stalking_options_warning'] || $tempInheritedValues['stalking_options_unknown'] || $tempInheritedValues['stalking_options_critical']) {
								if($tempInheritedValues['stalking_options_ok']) {
									print("Ok");
									if($tempInheritedValues['stalking_options_warning'] || $tempInheritedValues['stalking_options_unknown'] || $tempInheritedValues['stalking_options_critical'])
										print(",");
								}
								if($tempInheritedValues['stalking_options_warning']) {
									print("Warning");
									if($tempInheritedValues['stalking_options_unknown'] || $tempInheritedValues['stalking_options_critical'])
										print(",");
								}
								if($tempInheritedValues['stalking_options_unknown']) {
									print("Unknown");
									if($tempInheritedValues['stalking_options_critical'])
										print(",");
								}
								if($tempInheritedValues['stalking_options_critical']) {
									print("Critical");
								}
						}
						else {
							print("None");
						}
						print("<b> - Inherited From: </b><i>".$tempInheritedValuesSources['stalking_options_ok']."</i>");
						print("<br />");
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=notifications&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "checkcommand") {
			if(isset($tempServiceInfo['use_template_id'])) {
				$fruity->get_service_template_inherited_commandparameter_list($tempServiceInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			// Get List Of Parameters for this service and check
			$fruity->get_service_check_command_parameters($_GET['service_id'], $checkCommandParameters);
			$numOfCheckCommandParameters = count($checkCommandParameters);

			$parameterCounter = 0;
			?>
			<table width="90%" align="center" border="0">
			<tr>
			<td>
				<?php
				if(isset($tempServiceInfo['use_template_id']) && !isset($tempServiceInfo['check_command'])) {
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
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Command Syntax:</td>
					</tr>
					<tr class="altRow2">
					<?php
					$command_line = $fruity->return_command_line($_GET['service_id']);
					
					print("<td class='altLeft'>" . $command_line . "</td>\n");

					if (preg_match('/^(\S*)\/(\S*) .*$/', $command_line, $matches))
						print("<td class='altRigth'><input type=button name=command value='View Help'onClick=\"javascript:popUp('" . $path_config['doc_root'] . "get_help.php?command=$matches[2]')\"></td>\n");
					?>
					</tr>
				</table>
				<br>

				<form name="modify_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>services.php?section=checkcommand&service_id=<?=$_GET['service_id'];?>">
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
						<td height="20" width="55" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=checkcommand&request=delete&checkcommandparameter_id=<?=$checkCommandParameters[$counter]['checkcommandparameter_id'];?>">Delete</a> ]</td>
						<td height="20" class="altRight"> <b>$ARG<?=++$parameterCounter;?>$:</b> 
						<input style="width:80%;" type='text' name="ARG<?echo $parameterCounter;?>" value="<?=$checkCommandParameters[$counter]['parameter'];?>">
						
						</td>
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
			<form name="add_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>services.php?section=checkcommand&service_id=<?=$_GET['service_id'];?>">
			<input type="hidden" name="request" value="command_parameter_add" />
			<input type="hidden" name="service_manage[service_id]" value="<?=$_GET['service_id'];?>" />
			Value for $ARG<?=($counter+1);?>$: <input type="text" name="service_manage[parameter]" /> <input type="submit" value="Add Parameter" />
			</form>
			</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "extended") {
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
				<td valign="top"><?php
			if( $_GET['edit']) {
				?>
				<form name="service_manage" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=extended" method="post">
				<input type="hidden" name="request" value="update_service_extended" />
				<input type="hidden" name="service_manage[service_id]" value="<?=$_GET['service_id'];?>">
				<?php
				double_pane_form_window_start();
				double_pane_textarea_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[notes]", "Notes", $fruity->element_desc("notes", "nagios_services_extended_info_desc"), "3", "80", $_SESSION['tempData']['service_manage']['notes'], "notes", (isset($tempInheritedValuesSources['notes']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#eeeeee", "service_manage", "service_manage[notes_url]", "Notes URL", $fruity->element_desc("notes_url", "nagios_services_extended_info_desc"), "60","255", $_SESSION['tempData']['service_manage']['notes_url'], "notes_url", (isset($tempInheritedValuesSources['notes_url']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[action_url]", "Action URL", $fruity->element_desc("action_url", "nagios_services_extended_info_desc"), "60","255", $_SESSION['tempData']['service_manage']['action_url'], "action_url", (isset($tempInheritedValuesSources['action_url']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_select_form_element_with_enabler_and_viewer("#eeeeee", "service_manage", "service_manage[icon_image]", "Icon Image", $fruity->element_desc("icon_image", "nagios_services_extended_desc"), $directory_list, "value", "text", $_SESSION['tempData']['service_manage']['icon_image'], "icon_image", (isset($tempInheritedValuesSources['icon_image']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "service_manage", "service_manage[icon_image_alt]", "Icon Image Alt Text", $fruity->element_desc("icon_image_alt", "nagios_services_extended_info_desc"), "60","60", $_SESSION['tempData']['service_manage']['icon_image_alt'], "icon_image_alt", (isset($tempInheritedValuesSources['icon_image_alt']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_form_window_finish();
				?>
				<br />
				<input type="submit" value="Update Extended Information" /> [ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=extended">Cancel</a> ]
				</form>
				<?php
			} else {
				print "<b>Included in definition:</b><br />\n";
				if(isset($tempServiceExtendedInfo['notes'])) {
					?>
					<b>Notes:</b> <?=$tempServiceExtendedInfo['notes'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['notes'])) {
					?>
					<b>Notes:</b> <?=$_SESSION['tempData']['service_manage']['notes'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notes'];?></i><br />
					<?php
				}
				if(isset($tempServiceExtendedInfo['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$tempServiceExtendedInfo['notes_url'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$_SESSION['tempData']['service_manage']['notes_url'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['notes_url'];?></i><br />
					<?php
				}
				if(isset($tempServiceExtendedInfo['action_url'])) {
					?>
					<b>Action URL:</b> <?=$tempServiceExtendedInfo['action_url'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['action_url'])) {
					?>
					<b>Action URL:</b> <?=$_SESSION['tempData']['service_manage']['action_url'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['action_url'];?></i><br />
					<?php
				}
				if(isset($tempServiceExtendedInfo['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$tempServiceExtendedInfo['icon_image'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$_SESSION['tempData']['service_manage']['icon_image'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['icon_image'];?></i><br />
					<?php
				}
				if(isset($tempServiceExtendedInfo['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$tempServiceExtendedInfo['icon_image_alt'];?><br />
					<?php
				}
				elseif(isset($tempInheritedValuesSources['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$_SESSION['tempData']['service_manage']['icon_image_alt'];?> <b>- Inherited From:</b> <i><?=$tempInheritedValuesSources['icon_image_alt'];?></i><br />
					<?php
				}
				?>
				<br />
				[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=extended&edit=1">Edit</a> ]
				<?php
			}
			?>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "contactgroups") {
			if(isset($tempServiceInfo['use_template_id'])) {
				$fruity->get_service_template_inherited_contactgroups_list($tempServiceInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->return_service_contactgroups_list($_GET['service_id'], $contactgroups_list);			
			$numOfContactGroups = count($contactgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempServiceInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Contact Groups Inherited By Parent Template:</td>
								</tr>
								<?php
								if(count($inherited_list)) {
									$counter = 1;
									foreach($inherited_list as $contactgroup) {
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
										<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroup);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroup);?></td>
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
							<td colspan="2">Contact Groups Explicitly Linked to This Service:</td>
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
								<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=contactgroups&request=delete&contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>">Delete</a> ]</td>
								<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroups_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroups_list[$counter]['contactgroup_id']);?></td>
								</tr>
								<?php
							}
							?>
						</table>
				<?php	$fruity->get_contactgroup_list( $contactgroups_list); ?>
				<br />
				<br />
				<form name="service_template_contactgroup_add" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=contactgroups">
				<input type="hidden" name="request" value="add_contactgroup_command" />
				<input type="hidden" name="service_manage[contactgroup_add][service_id]" value="<?=$_GET['service_id'];?>" />
				<b>Add New Contact Group:</b> <?php print_select("service_manage[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Contact Group"><br />
				<?=$fruity->element_desc("contact_groups", "nagios_services_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "servicegroups") {
			if(isset($tempServiceInfo['use_template_id'])) {
				$fruity->get_service_template_inherited_servicegroups_list($tempServiceInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->return_service_servicegroups_list($_GET['service_id'], $servicegroups_list);			
			$numOfServiceGroups = count($servicegroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempServiceInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Service Groups Inherited By Parent Template:</td>
								</tr>
								<?php
								if(count($inherited_list)) {
									$counter = 1;
									foreach($inherited_list as $servicegroup) {
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
										<td height="20" class="altRight"><b><?=$fruity->return_servicegroup_name($servicegroup);?>:</b> <?=$fruity->return_servicegroup_alias($servicegroup);?></td>
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
							<td colspan="2">Service Groups Explicitly Linked to This Service Template:</td>
							</tr>
							<?php
							for($counter = 0; $counter < $numOfServiceGroups; $counter++) {
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
								<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=servicegroups&request=delete&servicegroup_id=<?=$servicegroups_list[$counter]['servicegroup_id'];?>">Delete</a> ]</td>
								<td height="20" class="altRight"><b><?=$fruity->return_servicegroup_name($servicegroups_list[$counter]['servicegroup_id']);?>:</b> <?=$fruity->return_servicegroup_alias($servicegroups_list[$counter]['servicegroup_id']);?></td>
								</tr>
								<?php
							}
							?>
						</table>
				<?php	$fruity->get_servicegroup_list( $servicegroups_list); ?>
				<br />
				<br />
				<form name="service_template_servicegroup_add" method="post" action="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=servicegroups">
				<input type="hidden" name="request" value="add_servicegroup_command" />
				<input type="hidden" name="service_manage[servicegroup_add][service_template_id]" value="<?=$_GET['service_id'];?>" />
				<b>Add New Service Group:</b> <?php print_select("service_manage[servicegroup_add][servicegroup_id]", $servicegroups_list, "servicegroup_id", "servicegroup_name", "0");?> <input type="submit" value="Add Service Group"><br />
				<?=$fruity->element_desc("service_groups", "nagios_services_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'dependencies') {
			if(isset($tempServiceInfo['use_template_id'])) {
				$fruity->get_service_template_inherited_dependencies_list($tempServiceInfo['use_template_id'], $inherited_list);
				$numOfInheritedDepdendencies = count($inherited_list);
			}
			$fruity->return_service_dependencies_list($_GET['service_id'], $dependencies_list);
			$numOfDependencies = count($dependencies_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempServiceInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Depdendencies Inherited By Parent Template:</td>
								</tr>
								<?php
								$counter = 0;
								if($numOfInheritedDepdendencies) {
									foreach($inherited_list as $dependency) {
									$fruity->get_service_info($dependency['target_service_id'], $tempServiceInfo);
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
										<td height="20" class="altRight"><b><?=$fruity->return_host_name($tempServiceInfo['host_id']);?> : <?=$fruity->return_service_description($dependency['target_service_id']);?></td>
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
							<td colspan="2">Depdendencies Explicitly Linked to This Service:</td>
							</tr>
							<?php
							$counter = 0;
							if($numOfDependencies) {
								foreach($dependencies_list as $dependency) {
									$fruity->get_service_info($dependency['target_service_id'], $tempServiceInfo);

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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=dependencies&request=delete&dependency_id=<?=$dependency['dependency_id'];?>">Delete</a> ]</td>
									<td height="20" class="altRight"><b><?=$fruity->return_host_name($tempServiceInfo['host_id']);?> : <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$dependency['dependency_id'];?>"><?=$fruity->return_service_description($dependency['target_service_id']);?></a></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						<?
						$fruity->get_service_info( $_GET['service_id'], $tempServiceInfo );
						?>
						[ <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_add=1&service_id=<?=$_GET['service_id'];?>&<?php if($tempServiceInfo['host_template_id']) print("host_template_id=".$tempServiceInfo['host_template_id']); else print("host_id=".$tempServiceInfo['host_id']);?>">Create A New Service Dependency For This Service</a> ]
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'escalations') {
			if(isset($tempServiceInfo['use_template_id'])) {
				$fruity->get_service_template_inherited_escalations_list($tempServiceInfo['use_template_id'], $inherited_list);
				$numOfInheritedEscalations = count($inherited_list);
			}
			$fruity->return_service_escalations_list($_GET['service_id'], $escalations_list);
			$numOfEscalations = count($escalations_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($tempServiceInfo['use_template_id'])) {
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
										<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$escalation;?>"><?=$fruity->return_escalation_description($escalation);?></a></b></td>
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
							<td colspan="2">Escalations Explicitly Linked to This Service:</td>
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$_GET['service_id'];?>&section=escalations&request=delete&escalation_id=<?=$escalation['escalation_id'];?>">Delete</a> ]</td>
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
						[ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_add=1&service_id=<?=$_GET['service_id'];?>">Create A New Escalation For This Service</a> ]
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
	if($_GET['service_add']) {
		$add_template_list[] = array("service_template_id" => NULL, "template_name" => "None");
		if(count($template_list))
		foreach($template_list as $tempTemplate)
			$add_template_list[] = $tempTemplate;
		$title = "Add A Service For ";
		if(isset($_GET['host_template_id'])) {
			$title .= "Host Template: " . $fruity->return_host_template_name($_GET['host_template_id']);
		}
		elseif(!isset($_GET['hostgroup_id'])) {
			$title .= "Host: " . $fruity->return_host_name($_GET['host_id']);
		}
		else {
			$title .= "Hostgroup: " . $fruity->return_hostgroup_name($_GET['hostgroup_id']);
		}
		
		print_window_header($title, "100%");
		?>
		<form name="service_add_form" method="post" action="<?=$path_config['doc_root'];?>services.php?service_add=1&<?php if(isset($_GET['host_template_id'])) print("host_template_id=".$_GET['host_template_id']); elseif(isset($_GET['host_id'])) print("host_id=".$_GET['host_id']); else print("hostgroup_id=".$_GET['hostgroup_id']);?>">
		<input type="hidden" name="request" value="add_service" />
		<?php
		if(isset($_GET['host_template_id'])) {
			?>
			<input type="hidden" name="service_manage[host_template_id]" value="<?=$_GET['host_template_id'];?>" />
			<?php
		}
		elseif(isset($_GET['host_id'])) {
			?>
			<input type="hidden" name="service_manage[host_id]" value="<?=$_GET['host_id'];?>" />
			<?php
		}
		else {
			?>
			<input type="hidden" name="service_manage[hostgroup_id]" value="<?=$_GET['hostgroup_id'];?>" />
			<?php
		}			
		double_pane_form_window_start(); ?>
		<tr bgcolor="eeeeee">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Description:</b><br />
			<input type="text" size="40" name="service_manage[service_description]" value="<?=$_SESSION['tempData']['service_manage']['service_description'];?>" onblur="this.value=changeCharCode(this.value);"><br />
			<?=$fruity->element_desc("service_description", "nagios_services_desc"); ?><br />
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
			<b>Uses Service Template:</b> <?php print_select("service_manage[use_template_id]", $add_template_list, "service_template_id", "template_name");?><br />
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
		<input type="submit" value="Add Service" />&nbsp;[ <a href="<?=$path_config['doc_root'];?><?php if(isset($_GET['host_template_id'])) print("host_templates.php?host_template_id=".$_GET['host_template_id']); else print("hosts.php?host_id=".$_GET['host_id']);?>&section=services">Cancel</a> ]
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
