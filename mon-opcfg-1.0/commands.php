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

// Reset form data
if(!isset($_GET['command_id']))
	unset($_SESSION['tempData']);


// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete") {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_command($_GET['command_id']);
			$status_msg = "Command Deleted";
			unset($_GET['command_id']);
			unset($_SESSION['tempData']['command_manage']);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['command_manage'])) {
		foreach($_POST['command_manage'] as $key=>$value) {
			$_SESSION['tempData']['command_manage'][$key] = $value;
		}
	}
	
	if($_POST['request'] == 'add_command') {
		// Error check for required fields
		if(!isset($_POST['command_manage']['command_name'])) {
			$status_msg = "You must provide a command name.";
			$_GET['command_add'] = 1;
		}
		else {
			// Check for pre-existing command with same name
			if($fruity->command_exists($_SESSION['tempData']['command_manage']['command_name'])) {
				$status_msg = "A command with that name already exists!";
				$_GET['command_add'] = 1;
			}
			else {
				// All is well for error checking, add the command into the db.
				$fruity->add_command($_SESSION['tempData']['command_manage']);
				// Remove session data
				unset($_SESSION['tempData']['command_manage']);
				$status_msg = "Command added.";
				unset($_GET['command_id']);
				unset($_GET['command_add']);
			}
		}
	}
	else if($_POST['request'] == 'modify_command') {
		if($_SESSION['tempData']['command_manage']['command_name'] != $_SESSION['tempData']['command_manage']['old_name'] && $fruity->command_exists($_SESSION['tempData']['command_manage']['command_name'])) {
			$status_msg = "A command with that name already exists!";
		}
		else {
			// All is well for error checking, modify the command.
			$fruity->modify_command($_GET['command_id'], $_SESSION['tempData']['command_manage']);
			// Remove session data
			unset($_SESSION['tempData']['command_manage']);
			unset($_GET['command_id']);
			$status_msg = "Command modified.";
			unset($_GET['command_id']);
		}
	}
}
if($_GET['command_id'] != '') {
	$fruity->get_command($_GET['command_id'], $_SESSION['tempData']['command_manage']);
	$_SESSION['tempData']['command_manage']['old_name'] = $_SESSION['tempData']['command_manage']['command_name'];
}

// Get list of commands
$fruity->return_command_list($command_list);
$numOfCommands = count($command_list);

print_header("Command Editor");
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
	if(isset($_GET['command_id']) || isset($_GET['command_add'])) {
		if($_GET['command_id'] != '') {
			print_window_header("Modify A Command", "100%");
		}
		else {
			print_window_header("Add A Command", "100%");
		}
		?>
		<form name="command_form" method="post" action="<?=$path_config['doc_root'];?>commands.php?command_id=<?=$_GET['command_id'];?>">
			<?php 
				if(isset($_GET['command_id']))	{
					?>
					<input type="hidden" name="request" value="modify_command" />
					<input type="hidden" name="command_id" value="<?=$_GET['command_id'];?>">
					<?php
				}
				else {
					?>
					<input type="hidden" name="request" value="add_command" />
					<?php
				}
			?>
			<b>Command Name:</b><br />
			<input type="text" size="40" name="command_manage[command_name]" value="<?=$_SESSION['tempData']['command_manage']['command_name'];?>"><br />
			<?=$fruity->element_desc("command_name", "nagios_commands_desc"); ?><br />
			<br />
			<b>Command Line:</b><br />
			<input type="text" size="130" name="command_manage[command_line]" value="<?=$_SESSION['tempData']['command_manage']['command_line'];?>"><br />
			<?=$fruity->element_desc("command_line", "nagios_commands_desc"); ?><br />
			<br />
			<b>Command Description:</b><br />
			<input type="text" size="130" name="command_manage[command_desc]" value="<?=$_SESSION['tempData']['command_manage']['command_desc'];?>"><br />
			<?=$fruity->element_desc("command_desc", "nagios_commands_desc"); ?><br />
			<br />		
			<br />
			<?php 
				if($_GET['command_id'] != '') {
					?>
					<a href="<?=$path_config['doc_root'];?>commands.php?command_id=<?=$_GET['command_id'];?>&request=delete">Delete</a>&nbsp;<input type="submit" value="Modify Command" />&nbsp;<a href="<?=$path_config['doc_root'];?>commands.php">Cancel</a>
					<?php
				}
				else {
					?>
					<input type="submit" value="Create Command" />&nbsp;<a href="<?=$path_config['doc_root'];?>commands.php">Cancel</a>
					<?php
				}
			?>
			<br /><br />
		<?php
		print_window_footer();
	}
	else {
		print_window_header("Command Listings", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>commands.php?command_add=1">Add A New Command</a><br />
		<?php
		if($numOfCommands) {
			?>
			<br />
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Command Name</td>
			<td>Command Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfCommands; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>commands.php?command_id=<?=$command_list[$counter]['command_id'];?>"><?=$command_list[$counter]['command_name'];?></a></td>
				<td height="20" class="altRight">&nbsp;<?=$command_list[$counter]['command_desc'];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
	
		}
		else {
			?>
			<br />
			<div class="statusmsg">No Commands Exist</div>
			<?php
		}
		print_window_footer();
	}
	
print_footer();
?>
