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


include_once('includes/config.inc');

if (isset( $argv[1])) {
	$_GET['confirmed'] = 1;
	$_SERVER["PHP_AUTH_USER"] = $argv[1];
	$_SERVER["REMOTE_ADDR"] = $argv[2];
	$fruity = new Fruity();
}

function recursiveRemoveDirectory($path)
{   
	try {
	$dir = new RecursiveDirectoryIterator($path);
	}
	catch(Exception $e) {
		return;
	}
	//Remove all files
	foreach(new RecursiveIteratorIterator($dir) as $file)
	{
	   unlink($file);
	}
	
	//Remove all subdirectories
	foreach($dir as $subDir)
	{
	   //If a subdirectory can't be removed, it's because it has subdirectories, so recursiveRemoveDirectory is called again passing the subdirectory as path
	   if(!@rmdir($subDir)) //@ suppress the warning message
	   {
	       recursiveRemoveDirectory($subDir);
	   }
	}
	//Remove main directory
	rmdir($path);
}

function prepare_for_export( &$obj) {
	if( $obj) {
		if( is_array( $obj)) {
			foreach( $obj as $key=>$val) {
				$obj[$key] = prepare_for_export($val);
			}
		} elseif( is_string( $obj)) {
			$obj = html_entity_decode( $obj);
		}
		
		return $obj;
	}
	// WAS return NULL;
	return $obj;
}

function write_host_template( $templateInfo) {
	global $tempHandler;
	global $fruity;
	// Let's remove the stuff that isn't used
	unset($templateInfo['template_description']);
	
	// Get template inherited values
	$inherited_hostgroups = array();
	$inherited_contactgroups = array();
	$inherited_parameters = array();
	// SF BUG# 1445803
	// templating error with fruity 1.0rc
	$tempHostTemplateInfo = array();
	$tempHostTemplateInfoSources = array();
	
	$fruity->get_host_template_inherited_hostgroups_list($templateInfo['host_template_id'], $inherited_hostgroups);
	$fruity->get_host_template_inherited_contactgroups_list($templateInfo['host_template_id'], $inherited_contactgroups);
	$fruity->get_host_template_inherited_commandparameter_list($templateInfo['use_template_id'], $inherited_parameters);
	$fruity->get_host_template_check_command_parameters($templateInfo['host_template_id'], $parameters);
	
	$fruity->get_host_template_membership_list($templateInfo['host_template_id'], $hostgroupList);
	
	$fruity->get_inherited_host_template_values( $templateInfo['use_template_id'], $tempHostTemplateInfo, $tempHostTemplateInfoSources);
		
	if( isset( $tempHostTemplateInfo['check_command'])
			&& !is_null( $tempHostTemplateInfo['check_command'])
			&& is_null( $templateInfo['check_command'])) {
		$templateInfo['check_command'] = $tempHostTemplateInfo['check_command'];
	} else {
		$inherited_parameters = array();
	}
	
	if( count( $parameters) == 0 && count( $inherited_parameters) > 0) {
		unset( $templateInfo['check_command']);
	}
	
	unset($templateInfo['host_template_id']);
	
	fputs($tempHandler, "define host {\n");
	fputs($tempHandler, "\tname ".$templateInfo['template_name'] ."\n");
	unset($templateInfo['template_name']);
	$templateInfo['register'] = 0;
	if(isset($templateInfo['use_template_id'])) {
		fputs($tempHandler, "\tuse " . $fruity->return_host_template_name($templateInfo['use_template_id']) ."\n");
	}
	unset($templateInfo['use_template_id']);
	unset($templateInfo['notification_options']);
	unset($templateInfo['stalking_options']);
	
	unset($hostTemplateParameters);
	if(count($inherited_parameters)) {
		foreach($inherited_parameters as $parameter) {
				$hostTemplateParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$serviceParameters .= $parameter['parameter'];
		}
	}
	if(count($parameters)) {
		foreach($parameters as $parameter) {
				$hostTemplateParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$hostTemplateParameters .= $parameter['parameter'];
		}
	}
	
	if(count($templateInfo)) {
		foreach($templateInfo as $key => $value) {
			switch($key) {
				case "check_command":
					$value = $fruity->return_command_name($value) . $hostTemplateParameters;
					break;
				case "notification_period":
					$value = $fruity->return_period_name($value);
					break;
				case "event_handler":
					$value = $fruity->return_command_name($value);
					break;
				case "check_period":
					$value = $fruity->return_period_name($value);
					break;
				case "notification_options_down":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "d";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_unreachable":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "u";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_recovery":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "r";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_flapping":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "f";
					}
					unset($templateInfo[$key]);
					break;					
				case "stalking_options_up":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "o";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_down":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "d";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_unreachable":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "o";
					}
					unset($templateInfo[$key]);
					break;
			}
			if(isset($templateInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($templateInfo['notification_options']))
		fputs($tempHandler, "\tnotification_options " . $templateInfo['notification_options'] ."\n");
	if(isset($templateInfo['stalking_options']))
		fputs($tempHandler, "\tstalking_options " . $templateInfo['stalking_options'] ."\n");
	// Host groups
	unset($templateInfo['hostgroups']);
	if(count($inherited_hostgroups)) {
		foreach($inherited_hostgroups as $hostgroup) {
			if(isset($templateInfo['hostgroups'])) {
				$templateInfo['hostgroups'] .= ",";
			}
			$templateInfo['hostgroups'] .= $fruity->return_hostgroup_name($hostgroup);
		}
	}
	if(isset($templateInfo['hostgroups'])) {
		prepare_for_export( $templateInfo['hostgroups']);
		fputs($tempHandler, "\thostgroups " . $templateInfo['hostgroups'] ."\n");
	}
	// END Host Groups
	// Contact groups
	unset($templateInfo['contact_groups']);
	if(count($inherited_contactgroups)) {
		foreach($inherited_contactgroups as $contactgroup) {
			if(isset($templateInfo['contact_groups'])) {
				$templateInfo['contact_groups'] .= ",";
			}
			$templateInfo['contact_groups'] .= $fruity->return_contactgroup_name($contactgroup);
		}
	}
	if(isset($templateInfo['contact_groups'])) {
		prepare_for_export( $templateInfo['contact_groups']);
		fputs($tempHandler, "\tcontact_groups " . $templateInfo['contact_groups'] ."\n");
	}
	// END Contact Groups			
		
	fputs($tempHandler, "}\n\n"); 
}

function write_host($hostInfo) {
	global $tempHandler;
	global $fruity;
	
	// Add to Nagios Tables
	//add_host_to_opmon( $hostInfo['host_id'], $hostInfo['host_name'] );

	fputs($tempHandler, "define host {\n");
	if(isset($hostInfo['use_template_id'])) {

		fputs($tempHandler, "\tuse ".$fruity->return_host_template_name($hostInfo['use_template_id']) . "\n");
		// Get template inherited values
		$inherited_hostgroups = array();
		$inherited_contactgroups = array();
		$inherited_paramters = array();
		// SF BUG# 1445803
		// templating error with fruity 1.0rc
		$tempHostTemplateInfo = array();
		$tempHostTemplateInfoSources = array();
		
		$fruity->get_host_template_inherited_hostgroups_list($hostInfo['use_template_id'], $inherited_hostgroups);
		$fruity->get_host_template_inherited_contactgroups_list($hostInfo['use_template_id'], $inherited_contactgroups);
		$fruity->get_host_template_inherited_commandparameter_list($hostInfo['use_template_id'], $inherited_parameters);
		
		$fruity->get_inherited_host_template_values( $hostInfo['use_template_id'], $tempHostTemplateInfo, $tempHostTemplateInfoSources);
		
		if( isset( $tempHostTemplateInfo['check_command'])
				&& !is_null( $tempHostTemplateInfo['check_command'])
				&& is_null( $hostInfo['check_command'])) {
			$hostInfo['check_command'] = $tempHostTemplateInfo['check_command'];
		} else {
			$inherited_parameters = array();
		}
		
		unset($hostInfo['use_template_id']);
	}
	$fruity->get_host_check_command_parameters($hostInfo['host_id'], $parameters);
	
	if( count( $parameters) == 0 && count( $inherited_parameters) > 0) {
		unset( $hostInfo['check_command']);
	}
	
	// Now let's get our hostgroups
	unset($hostgroupList);
	unset($contactgroupList);
	$fruity->get_host_membership_list($hostInfo['host_id'], $hostgroupList);
	$fruity->return_host_contactgroups_list($hostInfo['host_id'], $contactgroupList);
	$fruity->return_host_parents_list($hostInfo['host_id'], $parents);
	unset($hostInfo['host_id']);	
	unset($hostInfo['notification_options']);
	unset($hostInfo['stalking_options']);
	
	unset($hostParameters);
	if(count($inherited_parameters)) {
		foreach($inherited_parameters as $parameter) {
				$hostParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$hostParameters .= $parameter['parameter'];
		}
	}
	if(count($parameters)) {
		foreach($parameters as $parameter) {
				$hostParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$hostParameters .= $parameter['parameter'];
		}
	}
	
	if(count($hostInfo)) {
		foreach($hostInfo as $key => $value) {
			
			if ($key == "community" || $key == "snmp_port")
				continue;
			
			switch($key) {
				case "parents":
					$value = $fruity->return_host_name($value);
					
					// ALSO BUILD SUB PARENTS
					if(count($parents)) {
						foreach($parents as $parent) {
							$value .= "," . $fruity->return_host_name($parent['parent_id']);
						}
					}
					
					break;
				case "check_command":
					$value = $fruity->return_command_name($value) . $hostParameters;
					break;
				case "notification_period":
					$value = $fruity->return_period_name($value);
					break;
				case "event_handler":
					$value = $fruity->return_command_name($value);
					break;
				case "check_period":
					$value = $fruity->return_period_name($value);
					break;
				case "notification_options_down":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['notification_options']))
							$hostInfo['notification_options'] .= ",";
						$hostInfo['notification_options'] .= "d";
					}
					unset($hostInfo[$key]);
					break;
				case "notification_options_unreachable":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['notification_options']))
							$hostInfo['notification_options'] .= ",";
						$hostInfo['notification_options'] .= "u";
					}
					unset($hostInfo[$key]);
					break;
				case "notification_options_recovery":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['notification_options']))
							$hostInfo['notification_options'] .= ",";
						$hostInfo['notification_options'] .= "r";
					}
					unset($hostInfo[$key]);
					break;
				case "notification_options_flapping":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['notification_options']))
							$hostInfo['notification_options'] .= ",";
						$hostInfo['notification_options'] .= "f";
					}
					unset($hostInfo[$key]);
					break;	
				case "stalking_options_up":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['stalking_options']))
							$hostInfo['stalking_options'] .= ",";
						$hostInfo['stalking_options'] .= "o";
					}
					unset($hostInfo[$key]);
					break;
				case "stalking_options_down":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['stalking_options']))
							$hostInfo['stalking_options'] .= ",";
						$hostInfo['stalking_options'] .= "d";
					}
					unset($hostInfo[$key]);
					break;
				case "stalking_options_unreachable":
					if($hostInfo[$key] == 1) {
						if(isset($hostInfo['stalking_options']))
							$hostInfo['stalking_options'] .= ",";
						$hostInfo['stalking_options'] .= "o";
					}
					unset($hostInfo[$key]);
					break;
			}
			if(isset($hostInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($hostInfo['notification_options']))
		fputs($tempHandler, "\tnotification_options " . $hostInfo['notification_options'] ."\n");
	if(isset($hostInfo['stalking_options']))
		fputs($tempHandler, "\tstalking_options " . $hostInfo['stalking_options'] ."\n");
	// Host groups
	unset($hostInfo['hostgroups']);
	unset($tempHostGroups);
	// First check for inherited host groups
	if(count($inherited_hostgroups)) {
		foreach($inherited_hostgroups as $hostgroup) {
				$tempHostGroups[] = $fruity->return_hostgroup_name($hostgroup);
		}
	}
	if(count($hostgroupList)) {
		foreach($hostgroupList as $hostgroup) {
				if(!is_array($tempHostGroups) || !in_array($hostgroup, $tempHostGroups))
					$tempHostGroups[] = $fruity->return_hostgroup_name($hostgroup['hostgroup_id']);
		}
	}
	if(count($tempHostGroups)) {
		foreach($tempHostGroups as $hostgroup) {
			if(isset($hostInfo['hostgroups']))
				$hostInfo['hostgroups'] .= ",";
			$hostInfo['hostgroups'] .= $hostgroup;
		}
	}
	
	if(isset($hostInfo['hostgroups'])) {
		prepare_for_export( $hostInfo['hostgroups']);
		fputs($tempHandler, "\thostgroups " . $hostInfo['hostgroups'] ."\n");
	}
	// END Host Groups	
	
	// Contact groups
	unset($hostInfo['contactgroups']);
	unset($tempContactGroups);
	// First check for inherited host groups
	if(count($inherited_contactgroups)) {
		foreach($inherited_contactgroups as $contactgroup) {
				$tempContactGroups[] = $fruity->return_contactgroup_name($contactgroup);
		}
	}
	if(count($contactgroupList)) {
		foreach($contactgroupList as $contactgroup) {
				if(!is_array($tempContactGroups) || !in_array($contactgroup, $tempContactGroups))
					$tempContactGroups[] = $fruity->return_contactgroup_name($contactgroup['contactgroup_id']);
		}
	}
	if(count($tempContactGroups)) {
		foreach($tempContactGroups as $contactgroup) {
			if(isset($hostInfo['contact_groups']))
				$hostInfo['contact_groups'] .= ",";
			$hostInfo['contact_groups'] .= $contactgroup;
		}
	}
	
	if(isset($hostInfo['contact_groups'])) {
		prepare_for_export( $hostInfo['contact_groups']);
		fputs($tempHandler, "\tcontact_groups " . $hostInfo['contact_groups'] ."\n");
	}
	// END Contact Groups	
		
		
	fputs($tempHandler, "}\n\n"); 
}

function write_command($commandInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $commandInfo);
	fputs($tempHandler, "define command{\n");
	fputs($tempHandler, "command_name	".$commandInfo['command_name']."\n");
	fputs($tempHandler, "command_line	".$commandInfo['command_line']."\n");
	fputs($tempHandler, "}\n\n");
}

function write_timeperiod($periodInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $periodInfo);
	fputs($tempHandler, "define timeperiod{\n");
	fputs($tempHandler, "\ttimeperiod_name	".$periodInfo['timeperiod_name']."\n");
	fputs($tempHandler, "\talias	".$periodInfo['alias']."\n");
	if($periodInfo['sunday'])
		fputs($tempHandler, "\tsunday	".$periodInfo['sunday']."\n");
	if($periodInfo['monday'])
		fputs($tempHandler, "\tmonday	".$periodInfo['monday']."\n");
	if($periodInfo['tuesday'])
		fputs($tempHandler, "\ttuesday	".$periodInfo['tuesday']."\n");
	if($periodInfo['wednesday'])
		fputs($tempHandler, "\twednesday	".$periodInfo['wednesday']."\n");
	if($periodInfo['thursday'])
		fputs($tempHandler, "\tthursday	".$periodInfo['thursday']."\n");
	if($periodInfo['friday'])
		fputs($tempHandler, "\tfriday	".$periodInfo['friday']."\n");
	if($periodInfo['saturday'])
		fputs($tempHandler, "\tsaturday	".$periodInfo['saturday']."\n");
	fputs($tempHandler, "}\n\n");
}

function write_contactgroup($groupInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $groupInfo);
	fputs($tempHandler, "define contactgroup{\n");
	fputs($tempHandler, "\tcontactgroup_name	".$groupInfo['contactgroup_name']."\n");
	fputs($tempHandler, "\talias	".$groupInfo['alias']."\n");
	fputs($tempHandler, "\tmembers	");
	$fruity->return_contactgroup_member_list($groupInfo['contactgroup_id'], $memberList);
	$numOfMembers = count($memberList);	
	for($subcounter = 0; $subcounter < $numOfMembers; $subcounter++ ) {
		if($subcounter)
			fputs($tempHandler,",");
		$contactName = $fruity->return_contact_name($memberList[$subcounter]['contact_id']);
		prepare_for_export( $contactName);
		fputs($tempHandler,$contactName);
	}
	fputs($tempHandler, "\n}\n\n");
}

function write_contact($contactInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $contactInfo);
	fputs($tempHandler, "define contact{\n");
	fputs($tempHandler, "\tcontact_name	".$contactInfo['contact_name']."\n");
	fputs($tempHandler, "\talias	".$contactInfo['alias']."\n");
	$host_period_name = $fruity->return_period_name($contactInfo['host_notification_period']);
	fputs($tempHandler, "\thost_notification_period	".prepare_for_export($host_period_name)."\n");
	$service_period_name = $fruity->return_period_name($contactInfo['service_notification_period']);
	fputs($tempHandler, "\tservice_notification_period	".prepare_for_export($service_period_name)."\n");

	fputs($tempHandler, "\thost_notification_options	");
	if(!$contactInfo['host_notification_options_down'] && !$contactInfo['host_notification_options_unreachable'] && !$contactInfo['host_notification_options_recovery'] && !$contactInfo['host_notification_options_flapping']) {
		fputs($tempHandler, "n");
	}
	else {
		if($contactInfo['host_notification_options_down']) {
			fputs($tempHandler, "d");
			if($contactInfo['host_notification_options_unreachable'] || $contactInfo['host_notification_options_recovery'] || $contactInfo['host_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['host_notification_options_unreachable']) {
			fputs($tempHandler, "u");
			if($contactInfo['host_notification_options_recovery'] || $contactInfo['host_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['host_notification_options_recovery']) {
			fputs($tempHandler, "r");
			if($contactInfo['host_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['host_notification_options_flapping']) {
			fputs($tempHandler, "f");
		}
	}
	fputs($tempHandler,"\n");
	
	fputs($tempHandler, "\tservice_notification_options	");
	if(!$contactInfo['service_notification_options_warning'] && !$contactInfo['service_notification_options_unknown'] && !$contactInfo['service_notification_options_critical'] && !$contactInfo['service_notification_options_recovery'] && !$contactInfo['service_notification_options_flapping']) {
		fputs($tempHandler, "n");
	}
	else {
		if($contactInfo['service_notification_options_warning']) {
			fputs($tempHandler, "w");
			if($contactInfo['service_notification_options_unknown'] || $contactInfo['service_notification_options_critical'] || $contactInfo['service_notification_options_recovery'] || $contactInfo['service_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['service_notification_options_unknown']) {
			fputs($tempHandler, "u");
			if($contactInfo['service_notification_options_critical'] || $contactInfo['service_notification_options_recovery'] || $contactInfo['service_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['service_notification_options_critical']) {
			fputs($tempHandler, "c");
			if($contactInfo['service_notification_options_recovery'] || $contactInfo['service_notification_options_flapping'])
				fputs($tempHandler, ",");
		}
		if($contactInfo['service_notification_options_recovery']) {
			fputs($tempHandler, "r");
			if($contactInfo['service_notification_options_flapping'])
				fputs($tempHandler, ",");
		}		
		if($contactInfo['service_notification_options_flapping']) {
			fputs($tempHandler, "f");
		}		
	}		
	fputs($tempHandler,"\n");
	
	$fruity->get_contacts_notification_commands($contactInfo['contact_id'], $notification_list);
	prepare_for_export( $notification_list);
	$numOfHostCommands = count($notification_list['host']);
	if($numOfHostCommands) {
		fputs($tempHandler, "\thost_notification_commands	");
		for($subcounter = 0; $subcounter < $numOfHostCommands; $subcounter++) {
			if($subcounter)
				fputs($tempHandler,",");
			$command_name = $fruity->return_command_name($notification_list['host'][$subcounter]['command_id']);
			fputs($tempHandler,prepare_for_export($command_name));
		}
		fputs($tempHandler, "\n");
	}
	$numOfServiceCommands = count($notification_list['service']);
	if($numOfServiceCommands) {
		fputs($tempHandler, "\tservice_notification_commands ");
		for($subcounter = 0; $subcounter < $numOfServiceCommands; $subcounter++) {
			if($subcounter)
				fputs($tempHandler,",");
			$command_name = $fruity->return_command_name($notification_list['service'][$subcounter]['command_id']);
			fputs($tempHandler,prepare_for_export($command_name));
		}
		fputs($tempHandler, "\n");
	}
	if($contactInfo['email'])
		fputs($tempHandler, "\temail	".$contactInfo['email']."\n");
	if($contactInfo['pager'])			
		fputs($tempHandler, "\tpager	".$contactInfo['pager']."\n");
	$fruity->get_contact_addresses($contactInfo['contact_id'], $contactAddresses);
	$numOfAddresses = count($contactAddresses);
	for($subcounter = 0; $subcounter < $numOfAddresses; $subcounter++) {
		fputs($tempHandler, "\taddress".($subcounter+1)."	".$contactAddresses[$subcounter]['address']."\n");
	}
	fputs($tempHandler, "}\n\n");
}

function write_hostgroup($groupInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $groupInfo);
	fputs($tempHandler, "define hostgroup{\n");
	fputs($tempHandler, "\thostgroup_name	".$groupInfo['hostgroup_name']."\n");
	fputs($tempHandler, "\talias	".$groupInfo['alias']."\n");
	fputs($tempHandler, "}\n\n");
}

function write_service_template($templateInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $templateInfo);
	// Let's remove the stuff that isn't used
	unset($templateInfo['template_description']);
	
	// Get template inherited values
	$inherited_servicegroups = array();
	$inherited_contactgroups = array();
	$inherited_parameters = array();
	$parameters = array();
	// SF BUG# 1445803
	// templating error with fruity 1.0rc
	$tempServiceTemplateInfo = array();
	$tempServiceTemplateInfoSources = array();
	
	$fruity->get_service_template_inherited_servicegroups_list($templateInfo['service_template_id'], $inherited_servicegroups);
	$fruity->get_service_template_inherited_contactgroups_list($templateInfo['service_template_id'], $inherited_contactgroups);
	if(!isset($templateInfo['check_command']))
		$fruity->get_service_template_inherited_commandparameter_list($templateInfo['use_template_id'], $inherited_parameters);
	$fruity->get_service_template_check_command_parameters( $templateInfo['service_template_id'], $parameters);
	
	$fruity->get_inherited_service_template_values( $templateInfo['use_template_id'], $tempServiceTemplateInfo, $tempServiceTemplateInfoSources);
	
	
	if( isset( $tempServiceTemplateInfo['check_command'])
			&& !is_null( $tempServiceTemplateInfo['check_command'])
			&& is_null( $templateInfo['check_command'])) {
		$templateInfo['check_command'] = $tempServiceTemplateInfo['check_command'];
	} else {
		//$inherited_parameters = array();
	}
	
	/*
	if( count( $parameters) == 0 && count( $inherited_parameters) > 0) {
		unset( $templateInfo['check_command']);
	}
	*/
	
	unset($templateInfo['service_template_id']);
	
	fputs($tempHandler, "define service {\n");
	fputs($tempHandler, "\tname ".$templateInfo['template_name'] ."\n");
	unset($templateInfo['template_name']);
	$templateInfo['register'] = 0;
	if(isset($templateInfo['use_template_id'])) {
		$templateName = $fruity->return_service_template_name($templateInfo['use_template_id']);
		fputs($tempHandler, "\tuse " . prepare_for_export( $templateName) ."\n");
	}
	
	unset($templateInfo['use_template_id']);
	unset($templateInfo['notification_options']);
	unset($templateInfo['stalking_options']);
	
	unset($serviceTemplateParameters);
	if(count($inherited_parameters)) {
		foreach($inherited_parameters as $parameter) {
				$serviceTemplateParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$serviceTemplateParameters .= $parameter['parameter'];
		}
	}
	if(count($parameters)) {
		foreach($parameters as $parameter) {
				$serviceTemplateParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$serviceTemplateParameters .= $parameter['parameter'];
		}
	}
	if(count($templateInfo)) {
		foreach($templateInfo as $key => $value) {
			switch($key) {
				case "check_command":
					$value = $fruity->return_command_name($value) . $serviceTemplateParameters;
					break;
				case "notification_period":
					$value = $fruity->return_period_name($value);
					break;
				case "event_handler":
					$value = $fruity->return_command_name($value);
					break;
				case "check_period":
					$value = $fruity->return_period_name($value);
					break;
				case "notification_options_warning":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "w";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_unknown":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "u";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_critical":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "c";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_recovery":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "r";
					}
					unset($templateInfo[$key]);
					break;
				case "notification_options_flapping":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['notification_options']))
							$templateInfo['notification_options'] .= ",";
						$templateInfo['notification_options'] .= "f";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_ok":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "o";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_warning":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "w";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_unknown":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "u";
					}
					unset($templateInfo[$key]);
					break;
				case "stalking_options_critical":
					if($templateInfo[$key] == 1) {
						if(isset($templateInfo['stalking_options']))
							$templateInfo['stalking_options'] .= ",";
						$templateInfo['stalking_options'] .= "c";
					}
					unset($templateInfo[$key]);
					break;
			}
			if(isset($templateInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($templateInfo['notification_options']))
		fputs($tempHandler, "\tnotification_options " . $templateInfo['notification_options'] ."\n");
	if(isset($templateInfo['stalking_options']))
		fputs($tempHandler, "\tstalking_options " . $templateInfo['stalking_options'] ."\n");
	// Service groups
	unset($templateInfo['servicegroups']);
	if(count($inherited_servicegroups)) {
		foreach($inherited_servicegroups as $servicegroup) {
			if(isset($templateInfo['servicegroups'])) {
				$templateInfo['servicegroups'] .= ",";
			}
			$templateInfo['servicegroups'] .= $fruity->return_servicegroup_name($servicegroup);
		}
	}
	if(isset($templateInfo['servicegroups'])) {
		prepare_for_export( $templateInfo['servicegroups']);
		fputs($tempHandler, "\tservicegroups " . $templateInfo['servicegroups'] ."\n");
	}
	// END Service Groups
	// Contact groups
	unset($templateInfo['contact_groups']);
	if(count($inherited_contactgroups)) {
		foreach($inherited_contactgroups as $contactgroup) {
			if(isset($templateInfo['contact_groups'])) {
				$templateInfo['contact_groups'] .= ",";
			}
			$templateInfo['contact_groups'] .= $fruity->return_contactgroup_name($contactgroup);
		}
	}
	if(isset($templateInfo['contact_groups'])) {
		prepare_for_export( $templateInfo['contact_groups']);
		fputs($tempHandler, "\tcontact_groups " . $templateInfo['contact_groups'] ."\n");
	}
	// END Contact Groups			
	
	fputs($tempHandler, "}\n\n"); 
}

function write_servicegroup($groupInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $groupInfo);
	fputs($tempHandler, "define servicegroup {\n");
	fputs($tempHandler, "\tservicegroup_name	".$groupInfo['servicegroup_name']."\n");
	if($groupInfo['alias'] != '')
		fputs($tempHandler, "\talias	".$groupInfo['alias']."\n");
	fputs($tempHandler, "}\n\n");
}

function write_host_ext_info($extendedInfo) {
	global $tempHandler;
	global $fruity;
	$fruity->get_host_info($extendedInfo['host_id'], $tempHostInfo);
	if(isset($tempHostInfo['use_template_id'])) {	// Oh noes, we've gots the template!
		$tempInheritedValues = array();
		$tempInheritedValuesSources = array();
		$result = $fruity->get_inherited_host_template_extended_values($tempHostInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
		if(count($tempInheritedValues))
		foreach($tempInheritedValues as $key=>$value) {
			if(isset($tempInheritedValues[$key]) && !isset($extendedInfo[$key])) {
				$extendedInfo[$key] = $value;
			}
		}
	}
	
	prepare_for_export( $extendedInfo);
	fputs($tempHandler, "define hostextinfo{\n");
	$host_name = $fruity->return_host_name($extendedInfo['host_id']);
	fputs($tempHandler, "host_name	".prepare_for_export($host_name) . "\n");
	if($extendedInfo['notes'] != '')
		fputs($tempHandler, "notes	".$extendedInfo['notes'] . "\n");
	if($extendedInfo['action_url'] != '')
		fputs($tempHandler, "action_url	".$extendedInfo['action_url'] . "\n");
	if($extendedInfo['notes_url'] != '')
		fputs($tempHandler, "notes_url	".$extendedInfo['notes_url'] . "\n");
	if($extendedInfo['icon_image'] != '')			
		fputs($tempHandler, "icon_image	".$extendedInfo['icon_image'] . "\n");
	if($extendedInfo['icon_image_alt'] != '')
		fputs($tempHandler, "icon_image_alt	".$extendedInfo['icon_image_alt'] . "\n");
	if($extendedInfo['vrml_image'] != '')
		fputs($tempHandler, "vrml_image	".$extendedInfo['vrml_image'] . "\n");
	if($extendedInfo['statusmap_image'] != '')
		fputs($tempHandler, "statusmap_image	".$extendedInfo['statusmap_image'] . "\n");
	if($extendedInfo['two_d_coords'] != '')
		fputs($tempHandler, "2d_coords	".$extendedInfo['two_d_coords'] . "\n");
	if($extendedInfo['three_d_coords'] != '')
		fputs($tempHandler, "3d_coords	".$extendedInfo['three_d_coords'] . "\n");
	fputs($tempHandler, "}\n\n");
}
	
function write_service($serviceInfo) {
	global $tempHandler;
	global $fruity;

	// Add to Nagios Tables
	//add_service_to_opmon( $serviceInfo['service_id'], $serviceInfo['host_name'], $serviceInfo['service_description'] );

	fputs($tempHandler, "define service {\n");
	if(isset($serviceInfo['use_template_id'])) {

		fputs($tempHandler, "\tuse ".$fruity->return_service_template_name($serviceInfo['use_template_id']) . "\n");
		// Get template inherited values
		// SF BUG# 1445803
		// templating error with fruity 1.0rc
 		$inherited_servicegroups = array();
 		$inherited_contactgroups = array();
 		$inherited_parameters = array();
 		$parameters = array();
 		$tempServiceTemplateInfo = array();
 		$tempServiceTemplateInfoSources = array();
		
		$fruity->get_service_template_inherited_servicegroups_list($serviceInfo['use_template_id'], $inherited_servicegroups);
		$fruity->get_service_template_inherited_contactgroups_list($serviceInfo['use_template_id'], $inherited_contactgroups);
		$fruity->get_service_template_inherited_commandparameter_list($serviceInfo['use_template_id'], $inherited_parameters);
		
		$fruity->get_inherited_service_template_values( $serviceInfo['use_template_id'], $tempServiceTemplateInfo, $tempServiceTemplateInfoSources);
		
		if( isset( $tempServiceTemplateInfo['check_command'])
				&& !is_null( $tempServiceTemplateInfo['check_command'])
				&& is_null( $serviceInfo['check_command'])) {
			$serviceInfo['check_command'] = $tempServiceTemplateInfo['check_command'];
		} else {
			$inherited_parameters = array();
		}
		
		unset($serviceInfo['use_template_id']);
	}
	$fruity->return_service_servicegroups_list($serviceInfo['service_id'], $servicegroups);
	$fruity->return_service_contactgroups_list($serviceInfo['service_id'], $contactgroups);
	$fruity->get_service_check_command_parameters($serviceInfo['service_id'], $parameters);
	
	if( count( $parameters) == 0 && count( $inherited_parameters) > 0) {
		unset( $serviceInfo['check_command']);
	}

	unset($serviceInfo['service_id']);
	unset($serviceInfo['notification_options']);
	unset($serviceInfo['stalking_options']);
	
	// Parameters

	unset($serviceParameters);
	if(count($inherited_parameters)) {
		foreach($inherited_parameters as $parameter) {
				$serviceParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$serviceParameters .= $parameter['parameter'];
		}
	}
	if(count($parameters)) {
		foreach($parameters as $parameter) {
				$serviceParameters .= "!";
			$parameter['parameter'] = str_replace("\\", "\\\\", $parameter['parameter']);
			$serviceParameters .= $parameter['parameter'];
		}
	}
	if(count($serviceInfo)) {
		foreach($serviceInfo as $key => $value) {
			switch($key) {
				case "check_command":
					$value = $fruity->return_command_name($value) . $serviceParameters;
					break;
				case "notification_period":
					$value = $fruity->return_period_name($value);
					break;
				case "event_handler":
					$value = $fruity->return_command_name($value);
					break;
				case "check_period":
					$value = $fruity->return_period_name($value);
					break;
				case "notification_options_warning":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['notification_options']))
							$serviceInfo['notification_options'] .= ",";
						$serviceInfo['notification_options'] .= "w";
					}
					unset($serviceInfo[$key]);
					break;
				case "notification_options_unknown":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['notification_options']))
							$serviceInfo['notification_options'] .= ",";
						$serviceInfo['notification_options'] .= "u";
					}
					unset($serviceInfo[$key]);
					break;
				case "notification_options_critical":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['notification_options']))
							$serviceInfo['notification_options'] .= ",";
						$serviceInfo['notification_options'] .= "c";
					}
					unset($serviceInfo[$key]);
					break;
				case "notification_options_recovery":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['notification_options']))
							$serviceInfo['notification_options'] .= ",";
						$serviceInfo['notification_options'] .= "r";
					}
					unset($serviceInfo[$key]);
					break;
				case "notification_options_flapping":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['notification_options']))
							$serviceInfo['notification_options'] .= ",";
						$serviceInfo['notification_options'] .= "f";
					}
					unset($serviceInfo[$key]);
					break;
				case "stalking_options_ok":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['stalking_options']))
							$serviceInfo['stalking_options'] .= ",";
						$serviceInfo['stalking_options'] .= "o";
					}
					unset($serviceInfo[$key]);
					break;
				case "stalking_options_warning":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['stalking_options']))
							$serviceInfo['stalking_options'] .= ",";
						$serviceInfo['stalking_options'] .= "w";
					}
					unset($serviceInfo[$key]);
					break;
				case "stalking_options_unknown":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['stalking_options']))
							$serviceInfo['stalking_options'] .= ",";
						$serviceInfo['stalking_options'] .= "u";
					}
					unset($serviceInfo[$key]);
					break;
				case "stalking_options_critical":
					if($serviceInfo[$key] == 1) {
						if(isset($serviceInfo['stalking_options']))
							$serviceInfo['stalking_options'] .= ",";
						$serviceInfo['stalking_options'] .= "c";
					}
					unset($serviceInfo[$key]);
					break;
			}
			if(isset($serviceInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($serviceInfo['notification_options']))
		fputs($tempHandler, "\tnotification_options " . $serviceInfo['notification_options'] ."\n");
	if(isset($serviceInfo['stalking_options']))
		fputs($tempHandler, "\tstalking_options " . $serviceInfo['stalking_options'] ."\n");
	// Service groups
	unset($serviceInfo['servicegroups']);
	if(count($inherited_servicegroups)) {
		foreach($inherited_servicegroups as $servicegroup) {
			if(isset($serviceInfo['servicegroups'])) {
				$serviceInfo['servicegroups'] .= ",";
			}
				// SF BUG# 1449950
				// Inherited Service Groups not exporting correctly
				// Resolution: $serviceInfo['servicegroups'] .=$fruity->return_servicegroup_name($servicegroup);
				//
			$serviceInfo['servicegroups'] .=$fruity->return_servicegroup_name($servicegroup);
		}
	}
	if(count($servicegroups)) {
		foreach($servicegroups as $servicegroup) {
			if(isset($serviceInfo['servicegroups'])) {
				$serviceInfo['servicegroups'] .= ",";
			}
			$serviceInfo['servicegroups'] .= $fruity->return_servicegroup_name($servicegroup['servicegroup_id']);
		}
	}
	if(isset($serviceInfo['servicegroups'])) {
		prepare_for_export( $serviceInfo['servicegroups']);
		fputs($tempHandler, "\tservicegroups " . $serviceInfo['servicegroups'] ."\n");
	}
	// END Service Groups
	// Contact groups
	unset($serviceInfo['contact_groups']);
	if(count($inherited_contactgroups)) {
		foreach($inherited_contactgroups as $contactgroup) {
			if(isset($serviceInfo['contact_groups'])) {
				$serviceInfo['contact_groups'] .= ",";
			}
			$serviceInfo['contact_groups'] .= $fruity->return_contactgroup_name($contactgroup);
		}
	}
	if(count($contactgroups)) {
		foreach($contactgroups as $contactgroup) {
			if(isset($serviceInfo['contact_groups'])) {
				$serviceInfo['contact_groups'] .= ",";
			}
			$serviceInfo['contact_groups'] .= $fruity->return_contactgroup_name($contactgroup['contactgroup_id']);
		}
	}
	if(isset($serviceInfo['contact_groups'])) {
		prepare_for_export( $serviceInfo['contact_groups']);
		fputs($tempHandler, "\tcontact_groups " . $serviceInfo['contact_groups'] ."\n");
	}
	// END Contact Groups			
		
	
	
	fputs($tempHandler, "}\n\n"); 
}

function write_service_extinfo($tempExtendedInfo) {
	global $tempHandler;
	global $fruity;
	prepare_for_export( $tempExtendedInfo);
	fputs($tempHandler, "define serviceextinfo{\n");
	fputs($tempHandler, "host_name " . $tempExtendedInfo['host_name'] . "\n");
	fputs($tempHandler, "service_description	" . $tempExtendedInfo['service_description'] . "\n");
	if($tempExtendedInfo['notes'] != '')
		fputs($tempHandler, "notes	".$tempExtendedInfo['notes'] . "\n");
	if($tempExtendedInfo['action_url'] != '')
		fputs($tempHandler, "action_url	".$tempExtendedInfo['action_url'] . "\n");
	if($tempExtendedInfo['notes_url'] != '')
		fputs($tempHandler, "notes_url	".$tempExtendedInfo['notes_url'] . "\n");
	if($tempExtendedInfo['icon_image'] != '')			
		fputs($tempHandler, "icon_image	".$tempExtendedInfo['icon_image'] . "\n");
	if($tempExtendedInfo['icon_image_alt'] != '')
		fputs($tempHandler, "icon_image_alt	".$tempExtendedInfo['icon_image_alt'] . "\n");
	fputs($tempHandler, "}\n\n");
}

function write_dependency($dependencyInfo) {
	global $tempHandler;
	global $fruity;
	
	if(isset($dependencyInfo['target_service_id'])) {
		fputs($tempHandler, "define servicedependency {\n");
		$fruity->get_service_info($dependencyInfo['target_service_id'], $tempServiceInfo);
		$dependencyInfo['service_description'] = $tempServiceInfo['service_description'];
		$dependencyInfo['host_name'] = $fruity->return_host_name($tempServiceInfo['host_id']);
		unset($dependencyInfo['target_service_id']);
		// Additional Unsets
		unset($dependencyInfo['service_id']);
		unset($dependencyInfo['target_host_id']);
		unset($dependencyInfo['execution_failure_criteria_up']);
		unset($dependencyInfo['execution_failure_criteria_down']);
		unset($dependencyInfo['execution_failure_criteria_unreachable']);
		unset($dependencyInfo['notification_failure_criteria_up']);
		unset($dependencyInfo['notification_failure_criteria_down']);
		unset($dependencyInfo['notification_failure_criteria_unreachable']);
	}
	else if(isset($dependencyInfo['target_host_id'])) {
		fputs($tempHandler, "define hostdependency {\n");
		$dependencyInfo['host_name'] = $fruity->return_host_name($dependencyInfo['target_host_id']);
		unset($dependencyInfo['target_host_id']);
		
		// Additional Unsets
		unset($dependencyInfo['execution_failure_criteria_ok']);
		unset($dependencyInfo['execution_failure_criteria_warning']);
		unset($dependencyInfo['execution_failure_criteria_unknown']);
		unset($dependencyInfo['execution_failure_criteria_critical']);
		unset($dependencyInfo['notification_failure_criteria_ok']);
		unset($dependencyInfo['notification_failure_criteria_warning']);
		unset($dependencyInfo['notification_failure_criteria_unknown']);
		unset($dependencyInfo['notification_failure_criteria_critical']);
	}

	unset($dependencyInfo['dependency_id']);
	unset($dependencyInfo['execution_failure_criteria']);
	unset($dependencyInfo['notification_failure_criteria']);
	if(count($dependencyInfo)) {
		foreach($dependencyInfo as $key => $value) {
			switch($key) {
				
				case "execution_failure_criteria_up":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "o";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_down":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "d";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_unreachable":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "u";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_pending":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "p";
					}
					unset($dependencyInfo[$key]);
					break;;
				case "execution_failure_criteria_ok":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "o";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_warning":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "w";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_unknown":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "u";
					}
					unset($dependencyInfo[$key]);
					break;
				case "execution_failure_criteria_critical":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['execution_failure_criteria']))
							$dependencyInfo['execution_failure_criteria'] .= ",";
						$dependencyInfo['execution_failure_criteria'] .= "c";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_ok":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "o";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_warning":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "w";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_unknown":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "u";
					}
					unset($dependencyInfo[$key]);
					break;k;
				case "notification_failure_criteria_critical":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "c";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_pending":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "p";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_up":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "o";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_down":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "d";
					}
					unset($dependencyInfo[$key]);
					break;
				case "notification_failure_criteria_unreachable":
					if($dependencyInfo[$key] == 1) {
						if(isset($dependencyInfo['notification_failure_criteria']))
							$dependencyInfo['notification_failure_criteria'] .= ",";
						$dependencyInfo['notification_failure_criteria'] .= "u";
					}
					unset($dependencyInfo[$key]);
					break;
	
			}
			if(isset($dependencyInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($dependencyInfo['execution_failure_criteria'])) {
		fputs($tempHandler, "\texecution_failure_criteria " . $dependencyInfo['execution_failure_criteria'] ."\n");
	} else {
		if (isset($dependencyInfo['service_description'])) {
			fputs($tempHandler, "\texecution_failure_criteria " . "w,u,c" ."\n");
		} else {
			fputs($tempHandler, "\texecution_failure_criteria " . "d,u" ."\n");
		}
	}
		
	if(isset($dependencyInfo['notification_failure_criteria'])) {
		fputs($tempHandler, "\tnotification_failure_criteria " . $dependencyInfo['notification_failure_criteria'] ."\n");
	} else {
		if (isset($dependencyInfo['service_description'])) {
			fputs($tempHandler, "\tnotification_failure_criteria " . "w,u,c" ."\n");
		} else {
			fputs($tempHandler, "\tnotification_failure_criteria " . "d,u" ."\n");
		}
	}

	fputs($tempHandler, "}\n\n"); 
}

function write_escalation($escalationInfo) {
	global $tempHandler;
	global $fruity;

	if(isset($escalationInfo['service_description'])) {
		fputs($tempHandler, "define serviceescalation {\n");

		// Additional Unsets
		unset($escalationInfo['service_id']);
		unset($escalationInfo['target_host_id']);
		unset($escalationInfo['escalation_options_up']);
		unset($escalationInfo['escalation_options_down']);
		unset($escalationInfo['escalation_options_unreachable']);
	}
	else if(isset($escalationInfo['host_name'])) {
		fputs($tempHandler, "define hostescalation {\n");
		
		// Additional Unsets
		unset($escalationInfo['escalation_options_ok']);
		unset($escalationInfo['escalation_options_warning']);
		unset($escalationInfo['escalation_options_unknown']);
		unset($escalationInfo['escalation_options_critical']);
	}

	unset($escalationInfo['escalation_id']);
	unset($escalationInfo['escalation_options']);
	unset($escalationInfo['escalation_description']);
	if(count($escalationInfo)) {
		foreach($escalationInfo as $key => $value) {
			switch($key) {
				case "escalation_period":
					$value = $fruity->return_period_name($value);
					break;
				
				case "escalation_options_up":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "r";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_down":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "d";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_unreachable":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "u";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_ok":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "r";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_warning":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "w";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_unknown":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "u";
					}
					unset($escalationInfo[$key]);
					break;
				case "escalation_options_critical":
					if($escalationInfo[$key] == 1) {
						if(isset($escalationInfo['escalation_options']))
							$escalationInfo['escalation_options'] .= ",";
						$escalationInfo['escalation_options'] .= "c";
					}
					unset($escalationInfo[$key]);
					break;
			}
			if(isset($escalationInfo[$key])) {
				prepare_for_export( $value);
				fputs($tempHandler, "\t" . $key ." ".$value."\n");
			}
		}
	}
	if(isset($escalationInfo['escalation_options']))
		fputs($tempHandler, "\tescalation_options " . $escalationInfo['escalation_options'] ."\n");
	fputs($tempHandler, "}\n\n"); 
}

/**
 * Exports to a given path configuration files.
 *
 * @param unknown_type $path
 */
function export($path, $bkp_path = false) {
	global $fruity;
	global $tempHandler;
	global $sys_config;
	
	$fruity->get_main_conf($mainConfig);	// So we can get path data, for both steps.
	// We don't need this anymore
	unset($mainConfig['config_dir']);
	
	// First let's check for proper permissions.
	if(!@touch($path. "/fruitytmpfile") || !is_writeable( $path)) {
		?>
		There was a problem writing to <b><?=$path;?></b>.  Check your 
		permissions and try again.
		<?php
		die();
	}
	else {
		unlink($path. "/fruitytmpfile");
	}

	$configuration_path = $path;
	if( preg_match( "/\/$/", $configuration_path)) {
		// Strip the trailing /
		$configuration_path = substr( $configuration_path, 0, strlen($configuration_path)-1);
	}
	
	// Create a variable to test if the export was successful or not
	$noErrors = true;
	
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}

	// BACKUP ICINGA.CFG
	if(file_exists($configuration_path."/icinga.cfg")) {
		?>
		Backing Up icinga.cfg...
		<?php
		copy($configuration_path."/icinga.cfg", $configuration_path."/OpCfg_Backup/icinga.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	
	//EXPORT ICINGA.CFG
	?>
	Exporting icinga.cfg (Main Configuration File)...
	<?php
	flush();
	$tempHandler = @fopen($configuration_path."/icinga.cfg","w");
	if( !$tempHandler) {
		print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
		print "- Unable to write to " . $configuration_path . "/icinga.cfg -- verify your permissions.";
		$noErrors = false;
	} else {
		fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
		fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/host_templates.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/hosts.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/commands.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/timeperiods.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/contactgroups.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/contacts.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/hostgroups.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/service_templates.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/services.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/servicegroups.cfg\n");
		fputs($tempHandler, "resource_file=".$configuration_path."/resources.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/hostextinfo.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/serviceextinfo.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/dependencies.cfg\n");
		fputs($tempHandler, "cfg_file=".$configuration_path."/escalations.cfg\n");
		
		// No longer needed variables
		unset($path);
		if(count($mainConfig)) {
			foreach($mainConfig as $key => $value) {
				switch($key) {
					// Kludge Fix
					case "ochp_command":
						if($value == 0) {
							unset($mainConfig['ochp_command']);
						}
						else {
							$value = $fruity->return_command_name($value);
						}
						break;
					case "host_perfdata_command":
						if($value == 0) {
							unset($mainConfig['host_perfdata_command']);
						}
						else {
							$value = $fruity->return_command_name($value);
						}
						break;
					case "service_perfdata_command":
						if($value == 0) {
							unset($mainConfig['global_host_event_handler']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;
					case "host_perfdata_file_processing_command":
						if($value == 0) {
							unset($mainConfig['host_perfdata_file_processing_command']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;
					case "service_perfdata_file_processing_command":
						if($value == 0) {
							unset($mainConfig['service_perfdata_file_processing_command']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;						
					case "global_host_event_handler":
						if($value == 0) {
							unset($mainConfig['global_host_event_handler']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;
					case "global_service_event_handler":
						if($value == 0) {
							unset($mainConfig['global_service_event_handler']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;
					case "ocsp_command":
						if($value == 0) {
							unset($mainConfig['ocsp_command']);
						}
						else {				
							$value = $fruity->return_command_name($value);
						}
						break;
                                       case "stalking_notifications_for_hosts":
						// Check To See if the Stalking Notifications For Hosts Variable is Enabled or Not.
                                                if($value == 2) {
                                                        unset($mainConfig['stalking_notifications_for_hosts']);
                                                }
                                                else {
							$value = $mainConfig['stalking_notifications_for_hosts'];
                                                }
                                                break;
                                        case "stalking_notifications_for_services":
						// Check To See if the Stalking Notifications For Services Variable is Enabled or Not.
                                                if($value == 2) {
                                                        unset($mainConfig['stalking_notifications_for_services']);
                                                }
                                                else {
							$value = $mainConfig['stalking_notifications_for_services'];
                                                }
                                                break;
                                        case "keep_unknown_macros":
                                                // Check To See if the Keep Unknown Macros Option Variable is Enabled or Not.
                                                if($value == 2) {
                                                        unset($mainConfig['keep_unknown_macros']);
                                                }
                                                else {
                                                        $value = $mainConfig['keep_unknown_macros'];
                                                }
                                        case "max_check_result_list_items":
                                                // Check To See if the Limit Number Of Items In Check Result List Variable is Enabled or Not.
                                                if($value == -1) {
                                                        unset($mainConfig['max_check_result_list_items']);
                                                }
                                                else {
                                                        $value = $mainConfig['max_check_result_list_items'];
                                                }
                                                break;
					// End Kludge Fix
					default:
					// empty
				}
				if(isset($mainConfig[$key]) && $mainConfig[$key] != '') {
					prepare_for_export( $value);
					fputs($tempHandler, $key ."=".$value."\n");
				}
			}
		}
		// Event broker modules
		$fruity->return_broker_modules($module_list);
		$numOfModules = count($module_list);
		if($numOfModules) {
			foreach($module_list as $module) {
				$value = $module['module_line'];
				prepare_for_export($value);
				fputs($tempHandler, "broker_module=".$value."\n");
			}
		}
		
		fclose($tempHandler);
		print "<b>DONE</b>";
	}
	?><br />
	<?php
	flush();
	// BACKUP HOST_TEMPLATES.CFG
	if(file_exists($configuration_path."/host_templates.cfg")) {
		?>
		Backing Up host_templates.cfg...
		<?php
		copy($configuration_path."/host_templates.cfg", $configuration_path."/OpCfg_Backup/host_templates.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting host_templates.cfg (Host Templates Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/host_templates.cfg");
		copy( $bkp_path."/host_templates.cfg", $configuration_path."/host_templates.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/host_templates.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/host_templates.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_host_template_list($template_list);
			if(count($template_list)) {
				foreach($template_list as $template) {
					$fruity->get_host_template_info($template['host_template_id'], $templateInfo);
					write_host_template( $templateInfo);
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	flush();
	// BACKUP HOSTS.CFG
	if(file_exists($configuration_path."/hosts.cfg")) {
		?>
		Backing Up hosts.cfg...
		<?php
		copy($configuration_path."/hosts.cfg", $configuration_path."/OpCfg_Backup/hosts.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting hosts.cfg (Host Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/hosts.cfg");
		copy( $bkp_path."/hosts.cfg", $configuration_path."/hosts.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/hosts.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/hosts.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_host_list($host_list);
			$numOfHosts = count($host_list);
		
			for($counter = 0; $counter < $numOfHosts; $counter++) {
				$fruity->get_host_info($host_list[$counter]['host_id'], $tempHostInfo);
				write_host( $tempHostInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	flush();
	// BACKUP COMMANDS.CFG
	if(file_exists($configuration_path."/commands.cfg")) {
		?>
		Backing Up commands.cfg...
		<?php
		copy($configuration_path."/commands.cfg", $configuration_path."/OpCfg_Backup/commands.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting commands.cfg (Commands Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/commands.cfg");
		copy( $bkp_path."/commands.cfg", $configuration_path."/commands.cfg" );
		print "<b>DONE</b>";

	} else {
		$tempHandler = @fopen($configuration_path."/commands.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/commands.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->return_command_list($command_list);
			$numOfCommands = count($command_list);
			for($counter = 0; $counter < $numOfCommands; $counter++) {
				$fruity->get_command($command_list[$counter]['command_id'], $tempCommandInfo);
				write_command( $tempCommandInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	flush();
	?><br />
	<?php

	// BACKUP TIMEPERIODS.CFG
	if(file_exists($configuration_path."/timeperiods.cfg")) {
		?>
		Backing Up timeperiods.cfg...
		<?php
		copy($configuration_path."/timeperiods.cfg", $configuration_path."/OpCfg_Backup/timeperiods.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting timeperiods.cfg (Time Periods Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/timeperiods.cfg");
		copy( $bkp_path."/timeperiods.cfg", $configuration_path."/timeperiods.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/timeperiods.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/timeperiods.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->return_period_list($period_list);
			$numOfPeriods = count($period_list);
			for($counter = 0; $counter < $numOfPeriods; $counter++) {
				$fruity->get_period($period_list[$counter]['timeperiod_id'], $tempPeriodInfo);
				write_timeperiod( $tempPeriodInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	flush();
	// BACKUP CONTACTGROUPS.CFG
	if(file_exists($configuration_path."/contactgroups.cfg")) {
		?>
		Backing Up contactgroups.cfg...
		<?php
		copy($configuration_path."/contactgroups.cfg", $configuration_path."/OpCfg_Backup/contactgroups.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting contactgroups.cfg (Contact Groups Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/contactgroups.cfg");
		copy( $bkp_path."/contactgroups.cfg", $configuration_path."/contactgroups.cfg" );
		print "<b>DONE</b>";

	} else {
	
		$tempHandler = @fopen($configuration_path."/contactgroups.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/contactgroups.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_contactgroup_list($group_list);
			$numOfGroups = count($group_list);
			for($counter = 0; $counter < $numOfGroups; $counter++) {
				$fruity->get_contactgroup_info($group_list[$counter]['contactgroup_id'], $tempGroupInfo);
				write_contactgroup( $tempGroupInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	flush();
	// BACKUP CONTACTS.CFG
	if(file_exists($configuration_path."/contacts.cfg")) {
		?>
		Backing Up contacts.cfg...
		<?php
		copy($configuration_path."/contacts.cfg", $configuration_path."/OpCfg_Backup/contacts.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting contacts.cfg (Contacts Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/contacts.cfg");
		copy( $bkp_path."/contacts.cfg", $configuration_path."/contacts.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/contacts.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/contacts.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_contact_list($contact_list);
			$numOfContacts = count($contact_list);
			for($counter = 0; $counter < $numOfContacts; $counter++) {
				$fruity->get_contact_info($contact_list[$counter]['contact_id'], $tempContactInfo);
				write_contact( $tempContactInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php

	// BACKUP HOSTGROUPS.CFG
	if(file_exists($configuration_path."/hostgroups.cfg")) {
		?>
		Backing Up hostgroups.cfg...
		<?php
		copy($configuration_path."/hostgroups.cfg", $configuration_path."/OpCfg_Backup/hostgroups.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting hostgroups.cfg (Host Groups Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/hostgroups.cfg");
		copy( $bkp_path."/hostgroups.cfg", $configuration_path."/hostgroups.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/hostgroups.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/hostgroups.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_hostgroup_list($group_list);
			$numOfGroups = count($group_list);
			for($counter = 0; $counter < $numOfGroups; $counter++) {
				$fruity->get_hostgroup_info($group_list[$counter]['hostgroup_id'], $tempGroupInfo);
				write_hostgroup( $tempGroupInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	// BACKUP SERVICE_TEMPLATES.CFG
	if(file_exists($configuration_path."/service_templates.cfg")) {
		?>
		Backing Up service_templates.cfg...
		<?php
		copy($configuration_path."/service_templates.cfg", $configuration_path."/OpCfg_Backup/service_templates.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting service_templates.cfg (Service Templates Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/service_templates.cfg");
		copy( $bkp_path."/service_templates.cfg", $configuration_path."/service_templates.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/service_templates.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/service_templates.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_service_template_list($template_list);
			if(count($template_list)) {
				foreach($template_list as $template) {
					$fruity->get_service_template_info($template['service_template_id'], $templateInfo);
					write_service_template( $templateInfo);
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	
	// BACKUP SERVICES.CFG
	if(file_exists($configuration_path."/services.cfg")) {
		?>
		Backing Up services.cfg...
		<?php
		copy($configuration_path."/services.cfg", $configuration_path."/OpCfg_Backup/services.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting services.cfg (Services Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/services.cfg");
		copy( $bkp_path."/services.cfg", $configuration_path."/services.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/services.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/services.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_services_list($services_list);
			if( count( $services_list)) {
				foreach($services_list as $service) {
					unset($serviceInfo);
					$fruity->get_service_info($service['service_id'], $serviceInfo);
					if(isset($serviceInfo['host_id'])) {	// We're for a host
						$serviceInfo['host_name'] = $fruity->return_host_name($serviceInfo['host_id']);
						unset($serviceInfo['host_id']);
						write_service($serviceInfo);
					}
					else if(isset($serviceInfo['hostgroup_id'])) {	// We're for a hostgroup
						$serviceInfo['hostgroup_name'] = $fruity->return_hostgroup_name($serviceInfo['hostgroup_id']);
						unset($serviceInfo['hostgroup_id']);
						write_service($serviceInfo);
					}
					else if(isset($serviceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
						// We need to get a list of hosts which are affected by this template
						$fruity->get_hosts_affected_by_host_template($serviceInfo['host_template_id'], $affectedHosts);
						unset($serviceInfo['host_template_id']);
						if(count($affectedHosts)) {
							foreach($affectedHosts as $host_id) {
								$serviceInfo['host_name'] = $fruity->return_host_name($host_id);
								write_service($serviceInfo);
							}
						}
					}
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	
	// BACKUP SERVICEGROUPS.CFG
	if(file_exists($configuration_path."/servicegroups.cfg")) {
		?>
		Backing Up servicegroups.cfg...
		<?php
		copy($configuration_path."/servicegroups.cfg", $configuration_path."/OpCfg_Backup/servicegroups.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting servicegroups.cfg (Service Groups Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/servicegroups.cfg");
		copy( $bkp_path."/servicegroups.cfg", $configuration_path."/servicegroups.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/servicegroups.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/servicegroups.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_servicegroup_list($group_list);
			$numOfGroups = count($group_list);
			for($counter = 0; $counter < $numOfGroups; $counter++) {
				$fruity->get_servicegroup_info($group_list[$counter]['servicegroup_id'], $tempGroupInfo);
				write_servicegroup( $tempGroupInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	$fruity->get_cgi_conf($cgi_config);

	// BACKUP CGI.CFG
	if(file_exists($configuration_path."/cgi.cfg")) {
		?>
		Backing Up cgi.cfg...
		<?php
		copy($configuration_path."/cgi.cfg", $configuration_path."/OpCfg_Backup/cgi.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	
	//EXPORT ICINGA.CFG
	?>
	Exporting cgi.cfg (CGI Configuration File)...
	<?php
	flush();

	$tempHandler = @fopen($configuration_path."/cgi.cfg","w");
	if( !$tempHandler) {
		print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
		print "- Unable to write to " . $configuration_path . "/icinga.cfg -- verify your permissions.";
		$noErrors = false;
	} else {
		fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
		fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
		fputs($tempHandler, "main_config_file=".$configuration_path."/icinga.cfg\n");
		fputs($tempHandler, "resource_file=".$configuration_path."/resources.cfg\n");
		// No longer needed variables
	
		if(count($cgi_config)) {
			foreach($cgi_config as $key => $value) {
				switch($key) {
					default:
					// empty
				}
				if(isset($cgi_config[$key]) && $cgi_config[$key] != '')
					fputs($tempHandler, $key ."=".$value."\n");
			}
		}
		fclose($tempHandler);
		print "<b>DONE</b>";
	}
	?><br />
	<?php

	// BACKUP RESOURCES.CFG
	if(file_exists($configuration_path."/resources.cfg")) {
		?>
		Backing Up resources.cfg...
		<?php
		copy($configuration_path."/resources.cfg", $configuration_path."/OpCfg_Backup/resources.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting resources.cfg (Resources Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/resources.cfg");
		copy( $bkp_path."/resources.cfg", $configuration_path."/resources.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/resources.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/resources.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_resource_conf($resource_config);
			for($counter = 1; $counter <= 32; $counter++) {
				if($resource_config['user'.$counter] != '')
					fputs($tempHandler, "\$USER".$counter."\$=".$resource_config['user'.$counter]."\n");
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php

	// BACKUP HOSTEXTINFO.CFG
	if(file_exists($configuration_path."/hostextinfo.cfg")) {
		?>
		Backing Up hostextinfo.cfg...
		<?php
		copy($configuration_path."/hostextinfo.cfg", $configuration_path."/OpCfg_Backup/hostextinfo.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting hostextinfo.cfg (Host Extended Information File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/hostextinfo.cfg");
		copy( $bkp_path."/hostextinfo.cfg", $configuration_path."/hostextinfo.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/hostextinfo.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/hostextinfo.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_host_list($host_list);
			$numOfHosts = count($host_list);
			for($counter = 0; $counter < $numOfHosts; $counter++) {
				unset($tempHostInfo);
				$fruity->get_host_extended_info($host_list[$counter]['host_id'], $tempExtendedInfo);
				write_host_ext_info( $tempExtendedInfo);
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	
	// BACKUP SERVICEEXTINFO.CFG
	if(file_exists($configuration_path."/serviceextinfo.cfg")) {
		?>
		Backing Up serviceextinfo.cfg...
		<?php
		copy($configuration_path."/serviceextinfo.cfg", $configuration_path."/OpCfg_Backup/serviceextinfo.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting serviceextinfo.cfg (Service Extended Information File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/serviceextinfo.cfg");
		copy( $bkp_path."/serviceextinfo.cfg", $configuration_path."/serviceextinfo.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/serviceextinfo.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/serviceextinfo.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_services_list($service_list);
			$numOfServices = count($service_list);
			for($counter = 0; $counter < $numOfServices; $counter++) {
				$fruity->get_service_extended_info($service_list[$counter]['service_id'], $tempExtendedInfo);
				$fruity->get_service_info($service_list[$counter]['service_id'], $tempServiceInfo);
				if(isset($tempServiceInfo['use_template_id'])) {	// Oh noes, we've gots the template!
					$tempInheritedValues = array();
					$tempInheritedValuesSources = array();
					$result = $fruity->get_inherited_service_template_extended_values($tempServiceInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
					if(count($tempInheritedValues)) {
						foreach($tempInheritedValues as $key=>$value) {
							if(isset($tempInheritedValues[$key]) && !isset($tempExtendedInfo[$key])) {
								$tempExtendedInfo[$key] = $value;
							}
						}
					}
				}
				$tempExtendedInfo['service_description'] = $tempServiceInfo['service_description'];
				if(isset($tempServiceInfo['host_id'])) {	// We're for a host
					$tempExtendedInfo['host_name'] = $fruity->return_host_name($tempServiceInfo['host_id']);
					unset($serviceInfo['host_id']);
					write_service_extinfo($tempExtendedInfo);
				}
				else if(isset($tempServiceInfo['hostgroup_id'])) {	// We're for a hostgroup
					$fruity->return_hostgroup_member_list($tempServiceInfo['hostgroup_id'], $affectedHosts);
					if(count($affectedHosts)) {
						foreach($affectedHosts as $host) {
							unset($tempExtendedInfo['host_name']);
							$tempExtendedInfo['host_name'] = $fruity->return_host_name($host['host_id']);
							if(isset($tempExtendedInfo['host_name'])) {
								write_service_extinfo($tempExtendedInfo);
							}
						}
					}
				}
				else if(isset($tempServiceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
					// We need to get a list of hosts which are affected by this template
					$affectedHosts = NULL;
					$fruity->get_hosts_affected_by_host_template($tempServiceInfo['host_template_id'], $affectedHosts);
					unset($tempServiceInfo['host_template_id']);
					if(count($affectedHosts)) {
						foreach($affectedHosts as $host_id) {
							$tempExtendedInfo['host_name'] = $fruity->return_host_name($host_id);
							write_service_extinfo($tempExtendedInfo);
						}
					}
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	?><br />
	<?php
	
	// BACKUP DEPENDENCIES.CFG
	if(file_exists($configuration_path."/dependencies.cfg")) {
		?>
		Backing Up dependencies.cfg...
		<?php
		copy($configuration_path."/dependencies.cfg", $configuration_path."/OpCfg_Backup/dependencies.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting dependencies.cfg (Dependencies Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/dependencies.cfg");
		copy( $bkp_path."/dependencies.cfg", $configuration_path."/dependencies.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/dependencies.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/dependencies.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_dependencies_list($dependencies_list);
			if(count($dependencies_list)) {
				foreach($dependencies_list as $dependency) {
					unset($serviceInfo);
					unset($dependencyInfo['dependent_host_name']);
					unset($dependencyInfo['dependent_service_description']);
					$fruity->get_dependency($dependency['dependency_id'], $dependencyInfo);
					$dependencyInfo['dependent_service_description'] = $fruity->return_service_description($dependencyInfo['service_id']);
					if(isset($dependencyInfo['host_id'])) {	// We're for a host
						$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($dependencyInfo['host_id']);
						unset($dependencyInfo['host_id']);
						write_dependency($dependencyInfo);
					}
					else if(isset($dependencyInfo['host_template_id'])) {	// Uh oh, we've got a depoendency for a host template! AIYEE
						// We need to get a list of hosts which are affected by this template
						$fruity->get_hosts_affected_by_host_template($dependencyInfo['host_template_id'], $affectedHosts);
						unset($dependencyInfo['host_template_id']);
						if(count($affectedHosts)) {
							foreach($affectedHosts as $host_id) {
								$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($host_id);
								write_dependency($dependencyInfo);
							}
						}
					}
					else if(isset($dependencyInfo['service_template_id'])) {
						// We need to get a list of hosts which are affected by this template
						// First get a list of services which are effected
						$fruity->get_services_affected_by_service_template($dependencyInfo['service_template_id'], $affectedServices);
						unset($dependencyInfo['service_template_id']);
						if(count($affectedServices)) {
							foreach($affectedServices as $service_id) {
								unset($serviceInfo);
								unset($dependencyInfo['dependent_host_name']);
								unset($dependencyInfo['dependent_service_description']);
								$fruity->get_service_info($service_id, $serviceInfo);
								if(isset($serviceInfo['host_id'])) {	// We're for a host
									$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($serviceInfo['host_id']);
									$dependencyInfo['dependent_service_description'] = $serviceInfo['service_description'];
									write_dependency($dependencyInfo);
								}
								else if(isset($serviceInfo['hostgroup_id'])) {	// We're for a hostgroup
									$dependencyInfo['dependent_hostgroup_name'] = $fruity->return_hostgroup_name($serviceInfo['hostgroup_id']);
									$dependencyInfo['dependent_service_description'] = $serviceInfo['service_description'];
									unset($serviceInfo['hostgroup_id']);
									write_dependency($dependencyInfo);
								}
								else if(isset($serviceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
									// We need to get a list of hosts which are affected by this template
									$fruity->get_hosts_affected_by_host_template($serviceInfo['host_template_id'], $affectedHosts);
									$dependencyInfo['dependent_service_description'] = $serviceInfo['service_description'];
									unset($serviceInfo['host_template_id']);
									if(count($affectedHosts)) {
										foreach($affectedHosts as $host_id) {
											$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($host_id);
											write_dependency($dependencyInfo);
										}
									}
								}
							}
						}
					}
					else if(isset($dependencyInfo['service_id'])) {
						$fruity->get_service_info($dependencyInfo['service_id'], $serviceInfo);
						$dependencyInfo['dependent_service_description'] = $serviceInfo['service_description'];
						if(isset($serviceInfo['host_id'])) {	// We're for a host
							$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($serviceInfo['host_id']);
							unset($serviceInfo['host_id']);
							write_dependency($dependencyInfo);
						}
						else if(isset($serviceInfo['hostgroup_id'])) {	// We're for a hostgroup
							$dependencyInfo['dependent_hostgroup_name'] = $fruity->return_hostgroup_name($serviceInfo['hostgroup_id']);
							unset($serviceInfo['hostgroup_id']);
							write_dependency($dependencyInfo);
						}
						else if(isset($serviceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
							// We need to get a list of hosts which are affected by this template
							$fruity->get_hosts_affected_by_host_template($serviceInfo['host_template_id'], $affectedHosts);
							$dependencyInfo['dependent_service_description'] = $serviceInfo['service_description'];
							unset($serviceInfo['host_template_id']);
							if(count($affectedHosts)) {
								foreach($affectedHosts as $host_id) {
									$dependencyInfo['dependent_host_name'] = $fruity->return_host_name($host_id);
									write_dependency($dependencyInfo);
								}
							}
						}
					}
					
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	
	// BACKUP ESCALATIONS.CFG
	if(file_exists($configuration_path."/escalations.cfg")) {
		?>
		Backing Up escalations.cfg...
		<?php
		copy($configuration_path."/escalations.cfg", $configuration_path."/OpCfg_Backup/escalations.cfg.backup");
		?><b>DONE</b><br />
		<?php
	}
	?>
	Exporting escalations.cfg (Escalations Configuration File)...
	<?php
	flush();

	if ($bkp_path !== false) {

		unlink($configuration_path."/escalations.cfg");
		copy( $bkp_path."/escalations.cfg", $configuration_path."/escalations.cfg" );
		print "<b>DONE</b>";

	} else {

		$tempHandler = @fopen($configuration_path."/escalations.cfg","w");
		if( !$tempHandler) {
			print "<font style=\"color: red; font-weight: bold; font-style: italic\">ERROR</font><br />";
			print "- Unable to write to " . $configuration_path . "/escalations.cfg -- verify your permissions.";
			$noErrors = false;
		} else {
			fputs($tempHandler, "# Written by " . $sys_config['name'] . " (" . $sys_config['version'] . ") at: " . date("F j, Y, g:i a") . "\n");
			fputs($tempHandler, "# Generated for " . $sys_config['network_desc'] . "\n");
			$fruity->get_escalations_list($escalations_list);
			if(count($escalations_list)) {
				foreach($escalations_list as $escalation) {
					unset($escalationInfo);	// reset the escalation info
					$fruity->get_escalation($escalation['escalation_id'], $escalationInfo);
					
					
					// Now we need to get the contact groups for this escalation
					$fruity->return_escalation_contactgroups_list($escalation['escalation_id'], $contactgroups);
					if( count( $contactgroups) > 0) {
						foreach($contactgroups as $contactgroup) {
							if(isset($escalationInfo['contact_groups']))
								$escalationInfo['contact_groups'] .= ",";
							$escalationInfo['contact_groups'] .= $fruity->return_contactgroup_name($contactgroup['contactgroup_id']);
						}
					}
					if(isset($escalationInfo['host_id'])) {	// We're for a host
						$escalationInfo['host_name'] = $fruity->return_host_name($escalationInfo['host_id']);
						unset($escalationInfo['host_id']);
						write_escalation($escalationInfo);
					}
					else if(isset($escalationInfo['host_template_id'])) {	// Uh oh, we've got a depoendency for a host template! AIYEE
		
						// We need to get a list of hosts which are affected by this template
						$fruity->get_hosts_affected_by_host_template($escalationInfo['host_template_id'], $affectedHosts);
						unset($escalationInfo['host_template_id']);
						if(count($affectedHosts)) {
							foreach($affectedHosts as $host_id) {
								$escalationInfo['host_name'] = $fruity->return_host_name($host_id);
								prepare_for_export( $escalationInfo);
								write_escalation($escalationInfo);
							}
						}
					}
					else if(isset($escalationInfo['service_template_id'])) {
						// We need to get a list of hosts which are affected by this template
						// First get a list of services which are effected
						$fruity->get_services_affected_by_service_template($escalationInfo['service_template_id'], $affectedServices);
						unset($escalationInfo['service_template_id']);
						if(count($affectedServices)) {
							foreach($affectedServices as $service_id) {
								unset($serviceInfo);
								unset($escalationInfo['host_name']);
								unset($escalationInfo['service_description']);
								$fruity->get_service_info($service_id, $serviceInfo);
								if(isset($serviceInfo['host_id'])) {	// We're for a host
									$escalationInfo['host_name'] = $fruity->return_host_name($serviceInfo['host_id']);
									$escalationInfo['service_description'] = $serviceInfo['service_description'];
									write_escalation($escalationInfo);
								}
								else if(isset($serviceInfo['hostgroup_id'])) {	// We're for a hostgroup
									$escalationInfo['hostgroup_name'] = $fruity->return_hostgroup_name($serviceInfo['hostgroup_id']);
									$escalationInfo['service_description'] = $serviceInfo['service_description'];
									unset($serviceInfo['hostgroup_id']);
									write_escalation($escalationInfo);
								}
								else if(isset($serviceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
									// We need to get a list of hosts which are affected by this template
									$fruity->get_hosts_affected_by_host_template($serviceInfo['host_template_id'], $affectedHosts);
									$escalationInfo['service_description'] = $serviceInfo['service_description'];
									unset($serviceInfo['host_template_id']);
									if(count($affectedHosts)) {
										foreach($affectedHosts as $host_id) {
											$escalationInfo['host_name'] = $fruity->return_host_name($host_id);
											write_escalation($escalationInfo);
										}
									}
								}
							}
						}
					}
					else if(isset($escalationInfo['service_id'])) {
						$fruity->get_service_info($escalationInfo['service_id'], $serviceInfo);
						$escalationInfo['service_description'] = $serviceInfo['service_description'];
						if(isset($serviceInfo['host_id'])) {	// We're for a host
							$escalationInfo['host_name'] = $fruity->return_host_name($serviceInfo['host_id']);
							unset($serviceInfo['host_id']);
							write_escalation($escalationInfo);
						}
						else if(isset($serviceInfo['hostgroup_id'])) {	// We're for a hostgroup
							$escalationInfo['hostgroup_name'] = $fruity->return_hostgroup_name($serviceInfo['hostgroup_id']);
							unset($serviceInfo['hostgroup_id']);
							write_escalation($escalationInfo);
						}
						else if(isset($serviceInfo['host_template_id'])) {	// Uh oh, we've got a service for a host template! AIYEE
							// We need to get a list of hosts which are affected by this template
							$fruity->get_hosts_affected_by_host_template($serviceInfo['host_template_id'], $affectedHosts);
							$escalationInfo['service_description'] = $serviceInfo['service_description'];
							unset($serviceInfo['host_template_id']);
							if(count($affectedHosts)) {
								foreach($affectedHosts as $host_id) {
									$escalationInfo['host_name'] = $fruity->return_host_name($host_id);
									write_escalation($escalationInfo);
								}
							}
						}
					}
					
				}
			}
			fclose($tempHandler);
			print "<b>DONE</b>";
		}
	}
	print("<br />");
	
	if( !$noErrors) {
		print "<br /><b>There were errors while exporting.</b><br /><br />\n";
	}
	?>
	<br />
	<?php

	if ($bkp_path !== false)
		recursiveRemoveDirectory($bkp_path);

	return true;
}


$fruity->get_main_conf($mainConfig);	// So we can get path data, for both steps.


?>
<?php
if(!isset($_GET['confirmed'])) {
	print_window_header("Warning", "100%", "center");
	?>
	OpCfg is currently set to write it's configuration files to "<b><?=$mainConfig['config_dir'];?></b>", as defined 
	in Main Configuration, under Paths.OpCfg must have write access to this directory.OpCfg will also 
	attempt to create backups of existing files (if they exist) with a extension of .fruity.backup.<br />
	<br />
	<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
	<?php
	if($sys_config['nagios_preflight']) {
		?><b>Icinga Pre-Flight Check Is Enabled</b><br />
		OpCfg will attempt to run your config files against Icinga in a temporary directory away from your 
		real configuration.  If Icinga reports that your configuration is valid, OpCfg will then export your 
		configuration to <?=$mainConfig['config_dir'];?> and attempt to restart Icinga.
		<?php
	}
	else {
		?><b>Icinga Pre-Flight Check Is Disabled</b><br />
		If you continue, OpCfg will overwrite your Icinga configuration files located in <?=$mainConfig['config_dir'];?> but 
		will create backup copies.  However, OpCfg will be unable to validate your configuration files with Icinga.  If you 
		wish to enable the Icinga Pre-Flight, modify the config.inc file in the includes/ directory of your OpCfg 
		installation.
		<?php		
	}
	?>
	</div>
	<br />
	<div align="center">Are you *sure* you wish to export?  This will overwrite your current Icinga configuration files!<br 
/>
	<br />
	<a href="<?=$path_config['doc_root'];?>export.php?confirmed=1">YES</a>
	<?php
	print_window_footer();
}
else if($_GET['confirmed']) {

	// Check lock file
	$export_lock_file = "{$sys_config['web_dir']}/opcfg/export.lock";
	if (file_exists($export_lock_file)) {
		print("Another export process is running. Can't continue.");
		exit(0);
	}
	
	$fh = fopen( $export_lock_file, "w");
	fclose($fh);
	
	$start_time = getmicrotime();

	?>
	<center>User <b><?=$_SERVER["PHP_AUTH_USER"];?></b> on <b><?=$_SERVER["REMOTE_ADDR"];?></b> started an export process at <?=date('D M d H:i:s');?></center> <br><br>
	<?php
	if($sys_config['nagios_preflight']) {
		if(file_exists($sys_config['nagios_path'])) {
			// We want to do a preflight check first on our configs
			if (isset( $_SERVER['argc'])) {
				$path = $sys_config['temp_dir'] . DIRECTORY_SEPARATOR . "export";
			} else {
				$path = $sys_config['temp_dir'] . DIRECTORY_SEPARATOR . session_id();
			}
			?>
			Creating temporary directory <?=$path;?>...<br />
			<?php
			// Clean old directorys
			recursiveRemoveDirectory($path);	
			if(@mkdir($path)) {
				?>
				Attempting to export to temporary directory <?=$path;?>...<br />
				<?php flush(); ?>
				<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
				<?php
				flush();
				$rv = export($path);	// First write our configs to the tmp directory
				flush();
				?>
				</div>
				<?php
				if($rv === true) {
					?>
	
					<br />
					Performing Icinga Pre-Flight Check...<br />
					<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
					<?php
					$output = array();
					exec($sys_config['nagios_path'] . " -v " . $path . "/icinga.cfg" , $output, $rv);
					foreach($output as $line) {
						/*$line = str_replace( 'Icinga', 'Icinga', $line);
						$line = str_replace( 'icinga', 'opmon', $line);
						if (preg_match('/^Copyright/', $line))
							continue;
						if (preg_match('/^Last Modified/', $line))
							continue;
						if (preg_match('/^License/', $line))
							continue;*/
						print($line . "<br />");
					}
					?></div>
					<?php
					if($rv) {
						if($rv == 126) {
							?>
							Unable to execute the Icinga Binary.  Check permissions on the binary and try again...<br />
							<?php
							// Clean lock file
							if (file_exists($export_lock_file)) {
								unlink($export_lock_file);
							}
						}
						else {
							?>
							Icinga Pre-Flight Failed.  Review the Pre-Flight Results, modify your configuration and try 
							again.<br />
							<?php
							// Clean lock file
							if (file_exists($export_lock_file)) {
								unlink($export_lock_file);
							}
							exit(0);
						}
						?>
						Removing temporary directory <?=$path;?>...<br />
						<?php
						recursiveRemoveDirectory($path);	
					}
					else {
						?>
						Icinga Pre-Flight Succeeded!<br />
						<!--Removing temporary directory <?=$path;?>...<br /> -->
						<?php
						//recursiveRemoveDirectory($path);	
						?>
						<br />
						Exporting Final Configuration...<br />
						<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
						<?php
						$rv = export($mainConfig['config_dir'], $path);
						?>
						</div>
						<?php
						if($rv === true) {
							?>
							Attempting To Stop Icinga...<br />
							<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
							<?php
							$output = array();
							exec($sys_config['nagios_stop'], $output, $rv);
							foreach($output as $line) {
								print($line . "<br />");
							}
							if($rv) {
								if($rv == 126) {
									?>
									Unable to execute the Icinga Stop Command. Check permissions to run '<?=$sys_config['nagios_start'];?>' and try again...<br />
									<?php
									// Clean lock file
									if (file_exists($export_lock_file)) {
										unlink($export_lock_file);
									}
									recursiveRemoveDirectory($path);	
								}
								else {
									?>
									Stopping Icinga Failed via '<?=$sys_config['nagios_stop'];?>'.  Check the command and try again...<br />
									<?php
									// Clean lock file
									if (file_exists($export_lock_file)) {
										unlink($export_lock_file);
									}
									recursiveRemoveDirectory($path);	
								}
							}
							else {
								$output = array();
								exec($sys_config['nagios_start'], $output,  $rv);	
								foreach($output as $line) {
									print($line . "<br />");
								}
								?>
								</div>
								<?php
								if($rv) {
									if($rv == 126) {
										?>
										Unable to execute the Icinga Stop Command. Check permissions to run '<?=$sys_config['nagios_start'];?>' and try again...<br />
										<?php
										// Clean lock file
										if (file_exists($export_lock_file)) {
											unlink($export_lock_file);
										}
										recursiveRemoveDirectory($path);	
									}
									else {
										?>
										Stopping Icinga Failed via '<?=$sys_config['nagios_stop'];?>'.  Check the command and try again...<br />
										<?php
										// Clean lock file
										if (file_exists($export_lock_file)) {
											unlink($export_lock_file);
										}
										recursiveRemoveDirectory($path);	
									}
								}
								print("Icinga restarted with exported configuration.<br />");

							}
							?>
							</div>
							<?php
						}
						else {
							?>
							Export failed<br />
							<br />
							<?php
							// Clean lock file
							if (file_exists($export_lock_file)) {
								unlink($export_lock_file);
							}
							recursiveRemoveDirectory($path);	
						}
					}
				}
				else {
					?>
					Export failed<br />
					<br />
					<?php
					// Clean lock file
					if (file_exists($export_lock_file)) {
						unlink($export_lock_file);
					}
					recursiveRemoveDirectory($path);	
					
				}
			}
			else {
				?>
				Unable to create temporary directory.  Check your permissions and try again...<br />
				<?php
				// Clean lock file
				if (file_exists($export_lock_file)) {
					unlink($export_lock_file);
				}
				recursiveRemoveDirectory($path);	
			}
		}
		else {
			// Icinga binary does not exist!
			?>
			The path specified to the Icinga binary (<?=$sys_config['nagios_path'];?>) does not exist.  Check 
			your configuration in the config.inc file and try again...<br />
			<?php
			// Clean lock file
			if (file_exists($export_lock_file)) {
				unlink($export_lock_file);
			}
			recursiveRemoveDirectory($path);	
		}
			

	}
	else {	
		print("Exporting configuration files...<br />");
		?>
		<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">
		<?php
		export($mainConfig['config_dir']);
		?>
		</div>
		<br />
		<?php
		if($rv === false) {
			?>
			Configuration export failed.<br /><br />
			<?php
		}
		else {
			?>
			Configuration export completed successfully.<br /><br />
			<?php
		}
	}
	// Clean lock file
	if (file_exists($export_lock_file)) {
		unlink($export_lock_file);
	}
	recursiveRemoveDirectory($path);	

	$end_time = getmicrotime();
	$time = formattime( $end_time - $start_time );
	?>
	<center>Export process ended at <?=date('D M d H:i:s');?> - Total export time: <?=$time;?></center><br><br>
	<?php
	
}
?>
