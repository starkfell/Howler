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
Fruity Contacts Management Page
*/
include_once('includes/config.inc');

if(!isset($_GET['section']) && isset($_GET['contact_id']))
	$_GET['section'] = 'general';
	
if(!isset($_GET['edit'])) {
	unset($_SESSION['tempData']);
}

// If we're going to modify host data
if(isset($_GET['contact_id']) && 
		$_GET['section'] == "general" &&
		$_GET['edit']) {
	$fruity->get_contact_info($_GET['contact_id'], $_SESSION['tempData']['contact_manage']);
	$_SESSION['tempData']['contact_manage']['old_name'] = $_SESSION['tempData']['contact_manage']['contact_name'];
}


// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'notification') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_contact_notification_command($_GET['contact_notification_command_id']);
			$status_msg = "Command Deleted";
			unset($_GET['command_id']);
			unset($_SESSION['tempData']['contact_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'groups') {
			$fruity->get_contact_membership_list($_GET['contact_id'], $tempGroupList);
			$numOfGroups = count($tempGroupList);
			if($numOfGroups > 1) {
				$fruity->delete_contactgroup_member($_GET['contactgroup_id'], $_GET['contact_id']);
				$status_msg = "Membership Deleted";
				unset($_SESSION['tempData']['contact_manage']);
			}
			else {
				$status_msg = "There must be at least one contact group!";
			}
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'general') {
			$fruity->get_contact_list($tempList);
			$numOfContacts = count($tempList);
			if($numOfContacts > 1) {
				$fruity->delete_contact($_GET['contact_id']);
				$status_msg = "Contact deleted.";
				unset($_SESSION['tempData']['contact_manage']);
				unset($_GET['contact_id']);
			}
			else {
				$status_msg = "There must be at least one contact in the system.";
			}
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'addresses') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_contact_address($_GET['contactaddress_id']);

			$status_msg = "Contact address deleted.";
			unset($_SESSION['tempData']['contact_manage']);
		}
		
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['contact_manage'])) {
		foreach($_POST['contact_manage'] as $key=>$value) {
			$_SESSION['tempData']['contact_manage'][$key] = $value;
		}
	}
	if($_POST['request'] == 'add_contact') {
		// Error check!
		if(count($_SESSION['tempData']['contact_manage'])) {
			foreach($_SESSION['tempData']['contact_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		// We have checkboxes, let's verify the data against POST
		if(!isset($_POST['contact_manage']['host_notification_options_down']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_down'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_unreachable']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_unreachable'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_recovery']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_recovery'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_warning']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_warning'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_unknown']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_unknown'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_critical']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_critical'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_recovery']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_recovery'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_flapping']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_flapping'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_flapping']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_flapping'] = 0;
			
		// Field Error Checking
		if($_SESSION['tempData']['contact_manage']['contact_name'] == '' || $_SESSION['tempData']['contact_manage']['alias'] == '') {
			$addError = 1;
			$status_msg = "Fields shown are required and cannot be left blank.";
		}
		else {
			// Check for pre-existing contact with same name
			if($fruity->contact_exists($_SESSION['tempData']['contact_manage']['contact_name'])) {
				$status_msg = "A contact with that name already exists!";
			}
			else {
				
				$tempContactGroup = $_SESSION['tempData']['contact_manage']['contact_group'];
				unset($_SESSION['tempData']['contact_manage']['contact_group']);
				
				// Set Contact Password
				if($_SESSION['tempData']['contact_manage']['external_auth'] == 0) {
					if (strlen($_POST['contact_password']))
						$_SESSION['tempData']['contact_manage']['password'] = md5($_POST['contact_password']);
					else
						unset($_SESSION['tempData']['contact_manage']['password']);
				}
				
				// All is well for error checking, add the contact into the db.
				if($fruity->add_contact($_SESSION['tempData']['contact_manage'])) {
					$tempContactID = $fruity->return_contact_id_by_name($_SESSION['tempData']['contact_manage']['contact_name']);
					
					// Add default notification commands
					$host_command = $fruity->return_command_id_by_name("host-notify-by-email");
					$option = Array ( "contact_id" => $tempContactID,  "notification_type" => "host", "command_id" => $host_command );
					$fruity->add_contacts_notification_command( $tempContactID, $option );
					
					$service_command = $fruity->return_command_id_by_name("notify-by-email");
					$option = Array ( "contact_id" => $tempContactID,  "notification_type" => "service", "command_id" => $service_command );
					$fruity->add_contacts_notification_command( $tempContactID, $option );
					
					// Remove session data
					unset($_SESSION['tempData']['contact_manage']);
					$status_msg = "Contact added.";
					if( $tempContactGroup && $tempContactGroup != 0) {
						$fruity->add_contactgroup_member($tempContactGroup, $tempContactID);
					}
					unset($_GET['contact_add']);
				}
				else {
					$status_msg = "Error: add_contact failed.";
				}
			}
		}
	}
	else if($_POST['request'] == 'modify_contact') {
		// Error check!
		if(count($_SESSION['tempData']['contact_manage'])) {
			foreach($_SESSION['tempData']['contact_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		// We have checkboxes, let's verify the data against POST
		if(!isset($_POST['contact_manage']['host_notification_options_down']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_down'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_unreachable']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_unreachable'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_recovery']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_recovery'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_warning']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_warning'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_unknown']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_unknown'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_critical']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_critical'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_recovery']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_recovery'] = 0;
		if(!isset($_POST['contact_manage']['host_notification_options_flapping']))
			$_SESSION['tempData']['contact_manage']['host_notification_options_flapping'] = 0;
		if(!isset($_POST['contact_manage']['service_notification_options_flapping']))
			$_SESSION['tempData']['contact_manage']['service_notification_options_flapping'] = 0;
		if(!isset($_POST['contact_manage']['command_execution']))
			$_SESSION['tempData']['contact_manage']['command_execution'] = 0;
		if(!isset($_POST['contact_manage']['external_auth']))
			$_SESSION['tempData']['contact_manage']['external_auth'] = 0;
		if(!isset($_POST['contact_manage']['tab_dashboard']))
			$_SESSION['tempData']['contact_manage']['tab_dashboard'] = 0;									
		if(!isset($_POST['contact_manage']['tab_scats']))
			$_SESSION['tempData']['contact_manage']['tab_scats'] = 0;						
		if(!isset($_POST['contact_manage']['tab_maps']))
			$_SESSION['tempData']['contact_manage']['tab_maps'] = 0;						
		if(!isset($_POST['contact_manage']['tab_reports']))
			$_SESSION['tempData']['contact_manage']['tab_reports'] = 0;						
		if(!isset($_POST['contact_manage']['tab_monitoring']))
			$_SESSION['tempData']['contact_manage']['tab_monitoring'] = 0;						
		if(!isset($_POST['contact_manage']['tab_documentations']))
			$_SESSION['tempData']['contact_manage']['tab_documentations'] = 0;						
		if(!isset($_POST['contact_manage']['tab_additionalmodules']))
			$_SESSION['tempData']['contact_manage']['tab_additionalmodules'] = 0;						
		if(!isset($_POST['contact_manage']['tab_tools']))
			$_SESSION['tempData']['contact_manage']['tab_tools'] = 0;
						
		// Field Error Checking
		if($_SESSION['tempData']['contact_manage']['contact_name'] == '' || $_SESSION['tempData']['contact_manage']['alias'] == '') {
			$addError = 1;
			$status_msg = "Fields shown are required and cannot be left blank.";
		}
		else {
			if($_SESSION['tempData']['contact_manage']['contact_name'] != $_SESSION['tempData']['contact_manage']['old_name'] && $fruity->contact_exists($_SESSION['tempData']['contact_manage']['contact_name'])) {
				$status_msg = "A contact with that name already exists!";
			}
			else {
				
				// Set password
				if ($_POST['password_check'])
					$_SESSION['tempData']['contact_manage']['password'] = (strlen($_POST['contact_password'])) ? md5($_POST['contact_password']) : "";
				
				// Unset password if external_auth is set
				if ($_POST['contact_manage']['external_auth'])
					$_SESSION['tempData']['contact_manage']['password'] = "";

				// All is well for error checking, modify the contact.
				$fruity->modify_contact($_POST['contact_id'], $_SESSION['tempData']['contact_manage']);
				// Update contact type
				$status_msg = "Contact modified.";

				// Remove session data
				unset($_SESSION['tempData']['contact_manage']);
				unset($_GET['edit']);
			}
			$_GET['section'] = "general";
		}
	}
	else if($_POST['request'] == 'add_notification_command') {
		if($fruity->contact_has_notification_command($_GET['contact_id'], $_SESSION['tempData']['contact_manage']['notification_add'])) {
			$status_msg = "That notification command already exists in that list!";
			unset($_SESSION['tempData']['contact_manage']);
		}
		else {
			$fruity->add_contacts_notification_command($_GET['contact_id'], $_SESSION['tempData']['contact_manage']['notification_add']);
			$status_msg = "Notification Command added.";
			unset($_SESSION['tempData']['contact_manage']);
		}
	}
	else if($_POST['request'] == 'add_member_command') {
		if($fruity->contactgroup_has_member($_SESSION['tempData']['contact_manage']['group_add']['contactgroup_id'], $_GET['contact_id'])) {
			$status_msg = "That member already exists in that list!";
			unset($_SESSION['tempData']['contact_manage']);
		}
		else {
			$fruity->add_contactgroup_member($_SESSION['tempData']['contact_manage']['group_add']['contactgroup_id'], $_GET['contact_id']);
			$status_msg = "New Group membership added.";
			unset($_SESSION['tempData']['contact_manage']);
		}
	}
	else if($_POST['request'] == 'contact_address_add') {
		// All is well for error checking, modify the contact.
		$fruity->add_contact_address($_GET['contact_id'], $_SESSION['tempData']['contact_manage']);
		// Remove session data
		unset($_SESSION['tempData']['contact_manage']);
		$status_msg = "Contact Address added.";
	}
}

if(isset($_GET['contact_id']) && !isset($_GET['edit'])) {
	$fruity->get_contact_info($_GET['contact_id'], $tempContactInfo);
}

$fruity->get_contact_list($contact_list);
$numOfContacts = count($contact_list);

$fruity->return_period_list($period_list);

print_header("Contact Editor");
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($_GET['contact_id'])) {
		// PLACEHOLDER TO PUT CONTACT INFO
	print_window_header("Contact Info for " . $tempContactInfo['contact_name'], "100%");	
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=general">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=notification">Notification Commands</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=groups">Group Membership</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=addresses">Additional Addresses</a><br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing checks information
					?>
					<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=general&edit=1">
						<?php 
							if(isset($_GET['edit']))	{
								?>
								<input type="hidden" name="request" value="modify_contact" />
								<input type="hidden" name="contact_id" value="<?=$_GET['contact_id'];?>">
								<?php
							}
							else {
								?>
								<input type="hidden" name="request" value="add_contact" />
								<?php
							}
						?>
						<b>Contact Name:</b> <input type="text" name="contact_manage[contact_name]" value="<?=$_SESSION['tempData']['contact_manage']['contact_name'];?>"><br />
						<?=$fruity->element_desc("contact_name", "nagios_contacts_desc"); ?><br />
						<br />
						<b>Description:</b><br />
						<input type="text" size="80" name="contact_manage[alias]" value="<?=$_SESSION['tempData']['contact_manage']['alias'];?>"><br />
						<?=$fruity->element_desc("alias", "nagios_contacts_desc"); ?><br />
						<br />


						<b>Host Notification Period:</b> <?php print_select("contact_manage[host_notification_period]", $period_list, "timeperiod_id", "timeperiod_name",$_SESSION['tempData']['contact_manage']['host_notification_period']);?><br />
						<?=$fruity->element_desc("host_notification_period", "nagios_contacts_desc"); ?><br />
						<br />
						<b>Service Notification Period:</b> <?php print_select("contact_manage[service_notification_period]", $period_list, "timeperiod_id", "timeperiod_name",$_SESSION['tempData']['contact_manage']['service_notification_period']);?><br />
						<?=$fruity->element_desc("service_notification_period", "nagios_contacts_desc"); ?><br />
						<br />
						<b>Host Notification Options:</b>
						<table width="100%" border="0">
						<tr>
							<td width="100" valign="top">
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_down']) print("CHECKED");?> name="contact_manage[host_notification_options_down]" value="1">Down<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_unreachable']) print("CHECKED");?> name="contact_manage[host_notification_options_unreachable]" value="1">Unreachable<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_recovery']) print("CHECKED");?> name="contact_manage[host_notification_options_recovery]" value="1">Recovery<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_flapping']) print("CHECKED");?> name="contact_manage[host_notification_options_flapping]" value="1">Flapping<br />
							</td>
							<td valign="middle"><?=$fruity->element_desc("host_notification_options", "nagios_contacts_desc"); ?></td>
						</tr>
						</table>
						<br />
						<b>Service Notification Options:</b>
						<table width="100%" border="0">
						<tr>
							<td width="100" valign="top">
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_warning']) print("CHECKED");?> name="contact_manage[service_notification_options_warning]" value="1">Warning<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_unknown']) print("CHECKED");?> name="contact_manage[service_notification_options_unknown]" value="1">Unknown<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_critical']) print("CHECKED");?> name="contact_manage[service_notification_options_critical]" value="1">Critical<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_recovery']) print("CHECKED");?> name="contact_manage[service_notification_options_recovery]" value="1">Recovery<br />
							<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_flapping']) print("CHECKED");?> name="contact_manage[service_notification_options_flapping]" value="1">Flapping<br />
							</td>
							<td valign="middle"><?=$fruity->element_desc("service_notification_options", "nagios_contacts_desc"); ?></td>
						</tr>
						</table>
						<br />
			
						<b>Email:</b><br />
						<input type="text" size="80" name="contact_manage[email]" value="<?=$_SESSION['tempData']['contact_manage']['email'];?>"><br />
						<?=$fruity->element_desc("email", "nagios_contacts_desc"); ?><br />
						<br />
						<b>Pager:</b><br />
						<input type="text" size="80" name="contact_manage[pager]" value="<?=$_SESSION['tempData']['contact_manage']['pager'];?>"><br />
						<?=$fruity->element_desc("pager", "nagios_contacts_desc"); ?><br />
						<br />

						</div>

						<input type="submit" value="Modify Contact" />&nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>">Cancel</a> ]
						</form>
					<?php
				}
				else {
					?>				
					<b>Contact Name:</b> <?=$tempContactInfo['contact_name'];?><br />
					<b>Description:</b> <?=$tempContactInfo['alias'];?><br />
					<b>Email:</b> <?=$tempContactInfo['email'];?><br />
					<b>Pager:</b> <?=$tempContactInfo['pager'];?><br />
					<br />
					<b>Host Notification Period:</b> <?=$fruity->return_period_name($tempContactInfo['host_notification_period']);?><br />
					<b>Service Notification Period:</b> <?=$fruity->return_period_name($tempContactInfo['service_notification_period']);?><br />
					<b>Host Notification On:</b>
						<?php
						if(!$tempContactInfo['host_notification_options_down'] && !$tempContactInfo['host_notification_options_unreachable'] && !$tempContactInfo['host_notification_options_recovery'] && !$tempContactInfo['host_notification_options_flapping']) {
							print("None");
						}
						else {
							if($tempContactInfo['host_notification_options_down']) {
								print("Down");
								if($tempContactInfo['host_notification_options_unreachable'] || $tempContactInfo['host_notification_options_recovery'] || $tempContactInfo['host_notification_options_flapping'])
									print(",");
							}
							if($tempContactInfo['host_notification_options_unreachable']) {
								print("Unreachable");
								if($tempContactInfo['host_notification_options_recovery'] || $tempContactInfo['host_notification_options_flapping'])
									print(",");
							}
							if($tempContactInfo['host_notification_options_recovery']) {
								print("Recovery");
									if($tempContactInfo['host_notification_options_flapping'])
										print(",");
							}
							if($tempContactInfo['host_notification_options_flapping']) {
								print("Flapping");
							}
						}
						?>
						<br />
						<b>Service Notification On:</b>
						<?php
						if(!$tempContactInfo['service_notification_options_warning'] && !$tempContactInfo['service_notification_options_unknown'] && !$tempContactInfo['service_notification_options_critical'] && !$tempContactInfo['service_notification_options_recovery'])
							print("None");
						else {
							if($tempContactInfo['service_notification_options_warning']) {
								print("Warning");
								if($tempContactInfo['service_notification_options_unknown'] || $tempContactInfo['service_notification_options_critical'] || $tempContactInfo['service_notification_options_recovery'])
									print(",");
							}
							if($tempContactInfo['service_notification_options_unknown']) {
								print("Unknown");
								if($tempContactInfo['service_notification_options_critical'] || $tempContactInfo['service_notification_options_recovery'])
									print(",");
							}
							if($tempContactInfo['service_notification_options_critical']) {
								print("Critical");
								if($tempContactInfo['service_notification_options_recovery'] || $tempContactInfo['service_notification_options_flapping'])
									print(",");
							}
							if($tempContactInfo['service_notification_options_recovery']) {
								print("Recovery");
								if($tempContactInfo['service_notification_options_flapping'])
									print(",");
							}
							if($tempContactInfo['service_notification_options_flapping']) {
								print("Flapping");
							}
						}
						?>
					<br />
						<br />
					<br />
					[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&request=delete" onClick="javascript:return confirmDelete();" onClick="javascript:return confirmDelete();">Delete This Contact</a> ]
			<?php
		}
		else if($_GET['section'] == 'notification') {
			$fruity->return_command_list($command_list);			
			$fruity->get_contacts_notification_commands($_GET['contact_id'], $contactNotificationCommands);
			$numOfHostCommands = count($contactNotificationCommands['host']);
			$numOfServiceCommands = count($contactNotificationCommands['service']);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>notification.gif" />
				</td>
				<td valign="top">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
						<td colspan="2">Host Notification Commands:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfHostCommands; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=notification&request=delete&contact_notification_command_id=<?=$contactNotificationCommands['host'][$counter]['contact_notification_command_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->get_command_name($contactNotificationCommands['host'][$counter]['command_id']);?></b></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="notification_add" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=notification">
				<input type="hidden" name="request" value="add_notification_command" />
				<input type="hidden" name="contact_manage[notification_add][contact_id]" value="<?=$_GET['contact_id'];?>" />
				<input type="hidden" name="contact_manage[notification_add][notification_type]" value="host" />
				<b>Add New Host Notification Command:</b> <?php print_select("contact_manage[notification_add][command_id]", $command_list, "command_id", "command_name", "0");?> <input type="submit" value="Add Command"><br />
				<?=$fruity->element_desc("host_notification_commands", "nagios_contacts_desc"); ?><br />
				<br />
				</form>
				<br />
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Service Notification Commands:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfServiceCommands; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=notification&request=delete&contact_notification_command_id=<?=$contactNotificationCommands['service'][$counter]['contact_notification_command_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->get_command_name($contactNotificationCommands['service'][$counter]['command_id']);?></b></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="notification_add" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=notification">
				<input type="hidden" name="request" value="add_notification_command" />
				<input type="hidden" name="contact_manage[notification_add][contact_id]" value="<?=$_GET['contact_id'];?>" />
				<input type="hidden" name="contact_manage[notification_add][notification_type]" value="service" />
				<b>Add New Service Notification Command:</b> <?php print_select("contact_manage[notification_add][command_id]", $command_list, "command_id", "command_name", "0");?> <input type="submit" value="Add Command"><br />
				<?=$fruity->element_desc("service_notification_commands", "nagios_contacts_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'groups') {
			$fruity->get_contact_membership_list($_GET['contact_id'], $group_list);
			$numOfGroups = count($group_list);
			// Get list of contact groups
			$fruity->get_contactgroup_list($contactgroups_list);
			$numOfContactGroups = count($contactgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Contact Group Membership:</td>
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=groups&request=delete&contactgroup_id=<?=$group_list[$counter]['contactgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($group_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($group_list[$counter]['contactgroup_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="contactgroup_member_add" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=groups">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="contact_manage[group_add][contact_id]" value="<?=$_GET['contact_id'];?>" />
				<b>Add New Group Membership:</b> <?php print_select("contact_manage[group_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Group"><br />
				<?=$fruity->element_desc("members", "nagios_contactgroups_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "addresses") {
			// This is a Nagios v2.0 feature only
			// Get List Of Addresses For This Contact
			$fruity->get_contact_addresses($_GET['contact_id'], $contactAddresses);
			$numOfAddresses = count($contactAddresses);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>mail.gif" />
				</td>
				<td valign="top">
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Additional Addresses:</td>
						</tr>
						<?php
						for($counter = 0; $counter < $numOfAddresses; $counter++) {
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
							<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=addresses&request=delete&contactaddress_id=<?=$contactAddresses[$counter]['contactaddress_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
							<td height="20" class="altRight"><b>$CONTACTADDRESS<?=($counter+1);?>$:</b> <?=$contactAddresses[$counter]['address'];?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
					if($numOfAddresses < 6) {
						?>
						<br />
						<br />
						<form name="add_contact_address" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$_GET['contact_id'];?>&section=addresses">
						<input type="hidden" name="request" value="contact_address_add" />
						<input type="hidden" name="contact_manage[contact_id]" value="<?=$_GET['contact_id'];?>" />
						Value for $CONTACTADDRESS<?=($counter+1);?>$: <input type="text" name="contact_manage[address]" /> <input type="submit" value="Add Address" /><br />
						<?=$fruity->element_desc("address", "nagios_contacts_desc"); ?><br />
						</form>
						<?php
					}
					?>
					<br />
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

	if(!isset($_GET['contact_add'])) {
		print_window_header("Contact Listings", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>contacts.php?contact_add=1">Add A New Contact</a><br />
		<br />
		<?php
		
		if($numOfContacts) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Contact Name</td>
			<td>Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfContacts; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>contacts.php?contact_id=<?=$contact_list[$counter]['contact_id'];?>"><?=$contact_list[$counter]['contact_name'];?></a></td>
				<td height="20"><?=$contact_list[$counter]['alias'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Contacts Exist</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}

	if(isset($_GET['contact_add'])) {	
		$fruity->get_contactgroup_list($contactgroups_list);
		$contactgroups_list = array_merge( array( array( 'contactgroup_id'=>0, 'contactgroup_name'=>"None")), $contactgroups_list);
		print_window_header("Add A Contact", "100%");
		?>
		<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>contacts.php?contact_add=1">
			<?php 
				if(isset($_GET['edit']))	{
					?>
					<input type="hidden" name="request" value="modify_contact" />
					<input type="hidden" name="contact_id" value="<?=$_GET['contact_id'];?>">
					<?php
				}
				else {
					?>
					<input type="hidden" name="request" value="add_contact" />
					<?php
				}
			?>
			<b>Contact Name:</b> <input type="text" name="contact_manage[contact_name]" value="<?=$_SESSION['tempData']['contact_manage']['contact_name'];?>"><br />
			<?=$fruity->element_desc("contact_name", "nagios_contacts_desc"); ?><br />
			<br />
			<b>Description:</b><br />
			<input type="text" size="80" name="contact_manage[alias]" value="<?=$_SESSION['tempData']['contact_manage']['alias'];?>"><br />
			<?=$fruity->element_desc("alias", "nagios_contacts_desc"); ?><br />
			<br>
			<br>
			
			<br>
			<b>Host Notification Period:</b> <?php print_select("contact_manage[host_notification_period]", $period_list, "timeperiod_id", "timeperiod_name");?><br />
			<?=$fruity->element_desc("host_notification_period", "nagios_contacts_desc"); ?><br />
			<br />
			<b>Service Notification Period:</b> <?php print_select("contact_manage[service_notification_period]", $period_list, "timeperiod_id", "timeperiod_name");?><br />
			<?=$fruity->element_desc("service_notification_period", "nagios_contacts_desc"); ?><br />
			<br />
			<b>Host Notification Options:</b>
			<table width="100%" border="0">
			<tr>
				<td width="100" valign="top">
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_down']) print("CHECKED");?> name="contact_manage[host_notification_options_down]" value="1">Down<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_unreachable']) print("CHECKED");?> name="contact_manage[host_notification_options_unreachable]" value="1">Unreachable<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_recovery']) print("CHECKED");?> name="contact_manage[host_notification_options_recovery]" value="1">Recovery<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['host_notification_options_flapping']) print("CHECKED");?> name="contact_manage[host_notification_options_flapping]" value="1">Flapping<br />
				</td>
				<td valign="middle"><?=$fruity->element_desc("host_notification_options", "nagios_contacts_desc"); ?></td>
			</tr>
			</table>
			<br />
			<b>Service Notification Options:</b>
			<table width="100%" border="0">
			<tr>
				<td width="100" valign="top">
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_warning']) print("CHECKED");?> name="contact_manage[service_notification_options_warning]" value="1">Warning<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_unknown']) print("CHECKED");?> name="contact_manage[service_notification_options_unknown]" value="1">Unknown<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_critical']) print("CHECKED");?> name="contact_manage[service_notification_options_critical]" value="1">Critical<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_recovery']) print("CHECKED");?> name="contact_manage[service_notification_options_recovery]" value="1">Recovery<br />
				<input type="checkbox" <?php if($_SESSION['tempData']['contact_manage']['service_notification_options_flapping']) print("CHECKED");?> name="contact_manage[service_notification_options_flapping]" value="1">Flapping<br />
				</td>
				<td valign="middle"><?=$fruity->element_desc("service_notification_options", "nagios_contacts_desc"); ?></td>
			</tr>
			</table>
			<br />
			<b>Initial Contact Group:</b> <?php print_select("contact_manage[contact_group]", $contactgroups_list, "contactgroup_id", "contactgroup_name", $_SESSION['tempData']['contact_manage']['contact_group']);?><br />
			<?=$fruity->element_desc("members", "nagios_contactgroups_desc"); ?><br />
			<br />
			<b>Email:</b><br />
			<input type="text" size="80" name="contact_manage[email]" value="<?=$_SESSION['tempData']['contact_manage']['email'];?>"><br />
			<?=$fruity->element_desc("email", "nagios_contacts_desc"); ?><br />
			<br />
			<b>Pager:</b><br />
			<input type="text" size="80" name="contact_manage[pager]" value="<?=$_SESSION['tempData']['contact_manage']['pager'];?>"><br />
			<?=$fruity->element_desc("pager", "nagios_contacts_desc"); ?><br />

			<br>		
			
			<table border="0">
				<tr>
					<td><b>Password:</b> <input type="password" name="contact_password"></td>
					<td><input type="checkbox" name="contact_manage[external_auth]" onclick="javascript:enableFieldCheckbox('contact_password','contact_manage[external_auth]');"value="1"><b>External Authentication?</b></td>
				</tr>
			</table>
			
			<br />
			<br />
			<input type="submit" value="Add Contact" /> &nbsp;[ <a href="<?=$path_config['doc_root'];?>contacts.php">Cancel</a> ]
			</form>
			<br /><br />
		<?php
		print_window_footer();
	}
	?>
	<br />
	<?php
print_footer();
?>
