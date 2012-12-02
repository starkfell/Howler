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

define( "MODULE_SEARCH_SELECTIVE_ENABLED", "true");

class module_search_selective extends Module {
	private $dbHost;
	private $dbUsername;
	private $dbPassword;
	private $dbDatabase ;
	private $dbServ;	// Database driver to use
	private $dbConnection;
	
	// Associative array of search results
	private $searchResults;
	
	// Column names when displaying the search
	private $searchHeader;
	
	// Base-URI for links
	private $searchLink;
	
	function __construct() {
		$this->setVersionInfo('selective', 'Allows user to search Fruity DB, specifying what fields to check', 1, 0);
		global $sitedb_config;
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
	
	private function search_host( $type, $value, &$host_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select * from nagios_hosts where host_name like '$value' order by host_name ASC";
				break;
			case 'aslias':
				$sql = "select * from nagios_hosts where alias like '$value' order by host_name ASC";
				break;
			case 'id':
				$sql = "select * from nagios_hosts where host_id = $value order by host_name ASC";
				break;
			case 'ip':
				$sql = "select * from nagios_hosts where address like '$value' order by host_name ASC";
				break;
		}
		
		$host_list = array();
		$result = $this->dbConnection->Execute($sql);
		if($result){
			while (! $result->EOF) {
				$host_list[] = $result->fields;
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_hostgroup( $type, $value, &$hostgroup_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select nagios_hostgroups.hostgroup_id, nagios_hostgroups.hostgroup_name, nagios_hostgroups.alias, nagios_hosts.host_name as members from nagios_hostgroups, nagios_hostgroup_membership, nagios_hosts where nagios_hostgroups.hostgroup_name like '$value' and nagios_hostgroup_membership.hostgroup_id = nagios_hostgroups.hostgroup_id and nagios_hostgroup_membership.host_id = nagios_hosts.host_id order by nagios_hostgroups.hostgroup_name ASC, nagios_hosts.host_name ASC";
				break;
			case 'alias':
				$sql = "select nagios_hostgroups.hostgroup_id, nagios_hostgroups.hostgroup_name, nagios_hostgroups.alias, nagios_hosts.host_name as members from nagios_hostgroups, nagios_hostgroup_membership, nagios_hosts where nagios_hostgroups.alias like '$value' and nagios_hostgroup_membership.hostgroup_id = nagios_hostgroups.hostgroup_id and nagios_hostgroup_membership.host_id = nagios_hosts.host_id order by nagios_hostgroups.hostgroup_name ASC, nagios_hosts.host_name ASC";
				break;
			case 'id':
				$sql = "select nagios_hostgroups.hostgroup_id, nagios_hostgroups.hostgroup_name, nagios_hostgroups.alias, nagios_hosts.host_name as members from nagios_hostgroups, nagios_hostgroup_membership, nagios_hosts where nagios_hostgroups.hostgroup_id = $value and nagios_hostgroup_membership.hostgroup_id = nagios_hostgroups.hostgroup_id and nagios_hostgroup_membership.host_id = nagios_hosts.host_id order by nagios_hostgroups.hostgroup_name ASC, nagios_hosts.host_name ASC";		
				break;
			case 'hostmember':
				$sql = "select nagios_hostgroups.hostgroup_id, nagios_hostgroups.hostgroup_name, nagios_hostgroups.alias, nagios_hosts.host_name as members from nagios_hostgroups, nagios_hostgroup_membership, nagios_hosts where nagios_hosts.host_name like '$value' and nagios_hostgroup_membership.hostgroup_id = nagios_hostgroups.hostgroup_id and nagios_hostgroup_membership.host_id = nagios_hosts.host_id order by nagios_hostgroups.hostgroup_name ASC, nagios_hosts.host_name ASC";		
				break;
		}
		
		$hostgroup_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['hostgroup_id'] != $lastid) {
					$hostgroup_list[] = $result->fields;
					$lastid = $result->fields['hostgroup_id'];
				} else {
					$index = count( $hostgroup_list) -1;
					$hostgroup_list[$index]['members'] .= ", " . $result->fields['members'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_host_template( $type, $value, &$host_template_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select nht.host_template_id, nht.template_name as host_template_name, nht.template_description as description, nh.host_name as affects from nagios_host_templates as nht left join nagios_hosts as nh on nh.use_template_id=nht.host_template_id where nht.template_name like '$value' order by nht.template_name ASC, nh.host_name ASC";
				break;
			case 'description':
				$sql = "select nht.host_template_id, nht.template_name as host_template_name, nht.template_description as description, nh.host_name as affects from nagios_host_templates as nht left join nagios_hosts as nh on nh.use_template_id=nht.host_template_id where nht.template_description like '$value' order by nht.template_name ASC, nh.host_name ASC";
				break;
			case 'id':
				$sql = "select nht.host_template_id, nht.template_name as host_template_name, nht.template_description as description, nh.host_name as affects from nagios_host_templates as nht left join nagios_hosts as nh on nh.use_template_id=nht.host_template_id where nht.host_template_id = $value order by nht.template_name ASC, nh.host_name ASC";
				break;
			case 'affects':
				$sql = "select nht.host_template_id, nht.template_name as host_template_name, nht.template_description as description, nh.host_name as affects from nagios_host_templates as nht left join nagios_hosts as nh on nh.use_template_id=nht.host_template_id where nh.host_name like '$value' order by nht.template_name ASC, nh.host_name ASC";
				break;
		}
		
		$host_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['host_template_id'] != $lastid) {
					$host_template_list[] = $result->fields;
					$lastid = $result->fields['host_template_id'];
				} else {
					$index = count( $host_template_list) -1;
					$host_template_list[$index]['affects'] .= ", " . $result->fields['host_name'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_service( $type, $value, &$service_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select ns.service_id, ns.service_description, nh.host_name, nh.host_id, nss.servicegroup_id, nsg.servicegroup_name as servicegroups from nagios_services as ns left join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id left join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id, nagios_hosts as nh where ns.service_description like '$value' and ns.host_id = nh.host_id order by ns.service_name ASC";
				break;
			case 'id':
				$sql = "select ns.service_id, ns.service_description, nh.host_name, nh.host_id, nss.servicegroup_id, nsg.servicegroup_name as servicegroups from nagios_services as ns left join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id left join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id, nagios_hosts as nh where ns.service_id = $value and ns.host_id = nh.host_id order by ns.service_name ASC";		
				break;
			case 'host':
				$sql = "select ns.service_id, ns.service_description, nh.host_name, nh.host_id, nss.servicegroup_id, nsg.servicegroup_name as servicegroups from nagios_services as ns left join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id left join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id, nagios_hosts as nh where nh.host_name like '$value' and ns.host_id = nh.host_id order by ns.service_name ASC";		
				break;
			case 'servicegroup':
				$sql = "select ns.service_id, ns.service_description, nh.host_name, nh.host_id, nss.servicegroup_id, nsg.servicegroup_name as servicegroups from nagios_services as ns left join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id left join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id, nagios_hosts as nh where nsg.servicegroup_name like '$value' and ns.host_id = nh.host_id order by ns.service_name ASC";		
				break;
	
		}
		
		$service_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['service_id'] != $lastid) {
					$service_list[] = $result->fields;
					$lastid = $result->fields['service_id'];
				} else {
					$index = count( $service_list) -1;
					$service_list[$index]['servicegroups'] .= ", " . $result->fields['servicegroups'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_servicegroup( $type, $value, &$servicegroup_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select nsg.servicegroup_id, nsg.servicegroup_name, nsg.alias, ns.service_description as memberservices from nagios_services as ns right join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id right join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id where nsg.servicegroup_name like '$value' order by nsg.servicegroup_name ASC, ns.service_name ASC";
				
				break;
			case 'description':
				$sql = "select nsg.servicegroup_id, nsg.servicegroup_name, nsg.alias, ns.service_description as memberservices from nagios_services as ns right join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id right join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id where nsg.alias like '$value' order by nsg.servicegroup_name ASC, ns.service_name ASC";
				
				break;
			case 'servicemember':
				$sql = "select nsg.servicegroup_id, nsg.servicegroup_name, nsg.alias, ns.service_description as memberservices from nagios_services as ns right join nagios_servicegroup_membership as nss on ns.service_id = nss.service_id right join nagios_servicegroups as nsg on nss.servicegroup_id = nsg.servicegroup_id where ns.service_description like '$value' order by nsg.servicegroup_name ASC, ns.service_name ASC";
				break;
		}
		
		$servicegroup_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['servicegroup_id'] != $lastid) {
					$servicegroup_list[] = $result->fields;
					$lastid = $result->fields['servicegroup_id'];
				} else {
					$index = count( $servicegroup_list) -1;
					$servicegroup_list[$index]['memberservices'] .= ", " . $result->fields['memberservices'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_service_template( $type, $value, &$service_template_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select nst.service_template_id, nst.template_name as service_template_name, nst.template_description as description, nh.host_name as affects from nagios_service_templates as nst left join nagios_services as ns on ns.use_template_id=nst.service_template_id left join nagios_hosts as nh on ns.host_id=nh.host_id where nst.template_name like '$value' order by nst.template_name ASC, nh.host_name ASC";
				break;
			case 'description':
				$sql = "select nst.service_template_id, nst.template_name as service_template_name, nst.template_description as description, nh.host_name as affects from nagios_service_templates as nst left join nagios_services as ns on ns.use_template_id=nst.service_template_id left join nagios_hosts as nh on ns.host_id=nh.host_id where nst.template_description like '$value' order by nst.template_name ASC, nh.host_name ASC";
				break;
			case 'id':
				$sql = "select nst.service_template_id, nst.template_name as service_template_name, nst.template_description as description, nh.host_name as affects from nagios_service_templates as nst left join nagios_services as ns on ns.use_template_id=nst.service_template_id left join nagios_hosts as nh on ns.host_id=nh.host_id where nst.service_template_id = $value order by nst.template_name ASC, nh.host_name ASC";
				break;
			case 'affects':
				$sql = "select nst.service_template_id, nst.template_name as service_template_name, nst.template_description as description, nh.host_name as affects from nagios_service_templates as nst left join nagios_services as ns on ns.use_template_id=nst.service_template_id left join nagios_hosts as nh on ns.host_id=nh.host_id where nh.host_name like '$value' order by nst.template_name ASC, nh.host_name ASC";
				break;
		}
		
		$service_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['service_template_id'] != $lastid) {
					$service_template_list[] = $result->fields;
					$lastid = $result->fields['service_template_id'];
				} else {
					$index = count( $service_template_list) -1;
					$service_template_list[$index]['affects'] .= ", " . $result->fields['host_name'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_command( $type, $value, &$command_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select command_id, command_name, command_desc, command_line from nagios_commands where command_name like '$value' order by command_name ASC";
				break;
			case 'description':
				$sql = "select command_id, command_name, command_desc, command_line from nagios_commands where command_desc like '$value' order by command_name ASC";
				break;
		}
		
		$command_list = array();
		$result = $this->dbConnection->Execute($sql);
		if($result){
			while (! $result->EOF) {
				$command_list[] = $result->fields;
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_contact( $type, $value, &$contact_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select nc.contact_id, nc.contact_name, nc.alias, ncg.contactgroup_name as contactgroups from nagios_contacts as nc right join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where nc.contact_name like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by nc.contact_name ASC";
				break;
			case 'description':
				$sql = "select nc.contact_id, nc.contact_name, nc.alias, ncg.contactgroup_name as contactgroups from nagios_contacts as nc right join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where nc.alias like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by nc.contact_name ASC";
				break;
			case 'contactgroup':
				$sql = "select nc.contact_id, nc.contact_name, nc.alias, ncg.contactgroup_name as contactgroups from nagios_contacts as nc right join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where ncg.contactgroup_name like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by nc.contact_name ASC";
				break;
		}
		
		$contact_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['contact_id'] != $lastid) {
					$contact_list[] = $result->fields;
					$lastid = $result->fields['contact_id'];
				} else {
					$index = count( $contact_list) -1;
					$contact_list[$index]['contactgroups'] .= ", " . $result->fields['contactgroups'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	private function search_contactgroup( $type, $value, &$contactgroup_list) {
		$sql = "";
		switch($type) {
			case 'name':
				$sql = "select ncg.contactgroup_id, ncg.contactgroup_name, ncg.alias, nc.contact_name as contacts from nagios_contacts as nc left join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where ncg.contactgroup_name like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by contactgroup_id order by ncg.contactgroup_name ASC, nc.contact_name ASC";
				break;
			case 'description':
				$sql = "select ncg.contactgroup_id, ncg.contactgroup_name, ncg.alias, nc.contact_name as contacts from nagios_contacts as nc left join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where ncg.alias like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by contactgroup_id order by ncg.contactgroup_name ASC, nc.contact_name ASC";
				break;
			case 'contactmember':
				$sql = "select ncg.contactgroup_id, ncg.contactgroup_name, ncg.alias, nc.contact_name as contacts from nagios_contacts as nc left join nagios_contactgroup_membership as ncgm on nc.contact_id = ncgm.contact_id, nagios_contactgroups as ncg where nc.contact_name like '$value' and ncg.contactgroup_id = ncgm.contactgroup_id order by contactgroup_id order by ncg.contactgroup_name ASC, nc.contact_name ASC";
				break;
		}
		
		$contactgroup_list = array();
		$result = $this->dbConnection->Execute($sql);
		$lastid = -1;
		if($result){
			while (! $result->EOF) {
				if( $result->fields['contactgroup_id'] != $lastid) {
					$contactgroup_list[] = $result->fields;
					$lastid = $result->fields['contactgroup_id'];
				} else {
					$index = count( $contactgroup_list) -1;
					$contactgroup_list[$index]['contacts'] .= ", " . $result->fields['contacts'];
				}
				$result->MoveNext();
			}
			
			return true;
		} else
			return false;
	}
	
	// Function:	doSearch
	// Description:	Searches the database using predefined parameters
	private function doSearch() {
		$this->searchResults = null;
		if( $_REQUEST['searchObjectType']) {
			$searchType = $_REQUEST['searchType'];
			$value = $_REQUEST['search'];
			if($searchType != 'id'){
				if(!preg_match('/\*/i', $value))
					$value = "%" . $value . "%";
			}
			$value = str_replace("*", "%", $value);
			$value = str_replace("_", "\\_", $value);
			
			if( $_REQUEST['searchObjectType'] == "host") {
				$searchHeader = array(
					array( "Host ID", "host_id"),
					array( "Host Name", "host_name"),
					array( "Alias", "alias"),
					array( "IP Address", "address"));
				$this->search_host( $searchType, $value, $searchInfo);
				$url = array( $path_config['doc_root'] . "hosts.php?host_id=@1@", "host_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "hostgroup") {
				$searchHeader = array(
					array( "Hostgroup ID", "hostgroup_id"),
					array( "Hostgroup Name", "hostgroup_name"),
					array( "Alias", "alias"),
					array( "Members", "members") );
				$this->search_hostgroup( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "hostgroups.php?hostgroup_id=@1@", "hostgroup_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "hosttemplate") {
				$searchHeader = array(
					array( "Host Template ID", "host_template_id"),
					array( "Template Name", "host_template_name"),
					array( "Description", "description"),
					array( "Affects", "affects"));
				$this->search_host_template( $searchType, $value, $searchInfo);
				$url = array( $path_config['doc_root'] . "host_templates.php?host_template_id=@1@", "host_template_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "service") {
				$searchHeader = array(
					array( "Service ID", "service_id"),
					array( "Service Name", "service_description"),
					array( "Host Name", "host_name"),
					array( "Servicegroups", "servicegroups") );
				$this->search_service( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "hosts.php?host_id=@1@&section=services&service_id=@2@", "host_id", "service_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "servicegroup") {
				$searchHeader = array(
					array( "Servicegroup ID", "servicegroup_id"),
					array( "Servicegroup Name", "servicegroup_name"),
					array( "Description", "alias"),
					array( "Services", "memberservices") );
				$this->search_servicegroup( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "servicegroups.php?servicegroup_id=@1@", "servicegroup_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "servicetemplate") {
				$searchHeader = array(
					array( "Service Template ID", "service_template_id"),
					array( "Template Name", "service_template_name"),
					array( "Description", "description"),
					array( "Affects", "affects") );
				$this->search_service_template( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "service_templates.php?service_template_id=@1@", "service_template_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "command") {
				$searchHeader = array(
					array( "Command Name", "command_name"),
					array( "Description", "command_desc"),
					array( "Command", "command_line") );
				$this->search_command( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "commands.php?command_id=@1@", "command_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "contact") {
				$searchHeader = array(
					array( "Contact Name", "contact_name"),
					array( "Description", "alias"),
					array( "Contactgroups", "contactgroups") );
				$this->search_contact( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "contacts.php?contact_id=@1@", "contact_id");
			}
			
			if( $_REQUEST['searchObjectType'] == "contactgroup") {
				$searchHeader = array(
					array( "Contactgroup Name", "contactgroup_name"),
					array( "Description", "alias"),
					array( "Contacts", "contacts") );
				$this->search_contactgroup( $searchType, $value,  $searchInfo);
				$url = array( $path_config['doc_root'] . "contactgroups.php?contactgroup_id=@1@", "contactgroup_id");
			}
		}
		
		if( $searchInfo && $url && count( $searchInfo) == 1) {
			$link = $url[0];
			for( $i=1;$i<count($url);$i++) {
				$link = str_replace( "@$i@", $searchInfo[0][$url[$i]], $link);
			}
			print "<html><body><script>window.location='$link';</script></body></html>";
			exit();
		}
		
		$this->searchResults = $searchInfo;
		$this->searchHeader = $searchHeader;
		$this->searchLink = $url;
	}
	
	// Function:	renderBasicSearch
	// Description:	Creates a "small" version of this search
	public function renderBasicSearch() {
	?>
<script type="text/javascript">
<!--
function UpdateTypeList(objid) {
	for(i=(document.forms['searchForm'].searchType.length-1); i>=0; i--){
		document.forms['searchForm'].searchType.options[i] = null;
	}
	var count=0;
	for (var i in ObjectTypearray[objid]){
		try{
			document.forms['searchForm'].searchType.options[count] = new Option(ObjectTypearray[objid][i][0], ObjectTypearray[objid][i][1]);
		} catch(e){
		}
		count++;
	}
		
}

var ObjectTypearray= new Array();
ObjectTypearray["host"]= new Array();
ObjectTypearray['host'][0]= new Array();
ObjectTypearray['host'][1]= new Array();
ObjectTypearray['host'][2]= new Array();
ObjectTypearray['host'][3]= new Array();
ObjectTypearray['hostgroup']= new Array();
ObjectTypearray['hostgroup'][0]= new Array();
ObjectTypearray['hostgroup'][1]= new Array();
ObjectTypearray['hostgroup'][2]= new Array();
ObjectTypearray['hostgroup'][3]= new Array();
ObjectTypearray['hosttemplate'] = new Array();
ObjectTypearray['hosttemplate'][0]= new Array();
ObjectTypearray['hosttemplate'][1]= new Array();
ObjectTypearray['hosttemplate'][2]= new Array();
ObjectTypearray['hosttemplate'][3]= new Array();
ObjectTypearray['service']= new Array();
ObjectTypearray['service'][0]= new Array();
ObjectTypearray['service'][1]= new Array();
ObjectTypearray['service'][2]= new Array();
ObjectTypearray['service'][3]= new Array();
ObjectTypearray['servicegroup']= new Array();
ObjectTypearray['servicegroup'][0]= new Array();
ObjectTypearray['servicegroup'][1]= new Array();
ObjectTypearray['servicegroup'][2]= new Array();
ObjectTypearray['servicetemplate'] = new Array();
ObjectTypearray['servicetemplate'][0]= new Array();
ObjectTypearray['servicetemplate'][1]= new Array();
ObjectTypearray['servicetemplate'][2]= new Array();
ObjectTypearray['servicetemplate'][3]= new Array();
ObjectTypearray['command']= new Array();
ObjectTypearray['command'][0]= new Array();
ObjectTypearray['command'][1]= new Array();
ObjectTypearray['contact']= new Array();
ObjectTypearray['contact'][0]= new Array();
ObjectTypearray['contact'][1]= new Array();
ObjectTypearray['contact'][2]= new Array();
ObjectTypearray['contactgroup']= new Array();
ObjectTypearray['contactgroup'][0]= new Array();
ObjectTypearray['contactgroup'][1]= new Array();
ObjectTypearray['contactgroup'][2]= new Array();

ObjectTypearray['host'][0][0] = 'Name';
ObjectTypearray['host'][1][0] = 'Alias';
ObjectTypearray['host'][2][0] = 'ID';
ObjectTypearray['host'][3][0] = 'IP Address';
ObjectTypearray['host'][0][1] = 'name';
ObjectTypearray['host'][1][1] = 'alias';
ObjectTypearray['host'][2][1] = 'id';
ObjectTypearray['host'][3][1] = 'ip';

ObjectTypearray['hostgroup'][0][0] = 'Name';
ObjectTypearray['hostgroup'][1][0] = 'Alias';
ObjectTypearray['hostgroup'][2][0] = 'ID';
ObjectTypearray['hostgroup'][3][0] = 'Host Member';	
ObjectTypearray['hostgroup'][0][1] = 'name';
ObjectTypearray['hostgroup'][1][1] = 'alias';
ObjectTypearray['hostgroup'][2][1] = 'id';
ObjectTypearray['hostgroup'][3][1] = 'hostmember';

ObjectTypearray['hosttemplate'][0][0] = 'Name';
ObjectTypearray['hosttemplate'][1][0] = 'Description';
ObjectTypearray['hosttemplate'][2][0] = 'ID';
ObjectTypearray['hosttemplate'][3][0] = 'Affects';
ObjectTypearray['hosttemplate'][0][1] = 'name';
ObjectTypearray['hosttemplate'][1][1] = 'description';
ObjectTypearray['hosttemplate'][2][1] = 'id';
ObjectTypearray['hosttemplate'][3][1] = 'affects';

ObjectTypearray['service'][0][0] = 'Name';
ObjectTypearray['service'][1][0] = 'ID';
ObjectTypearray['service'][2][0] = 'Host';
ObjectTypearray['service'][3][0] = 'Service Group';
ObjectTypearray['service'][0][1] = 'name';
ObjectTypearray['service'][1][1] = 'id';
ObjectTypearray['service'][2][1] = 'host';
ObjectTypearray['service'][3][1] = 'servicegroup';

ObjectTypearray['servicegroup'][0][0] = 'Name';
ObjectTypearray['servicegroup'][1][0] = 'Description';
ObjectTypearray['servicegroup'][2][0] = 'Service Member';
ObjectTypearray['servicegroup'][0][1] = 'name';
ObjectTypearray['servicegroup'][1][1] = 'description';
ObjectTypearray['servicegroup'][2][1] = 'servicemember';

ObjectTypearray['servicetemplate'][0][0] = 'Name';
ObjectTypearray['servicetemplate'][1][0] = 'ID';
ObjectTypearray['servicetemplate'][2][0] = 'Description';
ObjectTypearray['servicetemplate'][3][0] = 'Affects';
ObjectTypearray['servicetemplate'][0][1] = 'name';
ObjectTypearray['servicetemplate'][1][1] = 'id';
ObjectTypearray['servicetemplate'][2][1] = 'description';
ObjectTypearray['servicetemplate'][3][1] = 'affects';

ObjectTypearray['command'][0][0] = 'Name';
ObjectTypearray['command'][1][0] = 'Description';
ObjectTypearray['command'][0][1] = 'name';
ObjectTypearray['command'][1][1] = 'description';

ObjectTypearray['contact'][0][0] = 'Name';
ObjectTypearray['contact'][1][0] = 'Description';
ObjectTypearray['contact'][2][0] = 'Contact Group';
ObjectTypearray['contact'][0][1] = 'name';
ObjectTypearray['contact'][1][1] = 'description';
ObjectTypearray['contact'][2][1] = 'contactgroup';

ObjectTypearray['contactgroup'][0][0] = 'Name';
ObjectTypearray['contactgroup'][1][0] = 'Description';
ObjectTypearray['contactgroup'][2][0] = 'Contact Member';
ObjectTypearray['contactgroup'][0][1] = 'name';
ObjectTypearray['contactgroup'][1][1] = 'description';
ObjectTypearray['contactgroup'][2][1] = 'contactmember';

-->
</script>
<form target="rightHome" method="post" action="<?=$path_config['doc_root']?>search.php" id="searchForm" name="searchForm">
<table cellspacing="0" cellpadding="0">
<tr>
	<td><font color="black">Search for</font>&nbsp;</td>
	<td><select id="searchObjectType" name="searchObjectType" onchange="UpdateTypeList(this.options[this.selectedIndex].value);">
		<option selected="selected" value="host">Host</option>
		<option value="hostgroup">Host Group</option>
		<option value="hosttemplate">Host Template</option>
		<option value="service">Service</option>
		<option value="servicegroup">Service Group</option>
		<option value="servicetemplate">Service Template</option>
		<option value="command">Command</option>
		<option value="contact">Contact</option>
		<option value="contactgroup">Contact Group</option>
	</select></td>
	<td>&nbsp;<font color="black">Search by&nbsp;</font></td>
	<td><select name="searchType" id="searchType">
		<option selected="selected" value="name">Name</option>
		<option value="alias">Alias</option>
		<option value="id">ID</option>
		<option value="ip">IP Address</option>
	</select></td>
</tr>
<tr>
	<td align="right" colspan="4"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="4" width="1" /></td>
</tr>
<tr>
	<td colspan=3><input size=43 type="text" name="search" id="search" /></td>
	<td>&nbsp;<input type="submit" value="Search" /></td>
</tr>
</table></form>
	<?php
	}
	
	// Function:	renderAdvancedSearch
	// Description:	Creates a more thorough version of this search
	public function renderAdvancedSearch() {
	}
	
	// Function:	renderResults
	// Description:	Displays all results of the search
	public function renderResults() {
		// Get the results first...
		$this->doSearch();
		$url = $this->searchLink;
		
		global $fruity;
		$fruity->prepare_for_use( $this->searchResults);
		
		print_header("Search");
		echo "<br />\n";
		
		// Check if nothing has been returned
		if( $this->searchResults === null || $this->searchHeader === null) {
			print_window_header( "Search Error", "100%", "center");
			echo "Please try re-submitting your search";
			print_window_footer();
		} else {
			print_window_header( "Search Results", "100%", "center");
			
			// Check if there were no results returned
			if( count( $this->searchResults) == 0) {
				print "No results returned.  Please try your search again.";
			} else {
				// More than 1 result returned
				$size = count( $this->searchHeader);
				$numOfResults = count( $this->searchResults);
				
?>
<b>Results:</b> <?=$numOfResults?><br />
<table width="95%" border="0" align="center">
<tr>
<td valign="top">
<table align="center" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td width="1" bgcolor="#ffffff"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	<?
	if(count($this->searchHeader)) {
		foreach( $this->searchHeader as $header) {
			print "		<td style=\"padding: 2px;\" align=\"center\" valign=\"bottom\"><b>" . $header[0] . "</b></td>";
		}
	}
	?>
		<td width="1" bgcolor="#ffffff"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="<?=$size?>" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
	for($counter = 0; $counter < $numOfResults; $counter++) {
		$link = $url[0];
		for( $i=1;$i<count($url);$i++) {
			$link = str_replace( "@$i@", $this->searchResults[$counter][$url[$i]], $link);	
		}
		
		if($counter % 2) {
			?>
			<tr bgcolor="#cccccc">
			<?php
		}
		else {
			?>
			<tr bgcolor="#f0f0f0">
			<?php
		}
		?>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<?
			if(count($this->searchHeader)) {
				foreach( $this->searchHeader as $header) {
					print "			<td style=\"padding: 2px\" valign=\"top\"><a href=\"$link\">" . $this->searchResults[$counter][$header[1]] . "</a></td>\n";
				}
			}
			?>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<tr>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td colspan="<?=$size?>" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
			<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
		<?php
	}
	?>
</table><br />
</table>
<?
			}
			
			print_window_footer();
		}
		echo "<br />";
		
		print_footer();
	}
}

// $module_search = new module_search_selective;

?>