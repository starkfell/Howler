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
Fruity Service Groups Management Page
*/
include_once('includes/config.inc');

// SF BUG# 1449764
// servicegroup session data not being unset
// Resolution: 
// if(!isset($_GET['servicegroup_id']))
// unset($_SESSION['tempData']);

if(!isset($_GET['servicegroup_id']))
unset($_SESSION['tempData']);

if(!isset($_GET['section']) && isset($_GET['servicegroup_id']))
	$_GET['section'] = 'general';

// If we're going to modify host data
if(isset($_GET['servicegroup_id']) && 
		$_GET['section'] == "general" &&
		$_GET['edit']) {
	$fruity->get_servicegroup_info($_GET['servicegroup_id'], $_SESSION['tempData']['servicegroup_manage']);
	$_SESSION['tempData']['servicegroup_manage']['old_name'] = $_SESSION['tempData']['servicegroup_manage']['servicegroup_name'];
}
	

// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'members') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_service_servicegroup($_GET['service_id'], $_GET['servicegroup_id']);
			$status_msg = "Member Deleted";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_hostgroup_contactgroup($_GET['servicegroup_id'], $_GET['contactgroup_id']);
			$status_msg = "Contact Group Deleted";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'general') {
			$fruity->delete_servicegroup($_GET['servicegroup_id']);
			$status_msg = "Service Group Deleted.";
			unset($_SESSION['tempData']['servicegroup_manage']);
			unset($_GET['request']);
			unset($_GET['servicegroup_id']);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['servicegroup_manage'])) {
		foreach($_POST['servicegroup_manage'] as $key=>$value) {
			$_SESSION['tempData']['servicegroup_manage'][$key] = $value;
		}
	}

	if($_POST['request'] == 'add_servicegroup') {
		// Check for pre-existing contact with same name
		if($fruity->servicegroup_exists($_SESSION['tempData']['servicegroup_manage']['servicegroup_name'])) {
			$status_msg = "A service group with that name already exists!";
		}
		else {
			// Error check!
			if(count($_SESSION['tempData']['servicegroup_manage'])) {
				foreach($_SESSION['tempData']['servicegroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			// Field Error Checking
			if($_SESSION['tempData']['servicegroup_manage']['servicegroup_name'] == '' || $_SESSION['tempData']['servicegroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				// All is well for error checking, add the contact into the db.
				if (isset($_POST['in_map']))
					$fruity->add_servicegroup_with_map($_SESSION['tempData']['servicegroup_manage']);
				else
					$fruity->add_servicegroup($_SESSION['tempData']['servicegroup_manage']);
				// Remove session data
				unset($_SESSION['tempData']['servicegroup_manage']);
				$status_msg = "Service group added.";
				unset($_GET['servicegroup_add']);
			}
		}
	}
	else if($_POST['request'] == 'modify_servicegroup') {
		if($_SESSION['tempData']['servicegroup_manage']['servicegroup_name'] != $_SESSION['tempData']['servicegroup_manage']['old_name'] && $fruity->servicegroup_exists($_SESSION['tempData']['servicegroup_manage']['servicegroup_name'])) {
			$status_msg = "A service group with that name already exists!";
		}
		else {
			// Error check!
			if(count($_SESSION['tempData']['servicegroup_manage'])) {
				foreach($_SESSION['tempData']['servicegroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			// Field Error Checking
			if($_SESSION['tempData']['servicegroup_manage']['servicegroup_name'] == '' || $_SESSION['tempData']['servicegroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				// All is well for error checking, modify the group.
				$fruity->modify_servicegroup($_POST['servicegroup_id'], $_SESSION['tempData']['servicegroup_manage'], $_POST['in_map']);
				// Remove session data
				unset($_SESSION['tempData']['servicegroup_manage']);
				$status_msg = "Service group modified.";
				unset($_GET['edit']);
			}
		}
		$_GET['section'] = "general";
	}
	else if($_POST['request'] == 'add_member_command') {
		if($fruity->servicegroup_has_member($_REQUEST['servicegroup_manage']['member_add']['servicegroup_id'],  $_REQUEST['servicegroup_manage']['member_add']['service_id'])) {
			$status_msg = "That member already exists in that list!";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
		else {
			$fruity->add_service_servicegroup($_REQUEST['servicegroup_manage']['member_add']['service_id'], $_REQUEST['servicegroup_manage']['member_add']['servicegroup_id']);
			$status_msg = "New Service Group Member added.";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->hostgroup_has_contactgroup($_GET['servicegroup_id'], $_SESSION['tempData']['servicegroup_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
		else {
			$fruity->add_hostgroup_contactgroup($_GET['servicegroup_id'], $_SESSION['tempData']['servicegroup_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Host Group Contact Group Link added.";
			unset($_SESSION['tempData']['servicegroup_manage']);
		}
	}	
}

// Get list of service groups
$fruity->get_servicegroup_list($servicegroups_list);
$numOfServiceGroups = count($servicegroups_list);

if(isset($_GET['servicegroup_id']) && !isset($_GET['edit']))
	$fruity->get_servicegroup_info($_GET['servicegroup_id'], $tempServiceGroupInfo);



print_header("Service Group Editor");
?>
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($_GET['servicegroup_id'])) {
		// PLACEHOLDER TO PUT CONTACT GROUP INFO
		print_window_header("Group Info for " . $tempServiceGroupInfo['servicegroup_name'], "100%");	
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=general">General</a> | 
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=members">Members</a>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>services.gif" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {
					?>
					<form name="servicegroup_form" method="post" action="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=general&edit=1">
						<input type="hidden" name="request" value="modify_servicegroup" />
						<input type="hidden" name="servicegroup_id" value="<?=$_GET['servicegroup_id'];?>">

						<b>Service Group Name:</b> <input type="text" name="servicegroup_manage[servicegroup_name]" value="<?=$_SESSION['tempData']['servicegroup_manage']['servicegroup_name'];?>"><br />
						<?=$fruity->element_desc("servicegroup_name", "nagios_servicegroups_desc"); ?><br />
						<br />
						<b>Description:</b><br />
						<input type="text" size="80" name="servicegroup_manage[alias]" value="<?=$_SESSION['tempData']['servicegroup_manage']['alias'];?>"><br />
						<?=$fruity->element_desc("alias", "nagios_servicegroups_desc"); ?><br />
						<?php
						if($fruity->servicegroup_in_map($_GET['servicegroup_id']))
							print("<input CHECKED type=\"checkbox\" name=\"in_map\" value=\"1\"><b> Show in Map </b>");
						else
							print("<input type=\"checkbox\" name=\"in_map\" value=\"1\"><b> Show in Map </b>");
						?>	
						<br />
						<br />						
						<input type="submit" value="Modify Service Group" />&nbsp;<a href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>">Cancel</a>
					</form>
			<?php
				}
				else {
					?>
					<b>Service Group Name:</b> <?=$tempServiceGroupInfo['servicegroup_name'];?><br />
					<b>Description:</b> <?=$tempServiceGroupInfo['alias'];?><br />
					<?php
					print("<b>Exported to Map:</b> ");
					if($fruity->servicegroup_in_map($_GET['servicegroup_id']))
						print("Yes<br />");
					else
						print("No<br />");
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			[ <a href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&request=delete" onClick="javascript:return confirmDelete();" onClick="javascript:return confirmDelete();">Delete This Service Group</a> ]
			<?php
		}
		else if($_GET['section'] == 'members') {
			$fruity->get_hosts_services_list( $servicesList );
			//print_r($servicesList);
			$fruity->return_servicegroup_member_list($_GET['servicegroup_id'], $member_list);			
			$numOfMembers = count($member_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>services.gif" />
				</td>
				<td valign="top">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Members:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfMembers; $counter++) {
						
						$fruity->get_service_info( $member_list[$counter]['service_id'], $tempServiceInfo );
						
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=members&request=delete&service_id=<?=$member_list[$counter]['service_id'];?>" onClick="javascript:return confirmDelete();" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_host_name($tempServiceInfo['host_id']);?></b>:<?=$fruity->return_service_description($member_list[$counter]['service_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="servicegroup_member_add" method="post" action="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$_GET['servicegroup_id'];?>&section=members">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="servicegroup_manage[member_add][servicegroup_id]" value="<?=$_GET['servicegroup_id'];?>" />
				<b>Add New Member:</b> <?php print_select("servicegroup_manage[member_add][service_id]", $servicesList, "service_id", "display", "0");?> <input type="submit" value="Add Member"><br />
				<?=$fruity->element_desc("members", "nagios_hostgroups_desc"); ?><br />
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
	if(!isset($_GET['servicegroup_add'])) {	
		print_window_header("Service Group Listing", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_add=1">Add A New Service Group</a><br />
		<br />
		<?php
		if($numOfServiceGroups) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Group Name</td>
			<td>Description</td>
			<td>Exported to Map</td>
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>servicegroups.php?servicegroup_id=<?=$servicegroups_list[$counter]['servicegroup_id'];?>"><?=$servicegroups_list[$counter]['servicegroup_name'];?></a></td>
				<td height="20"><?=$servicegroups_list[$counter]['alias'];?></td>
				<?php
				print('<td height="20" class="altRight">');
				if($fruity->servicegroup_in_map($servicegroups_list[$counter]['servicegroup_id']))
						print("Yes</td>\n");
					else
						print("No</td>\n");
				?>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Service Groups Exist</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}


	if(isset($_GET['servicegroup_add'])) {	
		print_window_header("Add A Service Group", "100%");
		?>
		<form name="servicegroup_form" method="post" action="<?=$path_config['doc_root'];?>servicegroups.php">
			<input type="hidden" name="request" value="add_servicegroup" />
			<b>Service Group Name:</b> <input type="text" name="servicegroup_manage[servicegroup_name]" value="<?=$_SESSION['tempData']['servicegroup_manage']['servicegroup_name'];?>"><br />
			<?=$fruity->element_desc("servicegroup_name", "nagios_servicegroups_desc"); ?><br />
			<br />
			<b>Description:</b><br />
			<input type="text" size="80" name="servicegroup_manage[alias]" value="<?=$_SESSION['tempData']['servicegroup_manage']['alias'];?>"><br />
			<?=$fruity->element_desc("alias", "nagios_servicegroups_desc"); ?><br />
			<input type="checkbox" name="servicegroup_manage[in_map]" value="1"><b> Show in Map </b><br />
			<br />
			<input type="submit" value="Add Service Group" /> [ <a href="servicegroups.php">Cancel</a> ]
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