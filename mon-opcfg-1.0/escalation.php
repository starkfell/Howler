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
 * escalation.php
 * Author:	Taylor Dondich (tdondich at gmail.com)
 * Description:
 * 	Provides interface to maintain escalations
 *
*/
 


include_once('includes/config.inc');

// Data preparation
if(!isset($_GET['section']))
	$_GET['section'] = 'general';

$_SESSION['tempData']['escalation_manage'] = NULL;
unset($_SESSION['tempData']['escalation_manage']);

if(isset($_GET['temp_host_id'])) {
	$_SESSION['tempData']['escalation_manage']['target_host_id'] = NULL;
}

if(isset($_GET['escalation_add'])) {
	$sublink = "?escalation_add=1";
	if(isset($_GET['host_id']))
		$sublink .= "&host_id=".$_GET['host_id'];
	if(isset($_GET['host_template_id']))
		$sublink .= "&host_template_id=".$_GET['host_template_id'];
	if(isset($_GET['service_id']))
		$sublink .= "&service_id=".$_GET['service_id'];
	if(isset($_GET['service_template_id']))
		$sublink .= "&service_template_id=".$_GET['service_template_id'];
}

// If we're going to modify escalation data
if(isset($_GET['escalation_id']) && 
		$_GET['section'] == "general" && $_GET['edit']) {
$fruity->return_period_list($period_list);

	$fruity->get_escalation($_GET['escalation_id'], $_SESSION['tempData']['escalation_manage']);
}

if(isset($_GET['request'])) {
	if($_GET['request'] == "delete" && $_GET['section'] == 'contactgroups') {
		$fruity->delete_escalation_contactgroup($_GET['escalation_id'], $_GET['contactgroup_id']);
		$status_msg = "Contact Group Deleted";
		unset($_SESSION['tempData']['escalation_manage']);
		unset($_GET['request']);
	}
}
	
// Action Handlers
if(isset($_POST['request'])) {
	if(count($_POST['escalation_manage'])) {
		foreach($_POST['escalation_manage'] as $key=>$value) {
			$_SESSION['tempData']['escalation_manage'][$key] = $value;
		}

	}
	// Enabler checks
	if(count($_POST['escalation_manage_enablers'])) {
		foreach($_POST['escalation_manage_enablers'] as $key=>$value) {
			if($value == 0) {
				$_SESSION['tempData']['escalation_manage'][$key] = NULL;
			}
		}
	}

	
	if($_POST['request'] == 'add_escalation') {
		// Check to see what kind of escalation we've got
		if( (isset($_GET['host_id']) || isset($_GET['host_template_id']) ) && ( !isset($_GET['service_id']) && !isset($_GET['service_template_id']) ) ){
			// We're doing a host/host template escalation
			if(isset($_GET['host_template_id'])) {
				// We're doing a template escalation
				if(!$fruity->add_host_template_escalation($_GET['host_template_id'], $_SESSION['tempData']['escalation_manage']['escalation_description'])) {
					$status_msg = "Error: add_host_template_dependency failed.";
				}
				else {
					$tempID = $fruity->return_host_template_escalation($_GET['host_template_id'], $_SESSION['tempData']['escalation_manage']['escalation_description']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "escalation.php?escalation_id=".$tempID);
					die();
				}
			}
			else {
				if(!$fruity->add_host_escalation($_GET['host_id'], $_SESSION['tempData']['escalation_manage']['escalation_description'])) {
					$status_msg = "Error: add_host_escalation failed.";
				}
				else {
					$tempID = $fruity->return_host_escalation($_GET['host_id'], $_SESSION['tempData']['escalation_manage']['escalation_description']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "escalation.php?escalation_id=".$tempID);
					die();
				}
			}
			
		}
		else {
			if(isset($_GET['service_id'])) {
				if(!$fruity->add_service_escalation($_GET['service_id'], $_SESSION['tempData']['escalation_manage']['escalation_description'])) {
					$status_msg = "Error: add_service_template_escalation failed.";
				}
				else {
					$tempID = $fruity->return_service_escalation($_GET['service_id'], $_SESSION['tempData']['escalation_manage']['escalation_description']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "escalation.php?escalation_id=".$tempID);
					die();
				}
			}
			else {
				if(!$fruity->add_service_template_escalation($_GET['service_template_id'], $_SESSION['tempData']['escalation_manage']['escalation_description'])) {
					$status_msg = "Error: add_service_template_escalation failed.";
				}
				else {
					$tempID = $fruity->return_service_template_escalation($_GET['service_template_id'], $_SESSION['tempData']['escalation_manage']['escalation_description']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "escalation.php?escalation_id=".$tempID);
					die();
				}
			}
		}
	}
	else if($_POST['request'] == 'escalation_modify_general') {
		// Field Error Checking
		if(count($_SESSION['tempData']['escalation_manage'])) {
			foreach($_SESSION['tempData']['escalation_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
		if(($_POST['escalation_manage_enablers']['first_notification'] && !is_numeric($_SESSION['tempData']['escalation_manage']['first_notification'])) || 
		($_POST['escalation_manage_enablers']['first_notification'] && !($_SESSION['tempData']['escalation_manage']['first_notification'] >= 1)) ||
		($_POST['escalation_manage_enablers']['last_notification'] && !is_numeric($_SESSION['tempData']['escalation_manage']['last_notification'])) || 
		($_POST['escalation_manage_enablers']['last_notification'] && !($_SESSION['tempData']['service_manage']['last_notification'] >= 1)) ||
		($_POST['escalation_manage_enablers']['notification_interval'] && !is_numeric($_SESSION['tempData']['service_manage']['notification_interval'])) || 
		($_POST['escalation_manage_enablers']['notification_interval'] && !($_SESSION['tempData']['service_manage']['notification_interval'] >= 1)) ) {
			$status_msg = "Incorrect values for fields.  Please verify.";
		}

		if(!$_POST['escalation_manage_enablers']['escalation_options']) {
			$_SESSION['tempData']['escalation_manage']['escalation_options_down'] = NULL;
			$_SESSION['tempData']['escalation_manage']['escalation_options_unreachable'] = NULL;
			$_SESSION['tempData']['escalation_manage']['escalation_options_up'] = NULL;
			$_SESSION['tempData']['escalation_manage']['escalation_options_warning'] = NULL;
			$_SESSION['tempData']['escalation_manage']['escalation_options_unknown'] = NULL;
			$_SESSION['tempData']['escalation_manage']['escalation_options_critical'] = NULL;
		}
		else {
			if(!isset($_POST['escalation_manage']['escalation_options_down']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_down'] = 0;
			if(!isset($_POST['escalation_manage']['escalation_options_unreachable']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_unreachable'] = 0;
			if(!isset($_POST['escalation_manage']['escalation_options_up']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_up'] = 0;
			if(!isset($_POST['escalation_manage']['escalation_options_warning']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_warning'] = 0;
			if(!isset($_POST['escalation_manage']['escalation_options_unknown']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_unknown'] = 0;
			if(!isset($_POST['escalation_manage']['escalation_options_critical']))
				$_SESSION['tempData']['escalation_manage']['escalation_options_critical'] = 0;
		}
		// All is well for error checking, modify the command.
		if($fruity->modify_escalation($_SESSION['tempData']['escalation_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['escalation_manage']);
			$status_msg = "Escalation modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_escalation failed.";
		}
	}
	else if($_POST['request'] == 'add_contactgroup_command') {
		if($fruity->escalation_has_contactgroup($_GET['escalation_id'], $_SESSION['tempData']['escalation_manage']['contactgroup_add']['contactgroup_id'])) {
			$status_msg = "That contact group already exists in that list!";
			unset($_SESSION['tempData']['escalation_manage']);
		}
		else {
			$fruity->add_escalation_contactgroup($_GET['escalation_id'], $_SESSION['tempData']['escalation_manage']['contactgroup_add']['contactgroup_id']);
			$status_msg = "New Escalation Contact Group Link added.";
			unset($_SESSION['tempData']['escalation_manage']);
		}
	}

}

if(isset($_GET['escalation_id'])) {
	if(!$fruity->get_escalation($_GET['escalation_id'], $tempEscalationInfo)) {
		$invalidHost = 1;
		$status_msg = "That escalation is not valid in the database.";
		unset($_GET['escalation_id']);
	}
	else {
		// quick interation to enable values explicitly defined in this template, NOT inherited values
		if(is_array($tempEscalationInfo)) {
			foreach(array_keys($tempEscalationInfo) as $key) {
				if(isset($tempEscalationInfo[$key]))
					$_POST['escalation_manage_enablers'][$key] = '1';
			}
		}
		// special cases
		if(isset($tempEscalationInfo['escalation_options_up']) || 
				isset($tempEscalationInfo['escalation_options_down']) || 
				isset($tempEscalationInfo['escalation_options_unreachable']) || 
				isset($tempEscalationInfo['escalation_options_ok']) || 
				isset($tempEscalationInfo['escalation_options_warning']) || 
				isset($tempEscalationInfo['escalation_options_unknown']) || 
				isset($tempEscalationInfo['escalation_options_critical']))
			$_POST['escalation_manage_enablers']['escalation_options'] = 1;
	}
}

// Cute hack
if(isset($_GET['host_template_id']))
	$tempEscalationInfo['host_template_id'] = $_GET['host_template_id'];
else if(isset($_GET['host_id']))
	$tempEscalationInfo['host_id'] = $_GET['host_id'];

if(isset($_GET['service_id']))
	$tempEscalationInfo['service_id'] = $_GET['service_id'];
else if(isset($_GET['service_template_id']))
	$tempEscalationInfo['service_template_id'] = $_GET['service_template_id'];
	

if(isset($tempEscalationInfo['service_id']) || isset($tempEscalationInfo['service_template_id'])) {
	$title .= "Service ";
	if(isset($tempEscalationInfo['service_template_id'])) {
		$fruity->get_service_template_info($tempEscalationInfo['service_template_id'], $tempTitleInfo);
		$title .= "Template <i>" . $fruity->return_service_template_name($tempEscalationInfo['service_template_id']) . "</i>";
	}
	else {
		$fruity->get_service_info($tempEscalationInfo['service_id'], $tempTitleInfo);
		$title .= "<i>" . $fruity->return_service_description($tempEscalationInfo['service_id']) . "</i> On ";
	}
}		
	
if(isset($tempTitleInfo['host_template_id']) || isset($tempTitleInfo['host_id']))
	$title .= "Host ";
if(isset($tempTitleInfo['host_template_id']))
	$title .= "Template <i>" . $fruity->return_host_template_name($tempTitleInfo['host_template_id']) ."</i>";
else
	$title .= "<i>" .$fruity->return_host_name($tempTitleInfo['host_id']) ."</i>";

	
print_header("Escalation Editor for " . $title);
?>
<script language="javascript">
function form_element_switch(element, checkbox) {
        if(checkbox.checked) {
                element.readOnly = false;
                element.disabled = false;
        }
        else {
                element.readOnly = true;
                element.disabled = true;
        }
}

function enabler_switch(enabler) {
	if(enabler.value == '0') {
		enabler.value = '1';
	}
	else {
		enabler.value = '0';
	}
}
</script>
<?php
if(isset($tempEscalationInfo['host_template_id']) || (isset($tempEscalationInfo['host_id']) && !isset($tempEscalationInfo['service_id']))) {
	?>
	[ <a href="<?=$path_config['doc_root'];?><?php if(isset($tempEscalationInfo['host_template_id'])) print("host_templates.php?host_template_id=".$tempEscalationInfo['host_template_id']); else print("hosts.php?host_id=".$tempEscalationInfo['host_id']);?>&section=escalations">Return To Host <?php if(isset($tempEscalationInfo['host_template_id'])) print("Template ");?> Escalations</a> ]
	<?php
}
else {
	?>
	[ <a href="<?=$path_config['doc_root'];?><?php if(isset($tempEscalationInfo['service_template_id'])) print("service_templates.php?service_template_id=".$tempEscalationInfo['service_template_id']); else print("services.php?service_id=".$tempEscalationInfo['service_id']);?>&section=escalations">Return To Service <?php if(isset($tempEscalationInfo['service_template_id'])) print("Template ");?> Escalations</a> ]
	<?php
}
?>
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	
	// Show service information table if selected
	if($_GET['escalation_id']) {	
		print_window_header($tempEscalationInfo['escalation_description'] . " Escalation Information", "100%");	
		?>
		<a class="sublink" href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>">General</a> | <a class="sublink" href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=contactgroups">Contact Groups</a>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			if(!isset($tempEscalationInfo['service_id']) && !isset($tempEscalationInfo['service_template_id']))
				$escalation_image = $path_config['image_root'] . "server.gif";
			else
				$escalation_image = $path_config['image_root'] . "services.gif";
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$escalation_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					$host_escalation_options[0]['element_name'] = 'escalation_manage[escalation_options_up]';
					$host_escalation_options[0]['value'] = '1';
					$host_escalation_options[0]['element_title'] = 'Up';
					$host_escalation_options[1]['element_name'] = 'escalation_manage[escalation_options_down]';
					$host_escalation_options[1]['value'] = '1';
					$host_escalation_options[1]['element_title'] = 'Down';
					$host_escalation_options[2]['element_name'] = 'escalation_manage[escalation_options_unreachable]';
					$host_escalation_options[2]['value'] = '1';
					$host_escalation_options[2]['element_title'] = 'Unreachable';
					
					$service_escalation_options[0]['element_name'] = 'escalation_manage[escalation_options_ok]';
					$service_escalation_options[0]['value'] = '1';
					$service_escalation_options[0]['element_title'] = 'Ok';
					$service_escalation_options[1]['element_name'] = 'escalation_manage[escalation_options_warning]';
					$service_escalation_options[1]['value'] = '1';
					$service_escalation_options[1]['element_title'] = 'Warning';
					$service_escalation_options[2]['element_name'] = 'escalation_manage[escalation_options_unknown]';
					$service_escalation_options[2]['value'] = '1';
					$service_escalation_options[2]['element_title'] = 'Unknown';
					$service_escalation_options[3]['element_name'] = 'escalation_manage[escalation_options_critical]';
					$service_escalation_options[3]['value'] = '1';
					$service_escalation_options[3]['element_title'] = 'Critical';
					
					if($_SESSION['tempData']['escalation_manage']['escalation_options_up'])
						$host_escalation_options[0]['checked'] = 1;
					if($_SESSION['tempData']['escalation_manage']['escalation_options_down'])
						$host_escalation_options[1]['checked'] = 1;
					if($_SESSION['tempData']['escalation_manage']['escalation_options_unreachable']) 
						$host_escalation_options[2]['checked'] = 1;

					if($_SESSION['tempData']['escalation_manage']['escalation_options_ok'])
						$service_escalation_options[0]['checked'] = 1;
					if($_SESSION['tempData']['escalation_manage']['escalation_options_warning'])
						$service_escalation_options[1]['checked'] = 1;
					if($_SESSION['tempData']['escalation_manage']['escalation_options_unknown']) 
						$service_escalation_options[2]['checked'] = 1;
					if($_SESSION['tempData']['escalation_manage']['escalation_options_critical']) 
						$service_escalation_options[3]['checked'] = 1;
					?>
					<form name="escalation_manage" method="post" action="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=general&edit=1">
					<input type="hidden" name="request" value="escalation_modify_general" />
					<input type="hidden" name="escalation_manage[escalation_id]" value="<?=$_GET['escalation_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_text_form_element("#eeeeee", "escalation_manage", "escalation_manage[escalation_description]", "Escalation Description", $fruity->element_desc("escalation_description", "nagios_escalations_desc"), "40", "80", $_SESSION['tempData']['escalation_manage']['escalation_description']);
					double_pane_text_form_element_with_enabler("#f0f0f0", "escalation_manage", "escalation_manage[first_notification]", "First Notification", $fruity->element_desc("first_notification", "nagios_escalations_desc"), "2", "2", $_SESSION['tempData']['escalation_manage']['first_notification'], "first_notification", "Include In Definition"); 
					double_pane_text_form_element_with_enabler("#f0f0f0", "escalation_manage", "escalation_manage[last_notification]", "Last Notification", $fruity->element_desc("last_notification", "nagios_escalations_desc"), "2", "2", $_SESSION['tempData']['escalation_manage']['last_notification'], "last_notification", "Include In Definition"); 
					double_pane_text_form_element_with_enabler("#f0f0f0", "escalation_manage", "escalation_manage[notification_interval]", "Notification Interval", $fruity->element_desc("notification_interval", "nagios_escalations_desc"), "8", "8", $_SESSION['tempData']['escalation_manage']['notification_interval'], "notification_interval", "Include In Definition"); 
					double_pane_select_form_element_with_enabler("#eeeeee", "escalation_manage", "escalation_manage[escalation_period]", "Escalation Period", $fruity->element_desc("escalation_period", "nagios_escalations_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['escalation_manage']['escalation_period'], "escalation_period", "Include In Definition");					
					if($tempEscalationInfo['service_id'] || $tempEscalationInfo['service_template_id'])	{ // It's a service escalation
						double_pane_checkbox_group_form_element_with_enabler("#ffffff", "escalation_manage", $service_escalation_options, "Escalation Options", $fruity->element_desc("escalation_options", "nagios_escalations_desc"), "escalation_options", "Include In Definition");
					}
					else {
						double_pane_checkbox_group_form_element_with_enabler("#ffffff", "escalation_manage", $host_escalation_options, "Escalation Options", $fruity->element_desc("escalation_options", "nagios_escalations_desc"), "escalation_options", "Include In Definition");
					}
					double_pane_form_window_finish();
					?>
					

					<br />
					<br />
					<input type="submit" value="Update General" /> [ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Attached To <?php
					if(isset($tempEscalationInfo['host_id']) && !isset($tempEscalationInfo['service_id']))
						print("Host:</b> " . $fruity->return_host_name($tempEscalationInfo['host_id']));
					else if(isset($tempEscalationInfo['host_template_id']))
						print("Host Template:</b> " . $fruity->return_host_template_name($tempEscalationInfo['host_template_id']));
					else if(isset($tempEscalationInfo['service_id']))
						print("Service:</b> " . $fruity->return_service_description($tempEscalationInfo['service_id']) . " On " . $fruity->return_host_name($tempTitleInfo['host_id']));
					else if(isset($tempEscalationInfo['service_template_id']))
						print("Service Template:</b> " . $fruity->return_service_template_name($tempEscalationInfo['service_template_id']));
					?><br />
					<b>Description:</b> <?=$tempEscalationInfo['escalation_description'];?><br />
					<br />
					<b>Included In Definition:</b></br >
					<?php
					if(isset($tempEscalationInfo['first_notification'])) {
						?>
						<b>First Notification:</b> #<?=$tempEscalationInfo['first_notification'];?> Notification<br />
						<?php
					}
					if(isset($tempEscalationInfo['last_notification'])) {
						?>
						<b>Last Notification:</b> #<?=$tempEscalationInfo['last_notification'];?> Notification<br />
						<?php
					}
					if(isset($tempEscalationInfo['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$tempEscalationInfo['notification_interval'];?> Time-Units<br />
						<?php
					}
					if(isset($tempEscalationInfo['escalation_period'])) {
						?>
						<b>Escalation Period:</b> <?=$fruity->return_period_name($tempEscalationInfo['escalation_period']);?><br />
						<?php
					}
					if(isset($tempEscalationInfo['escalation_options_up']) || isset($tempEscalationInfo['escalation_options_down']) || isset($tempEscalationInfo['escalation_options_unreachable']) || isset($tempEscalationInfo['escalation_options_ok']) || isset($tempEscalationInfo['escalation_options_warning']) || isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending'])) {
						?>
						<b>Escalation Options:</b>
						<?php
						if($tempEscalationInfo['escalation_options_up']) {
							print("Up");
							if(isset($tempEscalationInfo['escalation_options_down']) || isset($tempEscalationInfo['escalation_options_unreachable']) || isset($tempEscalationInfo['escalation_options_ok']) || isset($tempEscalationInfo['escalation_options_warning']) || isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
								print(",");
						}
						if($tempEscalationInfo['escalation_options_down']) {
							print("Down");
							if(isset($tempEscalationInfo['escalation_options_unreachable']) || isset($tempEscalationInfo['escalation_options_ok']) || isset($tempEscalationInfo['escalation_options_warning']) || isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
								print(",");
						}
						if($tempEscalationInfo['escalation_options_unreachable']) {
							print("Unreachable");
							if(isset($tempEscalationInfo['escalation_options_ok']) || isset($tempEscalationInfo['escalation_options_warning']) || isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
								print(",");
						}
						if($tempEscalationInfo['escalation_options_ok']) {
							print("Ok");
								if(isset($tempEscalationInfo['escalation_options_warning']) || isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
									print(",");
						}
						if($tempEscalationInfo['escalation_options_warning']) {
							print("Warning");
								if(isset($tempEscalationInfo['escalation_options_unknown']) || isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
									print(",");
						}
						if($tempEscalationInfo['escalation_options_unknown']) {
							print("Unknown");
								if(isset($tempEscalationInfo['escalation_options_critical']) || isset($tempEscalationInfo['escalation_options_pending']))
									print(",");
						}
						if($tempEscalationInfo['escalation_options_critical']) {
							print("Critical");
								if(isset($tempEscalationInfo['escalation_options_pending']))
									print(",");
						}
						if($tempEscalationInfo['escalation_options_pending']) {
							print("Pending");
						}
						print("<br />");
					}
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			<?php				
		}
		else if($_GET['section'] == "contactgroups") {
			$fruity->return_escalation_contactgroups_list($_GET['escalation_id'], $contactgroups_list);			
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
					<td colspan="2">Contact Groups Explicitly Linked to This Escalation:</td>
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=contactgroups&request=delete&contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroups_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroups_list[$counter]['contactgroup_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php	$fruity->get_contactgroup_list( $contactgroups_list); ?>
				<br />
				<br />
				<form name="service_template_contactgroup_add" method="post" action="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$_GET['escalation_id'];?>&section=contactgroups">
				<input type="hidden" name="request" value="add_contactgroup_command" />
				<input type="hidden" name="escalation_manage[contactgroup_add][escalation_id]" value="<?=$_GET['escalation_id'];?>" />
				<b>Add New Contact Group:</b> <?php print_select("escalation_manage[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Contact Group"><br />
				<?=$fruity->element_desc("contact_groups", "nagios_services_desc"); ?><br />
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
	if($_GET['escalation_add'] && !isset($_SESSION['tempData']['escalation_manage']['target_host_id'])) {
		// Retrieve list of children
		$fruity->get_children_hosts_list($_GET['temp_host_id'], $children_list);
		$numOfChildren = count($children_list);
		print_window_header("Add A Escalation", "100%");
		?>
		<form name="service_add_form" method="post" action="<?=$path_config['doc_root'];?>escalation.php<?=$sublink;?>">
		<input type="hidden" name="request" value="add_escalation" />
		<?php
		double_pane_form_window_start(); ?>
		<tr bgcolor="eeeeee">
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" class="formcell">
			<b>Description:</b><br />
			<input type="text" size="40" name="escalation_manage[escalation_description]" value="<?=$_SESSION['tempData']['escalation_manage']['escalation_description'];?>"><br />
			<?=$fruity->element_desc("escalation_description", "nagios_escalations_desc"); ?><br />
			<br />
			</td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<?php double_pane_form_window_finish(); ?>
		<input type="submit" value="Add Escalation" />
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
