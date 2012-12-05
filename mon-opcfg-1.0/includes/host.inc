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
	Filename: host.inc
	Description:
	The class definition for the host object
*/

class hostObject extends baseObject{
	private $host_id;	// What host_id are we going to work with?
	private $tempHostInfo;
	private $tempInheritedValues;
	private $tempHostExtendedInfo;
	private $tempInheritedValuesSources;
	function __construct() {
		$this->myName = "host";
		parent::__construct();
	}
	
	public function init() {
		// does nothing
	}
	
	public function restart() {
		$this->reload();
	}
	
	public function reload($host_id = null) {
		global $fruity;
		// Reset values
		$this->host_id = null;
		$this->tempHostInfo = null;
		// SF BUG# 1445803
		// templating error with fruity 1.0rc
		$this->tempInheritedValues = array();
		$this->tempHostExtendedInfo = null;
		$this->tempInheritedValuesSources = array();
		
		if($host_id != NULL) {
			// We have a host_id, so let's initialize our data
			if(!$fruity->get_host_info($_GET['host_id'], $this->tempHostInfo)) {
				return false;
			}
			else {
				// Check to see if we inherit from another template
				if(isset($this->tempHostInfo['use_template_id'])) {
					// Then we actually use a template
					// We now need to obtain the inherited values
					$result = $fruity->get_inherited_host_template_values($this->tempHostInfo['use_template_id'], $this->tempInheritedValues, $this->tempInheritedValuesSources);
					
					// We need to load up $_SESSION with our inherited values.
					if(count($this->tempInheritedValues)) {
						foreach($this->tempInheritedValues as $key=>$value) {
							if(isset($this->tempInheritedValues[$key]) && !isset($this->tempHostInfo[$key])) {
								$_SESSION['tempData']['host_manage'][$key] = $value;
							}
						}
					}
				}
				
				
				// quick interation to enable values explicitly defined in this template, NOT inherited values
				if(count(array_keys($this->tempHostInfo))) {
					foreach(array_keys($this->tempHostInfo) as $key) {
						if(isset($this->tempHostInfo[$key]))
							$_POST['host_manage_enablers'][$key] = '1';
					}
				}
				// special cases
				if(isset($this->tempHostInfo['notification_options_down']) || isset($this->tempHostInfo['notification_options_unreachable']) || isset($this->tempHostInfo['notification_options_recovery']) || isset($this->tempHostInfo['notification_options_flapping']))
					$_POST['host_manage_enablers']['notification_options'] = 1;
				if(isset($this->tempHostInfo['stalking_options_up']) || isset($this->tempHostInfo['stalking_options_down']) || isset($this->tempHostInfo['stalking_options_unreachable']))
					$_POST['host_manage_enablers']['stalking_options'] = 1;
			}
		
			if($fruity->get_host_extended_info($_GET['host_id'], $this->tempHostExtendedInfo)) {
				if($this->tempHostExtendedInfo != NULL) {
					foreach(array_keys($this->tempHostExtendedInfo) as $key) {
						if(isset($this->tempHostExtendedInfo[$key]))
							$_POST['host_manage_enablers'][$key] = '1';
					}
				}
				
				if(isset($this->tempHostInfo['use_template_id'])) {
						$result = $fruity->get_inherited_host_template_extended_values($this->tempHostInfo['use_template_id'], $this->tempInheritedValues, $this->tempInheritedValuesSources);
						// Let's load up $_SESSION
						if(count($this->tempInheritedValues)) {
							foreach($this->tempInheritedValues as $key=>$value) {
								if(isset($this->tempInheritedValues[$key]) && !isset($this->tempHostExtendedInfo[$key])) {
									$_SESSION['tempData']['host_manage'][$key] = $value;
								}
							}
						}
				}
				
				
				// Okay, now let's overwrite those values with the ones from tempHostTemplateExtendedInfo
				if(isset($this->tempHostExtendedInfo)) {
					foreach($this->tempHostExtendedInfo as $key=>$value) {
						if(isset($this->tempHostExtendedInfo[$key]))
							$_SESSION['tempData']['host_manage'][$key] = $value;
					}
				}
			}
	
		}
	}		
	
	function render() {
		global $fruity;
		global $enable_list;
		global $path_config;
		global $sys_config;
		
		// To create a "default" command
		$command_list[] = array("command_id" => 0, "command_name" => "None");
		$fruity->return_command_list($command_list);
		
		$fruity->return_period_list($period_list);
		print_window_header("Host Info for " . $this->tempHostInfo['host_name'], "100%");
		?>
		&nbsp;
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">General</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checks">Checks</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=flapping">Flapping</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=logging">Logging</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=notifications">Notifications</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=services">Services</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=groups">Group Membership</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=contactgroups">Host Contact Groups</a> |
<a href="<?=$path_config['doc_root'];?>update_multiple_service_contactgroups.php?host_id=<?=$_GET['host_id'];?>">All Host Services Contact Groups</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=extended">Extended Information</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=dependencies">Dependencies</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=escalations">Escalations</a>

<?php if(isset($this->tempHostInfo['parents'])) { ?> | <a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=parents">Additional Parents</a><? }?><?php if(isset($this->tempHostInfo['check_command']) || isset($this->tempInheritedValuesSources['check_command'])) { ?> | <a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checkcommand">Check Command Parameters</a><?php } ?>
		<br />
		<br />
		<?php
		if($_GET['section'] == 'general') {
			if($fruity->get_host_template_icon_image($_GET['host_id'], $host_icon_image)) {
				$host_template_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_template_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_template_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general&edit=1">
					<input type="hidden" name="request" value="host_modify_general" />
					<input type="hidden" name="host_id" value="<?=$_GET['host_id'];?>">
					<b>Host Name:</b><br />
					<input type="text" size="40" name="host_manage[host_name]" value="<?=$_SESSION['tempData']['host_manage']['host_name'];?>" onblur="this.value=changeCharCode(this.value);"><br />
					<?=$fruity->element_desc("host_name", "nagios_hosts_desc"); ?><br />
					<br />
					<b>Address:</b><br />
					<input type="text" size="40" name="host_manage[address]" value="<?=$_SESSION['tempData']['host_manage']['address'];?>"><br />
					<?=$fruity->element_desc("address", "nagios_hosts_desc"); ?><br />
					<br />
					<b>Description:</b><br />
					<input type="text" size="80" name="host_manage[alias]" value="<?=stripslashes($_SESSION['tempData']['host_manage']['alias']);?>"><br />
					<?=$fruity->element_desc("alias", "nagios_hosts_desc"); ?><br />
					<br />
					<b>SNMP Community:</b><br />
					<input type="text" size="80" name="host_manage[community]" value="<?=stripslashes($_SESSION['tempData']['host_manage']['community']);?>"><br />
					<?=$fruity->element_desc("community", "nagios_hosts_desc"); ?><br />
					<br />
					<b>SNMP Port:</b><br />
					<input type="text" size="80" name="host_manage[snmp_port]" value="<?=stripslashes($_SESSION['tempData']['host_manage']['snmp_port']);?>"><br />
					<?=$fruity->element_desc("snmp_port", "nagios_hosts_desc"); ?><br />
					<br />


<!-- Begin Template and Parent Changer -->
					<?php
						$add_template_list[] = array("host_template_id" => '', "template_name" => "None");
						$fruity->get_host_template_list( $template_list);
						
						if(count($template_list)) {
							foreach($template_list as $tempTemplate) {
								$add_template_list[] = $tempTemplate;
							}
						}
				
						$add_host_list[] = array("host_id" => '', "host_name" => "None");
						$fruity->get_host_list( $host_list);
						
						if(count($host_list)) {
							foreach($host_list as $tempHostlist) {
								if ($tempHostlist[host_id] != $this->tempHostInfo['host_id'])
								{
									$add_host_list[] = $tempHostlist;
								}
							}
						}
					?>
					<b>Uses Host Template:</b><br />
					<?php print_select("host_manage[use_template_id]", $add_template_list, "host_template_id", "template_name", $this->tempHostInfo['use_template_id']);?><br />
					If this template is to inherit from another template, specify that template's name now.
					<br /><br />
					<b>Parent:</b><br />
					<?php print_select("host_manage[parents]", $add_host_list, "host_id", "host_name", $this->tempHostInfo['parents']);?><br />
					If this host has a parent, specify that parent's name now.
					<br />

<!-- End Template and Parent Changer -->
					<br />
					<input type="submit" value="Update General" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Host Name:</b> <?=$this->tempHostInfo['host_name'];?><br />
					<b>Address:</b> <?=$this->tempHostInfo['address'];?><br />
					<b>Description:</b> <?=stripslashes($this->tempHostInfo['alias']);?><br />
					<b>SNMP Community:</b> <?=stripslashes($this->tempHostInfo['community']);?><br />
					<b>SNMP Port:</b> <?=stripslashes($this->tempHostInfo['snmp_port']);?><br />
					<?php
					if(isset($this->tempHostInfo['use_template_id'])) {
						?>
						<b>Inherits From:</b> <?=$fruity->return_host_template_name($this->tempHostInfo['use_template_id']);?><br />
						<?php
					}
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>

			<div id="duplicatingHost">
				<div class="hd"></div>
				<div class="bd">
					<center><img src='<?=$path_config['doc_root'];?>/ajax_loader.gif'>Creating new host...</center>
				</div>
			</div>

			<div id="duplicateHost">
				<div class="hd">Duplicate Host</div>
				<div class="bd">
					<form method="POST" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&request=duplicate">
						<table border='0'>
							<tr>
								<td><label for="new_host_name">New Host Name:</label></td>
								<td><input type="textbox" name="new_host_name" onblur="this.value=changeCharCode(this.value);"/></td>
							</tr>
							<tr>
								<td><label for="new_description">Description:</label></td>
								<td><input type="textbox" name="new_description" /></td>
							</tr>
							<tr>
								<td><label for="new_address">Address:</label></td>
								<td><input type="textbox" name="new_address"/></td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			
			
			<div id="creatingTemplate">
				<div class="hd"></div>
				<div class="bd">
					<center><img src='<?=$path_config['doc_root'];?>ajax_loader.gif'>Creating new template...</center>
				</div>
			</div>

			<div id="createTemplate">
				<div class="hd">Create Template From This Host</div>
				<div class="bd">
					<form method="POST" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&request=create_template">
						<table border='0'>
							<tr>
								<td><label for="new_template_name">New Template Name:</label></td>
								<td><input type="textbox" name="new_template_name" onblur="this.value=changeCharCode(this.value);"/></td>
							</tr>
							<tr>
								<td><label for="new_template_description">Description:</label></td>
								<td><input type="textbox" name="new_template_description" /></td>
							</tr>
						</table>
					</form>
				</div>
			</div>

			<script>
				showDuplicateHostDialog();
				showCreateTemplateDialog();
			</script>

			<br />
			<?php
			
				/*$reports = getReportsByObjcet( $fruity->return_host_name($_GET['host_id']) );
				if ($reports !== false)
					$reports = "'$reports'";*/
				$reports = false;
					
			?>
			
			
			[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&request=delete" onClick="javascript:return confirmDelete(<?=$reports;?>);">Delete This Host</a> ] | 
			[ <a href="#" id="duplicateHostButton">Duplicate This Host</a> ] | 
			[ <a href="#" id="createTemplateButton">Create Template From This Host</a> ] 
			<?php
		}
		if($_GET['section'] == 'checks') {
			if($fruity->get_host_icon_image($_GET['host_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing checks information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checks&edit=1">
					<input type="hidden" name="request" value="host_modify_checks" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_id'];?>">
					<?php 
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[active_checks_enabled]", "Active Checks", $fruity->element_desc("active_checks_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['active_checks_enabled'], "active_checks_enabled", (isset($this->tempInheritedValuesSources['active_checks_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[passive_checks_enabled]", "Passive Checks", $fruity->element_desc("passive_checks_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['passive_checks_enabled'], "passive_checks_enabled", (isset($this->tempInheritedValuesSources['passive_checks_enabled']) ? "Override Inherited Value" : "Include In Definition"));			
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[check_period]", "Check Period", $fruity->element_desc("check_period", "nagios_hosts_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['host_manage']['check_period'], "check_period", (isset($this->tempInheritedValuesSources['check_period']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_command]", "Check Command", $fruity->element_desc("check_command", "nagios_hosts_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['host_manage']['check_command'], "check_command", (isset($this->tempInheritedValuesSources['check_command']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[max_check_attempts]", "Maximum Check Attempts", $fruity->element_desc("max_check_attempts", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['max_check_attempts'], "max_check_attempts", (isset($this->tempInheritedValuesSources['max_check_attempts']) ? "Override Inherited Value" : "Include In Definition")); 
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_interval]", "Check Interval In Time-Units", $fruity->element_desc("check_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['check_interval'], "check_interval", (isset($this->tempInheritedValuesSources['check_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[retry_interval]", "Retry Interval In Time-Units", $fruity->element_desc("retry_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['retry_interval'], "retry_interval", (isset($this->tempInheritedValuesSources['retry_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[obsess_over_host]", "Obsess Over Host", $fruity->element_desc("obsess_over_host", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['obsess_over_host'], "obsess_over_host", (isset($this->tempInheritedValuesSources['obsess_over_host']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[check_freshness]", "Check Freshness", $fruity->element_desc("check_freshness", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['check_freshness'], "check_freshness", (isset($this->tempInheritedValuesSources['check_freshness']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[freshness_threshold]", "Freshness Threshold in Seconds", $fruity->element_desc("freshness_threshold", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['freshness_threshold'], "freshness_threshold", (isset($this->tempInheritedValuesSources['freshness_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[event_handler]", "Event Handler", $fruity->element_desc("event_handler", "nagios_hosts_desc"), $command_list, "command_id", "command_name", $_SESSION['tempData']['host_manage']['event_handler'], "event_handler", (isset($this->tempInheritedValuesSources['event_handler']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[event_handler_enabled]", "Event Handler Enabled", $fruity->element_desc("event_handler_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['event_handler_enabled'], "event_handler_enabled", (isset($this->tempInheritedValuesSources['event_handler_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[failure_prediction_enabled]", "Failure Prediction", $fruity->element_desc("failure_prediction_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['failure_prediction_enabled'], "failure_prediction_enabled", (isset($this->tempInheritedValuesSources['failure_prediction_enabled']) ? "Override Inherited Value" : "Include In Template"));
					double_pane_form_window_finish();
					?>					
					<br />
					<input type="submit" value="Update Checks" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">Cancel</a> ]
					</form>
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($this->tempHostInfo['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($this->tempHostInfo['active_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['active_checks_enabled'])) {
						?>
						<b>Active Checks:</b> <? if($this->tempInheritedValues['active_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['active_checks_enabled'];?></i><br />
						<?php
					}
					if(isset($this->tempHostInfo['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($this->tempHostInfo['passive_checks_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['passive_checks_enabled'])) {
						?>
						<b>Passive Checks:</b> <? if($this->tempInheritedValues['passive_checks_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['passive_checks_enabled'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($this->tempHostInfo['check_period']);?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['check_period'])) {
						?>
						<b>Check Period:</b> <?=$fruity->return_period_name($this->tempInheritedValues['check_period']);?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['check_period'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_host_command($this->tempHostInfo['host_id'])); ?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['check_command'])) {
						?>
						<b>Check Command:</b> <? print_command( $fruity->return_host_command($this->tempHostInfo['host_id'])); ?> <i>(See </i><b>Check Command Parameters</b><i> for parameter inheritance information)</i><br />
						<?php
					}						
					if(isset($this->tempHostInfo['max_check_attempts'])) {
						?>
						<b>Maximum Check Attempts:</b> <?=$this->tempHostInfo['max_check_attempts'];?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['max_check_attempts'])) {
						?>
						<b>Maximum check Attempts:</b> <?=$this->tempInheritedValues['max_check_attempts'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['max_check_attempts'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['check_interval'])) {
						?>
						<b>Check Interval:</b> <?=$this->tempHostInfo['check_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['check_interval'])) {
						?>
						<b>Check Interval:</b> <?=$this->tempInheritedValues['check_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['check_interval'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['retry_interval'])) {
						?>
						<b>Retry Interval:</b> <?=$this->tempHostInfo['retry_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['retry_interval'])) {
						?>
						<b>Retry Interval:</b> <?=$this->tempInheritedValues['retry_interval'];?> Time-Units <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['retry_interval'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['obsess_over_host'])) {
						?>
						<b>Obsess Over Host:</b> <? if($this->tempHostInfo['obsess_over_host']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['obsess_over_host'])) {
						?>
						<b>Obsess Over Host:</b> <? if($this->tempInheritedValues['obsess_over_host']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['obsess_over_host'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($this->tempHostInfo['check_freshness']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['check_freshness'])) {
						?>
						<b>Check Freshness:</b> <? if($this->tempInheritedValues['check_freshness']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['check_freshness'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$this->tempHostInfo['freshness_threshold'];?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['freshness_threshold'])) {
						?>
						<b>Freshness Threshold:</b> <?=$this->tempInheritedValues['freshness_threshold'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['freshness_threshold'];?></i><br />
						<?php
					}						
					if(isset($this->tempHostInfo['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($this->tempHostInfo['event_handler']);?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['event_handler'])) {
						?>
						<b>Event Handler Command:</b> <?=$fruity->return_command_name($this->tempInheritedValues['event_handler']);?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['event_handler'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($this->tempHostInfo['event_handler_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['event_handler_enabled'])) {
						?>
						<b>Event Handler:</b> <? if($this->tempInheritedValues['event_handler_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['event_handler_enabled'];?></i><br />
						<?php
					}	
					if(isset($this->tempHostInfo['failure_prediction_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($this->tempHostInfo['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['event_handler_enabled'])) {
						?>
						<b>Failure Prediction:</b> <? if($this->tempInheritedValues['failure_prediction_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['failure_prediction_enabled'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checks&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'flapping') {
			if($fruity->get_host_icon_image($_GET['host_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=flapping&edit=1">
					<input type="hidden" name="request" value="host_modify_flapping" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[flap_detection_enabled]", "Flap Detection", $fruity->element_desc("flap_detection_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['flap_detection_enabled'], "flap_detection_enabled", (isset($this->tempInheritedValuesSources['flap_detection_enabled']) ? "Override Inherited Value" : "Include In Definition"));					
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[low_flap_threshold]", "Low Flap Threshold", $fruity->element_desc("low_flap_threshold", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['low_flap_threshold'], "low_flap_threshold", (isset($this->tempInheritedValuesSources['low_flap_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[high_flap_threshold]", "High Flap Threshold", $fruity->element_desc("high_flap_threshold", "nagios_hosts_desc"), "4", "4", $_SESSION['tempData']['host_manage']['high_flap_threshold'], "high_flap_threshold", (isset($this->tempInheritedValuesSources['high_flap_threshold']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Flapping" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($this->tempHostInfo['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($this->tempHostInfo['flap_detection_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['flap_detection_enabled'])) {
						?>
						<b>Flap Detection:</b> <? if($this->tempInheritedValues['flap_detection_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['flap_detection_enabled'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$this->tempHostInfo['low_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['low_flap_threshold'])) {
						?>
						<b>Low Flap Threshold:</b> <?=$this->tempInheritedValues['low_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['low_flap_threshold'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$this->tempHostInfo['high_flap_threshold'];?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['high_flap_threshold'])) {
						?>
						<b>High Flap Threshold:</b> <?=$this->tempInheritedValues['high_flap_threshold'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['high_flap_threshold'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=flapping&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'logging') {
			if($fruity->get_host_icon_image($_GET['host_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=logging&edit=1">
					<input type="hidden" name="request" value="host_modify_logging" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[process_perf_data]", "Process Performance Data", $fruity->element_desc("process_perf_data", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['process_perf_data'], "process_perf_data", (isset($this->tempInheritedValuesSources['process_perf_data']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[retain_status_information]", "Retain Status Information", $fruity->element_desc("retain_status_information", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['retain_status_information'], "retain_status_information", (isset($this->tempInheritedValuesSources['retain_status_information']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[retain_nonstatus_information]", "Retain Non-Status Information", $fruity->element_desc("retain_nonstatus_information", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['retain_nonstatus_information'], "retain_nonstatus_information", (isset($this->tempInheritedValuesSources['retain_nonstatus_information']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<input type="submit" value="Update Logging" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($this->tempHostInfo['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($this->tempHostInfo['process_perf_data']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['process_perf_data'])) {
						?>
						<b>Process Performance Data:</b> <? if($this->tempInheritedValues['process_perf_data']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['process_perf_data'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($this->tempHostInfo['retain_status_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['retain_status_information'])) {
						?>
						<b>Retain Status Information:</b> <? if($this->tempInheritedValues['retain_status_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['retain_status_information'];?></i><br />
						<?php
					}
					if(isset($this->tempHostInfo['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($this->tempHostInfo['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['retain_nonstatus_information'])) {
						?>
						<b>Retain Non-Status Information:</b> <? if($this->tempInheritedValues['retain_nonstatus_information']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['retain_nonstatus_information'];?></i><br />
						<?php
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=logging&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		if($_GET['section'] == 'notifications') {
			if($fruity->get_host_icon_image($_GET['host_id'], $host_icon_image)) {
				$host_icon_image = $path_config['doc_root'] . 'logos/' . $host_icon_image;
			} else {
				$host_icon_image = $path_config['image_root'] . "server.gif";
			}
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$host_icon_image;?>" />
				</td>
				<td valign="top">
				<?php
				if($_GET['edit']) {	// We're editing general information					
					$notification_options_checkbox_group[0]['element_name'] = 'host_manage[notification_options_down]';
					$notification_options_checkbox_group[0]['value'] = '1';
					$notification_options_checkbox_group[0]['element_title'] = 'Down';
					$notification_options_checkbox_group[1]['element_name'] = 'host_manage[notification_options_unreachable]';
					$notification_options_checkbox_group[1]['value'] = '1';
					$notification_options_checkbox_group[1]['element_title'] = 'Unreachable';
					$notification_options_checkbox_group[2]['element_name'] = 'host_manage[notification_options_recovery]';
					$notification_options_checkbox_group[2]['value'] = '1';
					$notification_options_checkbox_group[2]['element_title'] = 'Recovery';
					$notification_options_checkbox_group[3]['element_name'] = 'host_manage[notification_options_flapping]';
					$notification_options_checkbox_group[3]['value'] = '1';
					$notification_options_checkbox_group[3]['element_title'] = 'Flapping';
					
					if($_SESSION['tempData']['host_manage']['notification_options_down'])
						$notification_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_unreachable'])
						$notification_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_recovery']) 
						$notification_options_checkbox_group[2]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['notification_options_flapping'])
						$notification_options_checkbox_group[3]['checked'] = 1;
					$stalking_options_checkbox_group[0]['element_name'] = 'host_manage[stalking_options_up]';
					$stalking_options_checkbox_group[0]['value'] = '1';
					$stalking_options_checkbox_group[0]['element_title'] = 'Up';
					$stalking_options_checkbox_group[1]['element_name'] = 'host_manage[stalking_options_down]';
					$stalking_options_checkbox_group[1]['value'] = '1';
					$stalking_options_checkbox_group[1]['element_title'] = 'Down';
					$stalking_options_checkbox_group[2]['element_name'] = 'host_manage[stalking_options_unreachable]';
					$stalking_options_checkbox_group[2]['value'] = '1';
					$stalking_options_checkbox_group[2]['element_title'] = 'Unreachable';
					if($_SESSION['tempData']['host_manage']['stalking_options_up'])
						$stalking_options_checkbox_group[0]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['stalking_options_down'])
						$stalking_options_checkbox_group[1]['checked'] = 1;
					if($_SESSION['tempData']['host_manage']['stalking_options_unreachable'])
						$stalking_options_checkbox_group[2]['checked'] = 1;
					?>
					<form name="host_manage" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=notifications&edit=1">
					<input type="hidden" name="request" value="host_modify_notifications" />
					<input type="hidden" name="host_template_id" value="<?=$_GET['host_id'];?>">
					<?php
					double_pane_form_window_start();
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notifications_enabled]", "Notifications", $fruity->element_desc("notifications_enabled", "nagios_hosts_desc"), $enable_list, "values", "text", $_SESSION['tempData']['host_manage']['notifications_enabled'], "notifications_enabled", (isset($this->tempInheritedValuesSources['notifications_enabled']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[notification_interval]", "Notification Interval in Time-Units", $fruity->element_desc("notification_interval", "nagios_hosts_desc"), "8", "8", $_SESSION['tempData']['host_manage']['notification_interval'], "notification_interval", (isset($this->tempInheritedValuesSources['notification_interval']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_select_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notification_period]", "Notification Period", $fruity->element_desc("notification_period", "nagios_hosts_desc"), $period_list, "timeperiod_id", "timeperiod_name", $_SESSION['tempData']['host_manage']['notification_period'], "notification_period", (isset($this->tempInheritedValuesSources['notification_period']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_checkbox_group_form_element_with_enabler("#eeeeee", "host_manage", $notification_options_checkbox_group, "Notification Options", $fruity->element_desc("notification_options", "nagios_hosts_desc"), "notification_options", (isset($this->tempInheritedValuesSources['notification_options_down']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_checkbox_group_form_element_with_enabler("#f0f0f0", "host_manage", $stalking_options_checkbox_group, "Stalking Options", $fruity->element_desc("stalking_options", "nagios_hosts_desc"), "stalking_options", (isset($this->tempInheritedValuesSources['stalking_options_up']) ? "Override Inherited Value" : "Include In Definition"));
					double_pane_form_window_finish();
					?>
					<br />
					<input type="submit" value="Update Notifications" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">Cancel</a> ]
					<?php
				}
				else {
					?>
					<b>Included In Definition:</b><br />
					<?php
					if(isset($this->tempHostInfo['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($this->tempHostInfo['notifications_enabled']) print("Enabled"); else print("Disabled");?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['notifications_enabled'])) {
						?>
						<b>Notifications:</b> <? if($this->tempInheritedValues['notifications_enabled']) print("Enabled"); else print("Disabled");?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['notifications_enabled'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$this->tempHostInfo['notification_interval'];?> Time-Units<br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['notification_interval'])) {
						?>
						<b>Notification Interval:</b> <?=$this->tempInheritedValues['notification_interval'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['notification_interval'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($this->tempHostInfo['notification_period']);?><br />
						<?php
					}
					elseif(isset($this->tempInheritedValuesSources['notification_period'])) {
						?>
						<b>Notification Period:</b> <?=$fruity->return_period_name($this->tempInheritedValues['notification_period']);?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['notification_period'];?></i><br />
						<?php
					}					
					if(isset($this->tempHostInfo['notification_options_down']) || isset($this->tempHostInfo['notification_options_unreachable']) || isset($this->tempHostInfo['notification_options_recovery']) || isset($this->tempHostInfo['notification_options_flapping'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$this->tempHostInfo['notification_options_down'] && !$this->tempHostInfo['notification_options_unreachable'] && !$this->tempHostInfo['notification_options_recovery'] && !$this->tempHostInfo['notification_options_flapping']) {
							print("None");
						}
						else {
							if($this->tempHostInfo['notification_options_down']) {
								print("Down");
								if($this->tempHostInfo['notification_options_unreachable'] || $this->tempHostInfo['notification_options_recovery'] || $this->tempHostInfo['notification_options_flapping'])
									print(",");
							}
							if($this->tempHostInfo['notification_options_unreachable']) {
								print("Unreachable");
								if($this->tempHostInfo['notification_options_recovery'] || $this->tempHostInfo['notification_options_flapping'])
									print(",");
							}
							if($this->tempHostInfo['notification_options_recovery']) {
								print("Recovery");
									if($this->tempHostInfo['notification_options_flapping'])
										print(",");
							}
							if($this->tempHostInfo['notification_options_flapping']) {
								print("Flapping");
							}
						}
						print("<br />");
					}
					elseif(isset($this->tempInheritedValues['notification_options_down'])) {
						?>
						<b>Notification On:</b>
						<?php
						if(!$this->tempInheritedValues['notification_options_down'] && !$this->tempInheritedValues['notification_options_unreachable'] && !$this->tempInheritedValues['notification_options_recovery'] && !$this->tempInheritedValues['notification_options_flapping']) {
							print("None");
						}
						else {
							if($this->tempInheritedValues['notification_options_down']) {
								print("Down");
								if($this->tempInheritedValues['notification_options_unreachable'] || $this->tempInheritedValues['notification_options_recovery'] || $this->tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($this->tempInheritedValues['notification_options_unreachable']) {
								print("Unreachable");
								if($this->tempInheritedValues['notification_options_recovery'] || $this->tempInheritedValues['notification_options_flapping'])
									print(",");
							}
							if($this->tempInheritedValues['notification_options_recovery']) {
								print("Recovery");
									if($this->tempInheritedValues['notification_options_flapping'])
										print(",");
							}
							if($this->tempInheritedValues['notification_options_flapping']) {
								print("Flapping");
							}
						}
						print("<b> - Inherited From: </b><i>".$this->tempInheritedValuesSources['notification_options_down']."</i>");
						print("<br />");
					}
					if(isset($this->tempHostInfo['stalking_options_up']) || isset($this->tempHostInfo['stalking_options_down']) || isset($this->tempHostInfo['stalking_options_unreachable'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($this->tempHostInfo['stalking_options_up'] || $this->tempHostInfo['stalking_options_down'] || $this->tempHostInfo['stalking_options_unreachable']) {
								if($this->tempHostInfo['stalking_options_up']) {
									print("Up");
									if($this->tempHostInfo['stalking_options_down'] || $this->tempHostInfo['stalking_options_unreachable'])
										print(",");
								}
								if($this->tempHostInfo['stalking_options_down']) {
									print("Down");
									if($this->tempHostInfo['stalking_options_unreachable'])
										print(",");
								}
								if($this->tempHostInfo['stalking_options_unreachable']) {
									print("Unreachable");
								}
						}
						else {
							print("None");
						}
						print("<br />");
					}
					elseif(isset($this->tempInheritedValues['stalking_options_up'])) {
						?>
						<b>Stalking On:</b> 
						<?php
						if($this->tempInheritedValues['stalking_options_up'] || $this->tempInheritedValues['stalking_options_down'] || $this->tempInheritedValues['stalking_options_unreachable']) {
								if($this->tempInheritedValues['stalking_options_up']) {
									print("Up");
									if($this->tempInheritedValues['stalking_options_down'] || $this->tempInheritedValues['stalking_options_unreachable'])
										print(",");
								}
								if($this->tempInheritedValues['stalking_options_down']) {
									print("Down");
									if($this->tempInheritedValues['stalking_options_unreachable'])
										print(",");
								}
								if($this->tempInheritedValues['stalking_options_unreachable']) {
									print("Unreachable");
								}
						}
						else {
							print("None");
						}
						print("<b> - Inherited From: </b><i>".$this->tempInheritedValuesSources['stalking_options_up']."</i>");
						print("<br />");
					}					
					?>
					<br />
					[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=notifications&edit=1">Edit</a> ]
					<?php
				}
				?>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'groups') {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_hostgroups_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->get_host_membership_list($_GET['host_id'], $group_list);
			$numOfGroups = count($group_list);
			// Get list of host groups
			$fruity->get_hostgroup_list($hostgroups_list);
			$numOfHostGroups = count($hostgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>servergroup.gif" />
				</td>
				<td valign="top">
				<?php
				if(isset($this->tempHostInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Host Groups Inherited By Parent Template:</td>
						</tr>
						<?php
						sort( $inherited_list);
						for($counter = 0; $counter < $numOfInheritedGroups; $counter++) {
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
							<td height="20" width="60" class="altLeft">&nbsp;</td>
							<td height="20" class="altRight"><b><?=$fruity->return_hostgroup_name($inherited_list[$counter]);?>:</b> <?=$fruity->return_hostgroup_alias($inherited_list[$counter]);?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<br />
					<?php
				}
				?>
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Host Group Membership:</td>
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=groups&request=delete&hostgroup_id=<?=$group_list[$counter]['hostgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"><b><?=$fruity->return_hostgroup_name($group_list[$counter]['hostgroup_id']);?>:</b> <?=$fruity->return_hostgroup_alias($group_list[$counter]['hostgroup_id']);?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<br />
				<br />
				<form name="hostgroup_member_add" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=groups">
				<input type="hidden" name="request" value="add_member_command" />
				<input type="hidden" name="host_manage[group_add][host_id]" value="<?=$_GET['host_id'];?>" />
				<b>Add New Host Group Membership:</b> <?php print_select("host_manage[group_add][hostgroup_id]", $hostgroups_list, "hostgroup_id", "hostgroup_name", "0");?> <input type="submit" value="Add Group"><br />
				<?=$fruity->element_desc("members", "nagios_contactgroups_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'services') {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_services_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$numOfInheritedServices = count($inherited_list);
			}
			$fruity->get_host_services_list($_GET['host_id'], $hostServiceList);
			
			$numOfServices = count($hostServiceList);
			
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>services.gif" />
				</td>
				<td valign="top">
				<?php
				if(isset($this->tempHostInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Services Inherited By Parent Template:</td>
						</tr>
						<?php
						$counter = 0;
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
								<td height="20" width="60" class="altLeft">&nbsp;</td>
								<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$service['service_id'];?>"><?=$service['service_description'];?></a></b> from <b><?=$fruity->return_host_template_name($service['host_template_id']);?></b></td>
								</tr>
								<?php
								$counter++;
							}
						}
						?>
					</table>
					<br />
					<?php
				}
				?>
				
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Services Inherited By Host Group:</td>
					</tr>
					<?php

					// Inherited groups
					$inherited_list = array();
					if(isset($this->tempHostInfo['use_template_id']))
						$fruity->get_host_template_inherited_hostgroups_list($this->tempHostInfo['use_template_id'], $inherited_list);

					// Linked groups
					$group_list = array();
					$fruity->get_host_membership_list($_GET['host_id'], $group_list);

					foreach($group_list as $obj)
						$inherited_list[$obj['hostgroup_id']] = $obj['hostgroup_id'];
					
					// Get hostgroups services
					$allServices = array();
					foreach($inherited_list as $key => $group_id) {
						$services = array();
						$fruity->get_hostgroup_services_list( $group_id, $services );
						foreach($services as $svc) {
							$service_description = $fruity->return_service_description($svc['service_id']);
							$allServices[$service_description] = array( 	'service_id' => $svc['service_id'],
																						'hostgroup_name' => $fruity->return_hostgroup_name( $group_id ));
						}
					}
					
					ksort($allServices); // Sort service descriptions
					
					$counter = 0;
					foreach($allServices as $service_description => $service) {
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
						<td height="20" width="60" class="altLeft">&nbsp;</td>
						<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$service['service_id'];?>"><?=$service_description;?></a></b> from <b><?=$service['hostgroup_name'];?></b></td>
						</tr>
						<?php
						$counter++;
					}
					?>
				</table>
					<br />


                                <!-- Select all Checkboxes Toggle Function -->
                                <script language="JavaScript">
                                function SelectAll(source) {
                                  var checkboxes = document.getElementsByTagName("input");
                                  for(var i in checkboxes)
                                    checkboxes[i].checked = source.checked;
                                        }
                                </script>

				<form name="formSelected" action="<?=$path_config['doc_root'];?>services.php" method="get">
				<input type="hidden" name="host_id" value="<?=$_GET['host_id'];?>">
				<input type="hidden" name="section" value="clone">
				<input type="hidden" name="selected" value="1">
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Services Explicitly Linked to This Host:</td>
					</tr>
                                        <tr style='background-color:#cccccc; width: 10px'>
                                        <td></td>
                                        <td><input type='checkbox' onClick='SelectAll(this)' />&nbsp;&nbsp;<b>Select All Services</b></td>
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

						$reports = false;
						if ($reports !== false)
							$reports = "'$reports'";
						
						?>
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=services&request=delete&service_id=<?=$hostServiceList[$counter]['service_id'];?>" onClick="javascript:return confirmDelete(<?=$reports;?>);">Delete</a> ]</td>
						<td height="20" class="altRight"><input type='checkbox' name='service_id_<?=$hostServiceList[$counter]['service_id'];?>'> &nbsp;&nbsp;<b><a href="<?=$path_config['doc_root'];?>services.php?service_id=<?=$hostServiceList[$counter]['service_id'];?>"><?=$fruity->return_service_description($hostServiceList[$counter]['service_id']);?></a></b></td>
						</tr>
						<?php
					}
					?>
				</table>
				</form>
				<br />
				[ <a href="<?=$path_config['doc_root'];?>services.php?service_add=1&host_id=<?=$_GET['host_id'];?>">Create A New Service</a> ] |
			[ <a href="javascript:document.formSelected.section.value='delete';javascript:document.formSelected.submit();">Delete Selected Services</a> ] |
			[ <a href="javascript:document.formSelected.section.value='clone';javascript:document.formSelected.submit();">Clone Selected Services</a> ] |
			[ <a href="<?=$path_config['doc_root'];?>services.php?host_id=<?=$_GET['host_id'];?>&section=clone">Clone All Services</a> ]
				<br />
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == "checkcommand") {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_commandparameter_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			// Get List Of Parameters for this service and check
			$fruity->get_host_check_command_parameters($_GET['host_id'], $checkCommandParameters);
			$numOfCheckCommandParameters = count($checkCommandParameters);
			
			$parameterCounter = 0;
			?>
			<table width="90%" align="center" border="0">
			<tr>
			<td>
			
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
				<td colspan="2">Command Syntax:</td>
			</tr>
			<tr class="altRow2">
			<?php
				$command_line = $fruity->return_host_command_line($_GET['host_id']);
				print("<td class='altLeft'>" . $command_line . "</td>\n");
	
				if (preg_match('/^(\S*)\/(\S*) .*$/', $command_line, $matches))
					print("<td class='altRigth'><input type=button name=command value='View Help'onClick=\"javascript:popUp('" . $path_config['doc_root'] . "get_help.php?command=$matches[2]')\"></td>\n");
			?>
			</tr>
			</table>
			<br>			

				<?php
				if(isset($this->tempHostInfo['use_template_id'])) {
					?>
					<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
						<tr class="altTop">
						<td colspan="2">Parameters Inherited By Parent Template:</td>
						</tr>
						<?php
						if(count($inherited_list)) {
							$counter = 1;
							foreach($inherited_list as $parameter) {
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
								<td height="20" width="60" class="altLeft">&nbsp;</td>
								<td height="20" class="altRight"><b>$ARG<?=++$parameterCounter;?>$:</b> <?=$parameter['parameter'];?></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
					<br />
					<?php
				}
				?>
				<form name="modify_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>hosts.php?section=checkcommand&host_id=<?=$_GET['host_id'];?>">
				<input type="hidden" name="request" value="command_parameter_modify" />
				<input type="hidden" name="numCheckCommandParameters" value="<? echo $numOfCheckCommandParameters ?>" />
				<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
					<tr class="altTop">
					<td colspan="2">Check Command Parameters:</td>
					</tr>
					<?php
					for($counter = 0; $counter < $numOfCheckCommandParameters; $counter++) {
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
						<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checkcommand&request=delete&checkcommandparameter_id=<?=$checkCommandParameters[$counter]['checkcommandparameter_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
						<td height="20" class="altRight"> <b>$ARG<?=++$parameterCounter;?>$:</b> 
						<input type='text' name="ARG<?echo $parameterCounter;?>" value="<?=$checkCommandParameters[$counter]['parameter'];?>">						
						</tr>
						<?php
					}
					?>
				</table>
				<br>
				<?php
				if ($numOfCheckCommandParameters)
					print ('<input type="submit" value="Modify Parameter">' . "\n");
				?>
				</form>				
			<br />
			<form name="add_check_command_paramter" method="post" action="<?=$path_config['doc_root'];?>hosts.php?section=checkcommand&host_id=<?=$_GET['host_id'];?>">
			<input type="hidden" name="request" value="command_parameter_add" />
			<input type="hidden" name="host_manage[host_id]" value="<?=$_GET['host_id'];?>" />
			Value for $ARG<?=($counter+1);?>$: <input type="text" name="host_manage[parameter]" /> <input type="submit" value="Add Parameter" />
			</form>
			</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'extended') {
			if($fruity->get_host_extended_info($_GET['host_id'], $tempHostExtendedInfo)) {
				if($tempHostExtendedInfo != NULL) {
					foreach(array_keys($tempHostExtendedInfo) as $key) {
						if(isset($tempHostExtendedInfo[$key]))
							$_POST['host_manage_enablers'][$key] = '1';
					}
				}
				
				if(isset($tempHostInfo['use_template_id'])) {
						$result = $fruity->get_inherited_host_template_extended_values($tempHostInfo['use_template_id'], $tempInheritedValues, $tempInheritedValuesSources);
						// Let's load up $_SESSION
						if(count($tempInheritedValues)) {
							foreach($tempInheritedValues as $key=>$value) {
								if(isset($tempInheritedValues[$key]) && !isset($tempHostExtendedInfo[$key])) {
									$_SESSION['tempData']['host_manage'][$key] = $value;
								}
							}
						}
				}
				
				
				// Okay, now let's overwrite those values with the ones from tempServiceTemplateExtendedInfo (Do we really need this?)
				if(isset($tempHostExtendedInfo)) {
					foreach($tempHostExtendedInfo as $key=>$value) {
						if(isset($tempHostExtendedInfo[$key]))
							$_SESSION['tempData']['host_manage'][$key] = $value;
					}
				}
			}
			$tempDir = array();
			$directory_list[] = array("value" => '', "text" => 'None');
			if ($handle = @opendir($sys_config['logos_path'])) {
			   while (false !== ($file = readdir($handle))) {
			       if ($file != "." && $file != "..") {
			           $tempDir[] = $file;
			       }
			   }
			   closedir($handle);
			   asort($tempDir);
				foreach($tempDir as $value) {
					if(!is_dir($sys_config['logos_path'] ."/". $value))
						$directory_list[] = array("value" => $value, "text" => $value);
				}
			}
			$numOfImages = count($directory_list) - 1;
			?>
			<table width="100%" border="0">
			<tr>
			<td width="100" align="center" valign="top">
			<img src="<?=$path_config['image_root'];?>info.gif" />
			</td>
			<td valign="top">
			<?php
			if( $_GET['edit']) {
				?>
				<form name="host_manage" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=extended" method="post">
				<input type="hidden" name="request" value="update_host_extended" />
				<input type="hidden" name="host_manage[host_template_id]" value="<?=$_GET['host_id'];?>">
				<?php
				double_pane_form_window_start();
				double_pane_textarea_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[notes]", "Notes", $fruity->element_desc("notes", "nagios_hosts_extended_info_desc"), "3", "80", $_SESSION['tempData']['host_manage']['notes'], "notes", (isset($this->tempInheritedValuesSources['notes']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[notes_url]", "Notes URL", $fruity->element_desc("notes_url", "nagios_hosts_extended_info_desc"), "60","255", $_SESSION['tempData']['host_manage']['notes_url'], "notes_url", (isset($this->tempInheritedValuesSources['notes_url']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[action_url]", "Action URL", $fruity->element_desc("action_url", "nagios_hosts_extended_info_desc"), "60","255", $_SESSION['tempData']['host_manage']['action_url'], "action_url", (isset($this->tempInheritedValuesSources['action_url']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_select_form_element_with_enabler_and_viewer("#eeeeee", "host_manage", "host_manage[icon_image]", "Icon Image", $fruity->element_desc("icon_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['icon_image'], "icon_image", (isset($this->tempInheritedValuesSources['icon_image']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[icon_image_alt]", "Icon Image Alt Text", $fruity->element_desc("icon_image_alt", "nagios_hosts_extended_info_desc"), "60","60", $_SESSION['tempData']['host_manage']['icon_image_alt'], "icon_image_alt", (isset($this->tempInheritedValuesSources['icon_image_alt']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_select_form_element_with_enabler_and_viewer("#eeeeee", "host_manage", "host_manage[vrml_image]", "VRML Image", $fruity->element_desc("vrml_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['vrml_image'], "vrml_image", (isset($this->tempInheritedValuesSources['vrml_image']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_select_form_element_with_enabler_and_viewer("#f0f0f0", "host_manage", "host_manage[statusmap_image]", "Statusmap Image", $fruity->element_desc("statusmap_image", "nagios_hosts_desc"), $directory_list, "value", "text", $_SESSION['tempData']['host_manage']['statusmap_image'], "statusmap_image", (isset($this->tempInheritedValuesSources['statusmap_image']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#eeeeee", "host_manage", "host_manage[two_d_coords]", "2D Coordinates", $fruity->element_desc("two_d_coords", "nagios_hosts_extended_info_desc"), "30","30", $_SESSION['tempData']['host_manage']['two_d_coords'], "two_d_coords", (isset($this->tempInheritedValuesSources['two_d_coords']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_text_form_element_with_enabler("#f0f0f0", "host_manage", "host_manage[three_d_coords]", "3D Coordinates", $fruity->element_desc("three_d_coords", "nagios_hosts_extended_info_desc"), "30","30", $_SESSION['tempData']['host_manage']['three_d_coords'], "three_d_coords", (isset($this->tempInheritedValuesSources['three_d_coords']) ? "Override Inherited Value" : "Include In Definition"));
				double_pane_form_window_finish();
				?>
				<br />
				<input type="submit" value="Update Extended Information" /> [ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=extended">Cancel</a> ]
				</form>
				<?php
			} else {
				print "<b>Included in definition:</b><br />\n";
				if(isset($this->tempHostExtendedInfo['notes'])) {
					?>
					<b>Notes:</b> <?=$this->tempHostExtendedInfo['notes'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['notes'])) {
					?>
					<b>Notes:</b> <?=$_SESSION['tempData']['host_manage']['notes'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['notes'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$this->tempHostExtendedInfo['notes_url'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['notes_url'])) {
					?>
					<b>Notes URL:</b> <?=$_SESSION['tempData']['host_manage']['notes_url'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['notes_url'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['action_url'])) {
					?>
					<b>Action URL:</b> <?=$this->tempHostExtendedInfo['action_url'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['action_url'])) {
					?>
					<b>Action URL:</b> <?=$_SESSION['tempData']['host_manage']['action_url'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['action_url'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$this->tempHostExtendedInfo['icon_image'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['icon_image'])) {
					?>
					<b>Icon Image:</b> <?=$_SESSION['tempData']['host_manage']['icon_image'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['icon_image'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$this->tempHostExtendedInfo['icon_image_alt'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['icon_image_alt'])) {
					?>
					<b>Icon Image Alt Text:</b> <?=$_SESSION['tempData']['host_manage']['icon_image_alt'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['icon_image_alt'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['vrml_image'])) {
					?>
					<b>VRML Image:</b> <?=$this->tempHostExtendedInfo['vrml_image'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['vrml_image'])) {
					?>
					<b>VRML Image:</b> <?=$_SESSION['tempData']['host_manage']['vrml_image'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['vrml_image'];?></i><br />
					<?php
				}
				if(isset($tempHostExtendedInfo['statusmap_image'])) {
					?>
					<b>Statusmap Image:</b> <?=$this->tempHostExtendedInfo['statusmap_image'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['statusmap_image'])) {
					?>
					<b>Status Image:</b> <?=$_SESSION['tempData']['host_manage']['statusmap_image'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['statusmap_image'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['two_d_coords'])) {
					?>
					<b>2D Coordinates:</b> <?=$this->tempHostExtendedInfo['two_d_coords'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['two_d_coords'])) {
					?>
					<b>2D Coordinates:</b> <?=$_SESSION['tempData']['host_manage']['two_d_coords'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['two_d_coords'];?></i><br />
					<?php
				}
				if(isset($this->tempHostExtendedInfo['three_d_coords'])) {
					?>
					<b>3D Coordinates:</b> <?=$this->tempHostExtendedInfo['three_d_coords'];?><br />
					<?php
				}
				elseif(isset($this->tempInheritedValuesSources['three_d_coords'])) {
					?>
					<b>3D Coordinates:</b> <?=$_SESSION['tempData']['host_manage']['three_d_coords'];?> <b>- Inherited From:</b> <i><?=$this->tempInheritedValuesSources['three_d_coords'];?></i><br />
					<?php
				}
				?>
				<br />
				[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=extended&edit=1">Edit</a> ]
				<?php
			}
			?>
			</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'contactgroups') {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_contactgroups_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$inherited_list = array_values( $inherited_list);
				$numOfInheritedGroups = count($inherited_list);
			}
			$fruity->return_host_contactgroups_list($_GET['host_id'], $contactgroups_list);			
			$numOfContactGroups = count($contactgroups_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($this->tempHostInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Contact Groups Inherited By Parent Template:</td>
								</tr>
								<?php
								for($counter = 0; $counter < $numOfInheritedGroups; $counter++) {
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
									<td height="20" width="60" class="altLeft">&nbsp;</td>
									<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($inherited_list[$counter]);?>:</b> <?=$fruity->return_contactgroup_alias($inherited_list[$counter]);?></td>
									</tr>
									<?php
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Contact Groups Explicitly Linked to This Host:</td>
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
								<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=contactgroups&request=delete&contactgroup_id=<?=$contactgroups_list[$counter]['contactgroup_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
								<td height="20" class="altRight"><b><?=$fruity->return_contactgroup_name($contactgroups_list[$counter]['contactgroup_id']);?>:</b> <?=$fruity->return_contactgroup_alias($contactgroups_list[$counter]['contactgroup_id']);?></td>
								</tr>
								<?php
							}
							?>
						</table>
				<?php	$fruity->get_contactgroup_list($contactgroups_list); ?>
				<br />
				<br />
				<form name="host_template_contactgroup_add" method="post" action="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=contactgroups">
				<input type="hidden" name="request" value="add_contactgroup_command" />
				<input type="hidden" name="host_manage[contactgroup_add][host_id]" value="<?=$_GET['host_id'];?>" />
				<b>Add New Contact Group:</b> <?php print_select("host_manage[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");?> <input type="submit" value="Add Contact Group"><br />
				<?=$fruity->element_desc("contact_groups", "nagios_hosts_desc"); ?><br />
				<br />
				</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'dependencies') {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_dependencies_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$numOfInheritedDepdendencies = count($inherited_list);
			}
			$fruity->return_host_dependencies_list($_GET['host_id'], $dependencies_list);
			$numOfDependencies = count($dependencies_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($this->tempHostInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Depdendencies Inherited By Parent Template:</td>
								</tr>
								<?php
								$counter = 0;
								if($numOfInheritedDepdendencies) {
									foreach($inherited_list as $dependency) {
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
										<td height="20" width="60" class="altLeft">&nbsp;</td>
										<td height="20" class="altRight"><b><?=$fruity->return_host_name($dependency['target_host_id']);?></td>
										</tr>
										<?php
										$counter++;
									}
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Depdendencies Explicitly Linked to This Host:</td>
							</tr>
							<?php
							$counter = 0;
							if($numOfDependencies) {
								foreach($dependencies_list as $dependency) {
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=dependencies&request=delete&dependency_id=<?=$dependency['dependency_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
									<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>dependency.php?dependency_id=<?=$dependency['dependency_id'];?>"><?=$fruity->return_host_name($dependency['target_host_id']);?></a></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						[ <a href="<?=$path_config['doc_root'];?>dependency.php?dependency_add=1&host_id=<?=$_GET['host_id'];?>">Create A New Host Dependency For This Host</a> ]
				</td>
			</tr>
			</table>
			<?php
		}
		else if($_GET['section'] == 'escalations') {
			if(isset($this->tempHostInfo['use_template_id'])) {
				$fruity->get_host_template_inherited_escalations_list($this->tempHostInfo['use_template_id'], $inherited_list);
				$numOfInheritedEscalations = count($inherited_list);
			}
			$fruity->return_host_escalations_list($_GET['host_id'], $escalations_list);
			$numOfEscalations = count($escalations_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>contact.gif" />
				</td>
				<td valign="top">
						<?php
						if(isset($this->tempHostInfo['use_template_id'])) {
							?>
							<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
								<tr class="altTop">
								<td colspan="2">Escalations Inherited By Parent Template:</td>
								</tr>
								<?php
								$counter = 0;
								if($numOfInheritedEscalations) {
									foreach($inherited_list as $escalation) {
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
										<td height="20" width="60" class="altLeft">&nbsp;</td>
										<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$escalation;?>"><?=$fruity->return_escalation_description($escalation);?></a></b></td>
										</tr>
										<?php
										$counter++;
									}
								}
								?>
							</table>
							<br />
							<?php
						}
						?>
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Escalations Explicitly Linked to This Host:</td>
							</tr>
							<?php
							$counter = 0;
							if($numOfEscalations) {
								foreach($escalations_list as $escalation) {
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=escalations&request=delete&escalation_id=<?=$escalation['escalation_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
									<td height="20" class="altRight"><b><a href="<?=$path_config['doc_root'];?>escalation.php?escalation_id=<?=$escalation['escalation_id'];?>"><?=$escalation['escalation_description'];?></a></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						[ <a href="<?=$path_config['doc_root'];?>escalation.php?escalation_add=1&host_id=<?=$_GET['host_id'];?>">Create A New Escalation For This Host</a> ]
				</td>
			</tr>
			</table>
			<?php
		}
		
		else if($_GET['section'] == 'parents') {
			$fruity->return_host_parents_list($_GET['host_id'], $parents_list);
			$numOfParents = count($parents_list);
			?>
			<table width="100%" border="0">
			<tr>
				<td width="100" align="center" valign="top">
				<img src="<?=$path_config['image_root'];?>servergroup.gif" />
				</td>
				<td valign="top">
						<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
							<tr class="altTop">
							<td colspan="2">Additional Parents For This Host:</td>
							</tr>
							<?php
							
						$add_host_list[] = array("host_id" => '', "host_name" => "None");
						$fruity->get_host_list( $host_list);
						
						if(count($host_list)) {
							foreach($host_list as $tempHostlist) {
								if ($tempHostlist[host_id] != $this->tempHostInfo['host_id'])
								{
									$add_host_list[] = $tempHostlist;
								}
							}
						}
							$counter = 0;
							if($numOfParents) {
								foreach($parents_list as $parent) {
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
									<td height="20" width="60" class="altLeft">&nbsp;[ <a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=parents&request=delete&parent_id=<?=$parent['parent_id'];?>" onClick="javascript:return confirmDelete();">Delete</a> ]</td>
									<td height="20" class="altRight"><b><?=$fruity->return_host_name($parent['parent_id']);?></b></td>
									</tr>
									<?php
									$counter++;
								}
							}
							?>
						</table>
						<br />
						<br />
						<b>Add Additional Parent:</b>
						<form action="<?=$_SERVER['PHP_SELF'];?>?host_id=<?=$_GET['host_id'];?>&section=parents" method="post">
						<input type="hidden" name="request" value="parent_add" />
						Host Name: <?php print_select("parent_id", $add_host_list, "host_name", "host_name");?><br /><input type="submit" value="Add Parent" />
						
					
						</form>
						
				</td>
			</tr>
			</table>
			<?php
		}

		
		
		print_window_footer();
	}
}


?>
