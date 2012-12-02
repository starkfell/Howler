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
	Filename:	hosts.php
*/
include_once('includes/config.inc');

function build_navbar($host_id, &$navbar) {
	global $path_config;
	global $sys_config;
	global $fruity;
	$tempID = $host_id;
	$tempNavBar = '';
	while($tempID <> 0) {	// If anything other than the network object
		$fruity->get_host_nav_info($tempID, $tempHostInfo);
		$tempNavBar = "<a href=\"".$path_config['doc_root']."hosts.php?host_id=".$tempID."\">".$tempHostInfo['host_name']."</a> > " . $tempNavBar;
		$tempID = $tempHostInfo['parents'];
	}
	$tempNavBar = "<a href=\"".$path_config['doc_root']."hosts.php\">".$sys_config['network_desc']."</a> > " . $tempNavBar;
	$navbar = $tempNavBar;
}


if (isset($_REQUEST['delete_msg']))
	$status_msg = $_REQUEST['delete_msg'];

// Data preparation

if(!isset($_GET['section']))
	$_GET['section'] = 'general';

// Get rid of initial data
unset($_SESSION['tempData']['host_manage']);

// If we're going to modify host data
if(isset($_GET['host_id']) && 
		($_GET['section'] == "general" ||
		$_GET['section'] == "checks" ||
		$_GET['section'] == "flapping" || 
		$_GET['section'] == "logging" || 
		$_GET['section'] == "parents" || 
		$_GET['section'] == "notifications") &&
		$_GET['edit']) {
	$fruity->get_host_info($_GET['host_id'], $_SESSION['tempData']['host_manage']);
	$_SESSION['tempData']['host_manage']['old_name'] = $_SESSION['tempData']['host_manage']['host_name'];
}

// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'groups') {
				$fruity->delete_hostgroup_member($_GET['hostgroup_id'], $_GET['host_id']);
				$status_msg = "Membership Deleted";
				unset($_SESSION['tempData']['host_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'services') {
			
			// Remove this service from reports
//			removeReportsByObject( $fruity->return_host_name($_GET['host_id']) , $fruity->return_service_description( $_GET['service_id'] ) );
			
			// Remove service
			$fruity->restart();
			$fruity->delete_service($_GET['service_id']);
			$status_msg = "Service Deleted";
			unset($_SESSION['tempData']['host_manage']);
			
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'general') {
			if($fruity->host_has_children($_GET['host_id'])) {
				
				$status_msg = "Unable to delete Host.  This host has children.";
				
			}
			else {
				
				// Remove this host from reports
//				removeReportsByObject( $fruity->return_host_name($_GET['host_id']) );
				
				// Remove host
				$fruity->restart();
				$fruity->delete_host($_GET['host_id']);
				$status_msg = "Deleted Host.";
				unset($_SESSION['tempData']['host_manage']);
				unset($_GET['request']);
				unset($_GET['host_id']);
				
			}
		}
		else if($_GET['request'] == "duplicate" && $_GET['section'] == 'general') {

			// Check if new host already exist
			if ($fruity->host_exists( $_REQUEST['new_host_name'] )) {
				print "Host '{$_REQUEST['new_host_name']}' already exists!0";
				exit(0);
			}

			// Get info about the current host
			$hostInfo = array();
			$fruity->get_host_info( $_REQUEST['host_id'], $hostInfo );

			// Update variables and add host
			$old_host_id = $hostInfo['host_id'];
			$hostInfo['host_name'] = $_REQUEST['new_host_name'];
			$hostInfo['alias'] = $_REQUEST['new_description'];
			$hostInfo['address'] = $_REQUEST['new_address'];
			unset($hostInfo['host_id']);
			unset_empty_array_values( $hostInfo );
			$fruity->add_host( $hostInfo );

			// Get new host id
			$new_host_id = $fruity->return_host_id_by_name( $hostInfo['host_name'] );

			// Get host extended info
			$host_extended_info = array();
			$fruity->get_host_extended_info( $old_host_id, $host_extended_info );
			
			// Update variables and add extended host info
			unset($host_extended_info['host_id']);
			unset_empty_array_values( $host_extended_info );
			$fruity->modify_host_extended( $new_host_id, $host_extended_info );

			// Hostgroups
			$hostgroups = array();
			$fruity->get_host_membership_list( $old_host_id, $hostgroups );

			foreach( $hostgroups as $array ) {
				$fruity->add_hostgroup_member( $array['hostgroup_id'], $new_host_id );
			}	
		
			// Contactgroups
			$contactgroups = array();
			$fruity->return_host_contactgroups_list( $old_host_id, $contactgroups );
			foreach( $contactgroups as $array ) {
				$fruity->add_host_contactgroup( $new_host_id, $array['contactgroup_id'] );
			}

			// Dependencies
			$dependencies = array();
			$fruity->return_host_dependencies_list( $old_host_id, $dependencies );
			foreach( $dependencies as $array ) {
				$fruity->add_host_dependency( $new_host_id, $array['target_host_id'] );
				$depId = $fruity->return_host_dependency( $new_host_id, $array['target_host_id'] );
				$array['dependency_id'] = $depId;
				$array['host_id'] = $new_host_id;
				unset_empty_array_values( $array );
				$fruity->modify_dependency( $array );
			}

			// Escalation
			$escalations = array();
			$fruity->return_host_escalations_list( $old_host_id, $escalations );
			foreach( $escalations as $array ) {
				$contactgroups = array();
				$fruity->return_escalation_contactgroups_list( $array['escalation_id'], $contactgroups );
				unset($array['host_id']);
				unset($array['escalation_id']);
				unset_empty_array_values( $array );
				$fruity->add_host_escalation( $new_host_id, $array['escalation_description'] );
				$escId = $fruity->return_host_escalation( $new_host_id, $array['escalation_description'] );
				$array['host_id'] = $new_host_id;
				$array['escalation_id'] = $escId;
				$fruity->modify_escalation( $array );
				foreach( $contactgroups as $gArray ) {
					$fruity->add_escalation_contactgroup( $escId, $gArray['contactgroup_id'] );
				}
			}

			// Host Check Commands
			$parameters = array();
			$fruity->get_host_check_command_parameters( $old_host_id, $parameters );
			foreach( $parameters as $array ) {
				unset($array['checkcommand_id']);
				unset_empty_array_values( $array );
				$fruity->add_host_command_parameter( $new_host_id, $array );
			}

			// Now, lets clone the services
			$services_list = array();
			$fruity->get_host_services_list( $old_host_id, $services_list );
			foreach( $services_list as $key => $service ) {
				$service_info = array();
				$extended_service_info = array();
				$check_cmds = array();
				$contactgroups = array();
				$servicegroups = array();

				// handle service check
				$fruity->get_service_info($service['service_id'], $service_info);
				$service_info['host_id'] = $new_host_id;
				unset($service_info['service_id']);
				unset($service_info['host_template_id']);
				unset_empty_array_values( $service_info );

				if ($fruity->service_exists($service_info))
					$service_info['service_description'] = "_DUPLICATED_" . $service_info['service_description'];

				if ($fruity->service_exists($service_info))
					continue;
				
				// Add service
				$fruity->add_service($service_info);

				// get new service_id
				$new_service_id = $fruity->return_service_id_by_host_and_description($new_host_id, $service_info['service_description']);

				// handle extended info
				$fruity->get_service_extended_info($service['service_id'], $extended_service_info);
				if (count($extended_service_info)) {
					unset_empty_array_values( $extended_service_info );
					if (!empty($extended_service_info)) {
						$extended_service_info['service_id'] = $new_service_id;
						$fruity->modify_service_extended($new_service_id, $extended_service_info);
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

						$fruity->add_service_command_parameter($new_service_id, $check_cmd);
					}
				}

				// handle Contact Groups
				$fruity->return_service_contactgroups_list($service['service_id'], $contactgroups );
				if (!empty($contactgroups)) {
					foreach($contactgroups as $key => $value)
						$fruity->add_service_contactgroup( $new_service_id, $value['contactgroup_id']);
				}

				// handle Group Membership
				$fruity->get_service_servicegroups($service['service_id'], $servicegroups );
				if (!empty($servicegroups)) {
					foreach($servicegroups as $key => $value)
						$fruity->add_service_servicegroup( $new_service_id, $value['servicegroup_id']);
				}
				
			}
			
			print "Host duplicated successfully!$new_host_id";
			exit(0);

		}
		else if($_GET['request'] == "create_template" && $_GET['section'] == 'general') {
			
			// Check if new host already exist
			if ($fruity->host_template_exists( $_REQUEST['new_template_name'] )) {
				print "Template '{$_REQUEST['new_template_name']}' already exists!0";
				exit(0);
			}

			// Get info about the current host
			$hostInfo = array();
			$fruity->get_host_info( $_REQUEST['host_id'], $hostInfo );

			// Update variables and add host template
			$old_host_id = $hostInfo['host_id'];
			$hostInfo['template_name'] = $_REQUEST['new_template_name'];
			$hostInfo['template_description'] = $_REQUEST['new_template_description'];
			unset($hostInfo['host_name']);
			unset($hostInfo['alias']);
			unset($hostInfo['address']);
			unset($hostInfo['host_id']);
			unset($hostInfo['parents']);
			unset($hostInfo['community']);
			unset($hostInfo['snmp_port']);
			unset($hostInfo['check_command']);
			unset_empty_array_values( $hostInfo );
			//print_r($hostInfo);
			$fruity->add_host_template( $hostInfo );

			// Get new host id
			$new_host_template_id = $fruity->return_host_template_id_by_name( $hostInfo['template_name'] );
			
			// Get host extended info
			$host_extended_info = array();
			$fruity->get_host_extended_info( $old_host_id, $host_extended_info );
			
			// Update variables and add extended host info
			unset($host_extended_info['host_id']);
			unset_empty_array_values( $host_extended_info );
			$fruity->modify_host_template_extended( $new_host_template_id, $host_extended_info );

			// Hostgroups
			$hostgroups = array();
			$fruity->get_host_membership_list( $old_host_id, $hostgroups );

			foreach( $hostgroups as $array ) {
				$fruity->add_hostgroup_template_member( $array['hostgroup_id'], $new_host_template_id );
			}	
		
			// Contactgroups
			$contactgroups = array();
			$fruity->return_host_contactgroups_list( $old_host_id, $contactgroups );
			foreach( $contactgroups as $array ) {
				$fruity->add_host_template_contactgroup( $new_host_template_id, $array['contactgroup_id'] );
			}

			// Host Check Commands
			$parameters = array();
			$fruity->get_host_check_command_parameters( $old_host_id, $parameters );
			foreach( $parameters as $array ) {
				unset($array['checkcommand_id']);
				unset_empty_array_values( $array );
				$fruity->add_host_template_command_parameter( $new_host_template_id, $array );
			}
			
			// Now, lets clone the services
			$services_list = array();
			$fruity->get_host_services_list( $old_host_id, $services_list );
			foreach( $services_list as $key => $service ) {
				$service_info = array();
				$extended_service_info = array();
				$check_cmds = array();
				$contactgroups = array();
				$servicegroups = array();

				// handle service check
				$fruity->get_service_info($service['service_id'], $service_info);
				unset($service_info['host_id']);
				unset($service_info['service_id']);
				$service_info['host_template_id'] = $new_host_template_id;
				unset_empty_array_values( $service_info );

				if ($fruity->service_exists($service_info))
					$service_info['service_description'] = "_DUPLICATED_" . $service_info['service_description'];

				if ($fruity->service_exists($service_info))
					continue;
				
				// Add service
				$fruity->add_service($service_info);

				// get new service_id
				$new_service_id = $fruity->return_service_id_by_host_template_and_description($new_host_template_id, $service_info['service_description']);

				// handle extended info
				$fruity->get_service_extended_info($service['service_id'], $extended_service_info);
				if (count($extended_service_info)) {
					unset_empty_array_values( $extended_service_info );
					if (!empty($extended_service_info)) {
						$extended_service_info['service_id'] = $new_service_id;
						$fruity->modify_service_extended($new_service_id, $extended_service_info);
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

						$fruity->add_service_command_parameter($new_service_id, $check_cmd);
					}
				}

				// handle Contact Groups
				$fruity->return_service_contactgroups_list($service['service_id'], $contactgroups );
				if (!empty($contactgroups)) {
					foreach($contactgroups as $key => $value)
						$fruity->add_service_contactgroup( $new_service_id, $value['contactgroup_id']);
				}

				// handle Group Membership
				$fruity->get_service_servicegroups($service['service_id'], $servicegroups );
				if (!empty($servicegroups)) {
					foreach($servicegroups as $key => $value)
						$fruity->add_service_servicegroup( $new_service_id, $value['servicegroup_id']);
				}
				
			}
			
			print "Host Template created successfully!$new_host_template_id";
			exit(0);
			
		}


		if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_host_contactgroup($_GET['host_id'], $_GET['contactgroup_id']);
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
			$fruity->delete_host_checkcommand_parameter($_GET['checkcommandparameter_id']);
			$status_msg = "Check Command Parameter Deleted.";
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'parents') {
			$fruity->host_delete_parent($_GET['host_id'], $_GET['parent_id']);
			$status_msg = "Parent deleted.";
		}
		
		
}

if(isset($_POST['request'])) {
	if(count($_POST['host_manage'])) {
		foreach( $_POST['host_manage'] as $key=>$value) {
			$_SESSION['tempData']['host_manage'][$key] = $value;
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
	
	if($_POST['request'] == 'add_host') {
		// Check for pre-existing host template with same name
		if($fruity->host_exists($_SESSION['tempData']['host_manage']['host_name'])) {
			$status_msg = "A host with that name already exists!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['host_manage'])) {
				foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['host_manage']['host_name'] == '' || $_SESSION['tempData']['host_manage']['alias'] == '' || $_SESSION['tempData']['host_manage']['address'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				if($_SESSION['tempData']['host_manage']['use_template_id'] == '')
					unset($_SESSION['tempData']['host_manage']['use_template_id']);
				// All is well for error checking, add the host into the db.
				if($fruity->add_host( $_SESSION['tempData']['host_manage'])) {
					$tempHostTemplateID = $fruity->return_host_id_by_name($_SESSION['tempData']['host_manage']['host_name']);
					// Remove session data
					unset($_SESSION['tempData']['host_manage']);
					unset($_GET['child_host_add']);
					$status_msg = "Host Added.";
					
					// Redirect to the new Host
					$_GET['host_id'] = $tempHostTemplateID;
					
				}
				else {
					$addError = 1;
					$status_msg = "Error: add_host failed.";
				}
			}
		}
	}
	else if($_POST['request'] == 'host_modify_general') {
		if($_SESSION['tempData']['host_manage']['host_name'] != $_SESSION['tempData']['host_manage']['old_name'] && $fruity->host_exists($_SESSION['tempData']['host_manage']['host_name'])) {
			$status_msg = "A host with that name already exists!";
		}
		else {

			// Log Changes
			if($_SESSION['tempData']['host_manage']['host_name'] != $_SESSION['tempData']['host_manage']['old_name']) {
				$type_string = "host";
				$to_change_string = $_SESSION['tempData']['host_manage']['old_name'] . "#" . $_SESSION['tempData']['host_manage']['host_name'];
				$fruity->set_changes( $type_string, $to_change_string );
			}
			
			// Field Error Checking
			if(count($_SESSION['tempData']['host_manage'])) {
				foreach($_SESSION['tempData']['host_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			
			if($_SESSION['tempData']['host_manage']['host_name'] == '' || $_SESSION['tempData']['host_manage']['alias'] == '' || $_SESSION['tempData']['host_manage']['address'] == '') {
				$addError = 1;
				$status_msg = "Incorrect values for fields. Please verify.";
			}
			
			// Verify cyclic configurations in parents
			if (isset($_SESSION['tempData']['host_manage']['parents'])) {
				
				$host_id = $_SESSION['tempData']['host_manage']['host_id'];
				$parent_id = $_SESSION['tempData']['host_manage']['parents'];
				
				$fruity->get_host_info( $parent_id, $parentInfo );
				
				if ($parentInfo['parents'] == $host_id) {
					$addError = 1;
					$status_msg = "Cyclic parent configuration. Please verify.";
				}

			}

			if (!$addError) {
					
				if($fruity->modify_host($_SESSION['tempData']['host_manage'])) {
					// Remove session data
					unset($_SESSION['tempData']['host_manage']);
					$status_msg = "Host modified.";
					unset($_GET['edit']);
				}
				else {
					$status_msg = "Error: modify_host failed.";
				}
				
			}
		}
	}
	else if($_POST['request'] == 'host_modify_checks') {
		if(count($_SESSION['tempData']['host_manage'])) {
			foreach($_SESSION['tempData']['host_manage'] as $tempVariable) {
				$tempVariable = trim($tempVariable);
			}
		}
		if(($_POST['max_check_attempts_include'] && !is_numeric($_SESSION['tempData']['host_manage']['max_check_attempts'])) || ($_POST['max_check_attempts_include'] && !($_SESSION['tempData']['host_manage']['max_check_attempts'] >= 1)) || ($_POST['freshness_threshold_include'] && !($_SESSION['tempData']['host_manage']['freshness_threshold'] >= 0))) {
			$addError = 1;
			$status_msg = "Incorrect values for fields.  Please verify.";
		}
		// All is well for error checking, modify the template.
		else if($fruity->modify_host($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host failed.";
		}
	}
	else if($_POST['request'] == 'host_modify_flapping') {
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
		else if($fruity->modify_host($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host failed.";
		}
	}
	else if($_POST['request'] == 'host_modify_logging') {
		// Field Error Checking
		// None required for this process
		// All is well for error checking, modify the command.
		if($fruity->modify_host($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host failed.";
		}
	}
	else if($_POST['request'] == 'host_modify_notifications') {
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
		else if($fruity->modify_host($_SESSION['tempData']['host_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Host modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_host failed.";
		}
	}	
	else if($_POST['request'] == 'add_host_service') {
		if(host_has_service($_GET['host_id'], $_SESSION['tempData']['host_manage']['service_id'])) {
			$status_msg = "That host already has that service linked.";
		}
		else {
			// All is well, link the service definition.
			link_host_service($_GET['host_id'], $_SESSION['tempData']['host_manage']['service_id']);
			unset($_SESSION['tempData']['host_manage']);
			$status_msg = "Service linked to this host.";
		}
	}
	else if($_POST['request'] == 'add_member_command') {
		if($fruity->host_has_hostgroup($_GET['host_id'], $_SESSION['tempData']['host_manage']['group_add']['hostgroup_id'])) {
			$status_msg = "That host group already exists in that list!";
		}
		else {
			$fruity->add_hostgroup_member($_SESSION['tempData']['host_manage']['group_add']['hostgroup_id'], $_SESSION['tempData']['host_manage']['group_add']['host_id']);
			$status_msg = "Host Added To Host Group.";
			unset($_SESSION['tempData']['host_manage']);
		}
	}
	else if($_POST['request'] == 'command_parameter_add') {
		// All is well for error checking, modify the command.
		$fruity->add_host_command_parameter($_GET['host_id'], $_SESSION['tempData']['host_manage']);
		// Remove session data
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Command Parameter added.";
	}
	else if($_POST['request'] == 'command_parameter_modify') {
		
		$fruity->delete_host_checkcommand_parameter( "", $_GET['host_id']);
		
		$temp = array();
		$temp['host_id'] = $_GET['host_id'];
		for ( $i = 1; $i <= $_POST['numCheckCommandParameters']; $i++ ) {
			$temp['parameter'] = $_POST["ARG$i"];
			$fruity->add_host_command_parameter( $_GET['host_id'], $temp);
		}
		// Remove session data
		unset($temp);
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Command Parameter modified.";		
		
	}
	
	
	if($_POST['request'] == 'update_host_extended') {
		$fruity->modify_host_extended($_GET['host_id'], $_SESSION['tempData']['host_manage']);
		unset($_SESSION['tempData']['host_manage']);
		$status_msg = "Updated Host Extended Information";
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->host_has_contactgroup($_GET['host_id'], $_SESSION['tempData']['host_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['host_manage']);
		}
		else {
			$fruity->add_host_contactgroup($_GET['host_id'], $_SESSION['tempData']['host_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Host Contact Group Link added.";
			unset($_SESSION['tempData']['host_manage']);
		}
	}
	else if($_POST['request'] == 'parent_add') {
		// Check for valid host
		$id = $fruity->return_host_id_by_name($_POST['parent_id']);
		if(!$id) {
			// Couldn't find host
			$status_msg = "Unable to find host with name: " . $_POST['parent_id'];	
		}
		else {

			$host_info = array();
			$fruity->get_host_info( $_GET['host_id'], $host_info );
			
			if($fruity->host_has_parent($_GET['host_id'], $id) || $host_info['parents'] == $id) {
				$status_msg = "This host already has " . $_POST['parent_id'] . " as a parent!";
			}
			else {
				$fruity->host_add_parent($_GET['host_id'], $id);
				$status_msg = "Added parent to host.";
			}
		}
	}
}

// Reset data
if(!isset($_GET['host_id'])) {
	$_GET['host_id'] = 0;	// 0 Represents the "NAGIOS" object, the root of all evil.
}
if($_GET['host_id'] == 0) {
	$tempHostInfo['host_name'] = $sys_config['network_desc'];
}

if($_GET['host_id'] != 0) {
	$fruity->getHostObject()->reload($_GET['host_id']);	// Load this host information into Fruity's host object
}



// Get list of children hosts
$fruity->get_children_hosts_list($_GET['host_id'], $children_list);
$numOfChildren = count($children_list);

// Build the navigation bar
build_navbar($_GET['host_id'], $navbar);

$fruity->get_host_info($_GET['host_id'], $tempHostInfo);

print_header("Host Editor");

print($navbar);
?>
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($invalidHost)) {
		print_footer();
		die();
	}

	if($_GET['host_id'] != 0 && !isset($_GET['child_host_add'])) {
		$fruity->getHostObject()->render();
	}
	?>
	<br />
	<br />
	<?php
	if($_GET['section'] == "general" && !isset($_GET['child_host_add'])) {
		print_window_header("Children Hosts for " . $tempHostInfo['host_name'], "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&child_host_add=1">Add A New Child Host</a><br />
		<br />
		<?php
		if($numOfChildren) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Host Name</td>
			<td>Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfChildren; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$children_list[$counter]['host_id'];?>"><?=$children_list[$counter]['host_name'];?></a> <? $numOfSubChildren = $fruity->return_num_of_children($children_list[$counter]['host_id']); if($numOfSubChildren) print("(".$numOfSubChildren.")");?></td>
				<td height="20" class="altRight"><?=$children_list[$counter]['alias'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Children Hosts Exists</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}
	if($_GET['child_host_add']) {
		$add_template_list[] = array("host_template_id" => '', "template_name" => "None");
		$fruity->get_host_template_list( $template_list);
		
		if(count($template_list)) {
			foreach($template_list as $tempTemplate) {
				$add_template_list[] = $tempTemplate;
			}
		}
		
		print_window_header("Add A Host To " . $tempHostInfo['host_name'], "100%");
		?>
		<form name="host_add_form" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>">
		<input type="hidden" name="request" value="add_host" />
		<?php
		if($_GET['host_id'] != 0) {
			?>
			<input type="hidden" name="host_manage[parents]" value="<?=$_GET['host_id'];?>">
			<?php
		}
		?>
		<?php double_pane_form_window_start(); ?>
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Host Name:</b><br />
			<input type="text" size="40" name="host_manage[host_name]" value="<?=$_SESSION['tempData']['host_manage']['host_name'];?>" onblur="this.value=changeCharCode(this.value);"><br />
			<?=$fruity->element_desc("host_name", "nagios_hosts_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		
	<!---------------------------------------------------------------------------------------------->		
		
		<tr bgcolor="eeeeee">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Host Description:</b><br />
			<input type="text" size="40" name="host_manage[alias]" value="<?=$_SESSION['tempData']['host_manage']['alias'];?>"><br />
			<?=$fruity->element_desc("alias", "nagios_hosts_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		
	<!---------------------------------------------------------------------------------------------->
		
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Address:</b><br />
			<input type="text" size="40" name="host_manage[address]" value="<?=$_SESSION['tempData']['host_manage']['address'];?>"><br />
			<?=$fruity->element_desc("address", "nagios_hosts_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		
	<!---------------------------------------------------------------------------------------------->
		
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>SNMP Community:</b><br />
			<input type="text" size="40" name="host_manage[community]" value="<?=$_SESSION['tempData']['host_manage']['community'];?>"><br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr bgcolor="f0f0f0">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>SNMP Port:</b><br />
			<input type="text" size="40" name="host_manage[snmp_port]" value="<?=$_SESSION['tempData']['host_manage']['snmp_port'];?>"><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>		
		
	<!---------------------------------------------------------------------------------------------->		
		
		
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
		<input type="submit" value="Add Host" />&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php">Cancel</a> ]
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
