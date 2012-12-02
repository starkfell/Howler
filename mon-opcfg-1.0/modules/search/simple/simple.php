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
	Module Name: selective
	Module Category: search
	Module Description: 
	Allows a user to search the Fruity DB, specifying what field to check.
	
*/

define( "MODULE_SEARCH_SIMPLE_ENABLED", "true");

class module_search_simple extends Module {
	private $dbHost;
	private $dbUsername;
	private $dbPasswor;
	private $dbDatabase ;
	private $dbServ;	// Database driver to use
	private $dbConnection;
	
	private $searchResults;
	private $searchCount;
	private $searchTemplate;
	
	function __construct() {
		$this->setVersionInfo('simple', 'Simple Search', 1, 0);
		global $sitedb_config;
		global $path_config;
		
		$this->dbServ = $sitedb_config['dbserv'];
		$this->dbHost = $sitedb_config['host'];
		$this->dbUsername = $sitedb_config['username'];
		$this->dbPassword = $sitedb_config['password'];
		$this->dbDatabase = $sitedb_config['database'];
		
		
		$this->dbConnection = ADONewConnection($this->dbServ);
		$this->dbConnection->PConnect($this->dbHost, $this->dbUsername,
							$this->dbPassword,$this->dbDatabase);
		if(!$this->dbConnection->IsConnected()) {
			print("DBAUTH Failure: Unable to connect to auth database.  Please check your dbauth configuration.");
			die();
		}	
		$this->dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		
		$this->searchResults = array();
		$this->searchCount = 0;
		
		$this->searchTemplate = array();
		
		$this->searchTemplate['hosts'] = array( "type"=>"Hosts", "host_name"=>"Host Name", "address"=>"IP Address", "alias"=>"Alias");
		$this->searchTemplate['hostgroups'] = array( "type"=>"Hostgroups", "hostgroup_name"=>"Hostgroup Name", "alias"=>"Alias", "members"=>"Members");
		$this->searchTemplate['host_templates'] = array( "type"=>"Host Templates", "template_name"=>"Template Name", "template_description"=>"Description", "members"=>"Members");
		$this->searchTemplate['services'] = array( "type"=>"Services", "service_description"=>"Service", "host_name"=>"Associated Host");
		$this->searchTemplate['servicegroups'] = array( "type"=>"Servicegroups", "servicegroup_name"=>"servicegroup Name", "alias"=>"Alias", "members"=>"Members");
		$this->searchTemplate['service_templates'] = array( "type"=>"Service Templates", "template_name"=>"Template Name", "template_description"=>"Description", "members"=>"Members");
		$this->searchTemplate['contacts'] = array( "type"=>"Contacts", "contact_name"=>"Contact", "alias"=>"Alias", "email"=>"Email");
		$this->searchTemplate['contactgroups'] = array( "type"=>"Contactgroups", "contactgroup_name"=>"Contactgroup Name", "alias"=>"Alias", "members"=>"Members");
		$this->searchTemplate['timeperiods'] = array( "type"=>"Timeperiods", "timeperiod_name"=>"Timeperiod Name", "alias"=>"Alias");
		$this->searchTemplate['commands'] = array( "type"=>"Commands", "command_name"=>"Command Name", "command_desc"=>"Description");
		$this->searchTemplate['hostchecks'] = array( "type"=>"Host Check Parameters", "host_name"=>"Host Name", "command_name"=>"Command Name", "service_description"=>"Service Description");
		$this->searchTemplate['servicechecks'] = array( "type"=>"Service Check Parameters", "service_description"=>"Service Description", "command_name"=>"Command Name", "host_name"=>"Host Name");
		$this->searchTemplate['hostescalations'] = array( "type"=>"Host Escalations", "escalation_description"=>"Escalation", "host_name"=>"Attached Host", "members"=>"Contact Groups");
		$this->searchTemplate['serviceescalations'] = array( "type"=>"Service Escalations", "escalation_description"=>"Escalation", "service_description"=>"Attached Service", "host_name"=>"Related Host", "members"=>"Contact Groups");
		$this->searchTemplate['hosttemplateescalations'] = array( "type"=>"Host Template Escalations", "escalation_description"=>"Escalation", "template_name"=>"Attached Template", "members"=>"Contact Groups");
		$this->searchTemplate['servicetemplateescalations'] = array( "type"=>"Service Template Escalations", "escalation_description"=>"Escalation", "template_name"=>"Attached Template", "members"=>"Contact Groups");
		
		$this->searchTemplate['hosts']['url'] = $path_config['doc_root'] . "hosts.php?host_id=@1@";
		$this->searchTemplate['hostgroups']['url'] = $path_config['doc_root'] . "hostgroups.php?hostgroup_id=@1@";
		$this->searchTemplate['host_templates']['url'] = $path_config['doc_root'] . "host_templates.php?host_template_id=@1@";
		$this->searchTemplate['services']['url'] = $path_config['doc_root'] . "services.php?service_id=@1@";
		$this->searchTemplate['servicegroups']['url'] = $path_config['doc_root'] . "servicegroups.php?servicegroup_id=@1@";
		$this->searchTemplate['service_templates']['url'] = $path_config['doc_root'] . "service_templates.php?service_template_id=@1@";
		$this->searchTemplate['contacts']['url'] = $path_config['doc_root'] . "contacts.php?contact_id=@1@";
		$this->searchTemplate['contactgroups']['url'] = $path_config['doc_root'] . "contactgroups.php?contact_id=@1@";
		$this->searchTemplate['timeperiods']['url'] = $path_config['doc_root'] . "timeperiods.php?timeperiod_id=@1@";
		$this->searchTemplate['commands']['url'] = $path_config['doc_root'] . "commands.php?command_id=@1@";
		$this->searchTemplate['hostchecks']['url'] = $path_config['doc_root'] . "hosts.php?host_id=@1@&section=checkcommand";
		$this->searchTemplate['servicechecks']['url'] = $path_config['doc_root'] . "services.php?service_id=@1@&section=checkcommand";
		$this->searchTemplate['hostescalations']['url'] = $path_config['doc_root'] . "escalation.php?escalation_id=@1@";
		$this->searchTemplate['serviceescalations']['url'] = $path_config['doc_root'] . "escalation.php?escalation_id=@1@";
		$this->searchTemplate['hosttemplateescalations']['url'] = $path_config['doc_root'] . "escalation.php?escalation_id=@1@";
		$this->searchTemplate['servicetemplateescalations']['url'] = $path_config['doc_root'] . "escalation.php?escalation_id=@1@";
	}
	
	function __destruct() {
	}
	
	public function init() {
		// Does nothing
	}
	
	public function restart() {
		$this->dbConnection = ADONewConnection($this->dbServ);
		$this->dbConnection->PConnect($this->dbHost, $this->dbUsername,
							$this->dbPassword,$this->dbDatabase);
		if(!$this->dbConnection->IsConnected()) {
			print("DBAUTH Failure: Unable to connect to auth database.  Please check your dbauth configuration.");
			die();
		}	
		$this->dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
	}
	
	public function render() {
		$this->renderBasicSearch();
	}
	
	private function search_ip( $ip) {
		// Check host IP addresses
		$sql = "SELECT nh.host_id, nh.host_name, nh.address, nh.alias FROM nagios_hosts AS nh WHERE nh.address LIKE '%$ip%' ORDER BY nh.host_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['hosts'][$result->fields['host_id']])) {
					$this->searchResults['hosts'][$result->fields['host_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Check host check for IP addresses
		$sql = "SELECT nh.host_id, nh.host_name, ns.service_description, nc.command_name"
			." FROM nagios_hosts_check_command_parameters AS nhccp LEFT JOIN nagios_hosts AS nh ON nhccp.host_id=nh.host_id"
			." LEFT JOIN nagios_services AS ns ON nh.service_id=ns.service_id, nagios_commands AS nc"
			." WHERE nhccp.parameter LIKE '%$ip%'"
			." AND nc.command_id=nh.check_command"
			." ORDER BY nh.host_name ASC, nc.command_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['hostchecks'][$result->fields['host_id']])) {
					$this->searchResults['hostchecks'][$result->fields['host_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Check service check for IP addresses
		$sql = "SELECT ns.service_id, nh.host_name, ns.service_description, nc.command_name"
			." FROM nagios_services_check_command_parameters AS nsccp LEFT JOIN nagios_services AS ns ON nsccp.service_id=ns.service_id"
			." LEFT JOIN nagios_hosts AS nh ON ns.host_id=nh.host_id, nagios_commands AS nc"
			." WHERE nsccp.parameter LIKE '%$ip%'"
			." AND nc.command_id=ns.check_command"
			." ORDER BY ns.service_description ASC, nh.host_name ASC, nc.command_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['servicechecks'][$result->fields['service_id']])) {
					$this->searchResults['servicechecks'][$result->fields['service_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Done
	}
	
	private function search_text( $text) {
		global $fruity;
		// LOTS of places to check...
		// Hosts: name, alias
		$sql = "SELECT nh.host_id, nh.host_name, nh.address, nh.alias FROM nagios_hosts AS nh"
			." WHERE nh.host_name LIKE '%$text%' OR nh.alias LIKE '%$text%'"
			." ORDER BY nh.host_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['hosts'][$result->fields['host_id']])) {
					$this->searchResults['hosts'][$result->fields['host_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Hostgroups: name, alias
		$sql = "SELECT nhg.hostgroup_id, nhg.hostgroup_name, nhg.alias, nh.host_name AS members"
			." FROM nagios_hostgroups AS nhg LEFT JOIN nagios_hostgroup_membership AS nhgm ON nhg.hostgroup_id=nhgm.hostgroup_id"
			." LEFT JOIN nagios_hosts AS nh ON nhgm.host_id=nh.host_id"
			." WHERE nhg.hostgroup_name LIKE '%$text%' OR nhg.alias LIKE '%$text%'"
			." ORDER BY nhg.hostgroup_name ASC, nh.host_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				if( $result->fields['hostgroup_id'] == $lastid) {
					$this->searchResults['hostgroups'][$result->fields['hostgroup_id']]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['hostgroup_id'];
					$this->searchResults['hostgroups'][$result->fields['hostgroup_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Host Templates: name, description
		$sql = "SELECT nht.host_template_id, nht.template_name, nht.template_description, nh.host_name AS members"
			." FROM nagios_host_templates AS nht LEFT JOIN nagios_hosts AS nh ON nht.host_template_id=nh.use_template_id"
			." WHERE nht.template_name LIKE '%$text%' OR nht.template_description LIKE '%$text%'"
			." ORDER BY nht.template_name ASC, nh.host_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				if( $result->fields['host_template_id'] == $lastid) {
					$this->searchResults['host_templates'][$lastid]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['host_template_id'];
					$this->searchResults['host_templates'][$lastid] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Services: description
		$sql = "SELECT ns.service_id, ns.service_description, nh.host_name FROM nagios_services AS ns LEFT JOIN nagios_hosts AS nh ON ns.host_id=nh.host_id"
			." WHERE ns.service_description LIKE '%$text%'"
			." ORDER BY ns.service_description ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['services'][$result->fields['service_id']])) {
					$this->searchResults['services'][$result->fields['service_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Servicegroups: name, alias
		$sql = "SELECT nsg.servicegroup_id, nsg.servicegroup_name, nsg.alias, ns.service_description AS members"
			." FROM nagios_servicegroups AS nsg LEFT JOIN nagios_servicegroup_membership AS nsgm ON nsg.servicegroup_id=nsgm.servicegroup_id"
			." LEFT JOIN nagios_services AS ns ON nsgm.service_id=ns.service_id"
			." WHERE nsg.servicegroup_name LIKE '%$text%' OR nsg.servicegroup_alias LIKE '%$text%'"
			." ORDER BY nsg.servicegroup_name ASC, ns.service_description ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				if( $result->fields['servicegroup_id'] == $lastid) {
					$this->searchResults['servicegroups'][$result->fields['servicegroup_id']]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['servicegroup_id'];
					$this->searchResults['servicegroups'][$result->fields['servicegroup_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Service Templates: name, description
		$sql = "SELECT nst.service_template_id, nst.template_name, nst.template_description, ns.service_description AS members"
			." FROM nagios_service_templates AS nst LEFT JOIN nagios_services AS ns ON nst.service_template_id=ns.use_template_id"
			." WHERE nst.template_name LIKE '%$text%' OR nst.template_description LIKE '%$text%'"
			." ORDER BY nst.template_name ASC, ns.service_description ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				if( $result->fields['service_template_id'] == $lastid) {
					$this->searchResults['service_templates'][$lastid]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['service_template_id'];
					$this->searchResults['service_templates'][$lastid] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Contacts: name, alias, email
		$sql = "SELECT nc.contact_id, nc.contact_name, nc.alias, nc.email FROM nagios_contacts AS nc"
			." WHERE nc.contact_name LIKE '%$text%' OR nc.alias LIKE '%$text%' OR nc.email LIKE '%$text%'"
			." ORDER BY nc.contact_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['contacts'][$result->fields['contact_id']])) {
					$this->searchResults['contacts'][$result->fields['contact_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Contactgroups: name, alias
		$sql = "SELECT ncg.contactgroup_id, ncg.contactgroup_name, ncg.alias, nc.contact_name AS members"
			." FROM nagios_contactgroups AS ncg LEFT JOIN nagios_contactgroup_membership AS ncgm ON ncg.contactgroup_id=ncgm.contactgroup_id"
			." LEFT JOIN nagios_contacts AS nc ON ncgm.contact_id=nc.contact_id"
			." WHERE ncg.contactgroup_name LIKE '%$text%' OR ncg.alias LIKE '%$text%'"
			." ORDER BY ncg.contactgroup_name ASC, nc.contact_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				if( $result->fields['contactgroup_id'] == $lastid) {
					$this->searchResults['contactgroups'][$result->fields['contactgroup_id']]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['contactgroup_id'];
					$this->searchResults['contactgroups'][$result->fields['contactgroup_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Timeperiod: name, alias
		$sql = "SELECT nt.timeperiod_id, nt.timeperiod_name, nt.alias FROM nagios_timeperiods AS nt"
			." WHERE nt.timeperiod_name LIKE '%$text%' OR nt.alias LIKE '%$text%'"
			." ORDER BY nt.timeperiod_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['timeperiods'][$result->fields['timeperiod_id']])) {
					$this->searchResults['timeperiods'][$result->fields['timeperiod_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Commands: name, description
		$sql = "SELECT nc.command_id, nc.command_name, nc.command_desc FROM nagios_commands AS nc"
			." WHERE nc.command_name LIKE '%$text%' OR nc.command_desc LIKE '%$text%'"
			." ORDER BY nc.command_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['commands'][$result->fields['command_id']])) {
					$this->searchResults['commands'][$result->fields['command_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Check host check for match
		$sql = "SELECT nh.host_id, nh.host_name, ns.service_description, nc.command_name"
			." FROM nagios_hosts_check_command_parameters AS nhccp LEFT JOIN nagios_hosts AS nh ON nhccp.host_id=nh.host_id"
			." LEFT JOIN nagios_services AS ns ON nh.host_id=ns.host_id, nagios_commands AS nc"
			." WHERE nhccp.parameter LIKE '%$text%'"
			." AND nc.command_id=nh.check_command"
			." ORDER BY nh.host_name ASC, nc.command_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['hostchecks'][$result->fields['host_id']])) {
					$this->searchResults['hostchecks'][$result->fields['host_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		} 
		
		// Check service check for match
		$sql = "SELECT ns.service_id, nh.host_name, ns.service_description, nc.command_name"
			." FROM nagios_services_check_command_parameters AS nsccp LEFT JOIN nagios_services AS ns ON nsccp.service_id=ns.service_id"
			." LEFT JOIN nagios_hosts AS nh ON ns.host_id=nh.host_id, nagios_commands AS nc"
			." WHERE nsccp.parameter LIKE '%$text%'"
			." AND nc.command_id=ns.check_command"
			." ORDER BY ns.service_description ASC, nh.host_name ASC, nc.command_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if( !isset( $this->searchResults['servicechecks'][$result->fields['service_id']])) {
					$this->searchResults['servicechecks'][$result->fields['service_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
		
		// Escalations: description, host_name, contactgroups
		$sql = "SELECT ne.escalation_id, ne.escalation_description, ne.host_id, ne.host_template_id, ne.service_id, ne.service_template_id, ncg.contactgroup_name AS members"
			." FROM nagios_escalations AS ne LEFT JOIN nagios_escalation_contactgroups AS nec ON ne.escalation_id=nec.escalation_id"
			." LEFT JOIN nagios_contactgroups AS ncg ON nec.contactgroup_id=ncg.contactgroup_id"
			." WHERE ne.escalation_description LIKE '%$text%' OR ncg.contactgroup_name LIKE '%$text%'"
			." ORDER BY ne.escalation_description ASC, ncg.contactgroup_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			$lastid = -1;
			while( !$result->EOF) {
				$type = "none";
				if( isset($result->fields['host_id'])) {
					$type = "hostescalations";
				} elseif( isset($result->fields['host_template_id'])) {
					$type = "hosttemplateescalations";
				} elseif( isset($result->fields['service_id'])) {
					$type = "serviceescalations";
				} elseif( isset($result->fields['service_template_id'])) {
					$type = "servicetemplateescalations";
				}
				
				if( $result->fields['escalation_id'] == $lastid) {
					$this->searchResults[$type][$result->fields['escalation_id']]['members'] .= ", " . $result->fields['members'];
				} else {
					$lastid = $result->fields['escalation_id'];
					
					switch( $type) {
						case "hostescalations": {
								$fruity->get_host_info( $result->fields['host_id'], $tmp);
								$result->fields['host_name'] = $tmp['host_name'];
							} break;
						case "hosttemplateescalations": {
								$fruity->get_host_template_info( $result->fields['host_template_id'], $tmp);
								$result->fields['template_name'] = $tmp['template_name'];
							} break;
						case "serviceescalations": {
								$fruity->get_service_info( $result->fields['service_id'], $tmp);
								$result->fields['service_description'] = $tmp['service_description'];
								$fruity->get_host_info( $tmp['host_id'], $host);
								$result->fields['host_name'] = $host['host_name'];
							} break;
						case "servicetemplateescalations": {
								$fruity->get_service_template_info( $result->fields['service_template_id'], $tmp);
								$result->fields['template_name'] = $tmp['template_name'];
							} break;
					}
					
					$this->searchResults[$type][$result->fields['escalation_id']] = $result->fields;
					$this->searchCount++;
				}
				$result->MoveNext();
			}
		}
	}
	
	private function doSearch() {
		global $fruity;
		
		// Get settings from the POST
		$searchString = $_POST['searchField'];
		$tokens = explode( " ", $searchString);
		
		$this->searchResults = array();
		$this->searchCount = 0;
		
		if(count($tokens)) {
			foreach( $tokens as $token) {
				$token = trim( $token);
				$type = "text";
				$area = "any";
				// Determine the type of this token
				if( strpos( $token, ".") !== false) {
					// Might be an ip address
					if( preg_match( "/[0-9]{1,3}\.|\.[0-9]{1,3}/", $token)) {
						$type = "ip";
					}
				}
				
				$token = str_replace( "*", "%", $token);
				$fruity->prepare_for_sql( $token);
				$token = str_replace( "_", "\\_", $token);
				switch( $type) {
					case "ip": {
						$this->search_ip( $token);
						} break;
					case "text":
					default: {
						$this->search_text( $token);
						}
				}
			}
		}
		
		$fruity->prepare_for_use( $this->searchResults);
	}
	
	public function renderBasicSearch() {
		global $path_config;
		
		?>
<script type="text/javascript">
<!--
function clearSearch() {
	id = document.getElementById("searchField");
	if( id.value != "Enter a search") {
		id.value = "";
	}
}

function validateForm() {
	id = document.getElementById("searchField");
	if( id.value.length == 0) {
		alert( "Please enter a search string before searching");
		return false;
	}
	
	if( id.value == "Enter a search") {
		alert( "Please enter a search string before searching");
		return false;
	}
	
	return true;
}

-->
</script>
<form action="<?=$path_config['doc_root']?>search.php" method="post" target="rightHome" id="searchForm" onsubmit="return validateForm();">
<input type="hidden" id="simpleSearch" name="simpleSearch" value="1" />
<table align="right" cellspacing="0" cellpadding="0">
	<tr>
		<td><input type="text" size="35" name="searchField" id="searchField" value="Enter a search" onfocus="javascript:if(this.value=='Enter a search')this.value='';" />&nbsp;</td>
		<td><input type="submit" value="Search" />&nbsp;</td>
	</tr>
</table>
</form>
		<?php
	}
	
	public function renderAdvancedSearch() {
		print "Advanced search page<br>\n";
	}
	
	public function renderResults() {
		if( isset( $_POST['simpleSearch'])) {
			$this->doSearch();
		} else {
			$this->renderAdvancedSearch();
			exit;
		}
		
		if( $this->searchCount == 1) {
			$keys = array_keys( $this->searchResults);
			$id = array_keys( $this->searchResults[$keys[0]]);
			$url = $this->searchTemplate[$keys[0]]['url'];
			
			$link = str_replace( "@1@", $id[0], $url);
			header( "Location: $link");
			exit;
		}
		
		print_header("Search");
		if( $this->searchCount == 0) {
			print "<br />\n";
			print_window_header( "No Results", "100%", "center");
			print "<br />\nNo results returned.  Please adjust your search and try again.<br />\n<br />\n";
			print_window_footer();
		} else {
			if(count($this->searchResults)) {
				foreach( $this->searchResults as $group=>$results) {
					echo "<br />\n";
					print_window_header( "Results in " . $this->searchTemplate[$group]['type'] . ": " . count($results), "100%", "center");
	?>
	<table width="95%" border="0" align="center" cellspacing="0" cellpadding="0">
	<tr>
	<?php
					if(count($this->searchTemplate[$group])) {
						foreach( $this->searchTemplate[$group] as $key=>$value) {
							if( $key != "type" && $key != "url") {
								print "\t<td style=\"padding: 2px; border-bottom: 1px solid #aaaaaa;\"><b>$value</b></td>\n";
							}
						}
					}
	?>
	</tr>
	<?php
					$count = 0;
					$span = count( $this->searchTemplate[$group]) - 3;
					$url = $this->searchTemplate[$group]['url'];
					if(count($results)) {
						foreach( $results as $id=>$result) {
							$bgcolor = "#f0f0f0";
							if( $count % 2) {
								$bgcolor = "#cccccc";
							}
							print "<tr style=\"background-color: $bgcolor\">\n";
							
							$n = 0;
							if(count($this->searchTemplate[$group])) {
								foreach( $this->searchTemplate[$group] as $key=>$value) {
									if( $key != "type" && $key != "url") {
										if( $n == 0) {
											print "\t<td style=\"padding: 2px; border-left: 1px solid #aaaaaa; border-bottom: 1px solid #aaaaaa;\" valign=\"top\">";
										} elseif( $n == $span) {
											print "\t<td style=\"padding: 2px; border-right: 1px solid #aaaaaa; border-bottom: 1px solid #aaaaaa;\" valign=\"top\">";
										} else {
											print "\t<td style=\"padding: 2px; border-bottom: 1px solid #aaaaaa;\" valign=\"top\">";
										}
										
										if( strlen( $result[$key]) == 0) {
											$result[$key] = "&nbsp;";
										} else {
											$result[$key] = "<a href=\"" . str_replace( "@1@", $id, $url) . "\">" . $result[$key] . "</a>";
										}
										
										print $result[$key] . "</td>\n";
										$n++;
									}
								}
							}
							$count++;
							
							print "</tr>\n";
						}
					}
	?>
	</table>
	<br />
	<?php
					print_window_footer();
				}
			}
			echo "<br />";
		}
		
		print_footer();
	}
}

// $module_search = new module_search_simple;

?>
