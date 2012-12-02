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
Time Period Manager
*/

include_once('includes/config.inc');

// Reset form data
if(!isset($_GET['timeperiod_id']) && !isset($_POST['timeperiod_id']))
	unset($_SESSION['tempData']['timeperiod_manage']);


// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete") {
			// !!!!!!!!!!!!!! This is where we do dependency error checking
			$fruity->delete_period($_GET['timeperiod_id']);
			$status_msg = "Period Deleted";
			unset($_GET['timeperiod_id']);
			unset($_SESSION['tempData']['timeperiod_manage']);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['timeperiod_manage'])) {
		foreach( $_POST['timeperiod_manage'] as $key=>$value) {
			$_SESSION['tempData']['timeperiod_manage'][$key] = $value;
		}
	}
	
	if($_POST['request'] == 'add_period') {
		// Check for pre-existing command with same name
		if($fruity->period_exists($_SESSION['tempData']['timeperiod_manage']['timeperiod_name'])) {
			$status_msg = "A time period with that name already exists!";
		}
		else {
			// All is well for error checking, add the command into the db.
			$fruity->add_period($_SESSION['tempData']['timeperiod_manage']);
			// Remove session data
			unset($_SESSION['tempData']['timeperiod_manage']);
			unset($_GET['timeperiod_add']);
			$status_msg = "Time period added.";
		}
	}
	else if($_POST['request'] == 'modify_period') {
		if($_SESSION['tempData']['timeperiod_manage']['command_name'] != $_SESSION['tempData']['timeperiod_manage']['old_name'] && $fruity->period_exists($_SESSION['tempData']['timeperiod_manage']['command_name'])) {
			$status_msg = "A time period with that name already exists!";
		}
		else {
			// All is well for error checking, modify the command.
			$fruity->modify_period($_SESSION['tempData']['timeperiod_manage']);
			// Remove session data
			unset($_SESSION['tempData']['timeperiod_manage']);
			unset($_GET['timeperiod_id']);
			unset($_GET['timeperiod_add']);
			$status_msg = "Time period modified.";
		}
	}
}
if(isset($_GET['timeperiod_id'])) {
	$fruity->get_period($_GET['timeperiod_id'], $_SESSION['tempData']['timeperiod_manage']);
	$_SESSION['tempData']['timeperiod_manage']['old_name'] = $_SESSION['tempData']['timeperiod_manage']['timeperiod_name'];
}

// Get list of commands
$fruity->return_period_list($period_list);
$numOfPeriods = count($period_list);

print_header("Time Period Editor");
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
	if(isset($_GET['timeperiod_id']) || isset($_GET['timeperiod_add'])) {
		if($_GET['timeperiod_id'] != '') {
			print_window_header("Modify A Time Period", "100%");
		}
		else {
			print_window_header("Add A Time Period", "100%");
		}
		?>
		<form name="timeperiod_form" method="post" action="<?=$path_config['doc_root'];?>timeperiods.php?timeperiod_add=1">
			<?php 
				if(isset($_GET['timeperiod_id']))	{
					?>
					<input type="hidden" name="request" value="modify_period" />
					<input type="hidden" name="timeperiod_id" value="<?=$_GET['timeperiod_id'];?>">
					<?php
				}
				else {
					?>
					<input type="hidden" name="request" value="add_period" />
					<?php
				}
			?>
			<b>Time Period Name:</b><br />
			<input type="text" name="timeperiod_manage[timeperiod_name]" value="<?=$_SESSION['tempData']['timeperiod_manage']['timeperiod_name'];?>"><br />
			<?=$fruity->element_desc("timeperiod_name", "nagios_timeperiods_desc"); ?><br />
			<br />
			<b>Description:</b><br />
			<input type="text" size="80" name="timeperiod_manage[alias]" value="<?=htmlspecialchars($_SESSION['tempData']['timeperiod_manage']['alias']);?>"><br />
			<?=$fruity->element_desc("alias", "nagios_timeperiods_desc"); ?><br />
			<br />
			<table width="100%" border="0">
			<tr>
				<td width="200" valign="top">
				<b>Sunday</b><br /><input size="40" type="text" name="timeperiod_manage[sunday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['sunday'];?>"><br />
				<b>Monday</b><br /><input size="40" type="text" name="timeperiod_manage[monday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['monday'];?>"><br />
				<b>Tuesday</b><br /><input size="40" type="text" name="timeperiod_manage[tuesday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['tuesday'];?>"><br />
				<b>Wednesday</b><br /><input size="40" type="text" name="timeperiod_manage[wednesday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['wednesday'];?>"><br />
				<b>Thursday</b><br /><input size="40" type="text" name="timeperiod_manage[thursday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['thursday'];?>"><br />
				<b>Friday</b><br /><input size="40" type="text" name="timeperiod_manage[friday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['friday'];?>"><br />
				<b>Saturday</b><br /><input size="40" type="text" name="timeperiod_manage[saturday]" value="<?=$_SESSION['tempData']['timeperiod_manage']['saturday'];?>"><br />
				</td>
				<td valign="middle">
					<?=$fruity->element_desc("days", "nagios_timeperiods_desc"); ?>
				</td>
			</tr>
			</table>
			<br />
			<?php 
				if($_GET['timeperiod_id'] != '') {
					?>
					<a href="<?=$path_config['doc_root'];?>timeperiods.php?timeperiod_id=<?=$_GET['timeperiod_id'];?>&request=delete">Delete</a>&nbsp;<input type="submit" value="Modify Period" />&nbsp;<a href="<?=$path_config['doc_root'];?>timeperiods.php">Cancel</a>
					<?php
				}
				else {
					?>
					<input type="submit" value="Create Period" />&nbsp;<a href="<?=$path_config['doc_root'];?>timeperiods.php">Cancel</a>
					<?php
				}
			?>
			<br /><br />
		<?php
		print_window_footer();
	}
	else {
		?>
		<br />
		<?php
		print_window_header("Time Period Listings", "100%");
		?>
		&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>timeperiods.php?timeperiod_add=1">Add A New Time Period</a><br />
		<?php
		if($numOfPeriods) {
			?>
			<br />
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Period Name</td>
			<td>Period Description</td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfPeriods; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>timeperiods.php?timeperiod_id=<?=$period_list[$counter]['timeperiod_id'];?>"><?=$period_list[$counter]['timeperiod_name'];?></a></td>
				<td height="20" class="altRight"><?=$period_list[$counter]['alias'];?></td>
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
			<div class="statusmsg">No Periods Exist</div>
			<?php
		}
		print_window_footer();
	}
	
print_footer();
?>