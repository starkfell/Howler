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
Fruity Host Groups Management Page
*/
include_once('includes/config.inc');

if(!isset($_GET['section']) && isset($_GET['hostgroup_id']))
	$_GET['section'] = 'general';

if(!isset($_GET['edit'])) {
	unset($_SESSION['tempData']);
}

// If we're going to modify host data
if(isset($_GET['hostgroup_id']) && 
		$_GET['section'] == "general" &&
		$_GET['edit']) {
	$fruity->get_hostgroup_info($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']);
	$_SESSION['tempData']['hostgroup_manage']['old_name'] = $_SESSION['tempData']['hostgroup_manage']['hostgroup_name'];
}
	
	
// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'members') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_hostgroup_member($_GET['hostgroup_id'], $_GET['host_id']);
			$status_msg = "Member Deleted";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'services') {
			$fruity->delete_service($_GET['service_id']);
			$status_msg = "Service Deleted";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'general') {
			$fruity->delete_hostgroup($_GET['hostgroup_id']);
			$status_msg = "Hostgroup Deleted.";
			unset($_SESSION['tempData']['hostgroup_manage']);
			unset($_GET['request']);
			unset($_GET['hostgroup_id']);
		}
		else if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
			$fruity->delete_hostgroup_contactgroup($_GET['hostgroup_id'], $_GET['contactgroup_id']);
			$status_msg = "Contact Group Deleted";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['hostgroup_manage'])) {
		foreach($_POST['hostgroup_manage'] as $key=>$value) {
			$_SESSION['tempData']['hostgroup_manage'][$key] = $value;
		}
	}
	
	
	if($_POST['request'] == 'add_hostgroup') {
		// Check for pre-existing contact with same name
		if($fruity->hostgroup_exists($_SESSION['tempData']['hostgroup_manage']['hostgroup_name'])) {
			$status_msg = "A host group with that name already exists!";
		}
		else {
			// Error check!
			if(count($_SESSION['tempData']['hostgroup_manage'])) {
				foreach($_SESSION['tempData']['hostgroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			// Field Error Checking
			if($_SESSION['tempData']['hostgroup_manage']['hostgroup_name'] == '' || $_SESSION['tempData']['hostgroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				// All is well for error checking, add the contact into the db.
				if (isset($_POST['in_map'])) {
					$fruity->add_hostgroup_with_map($_SESSION['tempData']['hostgroup_manage']);
					// Remove session data
					unset($_SESSION['tempData']['hostgroup_manage']);
					$status_msg = "Host group added.";
					unset($_GET['hostgroup_add']);
				}else{
					$fruity->add_hostgroup($_SESSION['tempData']['hostgroup_manage']);
					// Remove session data
					unset($_SESSION['tempData']['hostgroup_manage']);
					$status_msg = "Host group added.";
					unset($_GET['hostgroup_add']);
				}
			}
		}
	}
	else if($_POST['request'] == 'modify_hostgroup') {
		if($_SESSION['tempData']['hostgroup_manage']['hostgroup_name'] != $_SESSION['tempData']['hostgroup_manage']['old_name'] && $fruity->hostgroup_exists($_SESSION['tempData']['hostgroup_manage']['hostgroup_name'])) {
			$status_msg = "A host group with that name already exists!";
		}
		else {
			// Error check!
			if(count($_SESSION['tempData']['hostgroup_manage'])) {
				foreach($_SESSION['tempData']['hostgroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			// Field Error Checking
			if($_SESSION['tempData']['hostgroup_manage']['hostgroup_name'] == '' || $_SESSION['tempData']['hostgroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				// All is well for error checking, modify the group.
				$fruity->modify_hostgroup($_POST['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage'], $_POST['in_map']);
				// Remove session data
				unset($_SESSION['tempData']['hostgroup_manage']);
				$status_msg = "Host group modified.";
				unset($_GET['edit']);
			}
		}
	}
	else if($_POST['request'] == 'add_member_command') {
		
		if($fruity->hostgroup_has_member($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['member_add']['host_id'])) {
			$status_msg = "That member already exists in that list!";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
		else {
			$fruity->add_hostgroup_member($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['member_add']['host_id']);
			$status_msg = "New Host Group Member added.";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->hostgroup_has_contactgroup($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
		else {
			$fruity->add_hostgroup_contactgroup($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Host Group Contact Group Link added.";
			unset($_SESSION['tempData']['hostgroup_manage']);
		}
	}	
	else if($_POST['request'] == 'add_hostgroup_service') {
		if(hostgroup_has_service($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['service_id'])) {
			$status_msg = "That hostgroup already has that service linked.";
		}
		else {
			// All is well, link the service definition.
			link_hostgroup_service($_GET['hostgroup_id'], $_SESSION['tempData']['hostgroup_manage']['service_id']);
			unset($_SESSION['tempData']['hostgroup_manage']);
			$status_msg = "Service linked to this hostgroup.";
		}
	}
}

// Get list of host groups
$fruity->get_hostgroup_list($hostgroups_list);
$numOfHostGroups = count($hostgroups_list);

if(isset($_GET['hostgroup_id']) && !isset($_GET['edit']))
	$fruity->get_hostgroup_info($_GET['hostgroup_id'], $tempHostGroupInfo);
	
print_header("Host Group Editor");
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($_GET['hostgroup_id'])) {
		// PLACEHOLDER TO PUT CONTACT GROUP INFO
		print_window_header("Group Info for " . $tempHostGroupInfo['hostgroup_name'], "100%");	
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=general">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=members">Members</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=services">Services</a>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>servergroup.gif" />
				</td>
				<td valign="top">
				<?php
				if(isset($_GET['edit'])) {
					?>
					<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=general&edit=1">
						<input type="hidden" name="request" value="modify_hostgroup" />
						<input type="hidden" name="hostgroup_id" value="<?=$_GET['hostgroup_id'];?>">
						<b>Host Group Name:</b> <input type="text" name="hostgroup_manage[hostgroup_name]" value="<?=$_SESSION['tempData']['hostgroup_manage']['hostgroup_name'];?>"><br />
						<?=$fruity->element_desc("hostgroup_name", "nagios_hostgroups_desc"); ?><br />
						<br />
						<b>Description:</b><br />
						<input type="text" size="80" name="hostgroup_manage[alias]" value="<?=$_SESSION['tempData']['hostgroup_manage']['alias'];?>"><br />
						<?=$fruity->element_desc("alias", "nagios_hostgroups_desc"); ?><br />
						<br />
						<input type="submit" value="Modify Host Group" />&nbsp; [<a href="<?=$path_config['doc_root'];?>hostgroups.php">Cancel</a> ]
					</form>
					<?php
				}
				else {
					?>
					<b>Host Group Name:</b> <?=$tempHostGroupInfo['hostgroup_name'];?><br />
					<b>Description:</b> <?=$tempHostGroupInfo['alias'];?><br />
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&request=delete" onClick="javascript:return confirmDelete();" onClick="javascript:return confirmDelete();">Delete This Host Group</a> ]
			<?php
		}
		else if($_GET['section'] == 'services') {
			$fruity->get_hostgroup_services_list($_GET['hostgroup_id'], $hostgroupServiceList);
			
			$numOfServices = count($hostgroupServiceList);
			
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>services.gif" />
				</td>
				<td valign="top">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Services Explicitly Linked to This Hostgroup:</td>
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
						?>
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=services&request=delete&service_id=<?=$hostgroupServiceList[$counter]['service_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$hostgroupServiceList[$counter]['service_id'];?>"><?=$fruity->return_service_description($hostgroupServiceList[$counter]['service_id']);?></a></b></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				[ <a href="<?=$path_config['doc_root'];?>services.php?service_add=1&hostgroup_id=<?=$_GET['hostgroup_id'];?>">Create A New Service</a> ]
				<br />
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'members') {
			$fruity->get_host_list( $host_list );
			$fruity->return_hostgroup_member_list($_GET['hostgroup_id'], $member_list);
			$numOfMembers = count($member_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>server.gif" />
				</td>
				<td valign="top">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Members:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfMembers; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=members&request=delete&host_id=<?=$member_list[$counter]['host_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_host_name($member_list[$counter]['host_id']);?>:</b> <?=$fruity->return_host_alias($member_list[$counter]['host_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="hostgroup_member_add" method="post" action="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=members">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="hostgroup_manage[member_add][hostgroup_id]" value="<?=$_GET['hostgroup_id'];?>" />
				<b>Add New Member:</b> <?php print_select("hostgroup_manage[member_add][host_id]", $host_list, "host_id", "host_name", "0");?> <input type="submit" value="Add Member"><br />
				<?=$fruity->element_desc("members", "nagios_hostgroups_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'contactgroups') {
			$fruity->return_hostgroup_contactgroups_list($_GET['hostgroup_id'], $contactgroups_list);			
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
					<td colspan="2">Contact Groups Linked To This Host Group:</td>
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=contactgroups&request=delete&contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroups_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroups_list[$counter]['contactgroup_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
				$fruity->get_contactgroup_list($contactgroups_list);
				?>
				<br />
				<br />
				<form name="hostgroup_contactgroup_add" method="post" action="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$_GET['hostgroup_id'];?>&section=contactgroups">
				<input type="hidden" name="request" value="add_contactgroup_command" />
				<input type="hidden" name="hostgroup_manage[contactgroup_add][hostgroup_id]" value="<?=$_GET['hostgroup_id'];?>" />
				<b>Add New Contact Group:</b> <?php print_select("hostgroup_manage[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Contact Group"><br />
				<?=$fruity->element_desc("contact_groups", "nagios_hostgroups_desc"); ?><br />
				<br />
				</form>
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
	if(!isset($_GET['hostgroup_add'])) {
		print_window_header("Host Group Listing", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_add=1">Add A New Host Group</a><br />
		<br />
		<?php
		
		if($numOfHostGroups) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Group Name</td>
			<td>Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfHostGroups; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>hostgroups.php?hostgroup_id=<?=$hostgroups_list[$counter]['hostgroup_id'];?>"><?=$hostgroups_list[$counter]['hostgroup_name'];?></a></td>
				<td height="20" ><?=$hostgroups_list[$counter]['alias'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Host Groups Exist</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}
	if(isset($_GET['hostgroup_add'])) {	
		if(isset($_GET['edit'])) {
			print_window_header("Modify A Host Group", "100%");
		}
		else {
			print_window_header("Add A Host Group", "100%");
		}
		?>
		<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>hostgroups.php">
			<input type="hidden" name="request" value="add_hostgroup" />
			<b>Host Group Name:</b> <input type="text" name="hostgroup_manage[hostgroup_name]" value="<?=$_SESSION['tempData']['hostgroup_manage']['hostgroup_name'];?>"><br />
			<?=$fruity->element_desc("hostgroup_name", "nagios_hostgroups_desc"); ?><br />
			<br />
			<b>Description:</b><br />
			<input type="text" size="80" name="hostgroup_manage[alias]" value="<?=$_SESSION['tempData']['hostgroup_manage']['alias'];?>"><br />
			<?=$fruity->element_desc("alias", "nagios_hostgroups_desc"); ?><br />

			

			<input type="submit" value="Add Host Group" /> [ <a href="<?=$path_config['doc_root'];?>hostgroups.php">Cancel</a> ]
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
