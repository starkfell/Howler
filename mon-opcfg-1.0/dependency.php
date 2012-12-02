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
 * dependency.php
 * Author:	Taylor Dondich (tdondich at gmail.com)
 * Description:
 * 	Provides interface to maintain dependencies
 *
*/
 


include_once('includes/config.inc');

// Data preparation
if(!isset($_GET['section']))
	$_GET['section'] = 'general';

	
if(!isset($_GET['temp_host_id']) && !isset($_GET['temp_service_id']) && !isset($_POST['target_host_id']) && !isset($_POST['target_service_id']))
	unset($_SESSION['tempData']['dependency_manage']);

if(isset($_GET['temp_host_id'])) {
	$_SESSION['tempData']['dependency_manage']['target_host_id'] = NULL;
}

if(isset($_GET['dependency_add'])) {
	$sublink = "?dependency_add=1";
	if(isset($_GET['host_id']))
		$sublink .= "&host_id=".$_GET['host_id'];
	if(isset($_GET['host_template_id']))
		$sublink .= "&host_template_id=".$_GET['host_template_id'];
	if(isset($_GET['service_id']))
		$sublink .= "&service_id=".$_GET['service_id'];
	if(isset($_GET['service_template_id']))
		$sublink .= "&service_template_id=".$_GET['service_template_id'];
}

// Functions
function build_navbar($host_id, &$navbar) {
	global $path_config;
	global $sys_config;
	global $fruity;
	global $sublink;
	$tempID = $host_id;
	$tempNavBar = '';
	while($tempID <> 0) {	// If anything other than the network object
		$fruity->get_host_nav_info($tempID, $tempHostInfo);
		$tempNavBar = "<a href=\"".$path_config['doc_root']."dependency.php".$sublink."&temp_host_id=".$tempID."\">".$tempHostInfo['host_name']."</a> > " . $tempNavBar;
		$tempID = $tempHostInfo['parents'];
	}
	$tempNavBar = "<a href=\"".$path_config['doc_root']."dependency.php".$sublink."\">".$sys_config['network_desc']."</a> > " . $tempNavBar;
	$navbar = $tempNavBar;
}


// If we're going to modify dependency data
if(isset($_GET['dependency_id']) && 
		$_GET['section'] == "general" && $_GET['edit']) {
	$fruity->get_dependency($_GET['dependency_id'], $_SESSION['tempData']['dependency_manage']);
}


	
// Action Handlers
if(isset($_POST['request'])) {
	if(count($_POST['dependency_manage'])) {
		foreach($_POST['dependency_manage'] as $key=>$value) {
			$_SESSION['tempData']['dependency_manage'][$key] = $value;
		}
	}
	// Enabler checks
	if(count($_POST['dependency_manage_enablers'])) {
		foreach($_POST['dependency_manage_enablers'] as $key=>$value) {
			if($value == 0) {
				$_SESSION['tempData']['dependency_manage'][$key] = NULL;
			}
		}
	}
	if($_POST['request'] == 'add_dependency') {
		// Check to see what kind of dependency we've got
		if( (isset($_GET['host_id']) || isset($_GET['host_template_id']) ) && ( !isset($_GET['service_id']) && !isset($_GET['service_template_id']) ) ){
			// We're doing a host/host template dependency
			if(isset($_GET['host_template_id'])) {
				// We're doing a template dependency
				if(!$fruity->add_host_template_dependency($_GET['host_template_id'], $_POST['target_host_id'])) {
					$status_msg = "Error: add_host_template_dependency failed.";
				}
				else {
					$tempID = $fruity->return_host_template_dependency($_GET['host_template_id'], $_POST['target_host_id']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "dependency.php?dependency_id=".$tempID);
					die();
				}
			}
			else {
				if(!$fruity->add_host_dependency($_GET['host_id'], $_POST['target_host_id'])) {
					$status_msg = "Error: add_host_dependency failed.";
				}
				else {
					$tempID = $fruity->return_host_dependency($_GET['host_id'], $_POST['target_host_id']);
					// Redirect
					header("Location: " . $path_config['doc_root'] . "dependency.php?dependency_id=".$tempID);
					die();
				}
			}
			
		}
		else {
			if(isset($_GET['service_id'])) {
				if(isset($_POST['target_host_id'])) {
					// We're at the stage of adding a host
					$_SESSION['tempData']['dependency_manage']['target_host_id'] = $_POST['target_host_id'];
				}
				else if(isset($_POST['target_service_id'])) {
					// We've chosen a service, add the dependency
					//if(!$fruity->add_service_dependency($_GET['service_id'], $_SESSION['tempData']['dependency_manage']['target_host_id'], $_POST['target_service_id'])) {
					if(!$fruity->add_service_dependency($_GET['host_id'],$_GET['service_id'], $_SESSION['tempData']['dependency_manage']['target_host_id'], $_POST['target_service_id'])) {
						$status_msg = "Error: add_service_dependency failed.";
					}
					else {
						$tempID = $fruity->return_service_dependency($_GET['service_id'], $_SESSION['tempData']['dependency_manage']['target_host_id'], $_POST['target_service_id']);
						// Redirect
						header("Location: " . $path_config['doc_root'] . "dependency.php?dependency_id=".$tempID);
						die();
					}
				}
			}
			else {
				if(isset($_POST['target_host_id'])) {
					// We're at the stage of adding a host
					$_SESSION['tempData']['dependency_manage']['target_host_id'] = $_POST['target_host_id'];
				}
				else if(isset($_POST['target_service_id'])) {
					// We've chosen a service, add the dependency
					if(!$fruity->add_service_template_dependency($_GET['service_template_id'], $_POST['target_service_id'])) {
						$status_msg = "Error: add_service_dependency failed.";
					}
					else {
						$tempID = $fruity->return_service_template_dependency($_GET['service_template_id'], $_POST['target_service_id']);
						// Redirect
						header("Location: " . $path_config['doc_root'] . "dependency.php?dependency_id=".$tempID);
						die();
					}
				}
			}
		}
	}
	else if($_POST['request'] == 'dependency_modify_general') {
		// Field Error Checking
		if(count($_SESSION['tempData']['dependency_manage'])) {
			foreach($_SESSION['tempData']['dependency_manage'] as $tempVariable)
				$tempVariable = trim($tempVariable);
		}
			
		if(!$_POST['dependency_manage_enablers']['execution_failure_criteria']) {
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_up'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_down'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unreachable'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_pending'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_ok'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_warning'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unknown'] = NULL;
			$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_critical'] = NULL;
		}
		else {
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_up']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_up'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_down']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_down'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_unreachable']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unreachable'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_pending']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_pending'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_ok']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_ok'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_warning']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_warning'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_unknown']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unknown'] = 0;
			if(!isset($_POST['dependency_manage']['execution_failure_criteria_critical']))
				$_SESSION['tempData']['dependency_manage']['execution_failure_criteria_critical'] = 0;
		}
		
		if(!$_POST['dependency_manage_enablers']['notification_failure_criteria']) {
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_up'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_down'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unreachable'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_pending'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_ok'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_warning'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unknown'] = NULL;
			$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_critical'] = NULL;
		}
		else {
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_up']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_up'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_down']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_down'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_unreachable']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unreachable'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_pending']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_pending'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_ok']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_ok'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_warning']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_warning'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_unknown']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unknown'] = 0;
			if(!isset($_POST['dependency_manage']['notification_failure_criteria_critical']))
				$_SESSION['tempData']['dependency_manage']['notification_failure_criteria_critical'] = 0;
		}
		// All is well for error checking, modify the command.
		if($fruity->modify_dependency($_SESSION['tempData']['dependency_manage'])) {
			// Remove session data
			unset($_SESSION['tempData']['dependency_manage']);
			$status_msg = "Dependency modified.";
			unset($_GET['edit']);
		}
		else {
			$status_msg = "Error: modify_dependency failed.";
		}
	}

}

if(isset($_GET['dependency_id'])) {
	if(!$fruity->get_dependency($_GET['dependency_id'], $tempDependencyInfo)) {
		$invalidHost = 1;
		$status_msg = "That dependency is not valid in the database.";
		unset($_GET['dependency']);
	}
	else {
		// quick interation to enable values explicitly defined in this template, NOT inherited values
		if(is_array($tempDependencyInfo)) {
			foreach(array_keys($tempDependencyInfo) as $key) {
				if(isset($tempDependencyInfo[$key]))
					$_POST['dependency_manage_enablers'][$key] = '1';
			}
		}
		// special cases
		if(isset($tempDependencyInfo['execution_failure_criteria_up']) || 
				isset($tempDependencyInfo['execution_failure_criteria_down']) || 
				isset($tempDependencyInfo['execution_failure_criteria_unreachable']) || 
				isset($tempDependencyInfo['execution_failure_criteria_pending']) || 
				isset($tempDependencyInfo['execution_failure_criteria_ok']) || 
				isset($tempDependencyInfo['execution_failure_criteria_warning']) || 
				isset($tempDependencyInfo['execution_failure_criteria_unknown']) || 
				isset($tempDependencyInfo['execution_failure_criteria_critical']))
			$_POST['dependency_manage_enablers']['execution_failure_criteria'] = 1;
		if(isset($tempDependencyInfo['notification_failure_criteria_up']) || 
				isset($tempDependencyInfo['notification_failure_criteria_down']) || 
				isset($tempDependencyInfo['notification_failure_criteria_unreachable']) || 
				isset($tempDependencyInfo['notification_failure_criteria_pending']) || 
				isset($tempDependencyInfo['notification_failure_criteria_ok']) || 
				isset($tempDependencyInfo['notification_failure_criteria_warning']) || 
				isset($tempDependencyInfo['notification_failure_criteria_unknown']) || 
				isset($tempDependencyInfo['notification_failure_criteria_critical']))
			$_POST['dependency_manage_enablers']['notification_failure_criteria'] = 1;
	}
}

// Cute hack
if(isset($_GET['host_template_id']))
	$tempDependencyInfo['host_template_id'] = $_GET['host_template_id'];
else if(isset($_GET['host_id']))
	$tempDependencyInfo['host_id'] = $_GET['host_id'];

if(isset($_GET['service_id']))
	$tempDependencyInfo['service_id'] = $_GET['service_id'];

	
else if(isset($_GET['service_template_id']))
	$tempDependencyInfo['service_template_id'] = $_GET['service_template_id'];
	

if(isset($tempDependencyInfo['service_id']) || isset($tempDependencyInfo['service_template_id'])) {
	$title .= "Service ";
	if(isset($tempDependencyInfo['service_template_id'])) {
		$fruity->get_service_template_info($tempDependencyInfo['service_template_id'], $tempTitleInfo);
		$title .= "Template <i>" . $fruity->return_service_template_name($tempDependencyInfo['service_template_id']) . "</i>";
	}
	else {
		$fruity->get_service_info($tempDependencyInfo['service_id'], $tempTitleInfo);
		$title .= "<i>" . $fruity->return_service_description($tempDependencyInfo['service_id']) . "</i> On ";
		if(isset($tempTitleInfo['host_id']) && !isset($tempTitleInfo['host_template_id']))
			$title .= "Host " . "<i>" .$fruity->return_host_name($tempTitleInfo['host_id']) ."</i>";;
		if(isset($tempTitleInfo['host_template_id']))
			$title .= "Host Template <i>" . $fruity->return_host_template_name($tempTitleInfo['host_template_id']) ."</i>";
	}
}		
	
if(isset($tempDependencyInfo['host_id']) && !isset($tempDependencyInfo['host_template_id']))
	$title .= "Host " . "<i>" .$fruity->return_host_name($tempDependencyInfo['host_id']) ."</i>";;
if(isset($tempDependencyInfo['host_template_id']))
	$title .= "Host Template <i>" . $fruity->return_host_template_name($tempDependencyInfo['host_template_id']) ."</i>";


	
print_header("Dependency Editor for " . $title);
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
if(isset($tempDependencyInfo['host_template_id']) || (isset($tempDependencyInfo['host_id']) && !isset($tempDependencyInfo['service_id']))) {
	?>
	[ <a href="<?=$path_config['doc_root'];?><?php if(isset($tempDependencyInfo['host_template_id'])) print("host_templates.php?host_template_id=".$tempDependencyInfo['host_template_id']); else print("hosts.php?host_id=".$tempDependencyInfo['host_id']);?>&section=dependencies">Return To Host <?php if(isset($tempDependencyInfo['host_template_id'])) print("Template ");?> Dependencies</a> ]
	<?php
}
else {
	?>
	[ <a href="<?=$path_config['doc_root'];?><?php if(isset($tempDependencyInfo['service_template_id'])) print("service_templates.php?service_template_id=".$tempDependencyInfo['service_template_id']); else print("services.php?service_id=".$tempDependencyInfo['service_id']);?>&section=dependencies">Return To Service <?php if(isset($tempDependencyInfo['service_template_id'])) print("Template ");?> Dependencies</a> ]
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
	if($_GET['dependency_id']) {	
		// Should set service dependency titlebar stuff here

		/*
		if(isset($tempDependencyInfo['service_id']) && !isset($tempDependencyInfo['service_template_id']))
			$titlebar .= "Service " . "<i>" . $fruity->return_service_description($tempDependencyInfo['service_id']) . "</i> On ";;
		if(isset($tempDependencyInfo['service_template_id'])) 
			$titlebar .= "Template <i>" . $fruity->return_service_template_name($tempDependencyInfo['service_template_id']) . "</i>";
		*/
		if(isset($tempDependencyInfo['service_id']) || isset($tempDependencyInfo['service_template_id'])) {
			$titlebar .= "Service ";
			if(isset($tempDependencyInfo['service_template_id'])) {
				$fruity->get_service_template_info($tempDependencyInfo['service_template_id'], $tempTitleInfo);
				$titlebar .= "Template <i>" . $fruity->return_service_template_name($tempDependencyInfo['service_template_id']) . "</i>";
			}
			else {
				$fruity->get_service_info($tempDependencyInfo['service_id'], $tempTitleInfo);
				$titlebar .= "<i>" . $fruity->return_service_description($tempDependencyInfo['service_id']) . "</i> On ";
				if(isset($tempTitleInfo['host_id']) && !isset($tempTitleInfo['host_template_id']))
					$titlebar .= "Host " . "<i>" .$fruity->return_host_name($tempTitleInfo['host_id']) ."</i>";;
				if(isset($tempTitleInfo['host_template_id']))
					$titlebar .= "Host Template <i>" . $fruity->return_host_template_name($tempTitleInfo['host_template_id']) ."</i>";
			}
		}	
		
		if(isset($tempDependencyInfo['host_id']))
			$titlebar .= "Host " . $fruity->return_host_name($tempDependencyInfo['host_id']);
		// Let's create the titlebar
		if(isset($tempDependencyInfo['host_template_id']))
			$titlebar .= "Template " . $fruity->return_host_template_name($tempTitleInfo['host_template_id']);

		$titlebar .= "'s Dependency On ";
		$titlebar .= $fruity->return_host_name($tempDependencyInfo['target_host_id']);
		if(isset($tempDependencyInfo['target_service_id'])) {
			$titlebar .= " : " .$fruity->return_service_description($tempDependencyInfo['target_service_id']);
		}

		print_window_header($titlebar, "100%");	
		?>
		<a class="sublink" href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$_GET['dependency_id'];?>">General</a>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			if(!isset($tempDependencyInfo['target_service_id']))
				$dependency_image = $path_config['image_root'] . "server.gif";
			else
				$dependency_image = $path_config['image_root'] . "services.gif";
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$dependency_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					$host_execution_failure_criteria[0]['element_name'] = 'dependency_manage[execution_failure_criteria_up]';
					$host_execution_failure_criteria[0]['value'] = '1';
					$host_execution_failure_criteria[0]['element_title'] = 'Up';
					$host_execution_failure_criteria[1]['element_name'] = 'dependency_manage[execution_failure_criteria_down]';
					$host_execution_failure_criteria[1]['value'] = '1';
					$host_execution_failure_criteria[1]['element_title'] = 'Down';
					$host_execution_failure_criteria[2]['element_name'] = 'dependency_manage[execution_failure_criteria_unreachable]';
					$host_execution_failure_criteria[2]['value'] = '1';
					$host_execution_failure_criteria[2]['element_title'] = 'Unreachable';
					$host_execution_failure_criteria[3]['element_name'] = 'dependency_manage[execution_failure_criteria_pending]';
					$host_execution_failure_criteria[3]['value'] = '1';
					$host_execution_failure_criteria[3]['element_title'] = 'Pending';
					
					$service_execution_failure_criteria[0]['element_name'] = 'dependency_manage[execution_failure_criteria_ok]';
					$service_execution_failure_criteria[0]['value'] = '1';
					$service_execution_failure_criteria[0]['element_title'] = 'Ok';
					$service_execution_failure_criteria[1]['element_name'] = 'dependency_manage[execution_failure_criteria_warning]';
					$service_execution_failure_criteria[1]['value'] = '1';
					$service_execution_failure_criteria[1]['element_title'] = 'Warning';
					$service_execution_failure_criteria[2]['element_name'] = 'dependency_manage[execution_failure_criteria_unknown]';
					$service_execution_failure_criteria[2]['value'] = '1';
					$service_execution_failure_criteria[2]['element_title'] = 'Unknown';
					$service_execution_failure_criteria[3]['element_name'] = 'dependency_manage[execution_failure_criteria_critical]';
					$service_execution_failure_criteria[3]['value'] = '1';
					$service_execution_failure_criteria[3]['element_title'] = 'Critical';
					$service_execution_failure_criteria[4]['element_name'] = 'dependency_manage[execution_failure_criteria_pending]';
					$service_execution_failure_criteria[4]['value'] = '1';
					$service_execution_failure_criteria[4]['element_title'] = 'Pending';
					
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_up'])
						$host_execution_failure_criteria[0]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_down'])
						$host_execution_failure_criteria[1]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unreachable']) 
						$host_execution_failure_criteria[2]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_pending']) {
						$host_execution_failure_criteria[3]['checked'] = 1;
						$service_execution_failure_criteria[4]['checked'] = 1;
					}

					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_ok'])
						$service_execution_failure_criteria[0]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_warning'])
						$service_execution_failure_criteria[1]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_unknown']) 
						$service_execution_failure_criteria[2]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['execution_failure_criteria_critical']) 
						$service_execution_failure_criteria[3]['checked'] = 1;

					$host_notification_failure_criteria[0]['element_name'] = 'dependency_manage[notification_failure_criteria_up]';
					$host_notification_failure_criteria[0]['value'] = '1';
					$host_notification_failure_criteria[0]['element_title'] = 'Up';
					$host_notification_failure_criteria[1]['element_name'] = 'dependency_manage[notification_failure_criteria_down]';
					$host_notification_failure_criteria[1]['value'] = '1';
					$host_notification_failure_criteria[1]['element_title'] = 'Down';
					$host_notification_failure_criteria[2]['element_name'] = 'dependency_manage[notification_failure_criteria_unreachable]';
					$host_notification_failure_criteria[2]['value'] = '1';
					$host_notification_failure_criteria[2]['element_title'] = 'Unreachable';
					$host_notification_failure_criteria[3]['element_name'] = 'dependency_manage[notification_failure_criteria_pending]';
					$host_notification_failure_criteria[3]['value'] = '1';
					$host_notification_failure_criteria[3]['element_title'] = 'Pending';
					
					$service_notification_failure_criteria[0]['element_name'] = 'dependency_manage[notification_failure_criteria_ok]';
					$service_notification_failure_criteria[0]['value'] = '1';
					$service_notification_failure_criteria[0]['element_title'] = 'Ok';
					$service_notification_failure_criteria[1]['element_name'] = 'dependency_manage[notification_failure_criteria_warning]';
					$service_notification_failure_criteria[1]['value'] = '1';
					$service_notification_failure_criteria[1]['element_title'] = 'Warning';
					$service_notification_failure_criteria[2]['element_name'] = 'dependency_manage[notification_failure_criteria_unknown]';
					$service_notification_failure_criteria[2]['value'] = '1';
					$service_notification_failure_criteria[2]['element_title'] = 'Unknown';
					$service_notification_failure_criteria[3]['element_name'] = 'dependency_manage[notification_failure_criteria_critical]';
					$service_notification_failure_criteria[3]['value'] = '1';
					$service_notification_failure_criteria[3]['element_title'] = 'Critical';
					$service_notification_failure_criteria[4]['element_name'] = 'dependency_manage[notification_failure_criteria_pending]';
					$service_notification_failure_criteria[4]['value'] = '1';
					$service_notification_failure_criteria[4]['element_title'] = 'Pending';
						
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_up'])
						$host_notification_failure_criteria[0]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_down'])
						$host_notification_failure_criteria[1]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unreachable']) 
						$host_notification_failure_criteria[2]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_pending']) {
						$host_notification_failure_criteria[3]['checked'] = 1;
						$service_notification_failure_criteria[4]['checked'] = 1;
					}
					
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_ok'])
						$service_notification_failure_criteria[0]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_warning'])
						$service_notification_failure_criteria[1]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_unknown']) 
						$service_notification_failure_criteria[2]['checked'] = 1;
					if($_SESSION['tempData']['dependency_manage']['notification_failure_criteria_pending']) 
						$service_notification_failure_criteria[3]['checked'] = 1;
					
					?>
					<form name="dependency_manage" method="post" action="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$_GET['dependency_id'];?>&section=general&edit=1">
					<input type="hidden" name="request" value="dependency_modify_general" />
					<input type="hidden" name="dependency_manage[dependency_id]" value="<?=$_GET['dependency_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#eeeeee", "dependency_manage", "dependency_manage[inherits_parent]", "Inherits Parents", $fruity->element_desc("inherits_parent", "nagios_dependency_desc"), $enable_list, "values", "text", $_SESSION['tempData']['dependency_manage']['inherits_parent'], "inherits_parent", "Include In Definition");
					if($tempDependencyInfo['target_service_id'])	{ // It's a service dependency
						double_pane_checkbox_group_form_element_with_enabler("#ffffff", "dependency_manage", $service_execution_failure_criteria, "Execution Failure Criteria", $fruity->element_desc("service_execution_failure_criteria", "nagios_dependency_desc"), "execution_failure_criteria", "Include In Definition");
						double_pane_checkbox_group_form_element_with_enabler("#eeeeee", "dependency_manage", $service_notification_failure_criteria, "Notification Failure Criteria", $fruity->element_desc("service_notification_failure_criteria", "nagios_dependency_desc"), "notification_failure_criteria", "Include In Definition");
					}
					else {
						double_pane_checkbox_group_form_element_with_enabler("#ffffff", "dependency_manage", $host_execution_failure_criteria, "Execution Failure Criteria", $fruity->element_desc("host_execution_failure_criteria", "nagios_dependency_desc"), "execution_failure_criteria", "Include In Definition");
						double_pane_checkbox_group_form_element_with_enabler("#eeeeee", "dependency_manage", $host_notification_failure_criteria, "Notification Failure Criteria", $fruity->element_desc("host_notification_failure_criteria", "nagios_dependency_desc"), "notification_failure_criteria", "Include In Definition");
					}
					double_pane_form_window_finish();
					?>
					

					<br />
					<br />
					<input type="submit" value="Update General" /> [ <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$_GET['dependency_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b></br >
					<?php
					if(isset($tempDependencyInfo['inherits_parent'])) {
						?>
						<b>Inherits Parent:</b> <? if($tempDependencyInfo['inherits_parent']) print("Yes"); else print("No");?><br />
						<?php
					}
					if(isset($tempDependencyInfo['execution_failure_criteria_up']) || isset($tempDependencyInfo['execution_failure_criteria_down']) || isset($tempDependencyInfo['execution_failure_criteria_unreachable']) || isset($tempDependencyInfo['execution_failure_criteria_ok']) || isset($tempDependencyInfo['execution_failure_criteria_warning']) || isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending'])) {
						?>
						<b>Execution Failure Criteria On:</b>
						<?php
						if($tempDependencyInfo['execution_failure_criteria_up']) {
							print("Up");
							if(isset($tempDependencyInfo['execution_failure_criteria_down']) || isset($tempDependencyInfo['execution_failure_criteria_unreachable']) || isset($tempDependencyInfo['execution_failure_criteria_ok']) || isset($tempDependencyInfo['execution_failure_criteria_warning']) || isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_down']) {
							print("Down");
							if(isset($tempDependencyInfo['execution_failure_criteria_unreachable']) || isset($tempDependencyInfo['execution_failure_criteria_ok']) || isset($tempDependencyInfo['execution_failure_criteria_warning']) || isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_unreachable']) {
							print("Unreachable");
							if(isset($tempDependencyInfo['execution_failure_criteria_ok']) || isset($tempDependencyInfo['execution_failure_criteria_warning']) || isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_ok']) {
							print("Ok");
								if(isset($tempDependencyInfo['execution_failure_criteria_warning']) || isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_warning']) {
							print("Warning");
								if(isset($tempDependencyInfo['execution_failure_criteria_unknown']) || isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_unknown']) {
							print("Unknown");
								if(isset($tempDependencyInfo['execution_failure_criteria_critical']) || isset($tempDependencyInfo['execution_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_critical']) {
							print("Critical");
								if(isset($tempDependencyInfo['execution_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['execution_failure_criteria_pending']) {
							print("Pending");
						}
						print("<br />");
					}
					if(isset($tempDependencyInfo['notification_failure_criteria_up']) || isset($tempDependencyInfo['notification_failure_criteria_down']) || isset($tempDependencyInfo['notification_failure_criteria_unreachable']) || isset($tempDependencyInfo['notification_failure_criteria_ok']) || isset($tempDependencyInfo['notification_failure_criteria_warning']) || isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending'])) {
						?>
						<b>Notification Failure Criteria On:</b>
						<?php
						if($tempDependencyInfo['notification_failure_criteria_up']) {
							print("Up");
							if(isset($tempDependencyInfo['notification_failure_criteria_down']) || isset($tempDependencyInfo['notification_failure_criteria_unreachable']) || isset($tempDependencyInfo['notification_failure_criteria_ok']) || isset($tempDependencyInfo['notification_failure_criteria_warning']) || isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_down']) {
							print("Down");
							if(isset($tempDependencyInfo['notification_failure_criteria_unreachable']) || isset($tempDependencyInfo['notification_failure_criteria_ok']) || isset($tempDependencyInfo['notification_failure_criteria_warning']) || isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_unreachable']) {
							print("Unreachable");
							if(isset($tempDependencyInfo['notification_failure_criteria_ok']) || isset($tempDependencyInfo['notification_failure_criteria_warning']) || isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
								print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_ok']) {
							print("Ok");
								if(isset($tempDependencyInfo['notification_failure_criteria_warning']) || isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_warning']) {
							print("Warning");
								if(isset($tempDependencyInfo['notification_failure_criteria_unknown']) || isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_unknown']) {
							print("Unknown");
								if(isset($tempDependencyInfo['notification_failure_criteria_critical']) || isset($tempDependencyInfo['notification_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_critical']) {
							print("Critical");
								if(isset($tempDependencyInfo['notification_failure_criteria_pending']))
									print(",");
						}
						if($tempDependencyInfo['notification_failure_criteria_pending']) {
							print("Pending");
						}
						print("<br />");
					}
					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$_GET['dependency_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<br />
			<?php				
		}
		print_window_footer();
		?>
		<br />
		<br />
		<?php
	}
	if($_GET['dependency_add'] && !isset($_SESSION['tempData']['dependency_manage']['target_host_id'])) {
		// Build the navigation bar
		build_navbar($_GET['temp_host_id'], $navbar);
		// Retrieve list of children
		$fruity->get_children_hosts_list($_GET['temp_host_id'], $children_list);
		$numOfChildren = count($children_list);
		
		print_window_header("Select A Host To Be Dependent Upon", "100%");
		print($navbar);
		if($_GET['temp_host_id'] != 0) {
			?>
			<div align="center">
			Currently Selected: <?=$fruity->return_host_name($_GET['temp_host_id']);?><br />
			<form name="temp_host_select" action="<?=$path_config['doc_root'];?>dependency.php<?=$sublink;?>" method="post">
			<input type="hidden" name="request" value="add_dependency">
			<input type="hidden" name="target_host_id" value="<?=$_GET['temp_host_id'];?>" />
			<input type="submit" value="Choose This Host" />
			</form>
			<br />
			</div>
			<?php
		}
		print("<br />");
		if($numOfChildren) {
			?>
			<div align="center"><b>Children Hosts:</b></div><br />
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>dependency.php<?=$sublink;?>&temp_host_id=<?=$children_list[$counter]['host_id'];?>"><?=$children_list[$counter]['host_name'];?></a> <? $numOfSubChildren = $fruity->return_num_of_children($children_list[$counter]['host_id']); if($numOfSubChildren) print("(".$numOfSubChildren.")");?></td>
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
			<br />
			<div class="statusmsg">No Children Hosts Exists At This Level</div>
			<br />
			<?php
		}
		print_window_footer();
		?>
		<br />
		<?php
	}
	elseif(isset($_GET['dependency_add'])) {	// We're doing a service dependency, so let's show services.
		$fruity->get_host_info($_SESSION['tempData']['dependency_manage']['target_host_id'], $tempHostInfo);
		
		// Build the navigation bar
		build_navbar($_SESSION['tempData']['dependency_manage']['target_host_id'], $navbar);
		// Retrieve list of children
		// First get list of template services
		if(isset($tempHostInfo['use_template_id'])) {
			$fruity->get_host_template_inherited_services_list($tempHostInfo['use_template_id'], $inherited_list);
			$numOfInheritedServices = count($inherited_list);
		}
		

		$fruity->get_host_services_list($_SESSION['tempData']['dependency_manage']['target_host_id'], $children_list);
		$numOfChildren = count($children_list);
		
		print_window_header("Select A Service To Be Dependent Upon", "100%");
		print($navbar);
		if($_GET['temp_service_id'] != 0) {
			?>
			<div align="center">
			Currently Selected: <?=$fruity->return_service_description($_GET['temp_service_id']);?><br />
			<form name="temp_host_select" action="<?=$path_config['doc_root'];?>dependency.php<?=$sublink;?>" method="post">
			<input type="hidden" name="request" value="add_dependency">
			<input type="hidden" name="target_service_id" value="<?=$_GET['temp_service_id'];?>" />
			<input type="submit" value="Choose This Service" />
			</form>
			<br />
			</div>
			<?php
		}
		print("<br />");
		if($numOfChildren || $numOfInheritedServices) {
			?>
			<div align="center"><b>Services For This Host:</b></div><br />
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Service Description</td>
			</tr>
			<?php
			if($numOfInheritedServices) {
				foreach($inherited_list as $service) {
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
					<td height="20" class="altSides">&nbsp;<b><a href="<?=$path_config['doc_root'];?>dependency.php<?=$sublink;?>&temp_service_id=<?=$service['service_id'];?>"><?=$service['service_description'];?></a></b> from <b><?=$fruity->return_host_template_name($service['host_template_id']);?></b></td>
					</tr>
					<?php
					$counter++;
				}
			}
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
				<td colspan="2" height="20" class="altSides">&nbsp;<a href="<?=$path_config['doc_root'];?>dependency.php<?=$sublink;?>&temp_service_id=<?=$children_list[$counter]['service_id'];?>"><?=$fruity->return_service_description($children_list[$counter]['service_id']);?></a></td>
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
			<div class="statusmsg">No Services Exists For This Host</div>
			<br />
			<?php
		}
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
