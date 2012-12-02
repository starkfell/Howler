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
Fruity Contact Groups Management Page
*/
include_once('includes/config.inc');

if(!isset($_GET['section']) && isset($_GET['contactgroup_id']))
	$_GET['section'] = 'general';

if(!isset($_GET['edit'])) {
	unset($_SESSION['tempData']);
}

// If we're going to modify host data
if(isset($_GET['contactgroup_id']) && 
		($_GET['section'] == "general" &&
		$_GET['edit'])) {
	$fruity->get_contactgroup_info($_GET['contactgroup_id'], $_SESSION['tempData']['contactgroup_manage']);
	$_SESSION['tempData']['contactgroup_manage']['old_name'] = $_SESSION['tempData']['contactgroup_manage']['contactgroup_name'];
}

// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete" && $_GET['section'] == 'members') {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_contactgroup_member($_GET['contactgroup_id'], $_GET['contact_id']);
			$status_msg = "Member Deleted";
			unset($_SESSION['tempData']['contactgroup_manage']);
		}
		if($_GET['request'] == "delete" && $_GET['section'] == 'general') {
			$fruity->delete_contactgroup($_GET['contactgroup_id']);
			$status_msg = "Contact Group Deleted";
			unset($_SESSION['tempData']['contactgroup_manage']);
			unset($_GET['contactgroup_id']);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['contactgroup_manage'])) {
		foreach($_POST['contactgroup_manage'] as $key=>$value) {
			$_SESSION['tempData']['contactgroup_manage'][$key] = $value;
		}
	}

	if($_POST['request'] == 'add_contactgroup') {
		// Check for pre-existing contact with same name
		if($fruity->contactgroup_exists($_SESSION['tempData']['contactgroup_manage']['contact_name'])) {
			$status_msg = "A contact group with that name already exists!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['contactgroup_manage'])) {
				foreach($_SESSION['tempData']['contactgroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['contactgroup_manage']['contactgroup_name'] == '' || $_SESSION['tempData']['contactgroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				$fruity->add_contactgroup($_SESSION['tempData']['contactgroup_manage']);
				// Remove session data
				unset($_SESSION['tempData']['contactgroup_manage']);
				$status_msg = "Contact group added.";
			}
		}
	}
	else if($_POST['request'] == 'modify_contactgroup') {
		if($_SESSION['tempData']['contactgroup_manage']['contactgroup_name'] != $_SESSION['tempData']['contactgroup_manage']['old_name'] && $fruity->contactgroup_exists($_SESSION['tempData']['contactgroup_manage']['contactgroup_name'])) {
			$status_msg = "A contact group with that name already exists!";
		}
		else {
			// Field Error Checking
			if(count($_SESSION['tempData']['contactgroup_manage'])) {
				foreach($_SESSION['tempData']['contactgroup_manage'] as $tempVariable)
					$tempVariable = trim($tempVariable);
			}
			if($_SESSION['tempData']['contactgroup_manage']['contactgroup_name'] == '' || $_SESSION['tempData']['contactgroup_manage']['alias'] == '') {
				$addError = 1;
				$status_msg = "Fields shown are required and cannot be left blank.";
			}
			else {
				// All is well for error checking, modify the contact.
				$fruity->modify_contactgroup($_POST['contactgroup_id'], $_SESSION['tempData']['contactgroup_manage']);
				// Remove session data
				unset($_SESSION['tempData']['contactgroup_manage']);
				$status_msg = "Contact group modified.";
				unset($_GET['edit']);
			}
		}
		$_GET['contactgroup_id'] = $_POST['contactgroup_id'];
		$_GET['section'] = "general";
	}
	else if($_POST['request'] == 'add_member_command') {
		if($fruity->contactgroup_has_member($_GET['contactgroup_id'], $_SESSION['tempData']['contactgroup_manage']['member_add']['contact_id'])) {
			$status_msg = "That member already exists in that list!";
			unset($_SESSION['tempData']['contactgroup_manage']);
		}
		else {
			$fruity->add_contactgroup_member($_GET['contactgroup_id'], $_SESSION['tempData']['contactgroup_manage']['member_add']['contact_id']);
			$status_msg = "New Contact Group Member added.";
			unset($_SESSION['tempData']['contactgroup_manage']);
		}
	}		
}

// Get list of contact groups
$fruity->get_contactgroup_list($contactgroups_list);
$numOfContactGroups = count($contactgroups_list);

if(isset($_GET['contactgroup_id']) && !isset($_GET['edit'])) {
	$fruity->get_contactgroup_info($_GET['contactgroup_id'], $tempContactGroupInfo);
}


print_header("Contact Group Editor");
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	if(isset($_GET['contactgroup_id'])) {
		// PLACEHOLDER TO PUT CONTACT GROUP INFO
		print_window_header("Group Info for " . $tempContactGroupInfo['contactgroup_name'], "100%");	
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&section=general">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&section=members">Members</a><br />
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
					<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id<?=$_GET['contactgroup_id'];?>&edit=1">
						<input type="hidden" name="request" value="modify_contactgroup" />
						<input type="hidden" name="contactgroup_id" value="<?=$_GET['contactgroup_id'];?>">
						<b>Contact Group Name:</b> <input type="text" name="contactgroup_manage[contactgroup_name]" value="<?=$_SESSION['tempData']['contactgroup_manage']['contactgroup_name'];?>"><br />
						<?=$fruity->element_desc("contactgroup_name", "nagios_contactgroups_desc"); ?><br />
						<br />
						<b>Description:</b><br />
						<input type="text" size="80" name="contactgroup_manage[alias]" value="<?=$_SESSION['tempData']['contactgroup_manage']['alias'];?>"><br />
						<?=$fruity->element_desc("alias", "nagios_contactgroups_desc"); ?><br />
						<br />
						<br />
						<input type="submit" value="Modify Contact Group" />&nbsp;[ <a href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>">Cancel</a> ]
					</form>
					<?php
				}
				else {
					?>
					<b>Contact Group Name:</b> <?=$tempContactGroupInfo['contactgroup_name'];?><br />
					<b>Description:</b> <?=$tempContactGroupInfo['alias'];?><br />
					<br />
					[ <a href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>				
				</td>
			</tr>
			</table>
			<br />
			[ <a href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&request=delete" onClick="javascript:return confirmDelete();">Delete This Contact Group</a> ]
			<?php
		}
		else if($_GET['section'] == 'members') {
			$fruity->return_contactgroup_member_list($_GET['contactgroup_id'], $member_list);			
			$numOfMembers = count($member_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>notification.gif" />
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&section=members&request=delete&contact_id=<?=$member_list[$counter]['contact_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_contact_name($member_list[$counter]['contact_id']);?>:</b> <?=$fruity->return_contact_alias($member_list[$counter]['contact_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
				$fruity->get_contact_list($contact_list);
				?>
				<br />
				<br />
				<form name="contactgroup_member_add" method="post" action="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$_GET['contactgroup_id'];?>&section=members">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="contactgroup_manage[member_add][contactgroup_id]" value="<?=$_GET['contactgroup_id'];?>" />
				<b>Add New Member:</b> <?php print_select("contactgroup_manage[member_add][contact_id]", $contact_list, "contact_id", "contact_name", "0");?> <input type="submit" value="Add Member"><br />
				<?=$fruity->element_desc("members", "nagios_contactgroups_desc"); ?><br />
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
	if(!isset($_GET['contactgroup_add'])) {	
		print_window_header("Contact Group Listings", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_add=1">Add A New Contact Group</a><br />
		<br />
		<?php
		if($numOfContactGroups) {
			?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Group Name</td>
			<td>Description</td>
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>contactgroups.php?contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>"><?=$contactgroups_list[$counter]['contactgroup_name'];?></a></td>
				<td height="20" class="altRight"><?=$contactgroups_list[$counter]['alias'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		else {
			?>
			<div class="statusmsg">No Contact Groups Exist</div>
			<?php
		}
		print_window_footer();
		print("<br /><br />");
	}


	if(isset($_GET['contactgroup_add'])) {	
		print_window_header("Add A Contact Group", "100%");
		?>
		<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>contactgroups.php">
			<input type="hidden" name="request" value="add_contactgroup" />
			<b>Contact Group Name:</b> <input type="text" name="contactgroup_manage[contactgroup_name]" value="<?=$_SESSION['tempData']['contactgroup_manage']['contactgroup_name'];?>"><br />
			<?=$fruity->element_desc("contactgroup_name", "nagios_contactgroups_desc"); ?><br />
			<br />
			<b>Description:</b><br />
			<input type="text" size="80" name="contactgroup_manage[alias]" value="<?=$_SESSION['tempData']['contactgroup_manage']['alias'];?>"><br />
			<?=$fruity->element_desc("alias", "nagios_contactgroups_desc"); ?><br />
			<br />
			<br />
			<input type="submit" value="Add Contact Group" /> [ <a href="<?=$path_config['doc_root'];?>contactgroups.php">Cancel</a> ]
			<br /><br />
		</form>
		<?php
		print_window_footer();
	}
	?>
	<br />
	<?php
print_footer();
?>