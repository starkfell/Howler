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

class Fruity {
	// Database connection parameters
	private $dbServ;	// Either mysql or sqlite
	private $dbHost;
	private $dbUsername;
	private $dbPassword;
	private $dbDatabase;

	// Internal Variables
	private $authHandler;  // Can only have one authentication module
	private $sidebarHandler;	// To hold the sidebar module
	private $searchHandler;	// To hold the search bar module
	
	// Base Object Instances
	/*
		These are not guaranteed to stay, only preliminary variables
	*/	
	private $service;
	private $servicegroup;
	private $host;
	private $hostgroup;
	private $contact;
	private $contactgroups;
	private $commands;
	private $timeperiods;
	private $serviceescalations;
	private $servicedependencies;
	private $hostescalations;
	private $hostdependencies;
	private $hostextended;
	private $serviceextended;
	// End Base Object Instances
	
	private $preExportHandlers = null;	// To hold handlers for export
	private $postExportHandlers = null;	// To hold handlers for post-export
	private $errorMsg = null;	// Holds the last error message (that is considered non fatal).
	private $dbConnection = null;	// To hold the adoDB connection
	
	private $outputHandler = null;	// Not sure if we want to do this.  This is ugly
	
	function __construct() {
		global $sitedb_config;
		$this->dbServ = $sitedb_config['dbserv'];
		$this->dbHost = $sitedb_config['host'];
		$this->dbUsername = $sitedb_config['username'];
		$this->dbPassword = $sitedb_config['password'];
		$this->dbDatabase = $sitedb_config['database'];	
		// Setup internal variables
		$this->outputHandler = new fruityOutput;
		// Connect database
		$this->dbConnection = ADONewConnection($this->dbServ);
		// Do an initial connection
		$this->dbConnection->PConnect($this->dbHost, $this->dbUsername,
							$this->dbPassword,$this->dbDatabase);
		if(!$this->dbConnection->IsConnected()) {
			print("Unable to connect to database.  Please check your configuration.");
			die();
		}
		$this->dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		
		// Default module load, will be replaced with dynamic load
		$this->setAuthHandler(new module_auth_alwaysadmin);
		// Commented out for testing
		$this->setSearchHandler(new module_search_simple);
		$this->setSideBarHandler(new module_sidenav_preload);
		
		$this->host = new hostObject;
		
		//Debug
		//$this->dbConnection->debug = true;
		
	}
	
	function __destruct() {
	}
	
	function init() {
		// Call the restart() for our handlers
		if(isset($this->authHandler)) {
			$this->authHandler->init();
		}
		if(isset($this->searchHandler)) {
			$this->searchHandler->init();
		}
		if(isset($this->sidebarHandler)) {
			$this->sidebarHandler->init();
		}
	}
	
	function restart() {
		// Recreate the dbConnection
		$this->dbConnection = ADONewConnection($this->dbServ);
		$this->dbConnection->PConnect($this->dbHost, $this->dbUsername,
							$this->dbPassword,$this->dbDatabase);
		if(!$this->dbConnection->IsConnected()) {
			print("Unable to connect to database.  Please check your configuration.");
			die();
		}
		$this->dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
		
		// Call the restart() for our handlers
		if(isset($this->authHandler)) {
			$this->authHandler->restart();
		}
		if(isset($this->searchHandler)) {
			$this->searchHandler->restart();
		}
		if(isset($this->sidebarHandler)) {
			$this->sidebarHandler->restart();
		}
	}
		
	
	public function sideBar() {
		// Calls sidebarHandler's render function
		if(isset($this->sidebarHandler)) {
			$this->sidebarHandler->render();
			return;
		}
		else {
			return;
		}
	}
	
	public function searchRender() {
		// Calls searchHandler's render function
		if(isset($this->searchHandler)) {
			$this->searchHandler->render();
			return;
		}
		else {
			return;
		}
	}
	
	public function isSideBarIncluded() {
		if(isset($this->sidebarHandler)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function isSearchIncluded() {
		if(isset($this->searchHandler)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function getOutputHandler() {
		return $this->outputHandler;
	}
	
	public function getSearchHandler() {
		return $this->searchHandler;
	}
	
	public function getHostObject() {
		return $this->host;
	}
	
	// For preparing variables for database entry
	public function prepare_for_sql(&$obj) {
		if( $obj != NULL) {
			if( is_array( $obj)) {
				foreach( $obj as $key=>$val) {
					$obj[$key] = $this->prepare_for_sql($val);
				}
			} elseif( is_string( $obj)) {
				$obj = html_entity_decode( $obj);
				$obj = addslashes( $obj);
				// Not good for default behaviour
				// Procedures which do LIKE statements should do additional formatting themselves before passing to sql.
				//$obj = str_replace( "_", "\\_", $obj);
			}
			
			return $obj;
		}
		
		return NULL;
	}
	
	// For preparing variables for usage
	public function prepare_for_use(&$obj) {
		if($obj != NULL) {
			if( is_array( $obj)) {
				foreach( $obj as $key=>$val) {			
					$obj[$key] = $this->prepare_for_use($val);
				}
			} elseif( is_string( $obj)) {
				$obj = stripslashes( $obj);
				$obj = htmlspecialchars( $obj);
			}
			
			return $obj;
		}
		return NULL;
	}
	
	function logout_user() {
		$this->authHandler->logout();
	}
	
	function setAuthHandler($authObject) {
		if($this->authHandler != NULL) {
			// We're only supposed to have one authhandler.  It was already 
			// set.  Therefore, we'll perform a fatal error.
			print_error(1000, "Error assigning Fruity's Authentication Handler.  A Authentication Handler was already assigned!", 1);
		}
		else {
			$this->authHandler = $authObject;
		}
	}
	
	function search_hosts($criteria) {
		$results = array();
		$sql = "SELECT nh.host_id, nh.host_name, nh.address, nh.alias FROM nagios_hosts AS nh"
			." WHERE nh.host_name LIKE '$criteria%'"
			." ORDER BY nh.host_name ASC";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if(!isset($results[$result->fields['host_id']])) {
					$results[$result->fields['host_id']] = $result->fields;
				}
				$result->MoveNext();
			}
		}
		return $results;		
	}

        // <--- Added 01.04.2012 --> Query returns all IP Addresses within the OpCfg 'nagios_hosts' Table.
	function search_ipaddress() {
		$results = array();
		$sql = "SELECT nh.address FROM nagios_hosts AS nh";
		$result = $this->dbConnection->Execute( $sql);
		
		if( $result->fields) {
			while( !$result->EOF) {
				if(!isset($results[$result->fields['address']])) {
					$results[$result->fields['address']] = $result->fields;
				}
				$result->MoveNext();
			}
		}
		return $results;		
	}

       // <--- Added 01.04.2012 --> Query returns all Server Names within the OpCfg 'nagios_hosts' Table.
        function search_hostnames() {
                $results = array();
                $sql = "SELECT nh.host_name FROM nagios_hosts AS nh";
                $result = $this->dbConnection->Execute( $sql);

                if( $result->fields) {
                        while( !$result->EOF) {
                                if(!isset($results[$result->fields['host_name']])) {
                                        $results[$result->fields['host_name']] = $result->fields;
                                }
                                $result->MoveNext();
                        }
                }
                return $results;
        }

	function setSidebarHandler($sidebarObject) {
		if($this->sidebarHandler != NULL) {
			print_error(1000, "Error assigning Fruity's Sidebar Handler.  A Sidebar Handler was already assigned!", 1);
		}
		else {
			$this->sidebarHandler = $sidebarObject;
		}
	}
	
	function setSearchHandler($searchObject) {
		if($this->searchHandler != NULL) {
			print_error(1000, "Error assigning Fruity's Search Handler.  A Search Handler was already assigned!", 1);
		}
		else {
			$this->searchHandler = $searchObject;
		}
	}
	
	function setErrorMsg($error_msg) {
		$this->errorMsg = $error_msg;
	}

	function hasPrivilege($priv) {
		return $this->authHandler->hasPrivilege($priv);
	}
	
	function ErrorMsgSet() {
		if($this->errorMsg != NULL) return true;
		else return false;
	}
	
	function ErrorMsg($erase = 0) {
		$tempMsg = $this->errorMsg;
		if($erase)
			$this->errorMsg = NULL;
		return $tempMsg;
	}
	
	function get_toplevel_hosts_list(&$children_lists) {
		$children_lists = array();
		//$query = "SELECT host_id, host_name, alias FROM nagios_hosts WHERE parents = '0' ORDER BY host_name ASC";
		$query = "SELECT host_id, host_name, alias FROM nagios_hosts WHERE parents IS NULL ORDER BY host_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$children_lists[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($children_lists);
		return 1;
	}
	
	function get_children_hosts_list($host_id, &$children_lists) {
		$children_lists = array();
		if($host_id == 0)
			$query = "SELECT host_id, host_name, alias FROM nagios_hosts WHERE parents IS NULL ORDER BY host_name ASC";
		else
			$query = "SELECT host_id, host_name, alias FROM nagios_hosts WHERE parents = '$host_id' ORDER BY host_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$children_lists[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($children_lists);
		return 1;
	}
	
	function return_period_list(&$period_list) {
		$period_list = array();
		$query = "SELECT timeperiod_id,timeperiod_name,alias FROM nagios_timeperiods ORDER BY timeperiod_name ASC";
		$result = $this->dbConnection->Execute($query);	
		if ($result == false)
		{
			print $this->dbConnection->ErrorMsg();
			return E_QUERY_FAILED;
		}
		while (! $result->EOF)
		{
			$period_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($period_list);
		return 1;
	}
	
	function get_contactgroup_list(&$contactgroups_list) {
		$contactgroups_list = array();
		$query = "SELECT contactgroup_id, contactgroup_name, alias FROM nagios_contactgroups ORDER BY contactgroup_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$contactgroups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($contactgroups_list);
		return 1;
	}
	
	function element_desc($element, $table) {
		$this->prepare_for_sql( $element);
		$query = "SELECT field_desc FROM $table WHERE field_name = '$element'";
		$result = $this->dbConnection->Execute($query);
		//return $this->prepare_for_use($result->fields['field_desc']);
		return $result->fields['field_desc'];
	}
	
	function contactgroup_exists($contactgroup_name) {
		$this->prepare_for_sql( $contactgroup_name);
		$query = "SELECT contactgroup_id FROM nagios_contactgroups WHERE contactgroup_name = '$contactgroup_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_template_exists($template_name) {
		$this->prepare_for_sql( $template_name);
		$query = "SELECT host_template_id FROM nagios_host_templates WHERE template_name = '$template_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function service_template_exists($template_name) {
		$this->prepare_for_sql( $template_name);
		$query = "SELECT service_template_id FROM nagios_service_templates WHERE template_name = '$template_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}	
	
	function get_command($command_id, &$commandInfo) {
		$query = "SELECT * FROM nagios_commands WHERE command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
		$commandInfo = $this->prepare_for_use($result->fields);
	}
	
	function get_command_by_name($command_name) {
		$query = "SELECT * FROM nagios_commands WHERE command_name = '$command_name'";
		$result = $this->dbConnection->Execute($query);
		if(!$result->EOF) {
			return $result->fields;
		}
		return NULL;
		
	}
	
	function get_host_extended_info($host_id, &$extended_info) {
		$query = "SELECT * FROM nagios_hosts_extended_info WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$extended_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else 
			return false;
	}
	
	function return_host_parents_list($host_id, &$parents_list) {
		$parents_list = array();
		$query = "SELECT * FROM nagios_host_parents WHERE child_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			while(!$result->EOF) {
				$parents_list[] = $result->fields;
				$result->MoveNext();
			}
		}
	}
	
	function host_has_parent($host_id, $parent_id) {
		$query = "SELECT * FROM nagios_host_parents WHERE child_id = '$host_id' AND parent_id = '$parent_id'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		return true;
	}
	
	function host_add_parent($host_id, $parent_id) {
		$query = "INSERT INTO nagios_host_parents VALUES('$host_id', '$parent_id')";
		$result = $this->dbConnection->Execute($query);
		return true;
	}
	
	function host_delete_parent($host_id, $parent_id) {
		$query = "DELETE FROM nagios_host_parents WHERE child_id = '$host_id' AND parent_id = '$parent_id'";
		$result = $this->dbConnection->Execute($query);
		return true;
		
	}
	
	function get_host_template_extended_info($host_template_id, &$extended_info) {
		$extended_info = NULL;
		$query = "SELECT * FROM nagios_host_template_extended_info WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$extended_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else 
			return false;
	}
	
	function get_service_template_extended_info($service_template_id, &$extended_info) {
		$extended_info = NULL;
		$query = "SELECT * FROM nagios_service_template_extended_info WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$extended_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else 
			return false;
	}
	
	function get_service_extended_info($service_id, &$extended_info) {
		$query = "SELECT * FROM nagios_services_extended_info WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$extended_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else 
			return false;
	}
	
	function return_host_command($host_id) {
		$complete_command = array();
		$this->get_host_info($host_id, $tempHost);
		
		if( isset( $tempHost['check_command'])) {
			$complete_command[] = $this->return_command_name( $tempHost['check_command']);
			
			$this->get_host_check_command_parameters( $host_id, $tempParameters);
			if(count($tempParameters)) {
				foreach( $tempParameters as $parameter) {
					$complete_command[] = $parameter['parameter'];
				}
			}
		} elseif( isset( $tempHost['use_template_id'])) {
			$complete_command = $this->return_host_template_command( $tempHost['use_template_id']);
			
			// Check if there might be arguments to add to this
			if( count( $complete_command) > 0) {
				$this->get_host_check_command_parameters( $host_id, $tempParameters);
				if(count($tempParameters)) {
					foreach( $tempParameters as $parameter) {
						$complete_command[] = $parameter['parameter'];
					}
				}
			}
		}
		
		return $complete_command;
	}
	
	function return_host_template_command($host_template_id) {
		$complete_command = array();
		$this->get_host_template_info($host_template_id, $tempHostTemplate);
		if( isset( $tempHostTemplate['check_command'])) {
			$complete_command[] = $this->return_command_name( $tempHostTemplate['check_command']);
			$this->get_host_template_check_command_parameters( $host_template_id, $tempParameters);
			if(count($tempParameters)) {
				foreach( $tempParameters as $parameter) {
					$complete_command[] = $parameter['parameter'];
				}
			}
		} elseif( isset( $tempHostTemplate['use_template_id'])) {
			$complete_command = $this->return_host_template_command( $tempHostTemplate['use_template_id']);
			
			// check if there might be arguments to add to this
			if( count( $complete_command) > 0) {
				$this->get_host_template_check_command_parameters( $host_template_id, $tempParameters);
				if(count($tempParameters)) {
					foreach( $tempParameters as $parameter) {
						$complete_command[] = $parameter['parameter'];
					}
				}
			}
		}
		
		return $complete_command;
	}
	
	function return_service_command($service_id) {
		$complete_command = array();
		$this->get_service_info($service_id, $tempService);
		if( isset( $tempService['check_command'])) {
			$complete_command[] = $this->return_command_name( $tempService['check_command']);
			$this->get_service_check_command_parameters( $service_id, $tempParameters);
			if(count($tempParameters)) {
				foreach( $tempParameters as $parameter) {
					$complete_command[] = $parameter['parameter'];
				}
			}
		} elseif( isset( $tempService['use_template_id'])) {
			$complete_command = $this->return_service_template_command( $tempService['use_template_id']);
			
			// Check if there might be arguments to add to this
			if( count( $complete_command) > 0) {
				$this->get_service_check_command_parameters( $service_id, $tempParameters);
				if(count($tempParameters)) {
					foreach( $tempParameters as $parameter) {
						$complete_command[] = $parameter['parameter'];
					}
				}
			}
		}
		
		return $complete_command;
	}
	
	function return_service_template_command($service_template_id) {
		$complete_command = array();
		$this->get_service_template_info($service_template_id, $tempServiceTemplate);
		if( isset( $tempServiceTemplate['check_command'])) {
			$complete_command[] = $this->return_command_name( $tempServiceTemplate['check_command']);
			$this->get_service_template_check_command_parameters( $service_template_id, $tempParameters);
			if(count($tempParameters)) {
				foreach( $tempParameters as $parameter) {
					$complete_command[] = $parameter['parameter'];
				}
			}
		} elseif( isset( $tempServiceTemplate['use_template_id'])) {
			$complete_command = $this->return_service_template_command( $tempServiceTemplate['use_template_id']);
			
			// Check if there might be arguments to add to this
			if( count( $complete_command) > 0) {
				$this->get_service_template_check_command_parameters( $service_template_id, $tempParameters);
				if(count($tempParameters)) {
					foreach( $tempParameters as $parameter) {
						$complete_command[] = $parameter['parameter'];
					}
				}
			}
		}
		
		return $complete_command;
	}
	
	function return_command_name($command_id) {
		$query = "SELECT command_name FROM nagios_commands WHERE command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_name']);
	}
	
	function get_cgi_conf(&$cgi_conf) {
		$query = "SELECT * FROM nagios_cgi";
		$result = $this->dbConnection->Execute($query);
		$cgi_conf = $this->prepare_for_use($result->fields);
	}
	
	
	function get_resource_conf(&$resource_conf) {
		$query = "SELECT * FROM nagios_resources";
		$result = $this->dbConnection->Execute($query);
		$resource_conf = $this->prepare_for_use($result->fields);
	}
	
	function update_resource_conf($resource_conf) {	
		$this->prepare_for_sql($resource_conf);
		$query = "DELETE FROM nagios_resources";
		$result = $this->dbConnection->Execute($query);
		$query = "INSERT INTO nagios_resources (";
		$query .= implode(", ", array_keys($resource_conf));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($resource_conf));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
	}
	
	function update_main_conf($main_conf) {
		$this->prepare_for_sql( $main_conf);
		$query = "SELECT * FROM nagios_main";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result, $main_conf,1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function get_main_conf(&$main_config) {
		$query = "SELECT * FROM nagios_main";
		$result = $this->dbConnection->Execute($query);
		$main_config = $this->prepare_for_use($result->fields);
	}
	
	function update_cgi_conf($cgi_conf) {
		$this->prepare_for_sql( $cgi_conf);
		
		$query = "SELECT * FROM nagios_cgi";
		$result = $this->dbConnection->Execute($query);
		
		if( !$result->_numOfRows) {
			$sql = "INSERT INTO `nagios_cgi` VALUES (NULL, NULL, NULL, NULL, NULL,"
					." NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,"
					." NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
			
			$result = $this->dbConnection->Execute( $sql);
			
			if( !$result) {
				return false;
			}
			
			$query = "SELECT * FROM nagios_cgi";
			$result = $this->dbConnection->Execute($query);
		}
		
		$query = $this->dbConnection->GetUpdateSQL($result, $cgi_conf,1);
		$result = $this->dbConnection->Execute($query);
		
		if($result)
			return true;
		else 
			return false;
	}
	
	function modify_host_extended($host_id, $extended_info) {
		$this->prepare_for_sql( $extended_info);
		$query = "SELECT * FROM nagios_hosts_extended_info WHERE host_id = '".$host_id."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$extended_info,1);
		$result = $this->dbConnection->Execute($query);
	}
	

	function modify_host_template_extended($host_template_id, $extended_info) {
		$this->prepare_for_sql( $extended_info);
		$query = "SELECT * FROM nagios_host_template_extended_info WHERE host_template_id = '".$host_template_id."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$extended_info,1);
		$result = $this->dbConnection->Execute($query);		
	}	
	
	function modify_service_template_extended($service_template_id, $extended_info) {
		$this->prepare_for_sql( $extended_info);
		$query = "SELECT * FROM nagios_service_template_extended_info WHERE service_template_id = '".$service_template_id."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$extended_info,1);
		$result = $this->dbConnection->Execute($query);		
	}
	
	function modify_service_extended($service_id, $extended_info) {
		$this->prepare_for_sql( $extended_info);
		$query = "DELETE FROM nagios_services_extended_info WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "INSERT INTO nagios_services_extended_info (";
		$query .= implode(", ", array_keys($extended_info));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($extended_info));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
	
	}
	
	
	function new_main_conf($main_conf, $config_dir) {
		//$main_conf['illegal_object_name_chars'] = addslashes($main_conf['illegal_object_name_chars']);
		//$main_conf['illegal_macro_output_chars'] = addslashes($main_conf['illegal_macro_output_chars']);
		$this->prepare_for_sql($main_conf);
		$this->prepare_for_sql($config_dir);
		
		// Check for "commands"
		if(isset($main_conf['ocsp_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['ocsp_command']);
			if($command) {
				$main_conf['ocsp_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['ocsp_command']);
			}
			
		}
		if(isset($main_conf['ochp_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['ochp_command']);
			if($command) {
				$main_conf['ochp_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['ochp_command']);
			}
			
		}
		if(isset($main_conf['host_perfdata_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['host_perfdata_command']);
			if($command) {
				$main_conf['host_perfdata_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['host_perfdata_command']);
			}
			
		}
		if(isset($main_conf['service_perfdata_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['service_perfdata_command']);
			if($command) {
				$main_conf['service_perfdata_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['service_perfdata_command']);
			}
			
		}
		if(isset($main_conf['host_perfdata_file_processing_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['host_perfdata_file_processing_command']);
			if($command) {
				$main_conf['host_perfdata_file_processing_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['host_perfdata_file_processing_command']);
			}
		}
		if(isset($main_conf['service_perfdata_file_processing_command'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['service_perfdata_file_processing_command']);
			if($command) {
				$main_conf['service_perfdata_file_processing_command'] = $command['command_id'];
			}
			else {
				unset($main_conf['service_perfdata_file_processing_command']);
			}
		}
		if(isset($main_conf['global_host_event_handler'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['global_host_event_handler']);
			if($command) {
				$main_conf['global_host_event_handler'] = $command['command_id'];
			}
			else {
				unset($main_conf['global_host_event_handler']);
			}
		}
		if(isset($main_conf['global_service_event_handler'])) {
			// Check for a valid command
			$command = $this->get_command_by_name($main_conf['global_service_event_handler']);
			if($command) {
				$main_conf['global_service_event_handler'] = $command['command_id'];
			}
			else {
				unset($main_conf['global_service_event_handler']);
			}
		}
                if(isset($main_conf['stalking_notifications_for_hosts'])) {
                        // Check To See if the Stalking Notifications For Hosts Variable is Enabled or Not.
                        $value = $this->get_stalking_notifications_for_hosts();
                        if($value == "2") {
				unset($main_conf['stalking_notifications_for_hosts']);
                        }
                        else {
                                $main_conf['stalking_notifications_for_hosts'] = $value['0']['stalking_notifications_for_hosts'];
                        }
                }
                if(isset($main_conf['stalking_notifications_for_services'])) {
                        // Check To See if the Stalking Notifications For Services Variable is Enabled or Not.
                        $value = $this->get_stalking_notifications_for_services();
                        if($value == "2") {
                                unset($main_conf['stalking_notifications_for_services']);
                        }
                        else {
                                $main_conf['stalking_notifications_for_services'] = $value['0']['stalking_notifications_for_services'];
                        }
                }
                if(isset($main_conf['keep_unknown_macros'])) {
                        // Check To See if the Keep Unknown Macros Option Variable is Enabled or Not.
                        $value = $this->get_keep_unknown_macros();
                        if($value == "2") {
                                unset($main_conf['keep_unknown_macros']);
                        }
                        else {
                                $main_conf['keep_unknown_macros'] = $value['0']['keep_unknown_macros'];
                        }
                }
                if(isset($main_conf['max_check_result_list_items'])) {
                        // Check To See if the Limit Number Of Items In Check Result List Variable is Enabled or Not.
                        $value = $this->get_max_check_result_list_items();
                        if($value == "-1") {
                                unset($main_conf['max_check_result_list_items']);
                        }
                        else {
                                $main_conf['max_check_result_list_items'] = $value['0']['max_check_result_list_items'];
                        }
                }
		if(isset($main_conf['broker_modules'])) {
			// We have more than one module
			foreach($main_conf['broker_modules'] as $module) {
				$this->add_broker_module($module);
			}
			unset($main_conf['broker_modules']);
		}
		unset($main_conf['resource_file']);
		
		
		
		$main_conf['config_dir'] = $config_dir;
		$query = "DELETE FROM nagios_main";
		$result = $this->dbConnection->Execute($query);
		$query = "INSERT INTO nagios_main (";
		$query .= implode(", ", array_keys($main_conf));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($main_conf));
		$query .= "')";

		$result = $this->dbConnection->Execute($query);
	
		if($result)
			return true;
		else 
			return false;
	}
	
	function new_cgi_conf($cgi_conf) {
		if(is_array($cgi_conf)) {
			$this->prepare_for_sql($cgi_conf);
			$query = "DELETE FROM nagios_cgi";
			$result = $this->dbConnection->Execute($query);
			$query = "INSERT INTO nagios_cgi (";
			$query .= implode(", ", array_keys($cgi_conf));
			$query .= ") VALUES ('";
		
			$query .= implode("', '", array_values($cgi_conf));
			$query .= "')";
			$result = $this->dbConnection->Execute($query);
			if($result)
				return true;
			else 
				return false;
		}
		else
			return false;
	}
	
	
	function get_period($timeperiod_id, &$periodInfo) {
		$query = "SELECT * FROM nagios_timeperiods WHERE timeperiod_id = '$timeperiod_id'";
		$result = $this->dbConnection->Execute($query);
		$periodInfo = $this->prepare_for_use($result->fields);
	}
	
	function return_period_id_by_name($name) {
		$query = "SELECT timeperiod_id FROM nagios_timeperiods WHERE timeperiod_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['timeperiod_id'];
	}
	
	function return_contact_id_by_name($name) {
		$query = "SELECT contact_id FROM nagios_contacts WHERE contact_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['contact_id'];
	}
	
	function return_command_id_by_name($name) {
		$query = "SELECT command_id FROM nagios_commands WHERE command_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['command_id'];
	}
	
	function return_contactgroup_id_by_name($name) {
		$query = "SELECT contactgroup_id FROM nagios_contactgroups WHERE contactgroup_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['contactgroup_id'];
	}
	
	function return_host_id_by_name($name) {
		$query = "SELECT host_id FROM nagios_hosts WHERE host_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['host_id'];
	}
	
	function return_host_template_id_by_name($name) {
		$query = "SELECT host_template_id FROM nagios_host_templates WHERE template_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['host_template_id'];
	}

	function return_service_template_id_by_name($name) {
		$query = "SELECT service_template_id FROM nagios_service_templates WHERE template_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['service_template_id'];
	}	
	
	function return_hostgroup_id_by_name($name) {
		$query = "SELECT hostgroup_id FROM nagios_hostgroups WHERE hostgroup_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['hostgroup_id'];
	}
	
	function return_servicegroup_id_by_name($name) {
		$query = "SELECT servicegroup_id FROM nagios_servicegroups WHERE servicegroup_name = '$name'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['servicegroup_id'];
	}
	
	function return_service_id_by_host_and_description($host_id, $description) {
		$query = "SELECT service_id FROM nagios_services WHERE host_id = '$host_id' AND service_description = '$description'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['service_id'];
	}
	
	function return_service_id_by_host_template_and_description($host_template_id, $description) {
		$query = "SELECT service_id FROM nagios_services WHERE host_template_id = '$host_template_id' AND service_description = '$description'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['service_id'];
	}
	
	function return_service_id_by_hostgroup_and_description($hostgroup_id, $description) {
		$query = "SELECT service_id FROM nagios_services WHERE hostgroup_id = '$hostgroup_id' AND service_description = '$description'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['service_id'];
	}
	
	function return_service_id_by_description($description) {
		$query = "SELECT service_id FROM nagios_services WHERE service_description = '$description'";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return false;
		else 
			return $result->fields['service_id'];
	}
		
	function return_command_list(&$command_list) {
		$command_list = array();
		$query = "SELECT command_id,command_name,command_desc FROM nagios_commands ORDER BY command_name ASC";
		$result = $this->dbConnection->Execute($query);	
		if ($result == false)
		{
			print $this->dbConnection->ErrorMsg();
			return E_QUERY_FAILED;
		}
		while (! $result->EOF)
		{
			$command_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($command_list);
		return 1;
	}
	
	function get_service_hosts($service_id, &$host_list) {
		$query = "SELECT * FROM nagios_host_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);	
		if ($result == false)
		{
			print $this->dbConnection->ErrorMsg();
			return E_QUERY_FAILED;
		}
		while (! $result->EOF)
		{
			$host_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($host_list);
		return 1;
	}
	
	function get_service_hostgroups($service_id, &$host_list) {
		$query = "SELECT * FROM nagios_hostgroup_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);	
		if ($result == false)
		{
			print $this->dbConnection->ErrorMsg();
			return E_QUERY_FAILED;
		}
		while (! $result->EOF)
		{
			$host_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($host_list);
		return 1;
	}
	
	function command_exists($command_name) {
		$this->prepare_for_sql($command_name);
		$query = "SELECT command_id FROM nagios_commands WHERE command_name = '$command_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_exists($host_name) {
		$this->prepare_for_sql($host_name);
		$query = "SELECT host_id FROM nagios_hosts WHERE host_name = '$host_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function service_exists($service_info) {
		$this->prepare_for_sql($service_info);
		$query = "SELECT service_id FROM nagios_services WHERE service_description = '".$service_info['service_description']."'";
		if(isset($service_info['host_id'])) {
			$query .= " AND host_id = '".$service_info['host_id']."'";
		}
		else {
			$query .= " AND host_template_id = '".$service_info['host_template_id']."'";
		}
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function contact_exists($contact_name) {
		$this->prepare_for_sql( $contact_name);
		$query = "SELECT contact_id FROM nagios_contacts WHERE contact_name = '$contact_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function hostgroup_exists($hostgroup_name) {
		$this->prepare_for_sql($hostgroup_name);
		$query = "SELECT hostgroup_id FROM nagios_hostgroups WHERE hostgroup_name = '$hostgroup_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function servicegroup_exists($servicegroup_name) {
		$this->prepare_for_sql($servicegroup_name);
		$query = "SELECT servicegroup_id FROM nagios_servicegroups WHERE servicegroup_name = '$servicegroup_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function period_exists($period_name) {
		$this->prepare_for_sql($period_name);
		$query = "SELECT timeperiod_id FROM nagios_timeperiods WHERE timeperiod_name = '$period_name'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function add_command($command) {
		$this->prepare_for_sql( $command);
		$query = "INSERT INTO nagios_commands (";
		$query .= implode(", ", array_keys($command));
		$query .= ") VALUES ('";
		$query .= implode("', '", array_values($command));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_contact($contact) {
		$this->prepare_for_sql( $command);
		$query = "INSERT INTO nagios_contacts (";
		$query .= implode(", ", array_keys($contact));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($contact));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function return_num_of_children($host_id) {
		$query = "SELECT host_id FROM nagios_hosts WHERE parents = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->dbConnection->Affected_Rows();
	}
	
	function add_contactgroup($contactgroup) {
		$this->prepare_for_sql($contactgroup);
		$query = "INSERT INTO nagios_contactgroups (";
		$query .= implode(", ", array_keys($contactgroup));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($contactgroup));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_hostgroup($hostgroup) {
		$this->prepare_for_sql($hostgroup);
		$query = "INSERT INTO nagios_hostgroups (";
		$query .= implode(", ", array_keys($hostgroup));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($hostgroup));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_hostgroup_with_map($hostgroup) {
		$this->prepare_for_sql($hostgroup);
		$query = "INSERT INTO nagios_hostgroups (";
		$query .= implode(", ", array_keys($hostgroup));
		$query .= ", in_map";
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($hostgroup));
		$query .= "', 1";
		$query .= ")";

		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_servicegroup($servicegroup) {
		$this->prepare_for_sql($servicegroup);
		$query = "INSERT INTO nagios_servicegroups (";
		$query .= implode(", ", array_keys($servicegroup));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($servicegroup));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else {
			return false;
		}
	}
	
	function add_servicegroup_with_map($servicegroup) {
		$this->prepare_for_sql($servicegroup);
		$query = "INSERT INTO nagios_servicegroups (";
		$query .= implode(", ", array_keys($servicegroup));
		$query .= ", in_map";
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($servicegroup));
		$query .= "')";
		
		print($query);
		
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else {
			return false;
		}
	}	
	
	function add_period($timeperiod) {
		$this->prepare_for_sql($timeperiod);
		$query = "INSERT INTO nagios_timeperiods (";
		$query .= implode(", ", array_keys($timeperiod));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($timeperiod));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		print($this->dbConnection->ErrorMsg());
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_command_parameter($service_id, $parameter) {
		$this->prepare_for_sql($parameter);
		$query = "INSERT INTO nagios_services_check_command_parameters (service_id, parameter) VALUES ('$service_id', '${parameter['parameter']}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_template_command_parameter($service_template_id, $parameter) {
		$this->prepare_for_sql($parameter);
		$query = "INSERT INTO nagios_services_check_command_parameters (service_template_id, parameter) VALUES ('$service_template_id', '${parameter['parameter']}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_template_dependency($host_template_id, $target_host_id) {
		$query = "INSERT INTO nagios_dependencies (host_template_id, target_host_id) VALUES ('$host_template_id', '$target_host_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	
	function add_host_template_escalation($host_template_id, $escalation_description) {
		$this->prepare_for_sql($escalation_description);
		$query = "INSERT INTO nagios_escalations (host_template_id, escalation_description) VALUES ('$host_template_id', '$escalation_description')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_template_escalation($service_template_id, $escalation_description) {
		$this->prepare_for_sql($escalation_description);
		$query = "INSERT INTO nagios_escalations (service_template_id, escalation_description) VALUES ('$service_template_id', '$escalation_description')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_escalation($service_id, $escalation_description) {
		$this->prepare_for_sql( $escalation_description);
		$query = "INSERT INTO nagios_escalations (service_id, escalation_description) VALUES ('$service_id', '$escalation_description')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_escalation($host_id, $escalation_description) {
		$this->prepare_for_sql( $escalation_description);
		$query = "INSERT INTO nagios_escalations (host_id, escalation_description) VALUES ('$host_id', '$escalation_description')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_dependency($host_id, $target_host_id) {
		$query = "INSERT INTO nagios_dependencies (host_id, target_host_id) VALUES ('$host_id', '$target_host_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_dependency($host_id, $service_id, $target_host_id, $target_service_id) {
		$query = "INSERT INTO nagios_dependencies (host_id, service_id, target_host_id, target_service_id) VALUES ('$host_id', '$service_id', '$target_host_id', '$target_service_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_template_dependency($service_template_id, $target_service_id) {
		$query = "INSERT INTO nagios_dependencies (service_template_id, target_service_id) VALUES ('$service_template_id', '$target_service_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_command_parameter($host_id, $parameter) {
		$this->prepare_for_sql( $parameter);
		$query = "INSERT INTO nagios_hosts_check_command_parameters (host_id, parameter) VALUES ('$host_id', '${parameter['parameter']}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_template_command_parameter($host_template_id, $parameter) {
		$this->prepare_for_sql( $parameter);
		$query = "INSERT INTO nagios_hosts_check_command_parameters (host_template_id, parameter) VALUES ('$host_template_id', '${parameter['parameter']}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_contact_address($contact_id, $address) {
		$this->prepare_for_sql( $address);
		$query = "INSERT INTO nagios_contact_addresses (contact_id, address) VALUES ('$contact_id', '${address['address']}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function link_host_service($host_id, $service_id) {
		$query = "INSERT INTO nagios_host_services (host_id, service_id) VALUES ('$host_id', '${service_id}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function link_hostgroup_service($hostgroup_id, $service_id) {
		$query = "INSERT INTO nagios_hostgroup_services (hostgroup_id, service_id) VALUES ('$hostgroup_id', '${service_id}')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host($hostinfo) {
		$this->prepare_for_sql( $hostinfo);
		$query = "INSERT INTO nagios_hosts (";
		$query .= implode(", ", array_keys($hostinfo));
		$query .= ") VALUES ('";
		$query .= implode("', '", array_values($hostinfo));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$tempID = $this->return_host_id_by_name($hostinfo['host_name']);
			$query = "INSERT INTO nagios_hosts_extended_info (host_id) VALUES ('".$tempID."')";
			$this->dbConnection->Execute($query);
			return true;
		}
		else 
			return false;
	}


        // [R. Irujo] - Added <--- 11.02.2012 --->
        // Function for Verifying if the Stalking Notifications Variables are Enabled or Not.
        function get_stalking_notifications_for_hosts() {
                $sql = "SELECT stalking_notifications_for_hosts FROM nagios_main";
		$result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }


        // [R. Irujo] - Added <--- 11.02.2012 --->
        // Function for Verifying if the Stalking Notifications Variables are Enabled or Not.
        function get_stalking_notifications_for_services() {
                $sql = "SELECT stalking_notifications_for_services FROM nagios_main";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }


        // [R. Irujo] - Added <--- 11.02.2012 --->
        // Function for Verifying if the Keep Unknown Macros Option Variable is Enabled or Not.
        function get_keep_unknown_macros() {
                $sql = "SELECT keep_unknown_macros FROM nagios_main";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo] - Added <--- 11.02.2012 --->
        // Function for Verifying if the Limit Number Of Items In Check Result List Variable is Enabled or Not.
        function get_max_check_result_list_items() {
                $sql = "SELECT max_check_result_list_items FROM nagios_main";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }


        // [R. Irujo] - Added <--- 01.10.2012 ---> Part 1 of [Add Multiple Hosts - XML] function - Adds entries 
        //into the 'nagios_hosts' table
	function add_multiple_hosts($HostName, $IP) {
		$query = "INSERT INTO nagios_hosts (
			use_template_id,host_name,alias,address,parents,
			check_command,max_check_attempts,check_interval,passive_checks_enabled,check_period,obsess_over_host,
			check_freshness,freshness_threshold,active_checks_enabled,checks_enabled,event_handler,event_handler_enabled,
			low_flap_threshold,high_flap_threshold,flap_detection_enabled,process_perf_data,retain_status_information,
			retain_nonstatus_information,notification_interval,notification_period,notifications_enabled,notification_options_down,
			notification_options_unreachable,notification_options_recovery,notification_options_flapping,stalking_options_up,
			stalking_options_down,stalking_options_unreachable,failure_prediction_enabled,community,snmp_port,retry_interval
			)
			VALUES (
			NULL,'$HostName','$HostName','$IP',NULL,'3','5',
			'5','1','1',NULL,'1','86400','1',NULL,'3','1','5','20','1','1','1','1','5','1','1',
			'1','1','1','1',NULL,NULL,NULL,'1',NULL,'0','5'
			)"or die(mysql_error());
		$result = $this->dbConnection->Execute($query);
		return $result;
		}

	// [R. Irujo ]<--- Added 01.10.2012 --> Part 2 of [Add Multiple Hosts - XML] function - Adds entries
        // into the 'nagios_hosts_extended_info' table
	function add_multiple_hosts_extended_info($HostName) {
		$tempID = $this->return_host_id_by_name($HostName);
		$query = "INSERT INTO nagios_hosts_extended_info (host_id) VALUES ('".$tempID."')";
		$this->dbConnection->Execute($query);
		return true;
	}
	
        // [R. Irujo] <--- Added 06.07.2012   --> Query returns all Services of a particular Host within the OpCfg Database.
	// [R. Irujo] <--- Updated 06.08.2012 --> Initial Notification Entries left out to keep from duplicating entries.
        function documentation_search_services($hostname) {
                $sql = "select nagios_hosts.host_name,nagios_hosts.address,nagios_services.service_description,
                        nagios_commands.command_name, nagios_services.max_check_attempts,nagios_services.service_id,
                        nagios_services.normal_check_interval,nagios_services.retry_check_interval,
                        nagios_services.notification_interval
                        FROM
                        nagios_commands,nagios_hosts,nagios_services
                        WHERE
                        nagios_hosts.host_name='$hostname'
                        AND
                        nagios_commands.command_id=nagios_services.check_command
                        AND
                        nagios_hosts.host_id=nagios_services.host_id";
		$result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo]<--- Added 06.01.2012 --> Query returns all Service Parameters based on the Service Description
        // of a Service within the OpCfg Database.
        function documentation_search_parameters($service_description) {
                $sql = "select nagios_services.service_id,nagios_services.service_description,nagios_services.check_command,
                	nagios_services_check_command_parameters.parameter
                	FROM
                	nagios_services,nagios_services_check_command_parameters
                	WHERE
                	nagios_services.service_description like '$service_description'
                	AND
                	nagios_services.service_id=nagios_services_check_command_parameters.service_id";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo] <--- Added 06.07.2012 --> Query returns Notifications for a specific Service.
        function documentation_search_notifications($hostname,$service_description) {
                $sql = "SELECT nagios_hosts.host_name,nagios_services.service_id,nagios_services.service_description,
                        nagios_contactgroups.contactgroup_id,nagios_contactgroups.alias,nagios_contactgroups.contactgroup_name
                        FROM
                        nagios_hosts,nagios_services,nagios_contactgroups,nagios_service_contactgroups
                        WHERE
                        nagios_hosts.host_name='$hostname'
                        AND
                        nagios_services.service_description='$service_description'
                        AND
                        nagios_hosts.host_id=nagios_services.host_id
                        AND
                        nagios_services.service_id=nagios_service_contactgroups.service_id
                        AND
                        nagios_service_contactgroups.contactgroup_id=nagios_contactgroups.contactgroup_id";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo] <--- Added 06.17.2012 ---> Query Returns all Services belonging to a particular Host.
        function get_host_services($host_id) {
                $sql = "SELECT
                        nagios_hosts.host_id,
                        nagios_hosts.host_name,
                        nagios_services.service_description,
                        nagios_services.service_id
                        FROM
                        nagios_hosts,
                        nagios_services
                        WHERE
                        nagios_hosts.host_id='$host_id'
                        AND
                        nagios_hosts.host_id=nagios_services.host_id";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo] <--- Added 06.17.2012 ---> Query Returns back all Contact Groups belonging to a Service based
        // on the Host ID the Service is associated with and the Service Description.
        function get_host_service_contactgroups($host_id,$service_description) {
                $sql = "SELECT
                        nagios_hosts.host_id,
                        nagios_hosts.host_name,
                        nagios_services.service_id,
                        nagios_services.service_description,
                        nagios_contactgroups.contactgroup_id,
                        nagios_contactgroups.alias,nagios_contactgroups.contactgroup_name
                        FROM
                        nagios_hosts,nagios_services,nagios_contactgroups,nagios_service_contactgroups
                        WHERE
                        nagios_hosts.host_id='$host_id'
                        AND
                        nagios_services.service_description='$service_description'
                        AND
                        nagios_hosts.host_id=nagios_services.host_id
                        AND
                        nagios_services.service_id=nagios_service_contactgroups.service_id
                        AND
                        nagios_service_contactgroups.contactgroup_id=nagios_contactgroups.contactgroup_id";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

        // [R. Irujo] <--- Added 09.20.2012 ---> Query Returns back the Hostname of a Server based upon their Host ID.
        function get_host_name($host_id) {
                $sql = "SELECT
                        nagios_hosts.host_name
                        FROM
                        nagios_hosts
                        WHERE
                        nagios_hosts.host_id='$host_id'";
                $result = mysql_query($sql);
                while($row=mysql_fetch_array($result)){
                        $return[] = $row;
                        }
                return $return;
        }

	function add_host_template($templateInfo) {
		$this->prepare_for_sql($templateInfo);
		$query = "INSERT INTO nagios_host_templates (";
		$query .= implode(", ", array_keys($templateInfo));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($templateInfo));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$tempID = $this->return_host_template_id_by_name($templateInfo['template_name']);
			$query = "INSERT INTO nagios_host_template_extended_info (host_template_id) VALUES ('".$tempID."')";
			$this->dbConnection->Execute($query);
			return true;
		}
		else 
			return false;
	}
	
	function add_service_template($templateInfo) {
		if(isset($templateInfo['service_description']))	 {
			// This came from import, let's assign it to desc
			$templateInfo['template_description'] = $templateInfo['service_description'];
			unset($templateInfo['service_description']);
		}
		$this->prepare_for_sql($templateInfo);
		$query = "INSERT INTO nagios_service_templates (";
		$query .= implode(", ", array_keys($templateInfo));
		$query .= ") VALUES ('";
	
		$query .= implode("', '", array_values($templateInfo));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			$tempID = $this->return_service_template_id_by_name($templateInfo['template_name']);
			$query = "INSERT INTO nagios_service_template_extended_info (service_template_id) VALUES ('".$tempID."')";
			$this->dbConnection->Execute($query);
			return true;
		}
		else 
			return false;
	}
	
	function add_service($serviceinfo) {
		$this->prepare_for_sql($serviceinfo);
		$query = "INSERT INTO nagios_services (";
		$query .= implode(", ", array_keys($serviceinfo));
		$query .= ") VALUES ('";
		$query .= implode("', '", array_values($serviceinfo));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result) {
			if(isset($serviceinfo['host_template_id']))
				$tempID = $this->return_service_id_by_host_template_and_description($serviceinfo['host_template_id'], $serviceinfo['service_description']);
			elseif(isset($serviceinfo['host_id']))
				$tempID = $this->return_service_id_by_host_and_description($serviceinfo['host_id'], $serviceinfo['service_description']);
			else
				$tempID = $this->return_service_id_by_hostgroup_and_description($serviceinfo['hostgroup_id'], $serviceinfo['service_description']);
			$query = "INSERT INTO nagios_services_extended_info (service_id) VALUES ('".$tempID."')";
			$this->dbConnection->Execute($query);
			return true;
		}
		else {
			return false;
		}
	}
	
	function modify_command($command_id, $command) {
		$this->prepare_for_sql($command);
		$query = "SELECT * FROM nagios_commands WHERE command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
		unset($command['old_name']);
		$query = $this->dbConnection->GetUpdateSQL($result,$command,1);
		$result = $this->dbConnection->Execute($query);
	}
	
	function modify_contact($contact_id, $contact) {
		$this->prepare_for_sql($contact);
		$query = "SELECT * FROM nagios_contacts WHERE contact_id = '".$contact['contact_id']."'";
		$result = $this->dbConnection->Execute($query);
		unset($command['old_name']);
		$query = $this->dbConnection->GetUpdateSQL($result,$contact,1);
		$result = $this->dbConnection->Execute($query);
	}
	
	function modify_contactgroup($contactgroup_id, $contactgroup) {
		$this->prepare_for_sql($contactgroup);
		$query = "SELECT * FROM nagios_contactgroups WHERE contactgroup_id = '".$contactgroup_id."'";
		$result = $this->dbConnection->Execute($query);
		unset($command['old_name']);
		$query = $this->dbConnection->GetUpdateSQL($result,$contactgroup,1);
		$result = $this->dbConnection->Execute($query);
	}
	
	function modify_hostgroup($hostgroup_id, $hostgroup, $in_map) {	
		$this->prepare_for_sql($hostgroup);
		$query = "SELECT * FROM nagios_hostgroups WHERE hostgroup_id = '".$hostgroup_id."'";
		$result = $this->dbConnection->Execute($query);
		unset($hostgroup['old_name']);
		
		if (isset($in_map))
			$hostgroup['in_map'] = 1;
		else
			$hostgroup['in_map'] = 0;
		$query = $this->dbConnection->GetUpdateSQL($result,$hostgroup,1);
		$result = $this->dbConnection->Execute($query);
		
	}
	
	function modify_servicegroup($servicegroup_id, $servicegroup, $in_map) {
		$this->prepare_for_sql($servicegroup);
		$query = "SELECT * FROM nagios_servicegroups WHERE servicegroup_id = '".$servicegroup_id."'";
		$result = $this->dbConnection->Execute($query);
		unset($servicegroup['old_name']);
		
		if (isset($in_map))
			$servicegroup['in_map'] = 1;
		else
			$servicegroup['in_map'] = 0;		
		$query = $this->dbConnection->GetUpdateSQL($result,$servicegroup,1);
		$result = $this->dbConnection->Execute($query);
	}
	
	function modify_period($period) {
		$this->prepare_for_sql($period);
		$query = "SELECT * FROM nagios_timeperiods WHERE timeperiod_id = '".$period['timeperiod_id']."'";
		$result = $this->dbConnection->Execute($query);
		unset($period['old_name']);
		$query = $this->dbConnection->GetUpdateSQL($result,$period,1);
		$result = $this->dbConnection->Execute($query);
	}
	
	function modify_host($host) {
		$this->prepare_for_sql($host);
		$query = "SELECT * FROM nagios_hosts WHERE host_id = '".$host['host_id']."'";
		$result = $this->dbConnection->Execute($query);
		unset($host['old_name']);	// Just in case
		$query = $this->dbConnection->GetUpdateSQL($result,$host, 1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function modify_host_template($host_template) {
		$this->prepare_for_sql($host_template);
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '".$host_template['host_template_id']."'";
		$result = $this->dbConnection->Execute($query);
		unset($host['old_name']);	// Just in case
		$query = $this->dbConnection->GetUpdateSQL($result,$host_template, 1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function modify_service_template($service_template) {
		$this->prepare_for_sql($service_template);
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '".$service_template['service_template_id']."'";
		$result = $this->dbConnection->Execute($query);
		unset($host['old_name']);	// Just in case
		$query = $this->dbConnection->GetUpdateSQL($result,$service_template, 1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	
	function modify_service($service) {
		$this->prepare_for_sql($service);
		if($service['check_command'] != '')
			unset($service['check_command_id']);
		$query = "SELECT * FROM nagios_services WHERE service_id = '".$service['service_id']."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$service,1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function modify_dependency($dependency) {
		$this->prepare_for_sql($dependency);
		$query = "SELECT * FROM nagios_dependencies WHERE dependency_id = '".$dependency['dependency_id']."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$dependency,1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function modify_escalation($escalation) {
		$this->prepare_for_sql($escalation);
		$query = "SELECT * FROM nagios_escalations WHERE escalation_id = '".$escalation['escalation_id']."'";
		$result = $this->dbConnection->Execute($query);
		$query = $this->dbConnection->GetUpdateSQL($result,$escalation,1);
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function delete_command($command_id) {
		$query = "DELETE FROM nagios_commands WHERE command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_dependency($dependency_id) {
		$query = "DELETE FROM nagios_dependencies WHERE dependency_id = '$dependency_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_escalation($escalation_id) {
		$query = "DELETE FROM nagios_escalations WHERE escalation_id = '$escalation_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_escalation_contactgroups WHERE escalation_id = '$escalation_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_period($timeperiod_id) {
		
		$query = "DELETE FROM nagios_timeperiods WHERE timeperiod_id = '$timeperiod_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_contact_notification_command($command_id) {
		
		$query = "DELETE FROM nagios_contacts_notification_commands WHERE contact_notification_command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_contactgroup_member($contactgroup_id, $contact_id) {
		
		$query = "DELETE FROM nagios_contactgroup_membership WHERE contactgroup_id = '$contactgroup_id' AND contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service($service_id) {
		$query = "DELETE FROM nagios_servicegroup_membership WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_hostgroup_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_services_check_command_parameters WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_services_extended_info WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_dependencies WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);	
		$query = "DELETE FROM nagios_escalations WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);	
	}
	
	function unlink_host_service($host_id, $service_id) {
		
		$query = "DELETE FROM nagios_host_services WHERE host_id = '$host_id' AND service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function unlink_hostgroup_service($hostgroup_id, $service_id) {
		
		$query = "DELETE FROM nagios_hostgroup_services WHERE hostgroup_id = '$hostgroup_id' AND service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_host_checkcommand_parameter($parameter_id, $host_id = false) {
		
		if ($host_id !== false) {
			$query = "DELETE FROM nagios_hosts_check_command_parameters WHERE host_id = '$host_id'";
		} else {
			$query = "DELETE FROM nagios_hosts_check_command_parameters WHERE checkcommandparameter_id = '$parameter_id'";
		}
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_checkcommand_parameter($parameter_id, $service_id = false) {
		
		if ($service_id !== false) {
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE service_id = '$service_id'";
		} else {
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE checkcommandparameter_id = '$parameter_id'";
		}
		
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_host_template_checkcommand_parameter($parameter_id, $host_template_id = false) {
		
		if ($host_template_id !== false) {
			$query = "DELETE FROM nagios_hosts_check_command_parameters WHERE host_template_id = '$host_template_id'";
		} else {
			$query = "DELETE FROM nagios_hosts_check_command_parameters WHERE checkcommandparameter_id = '$parameter_id'";
		}
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_template_checkcommand_parameter($parameter_id, $servicetemplate_id = false) {
		
		if ($servicetemplate_id !== false) {
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE service_template_id = '$servicetemplate_id'";
		} else {
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE checkcommandparameter_id = '$parameter_id'";
		}
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_contact_address($address_id) {
		
		$query = "DELETE FROM nagios_contact_addresses WHERE contactaddress_id = '$address_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_hostgroup_member($hostgroup_id, $host_id) {
		
		$query = "DELETE FROM nagios_hostgroup_membership WHERE hostgroup_id = '$hostgroup_id' AND host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_hostgroup_template_member($hostgroup_id, $host_template_id) {
		
		$query = "DELETE FROM nagios_hostgroup_template_membership WHERE hostgroup_id = '$hostgroup_id' AND host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	
	function delete_all_data() {
		$this->dbConnection->Execute("DELETE FROM nagios_broker_modules");
		$this->dbConnection->Execute("DELETE FROM nagios_commands");
		$this->dbConnection->Execute("DELETE FROM nagios_contact_addresses");
		$this->dbConnection->Execute("DELETE FROM nagios_contactgroup_membership");
		$this->dbConnection->Execute("DELETE FROM nagios_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_contacts");
		$this->dbConnection->Execute("DELETE FROM nagios_contacts_notification_commands");
		$this->dbConnection->Execute("DELETE FROM nagios_dependencies");
		$this->dbConnection->Execute("DELETE FROM nagios_escalation_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_escalations");
		$this->dbConnection->Execute("DELETE FROM nagios_host_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_host_services");
		$this->dbConnection->Execute("DELETE FROM nagios_host_template_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_host_template_extended_info");
		$this->dbConnection->Execute("DELETE FROM nagios_host_templates");
		$this->dbConnection->Execute("DELETE FROM nagios_hostgroup_membership");
		$this->dbConnection->Execute("DELETE FROM nagios_hostgroup_template_membership");
		$this->dbConnection->Execute("DELETE FROM nagios_hostgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_hosts");
		$this->dbConnection->Execute("DELETE FROM nagios_host_parents");
		$this->dbConnection->Execute("DELETE FROM nagios_hosts_extended_info");
		$this->dbConnection->Execute("DELETE FROM nagios_hosts_check_command_parameters");
		$this->dbConnection->Execute("DELETE FROM nagios_main");
		$this->dbConnection->Execute("DELETE FROM nagios_resources");
		$this->dbConnection->Execute("DELETE FROM nagios_service_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_servicegroup_membership");
		$this->dbConnection->Execute("DELETE FROM nagios_service_template_contactgroups");
		$this->dbConnection->Execute("DELETE FROM nagios_service_template_extended_info");
		$this->dbConnection->Execute("DELETE FROM nagios_servicegroup_template_membership");
		$this->dbConnection->Execute("DELETE FROM nagios_service_templates");
		$this->dbConnection->Execute("DELETE FROM nagios_servicegroups");
		$this->dbConnection->Execute("DELETE FROM nagios_services");
		$this->dbConnection->Execute("DELETE FROM nagios_services_check_command_parameters");
		$this->dbConnection->Execute("DELETE FROM nagios_services_extended_info");
		$this->dbConnection->Execute("DELETE FROM nagios_timeperiods");
		$this->dbConnection->Execute("INSERT INTO nagios_main(config_dir) VALUES(NULL)");
	}
	
	function delete_cgi_data() {
		$this->dbConnection->Execute("DELETE FROM nagios_cgi");
		$this->dbConnection->Execute("INSERT INTO nagios_cgi(physical_html_path) VALUES(NULL)");
	}
	
	function delete_host_contactgroup($host_id, $contactgroup_id) {
		
		$query = "DELETE FROM nagios_host_contactgroups WHERE host_id = '$host_id' AND contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_host_template_contactgroup($host_template_id, $contactgroup_id) {
		
		$query = "DELETE FROM nagios_host_template_contactgroups WHERE host_template_id = '$host_template_id' AND contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_template_contactgroup($service_template_id, $contactgroup_id) {
		
		$query = "DELETE FROM nagios_service_template_contactgroups WHERE service_template_id = '$service_template_id' AND contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_template_servicegroup($service_template_id, $servicegroup_id) {
		
		$query = "DELETE FROM nagios_servicegroup_template_membership WHERE service_template_id = '$service_template_id' AND servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_contactgroup($service_id, $contactgroup_id) {
		
		$query = "DELETE FROM nagios_service_contactgroups WHERE service_id = '$service_id' AND contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_escalation_contactgroup($escalation_id, $contactgroup_id) {
		
		$query = "DELETE FROM nagios_escalation_contactgroups WHERE escalation_id = '$escalation_id' AND contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_servicegroup($service_id, $servicegroup_id) {
		
		$query = "DELETE FROM nagios_servicegroup_membership WHERE service_id = '$service_id' AND servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_contact($contact_id) {
		
		$query = "DELETE FROM nagios_contactgroup_membership WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_contacts WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_host($host_id) {
		// Delete from additional parents
		$query = "DELETE FROM nagios_host_parents WHERE parent_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_hostgroup_membership WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		// Get a list of services which belong to this host
		$query = "SELECT service_id FROM nagios_services WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE service_id = '".$result->fields['service_id']."'";
			$subResult = $this->dbConnection->Execute($query);
			$serviceIDs[] = $result->fields['service_id'];
			$result->MoveNext();
		}
		$numOfServices = count($serviceIDs);
		for($counter = 0; $counter < $numOfServices; $counter++) {
			$query = "DELETE FROM nagios_services WHERE service_id = '${serviceIDs[$counter]}'";
			$result = $this->dbConnection->Execute($query);
		}
		// Get parent id
		$query = "SELECT parents FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$tempParentID = $result->fields['parents'];
		$query = "UPDATE nagios_hosts SET parents = '$tempParentID' WHERE parents = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_hosts_extended_info WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_dependencies WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);	
		$query = "DELETE FROM nagios_escalations WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);	
	}
	
	function delete_host_template($host_template_id) {
		
		$query = "DELETE FROM nagios_hostgroup_template_membership WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_host_template_contactgroups WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
	
		// TODO: Get list of services for this host template and remove them
		
		/*
		// Get a list of services which belong to this host
		$query = "SELECT service_id FROM nagios_services WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$query = "DELETE FROM nagios_services_check_command_parameters WHERE service_id = '".$result->fields['service_id']."'";
			$subResult = $this->dbConnection->Execute($query);
			$serviceIDs[] = $result->fields['service_id'];
			$result->MoveNext();
		}
		$numOfServices = count($serviceIDs);
		for($counter = 0; $counter < $numOfServices; $counter++) {
			$query = "DELETE FROM nagios_services WHERE service_id = '${serviceIDs[$counter]}'";
			$result = $this->dbConnection->Execute($query);
		}
		
		// Get parent id
		$query = "SELECT parents FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$tempParentID = $result->fields['parents'];
		$query = "UPDATE nagios_hosts SET parents = '$tempParentID' WHERE parents = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		*/
		// Let's get a list of dependent host templates
		$this->get_host_template_info($host_template_id, $tempTemplateInfo);
		unset($tempTemplateInfo['host_template_id']);
		unset($tempTemplateInfo['template_name']);
		unset($tempTemplateInfo['template_description']);
		
		$query = "SELECT * FROM nagios_host_templates WHERE use_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (!$result->EOF)
		{
			$dependentTemplate = $result->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentTemplate[$key]))
						$dependentTemplate[$key] = $value;
				}
			}
			// $dependentTemplate['use_template_id'] = NULL;
			$this->modify_host_template($dependentTemplate);
			$result->MoveNext();
		}
	
		// Extended info
		$this->get_host_template_extended_info($host_template_id, $tempTemplateInfo);
		unset($tempTemplateInfo['host_template_id']);
		
		$query = "SELECT * FROM nagios_host_templates WHERE use_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		while(!$result->EOF) {
			$query = "SELECT * FROM nagios_host_template_extended_info WHERE host_template_id = '".$result->fields['host_template_id']."'";
			$tempResult = $this->dbConnection->Execute($query);
			$dependentTemplateExtended = $tempResult->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentTemplate[$key]))
						$dependentTemplate[$key] = $value;
				}
			}
			$this->modify_host_template_extended($dependentTemplateExtended['host_template_id'], $dependentTemplate);
			$result->MoveNext();		
		}
		$query = "DELETE FROM nagios_host_template_extended_info WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);	
		$query = "DELETE FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		
		// Remove use_template_id from dependent templates
		$query = "UPDATE nagios_host_templates SET use_template_id = NULL WHERE use_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_service_template($service_template_id) {	
		$query = "DELETE FROM nagios_servicegroup_template_membership WHERE service_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_service_template_contactgroups WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_servicegroup_template_membership WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
	
		// Let's get a list of dependent service templates and services
		$this->get_service_template_info($service_template_id, $tempTemplateInfo);
		unset($tempTemplateInfo['service_template_id']);
		unset($tempTemplateInfo['template_name']);
		unset($tempTemplateInfo['template_description']);
		$query = "SELECT * FROM nagios_service_templates WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (!$result->EOF)
		{
			$dependentTemplate = $result->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentTemplate[$key]))
						$dependentTemplate[$key] = $value;
				}
			}
			// $dependentTemplate['use_template_id'] = NULL;
			$this->modify_service_template($dependentTemplate);
			$result->MoveNext();
		}
		$query = "SELECT * FROM nagios_services WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (!$result->EOF)
		{
			$dependentTemplate = $result->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentTemplate[$key]))
						$dependentTemplate[$key] = $value;
				}
			}
			// $dependentTemplate['use_template_id'] = NULL;
			$this->modify_service($dependentTemplate);
			$result->MoveNext();
		}
	
		// Extended info
		$this->get_service_template_extended_info($service_template_id, $tempTemplateInfo);
		unset($tempTemplateInfo['service_template_id']);
		$query = "SELECT * FROM nagios_service_templates WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while(!$result->EOF) {
			$query = "SELECT * FROM nagios_service_template_extended_info WHERE service_template_id = '".$result->fields['service_template_id']."'";
			$tempResult = $this->dbConnection->Execute($query);
			$dependentTemplateExtended = $tempResult->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentTemplateExtended[$key]))
						$dependentTemplateExtended[$key] = $value;
				}
			}
			$this->modify_service_template_extended($dependentTemplateExtended['service_template_id'], $dependentTemplateExtended);
			$result->MoveNext();		
		}
		$query = "SELECT * FROM nagios_service_templates WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while(!$result->EOF) {
			$query = "SELECT * FROM nagios_service_extended_info WHERE service_id = '".$result->fields['service_id']."'";
			$tempResult = $this->dbConnection->Execute($query);
			$dependentServiceExtended = $tempResult->fields;
			// Field checks
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key]) && !isset($dependentServiceExtended[$key]))
						$dependentServiceExtended[$key] = $value;
				}
			}
			$this->modify_service_extended($dependentServiceExtended['service_id'], $dependentServiceExtended);
			$result->MoveNext();		
		}
		
		
		$query = "DELETE FROM nagios_service_template_extended_info WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);	
		$query = "DELETE FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		
		// Remove use_template_id from dependent templates
		$query = "UPDATE nagios_service_templates SET use_template_id = NULL WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		// Remove use_template_id from dependent services
		$query = "UPDATE nagios_services SET use_template_id = NULL WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
	}
		
	function delete_hostgroup($hostgroup_id) {
		$query = "DELETE FROM nagios_hostgroup_membership WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_hostgroups WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_servicegroup($servicegroup_id) {
		
		$query = "DELETE FROM nagios_servicegroup_membership WHERE servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_servicegroups WHERE servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
	}
	
	function delete_contactgroup($contactgroup_id) {
		
		$query = "DELETE FROM nagios_contactgroup_membership WHERE contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
		$query = "DELETE FROM nagios_contactgroups WHERE contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);	
	}
	
	function get_contact_membership_list($contact_id, &$group_list) {
		$group_list = array();
		$query = "SELECT * FROM nagios_contactgroup_membership WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$group_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($group_list);
		return 1;
	}
	
	function get_host_services_list($host_id, &$service_list) {
		$service_list = array();
		$query = "SELECT service_id, host_id, service_description FROM nagios_services WHERE host_id = '$host_id' order by 3";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$service_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($service_list);
		return 1;
	}
	
	function get_host_template_services_list($host_template_id, &$service_list) {
		$service_list = array();
		$query = "SELECT service_id, host_template_id, service_description FROM nagios_services WHERE host_template_id = '$host_template_id' order by 3";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$service_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($service_list);
		return 1;
	}
	
	function get_hostgroup_services_list($hostgroup_id, &$service_list) {
		$service_list = array();
		$query = "SELECT service_id FROM nagios_services WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$service_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($service_list);
		return 1;
	}
	
	function get_host_check_command_parameters($host_id, &$parameter_list) {
		$parameter_list = array();;
		
		$query = "SELECT * FROM nagios_hosts_check_command_parameters WHERE host_id = '$host_id' ORDER BY checkcommandparameter_id ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$new_string = replace_backslashes($result->fields['parameter']);
			$result->fields['parameter'] = $new_string;
			$parameter_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($parameter_list);
		return 1;
	}
	
	function get_host_template_check_command_parameters($host_template_id, &$parameter_list) {
		$parameter_list = array();
		
		$query = "SELECT * FROM nagios_hosts_check_command_parameters WHERE host_template_id = '$host_template_id' ORDER BY checkcommandparameter_id ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$new_string = replace_backslashes($result->fields['parameter']);
			$result->fields['parameter'] = $new_string;
			$parameter_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($parameter_list);
		return 1;
	}
	
	function get_service_check_command_parameters($service_id, &$parameter_list) {
		$parameter_list = array();
		
		$query = "SELECT * FROM nagios_services_check_command_parameters WHERE service_id = '$service_id' ORDER BY checkcommandparameter_id ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$new_string = replace_backslashes($result->fields['parameter']);
			$result->fields['parameter'] = $new_string;
			$parameter_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($parameter_list);
		return 1;
	}
	
	function get_service_template_check_command_parameters($service_template_id, &$parameter_list) {
		$parameter_list = array();
		
		$query = "SELECT * FROM nagios_services_check_command_parameters WHERE service_template_id = '$service_template_id' ORDER BY checkcommandparameter_id ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$new_string = replace_backslashes($result->fields['parameter']);
			$result->fields['parameter'] = $new_string;
			$parameter_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($parameter_list);
		return 1;
	}
	
	function get_contact_addresses($contact_id, &$address_list) {
		$address_list = array();
		$query = "SELECT * FROM nagios_contact_addresses WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$address_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($address_list);
		return 1;
	}	
	
	function get_host_membership_list($host_id, &$group_list) {
		$group_list = array();
		$query = "SELECT * FROM nagios_hostgroup_membership WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$group_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($group_list);
		return 1;
	}
	
	function get_host_template_membership_list($host_template_id, &$group_list) {
		$group_list = array();
		$query = "SELECT * FROM nagios_hostgroup_template_membership WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$group_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($group_list);
		return 1;
	}
	
	function get_service_contactgroups($service_id, &$group_list) {
		$group_list = array();
		
		$query = "SELECT * FROM nagios_service_contactgroups WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$group_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($group_list);
		return 1;
	}
	
	function get_service_servicegroups($service_id, &$group_list) {
		$group_list = array();
		
		$query = "SELECT * FROM nagios_servicegroup_membership WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$group_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($group_list);
		return 1;
	}	
	
	function get_host_nav_info($host_id, &$hostinfo) {
		
		$query = "SELECT host_id, parents, host_name FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		$hostinfo = $this->prepare_for_use($result->fields);
	}
	
	function get_host_info($host_id, &$hostinfo) {
		
		$query = "SELECT * FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$hostinfo = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_dependency($dependency_id, &$dependency_info) {
		
		$query = "SELECT * FROM nagios_dependencies WHERE dependency_id = '$dependency_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$dependency_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_escalation($escalation_id, &$escalation_info) {
		
		$query = "SELECT * FROM nagios_escalations WHERE escalation_id = '$escalation_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$escalation_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	
	
	function get_host_template_info($host_template_id, &$hosttemplateinfo) {
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$hosttemplateinfo = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_service_template_info($service_template_id, &$servicetemplateinfo) {
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$servicetemplateinfo = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	// Function to recursively get the hosts affected by a template
	function get_hosts_affected_by_host_template($host_template_id, &$affectedHosts) {
		$affectedHosts = array();
		$query = "SELECT host_template_id FROM nagios_host_templates WHERE use_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			// Then there are additional templates dependent upon our template, let's call with them.
			while(! $result->EOF) {
				$this->get_hosts_affected_by_host_template($result->fields['host_template_id'], $moreHosts);
				if(is_array($moreHosts)) {
					$affectedHosts = array_merge( $affectedHosts, $moreHosts);
				}
				$result->MoveNext();
			}
		}
		// Okay, we're at a leaf, let's get all the hosts affected by this template
		$query = "SELECT host_id FROM nagios_hosts WHERE use_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->affected_Rows()) {
			while(! $result->EOF) {
				if(!is_array($affectedHosts) || !in_array($result->fields['host_id'], $affectedHosts)) {
					$affectedHosts[] = $result->fields['host_id'];
				}
				$result->MoveNext();
			}
		}
		$affectedHosts = $this->prepare_for_use( $affectedHosts);
	}
	
	// Function to recursively get the services affected by a template
	function get_services_affected_by_service_template($service_template_id, &$affectedServices) {
		$query = "SELECT service_template_id FROM nagios_service_templates WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			// Then there are additional templates dependent upon our template, let's call with them.
			while(! $result->EOF) {
				$this->get_services_affected_by_service_template($result->fields['service_template_id'], $affectedServices);
				$result->MoveNext();
			}
		}
		// Okay, we're at a leaf, let's get all the hosts affected by this template
		$query = "SELECT service_id FROM nagios_services WHERE use_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->affected_Rows()) {
			while(! $result->EOF) {
				if(!is_array($affectedServices) || !in_array($result->fields['service_id'], $affectedServices)) {
					$affectedServices[] = $result->fields['service_id'];
				}
				$result->MoveNext();
			}
		}
		$affectedServices = $this->prepare_for_use( $affectedServices);
	}
	
	
	// Function recursively gets the inherited template values, given a template
	function get_inherited_host_template_values($host_template_id, &$hosttemplateinfo, &$hosttemplateinfoSources) {
		$hosttemplateinfo = array();
		$hosttemplateinfoSources = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_inherited_host_template_values($tempTemplateInfo['use_template_id'], $tempinfo, $tempSources);
				if(is_array($tempinfo)) {
					$hosttemplateinfo = array_merge($hosttemplateinfo, $tempinfo);
					$hosttemplateinfoSources = array_merge( $hosttemplateinfoSources, $tempSources);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			unset($tempTemplateInfo['host_template_id']);
			unset($tempTemplateInfo['use_template_id']);
			unset($tempTemplateInfo['template_name']);
			unset($tempTemplateInfo['template_description']);		
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key])) {
						$hosttemplateinfo[$key] = $value;
						$hosttemplateinfoSources[$key] = $tempTemplateName;
					}
				}
			}
			$this->prepare_for_use( $hosttemplateinfo);
			$this->prepare_for_use( $hosttemplateinfoSources);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_inherited_service_template_values($service_template_id, &$servicetemplateinfo, &$servicetemplateinfoSources) {
		/*
		$servicetemplateinfo = array();
		$servicetemplateinfoSources = array();
		*/
		
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_inherited_service_template_values($tempTemplateInfo['use_template_id'], $tempinfo, $tempSources);
				if(is_array($tempinfo)) {
					$servicetemplateinfo = array_merge( $servicetemplateinfo, $tempinfo);
					$servicetemplateinfoSources = array_merge( $servicetemplateinfoSources, $tempSources);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			unset($tempTemplateInfo['service_template_id']);
			unset($tempTemplateInfo['use_template_id']);
			unset($tempTemplateInfo['template_name']);
			unset($tempTemplateInfo['template_description']);		
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key])) {
						$servicetemplateinfo[$key] = $value;
						$servicetemplateinfoSources[$key] = $tempTemplateName;
					}
				}
			}
			$this->prepare_for_use( $servicetemplateinfo);
			$this->prepare_for_use( $servicetemplateinfoSources);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_inherited_service_template_extended_values($service_template_id, &$servicetemplateinfo, &$servicetemplateinfoSources) {
		/*
		$servicetemplateinfo = array();
		$servicetemplateinfoSources = array();
		*/
		
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_inherited_service_template_extended_values($tempTemplateInfo['use_template_d'], $tempinfo, $tempSources);
				if(is_array($tempinfo)) {
					$servicetemplateinfo = array_merge( $servicetemplateinfo, $tempinfo);
					$servicetemplateinfoSources = array_merge( $servicetemplateinfoSources, $tempSources);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_service_template_extended_info WHERE service_template_id = '$service_template_id'";
			$result = $this->dbConnection->Execute($query);
			$tempTemplateInfo = $result->fields;
			unset($tempTemplateInfo['service_template_id']);		
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key])) {
						$servicetemplateinfo[$key] = $value;
						$servicetemplateinfoSources[$key] = $tempTemplateName;
					}
				}
			}
			$this->prepare_for_use( $servicetemplateinfo);
			$this->prepare_for_use( $servicetemplateinfoSources);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_inherited_host_template_extended_values($host_template_id, &$hosttemplateinfo, &$hosttemplateinfoSources) {
		/*
		$hosttemplateinfo = array();
		$hosttemplateinfoSources = array();
		*/
		
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_inherited_host_template_extended_values($tempTemplateInfo['use_template_id'], $tempinfo, $tempSources);
				if(is_array($tempinfo)) {
					$hosttemplateinfo = array_merge( $hosttemplateinfo, $tempinfo);
					$hosttemplateinfoSources = array_merge( $hosttemplateinfoSources, $tempSources);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_host_template_extended_info WHERE host_template_id = '$host_template_id'";
			$result = $this->dbConnection->Execute($query);
			$tempTemplateInfo = $result->fields;
			unset($tempTemplateInfo['host_template_id']);		
			if(count($tempTemplateInfo)) {
				foreach($tempTemplateInfo as $key=>$value) {
					if(isset($tempTemplateInfo[$key])) {
						$hosttemplateinfo[$key] = $value;
						$hosttemplateinfoSources[$key] = $tempTemplateName;
					}
				}
			}
			$this->prepare_for_use( $hosttemplateinfo);
			$this->prepare_for_use( $hosttemplateinfoSources);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_host_template_inherited_contactgroups_list($host_template_id, &$groupList) {
		$groupList = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_contactgroups_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$groupList = array_merge( $groupList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT contactgroup_id FROM nagios_host_template_contactgroups WHERE host_template_id = '$host_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempContactGroups[] = $result->fields['contactgroup_id'];
				$result->MoveNext();
			}
			if(count($tempContactGroups)) {
				foreach($tempContactGroups as $groupID) {
					if(!is_array($groupList) || !in_array($groupID, $groupList)) {
						$groupList[$groupID] = $groupID;
					}
				}
			}
			$this->prepare_for_use( $groupList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_host_template_inherited_commandparameter_list($host_template_id, &$parameterList) {
		$parameterList = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_commandparameter_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$parameterList = array_merge( $parameterList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_hosts_check_command_parameters WHERE host_template_id = '$host_template_id' ORDER BY checkcommandparameter_id ASC";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempParameters[] = $result->fields;
				$result->MoveNext();
			}
			if(count($tempParameters)) {
				foreach($tempParameters as $parameter) {
						$parameterList[] = $parameter;
				}
			}
			$this->prepare_for_use($parameterList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_host_template_inherited_escalations_list($host_template_id, &$escalationList) {
		$escalationList = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_escalations_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$escalationList = array_merge( $escalationList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT escalation_id FROM nagios_escalations WHERE host_template_id = '$host_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempEscalations[] = $result->fields['escalation_id'];
				$result->MoveNext();
			}
			if(count($tempEscalations)) {
				foreach($tempEscalations as $escalation) {
					if(!is_array($escalationList) || !in_array($escalation, $escalationList)) {
						$escalationList[$escalation] = $escalation;
					}
				}
			}
			$this->prepare_for_use($escalationList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_service_template_inherited_commandparameter_list($service_template_id, &$parameterList) {
		$parameterList = array();
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id']) && !isset($tempTemplateInfo['check_command'])) {
				$this->get_service_template_inherited_commandparameter_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$parameterList = array_merge( $parameterList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_services_check_command_parameters WHERE service_template_id = '$service_template_id' ORDER BY checkcommandparameter_id ASC";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempParameters[] = $result->fields;
				$result->MoveNext();
			}
			if(count($tempParameters)) {
				foreach($tempParameters as $parameter) {
						$parameterList[] = $parameter;
				}
			}
			$this->prepare_for_use($paramterList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	
	// Function recursively gets the inherited template values, given a template
	function get_service_template_inherited_escalations_list($service_template_id, &$escalationList) {
		$escalationList = array();
		
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_service_template_inherited_escalations_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$escalationList = array_merge( $escalationList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT escalation_id FROM nagios_escalations WHERE service_template_id = '$service_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempEscalations[] = $result->fields['escalation_id'];
				$result->MoveNext();
			}
			if(count($tempEscalations)) {
				foreach($tempEscalations as $escalation) {
					if(!is_array($escalationList) || !in_array($escalation, $escalationList)) {
						$escalationList[$escalation] = $escalation;
					}
				}
			}
			$this->prepare_for_use($escalationList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	function get_host_template_inherited_hostgroups_list($host_template_id, &$groupList) {
		$groupList = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_hostgroups_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$groupList = array_merge( $groupList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT hostgroup_id FROM nagios_hostgroup_template_membership WHERE host_template_id = '$host_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempHostGroups[] = $result->fields['hostgroup_id'];
				$result->MoveNext();
			}
			if(count($tempHostGroups)) {
				foreach($tempHostGroups as $groupID) {
					if(!is_array($groupList) || !in_array($groupID, $groupList)) {
						$groupList[$groupID] = $groupID;
					}
				}
			}
			$this->prepare_for_use($groupList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_host_template_inherited_dependencies_list($host_template_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
	
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_dependencies_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$dependencies_list = array_merge( $dependencies_list, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_dependencies WHERE host_template_id = '$host_template_id' AND service_id IS NULL";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempDependencies[] = $result->fields;
				$result->MoveNext();
			}
			if(count($tempDependencies)) {
				foreach($tempDependencies as $dependency) {
					if(!is_array($dependencies_list) || !in_array($dependency['host_id'], $dependencies_list)) {
						$dependencies_list[$dependency['host_id']] = $dependency;
					}
				}
			}
			$this->prepare_for_use($dependencies_list);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_service_template_inherited_dependencies_list($service_template_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
	
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_service_template_inherited_dependencies_list($tempTemplateInfo['use_template_id'], $temp_list);
				if(is_array($temp_list)) {
					$dependencies_list = array_merge( $dependencies_list, $temp_list);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT * FROM nagios_dependencies WHERE service_template_id = '$service_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempDependencies[] = $result->fields;
				$result->MoveNext();
			}
			if(count($tempDependencies)) {
				foreach($tempDependencies as $dependency) {
					if(!is_array($dependencies_list) || !in_array($dependency['target_service_id'], $dependencies_list)) {
						$dependencies_list[$dependency['target_service_id']] = $dependency;
					}
				}
			}
			$this->prepare_for_use($dependencies_list);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	
	
	// Function recursively gets the inherited template values, given a template
	function get_host_template_inherited_services_list($host_template_id, &$groupList) {
		$groupList = array();
		$query = "SELECT * FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
	
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_host_template_inherited_services_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$groupList = array_merge( $groupList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT service_id, host_template_id, service_description FROM nagios_services WHERE host_template_id = '$host_template_id' order by 3";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempServices[] = $result->fields;
				$result->MoveNext();
			}
			if(count($tempServices)) {
				foreach($tempServices as $service) {
					if(!is_array($groupList) || !in_array($service['service_description'], $groupList)) {
						$groupList[$service['service_description']] = $service;
					}
				}
			}
			$this->prepare_for_use($groupList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_service_template_inherited_contactgroups_list($service_template_id, &$groupList) {
		$groupList = array();
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_service_template_inherited_contactgroups_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$groupList = array_merge( $groupList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT contactgroup_id FROM nagios_service_template_contactgroups WHERE service_template_id = '$service_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			while (! $result->EOF)
			{
				$tempContactGroups[] = $result->fields['contactgroup_id'];
				$result->MoveNext();
			}
			if(count($tempContactGroups)) {
				foreach($tempContactGroups as $groupID) {
					if(!is_array($groupList) || !in_array($groupID, $groupList)) {
						$groupList[$groupID] = $groupID;
					}
				}
			}
			$this->prepare_for_use($groupList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	// Function recursively gets the inherited template values, given a template
	function get_service_template_inherited_servicegroups_list($service_template_id, &$groupList) {
		$groupList = array();
		$query = "SELECT * FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$tempTemplateInfo = $result->fields;
			if(isset($tempTemplateInfo['use_template_id'])) {
				$this->get_service_template_inherited_servicegroups_list($tempTemplateInfo['use_template_id'], $tempList);
				if(is_array($tempList)) {
					$groupList = array_merge($groupList, $tempList);
				}
			}
			$tempTemplateName = $tempTemplateInfo['template_name'];		
			$query = "SELECT servicegroup_id FROM nagios_servicegroup_template_membership WHERE service_template_id = '$service_template_id'";
			$result = $this->dbConnection->Execute($query);
			// While loop
			// SF BUG# 1444456
			// Service Templates - MoveNext() problem
			// Resolution: if($result) {
			//
			if($result) {
				while (! $result->EOF)
				{
					$tempContactGroups[] = $result->fields['servicegroup_id'];
					$result->MoveNext();
				}
			}
			if(count($tempContactGroups)) {
				foreach($tempContactGroups as $groupID) {
					if(!is_array($groupList) || !in_array($groupID, $groupList)) {
						$groupList[$groupID] = $groupID;
					}
				}
			}
			$this->prepare_for_use($groupList);
			return true;
		}
		else {
			return false;	// Should never really get here
		}
	}
	
	function get_contact_info($contact_id, &$contactinfo) {
		
		$query = "SELECT * FROM nagios_contacts WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		$contactinfo = $this->prepare_for_use($result->fields);
	}
	
	function get_contactgroup_info($contactgroup_id, &$contactgroupinfo) {
		
		$query = "SELECT * FROM nagios_contactgroups WHERE contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
		$contactgroupinfo = $this->prepare_for_use($result->fields);
	}
	
	
	function get_hostgroup_info($hostgroup_id, &$hostgroupinfo) {
		
		$query = "SELECT * FROM nagios_hostgroups WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
		$hostgroupinfo = $this->prepare_for_use($result->fields);
	}
	
	function get_servicegroup_info($servicegroup_id, &$servicegroupinfo) {
		
		$query = "SELECT * FROM nagios_servicegroups WHERE servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
		$servicegroupinfo = $this->prepare_for_use($result->fields);
	}
	
	function get_service_info($service_id, &$service_info) {
		
		$query = "SELECT * FROM nagios_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		if($this->dbConnection->Affected_Rows()) {
			$service_info = $this->prepare_for_use($result->fields);
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_command_name($command_id) {
		
		$query = "SELECT command_name FROM nagios_commands WHERE command_id = '$command_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_name']);
	}
	
	function return_contact_name($contact_id) {
		
		$query = "SELECT contact_name FROM nagios_contacts WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['contact_name']);
	}
	
	function return_period_name($timeperiod_id) {
		
		$query = "SELECT timeperiod_name FROM nagios_timeperiods WHERE timeperiod_id = '$timeperiod_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['timeperiod_name']);
	}
	
	function return_service_description($service_id) {
		
		$query = "SELECT service_description FROM nagios_services WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['service_description']);
	}
	
	function return_escalation_description($escalation_id) {
		
		$query = "SELECT escalation_description FROM nagios_escalations WHERE escalation_id = '$escalation_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['escalation_description']);
	}
	
	function return_contactgroup_name($contactgroup_id) {
		
		$query = "SELECT contactgroup_name FROM nagios_contactgroups WHERE contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['contactgroup_name']);
	}
	
	function return_servicegroup_name($servicegroup_id) {
		
		$query = "SELECT servicegroup_name FROM nagios_servicegroups WHERE servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['servicegroup_name']);
	}
	
	
	function return_hostgroup_name($hostgroup_id) {
		
		$query = "SELECT hostgroup_name FROM nagios_hostgroups WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['hostgroup_name']);
	}
	
	function return_host_template_dependency($host_template_id, $target_host_id) {
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE host_template_id = '$host_template_id' AND target_host_id = '$target_host_id'";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['dependency_id'];
	}
	
	function return_host_template_escalation($host_template_id, $escalation_description) {
		
		$query = "SELECT escalation_id FROM nagios_escalations WHERE host_template_id = '$host_template_id' AND escalation_description = '$escalation_description' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['escalation_id'];
	}
	
	function return_service_template_escalation($service_template_id, $escalation_description) {
		
		$query = "SELECT escalation_id FROM nagios_escalations WHERE service_template_id = '$service_template_id' AND escalation_description = '$escalation_description' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['escalation_id'];
	}
	
	function return_service_escalation($service_id, $escalation_description) {
		
		$query = "SELECT escalation_id FROM nagios_escalations WHERE service_id = '$service_id' AND escalation_description = '$escalation_description'";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['escalation_id'];
	}
	
	function return_host_escalation($host_id, $escalation_description) {
		
		$query = "SELECT escalation_id FROM nagios_escalations WHERE host_id = '$host_id' AND escalation_description = '$escalation_description' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['escalation_id'];
	}
	
	
	function return_host_dependency($host_id, $target_host_id) {
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE host_id = '$host_id' AND target_host_id = '$target_host_id'";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['dependency_id'];
	}
	
	function return_service_dependency($service_id, $target_host_id, $target_service_id) {
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE service_id = '$service_id' AND target_service_id = '$target_service_id' AND target_host_id = '$target_host_id'";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['dependency_id'];
	}
	
	function return_service_template_dependency($service_template_id, $target_service_id) {
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE service_template_id = '$service_template_id' AND target_service_id = '$target_service_id'";
		$result = $this->dbConnection->Execute($query);
		return $result->fields['dependency_id'];
	}
	
	
	function return_hostgroup_alias($hostgroup_id) {
		
		$query = "SELECT alias FROM nagios_hostgroups WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['alias']);
	}
	
	function return_contactgroup_alias($contactgroup_id) {
		
		$query = "SELECT alias FROM nagios_contactgroups WHERE contactgroup_id = '$contactgroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['alias']);
	}	
	
	function return_servicegroup_alias($servicegroup_id) {
		
		$query = "SELECT alias FROM nagios_servicegroups WHERE servicegroup_id = '$servicegroup_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['alias']);
	}	
	
	
	function return_contact_alias($contact_id) {
		
		$query = "SELECT alias FROM nagios_contacts WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['alias']);
	}
	
	function return_host_name($host_id) {
		
		$query = "SELECT host_name FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['host_name']);
	}
	
	function return_host_template_name($host_template_id) {
		
		$query = "SELECT template_name FROM nagios_host_templates WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['template_name']);
	}
	
	function return_service_template_name($service_template_id) {
		
		$query = "SELECT template_name FROM nagios_service_templates WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['template_name']);
	}
	
	function return_host_alias($host_id) {
		
		$query = "SELECT alias FROM nagios_hosts WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['alias']);
	}
	
	function get_host_template_list(&$template_list) {
		$template_list = array();
		
		$query = "SELECT host_template_id, template_name, template_description FROM nagios_host_templates ORDER BY template_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$template_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $template_list);
		return 1;
	}
	
	function get_service_template_list(&$template_list) {
		$template_list = array();
		
		$query = "SELECT service_template_id, template_name, template_description FROM nagios_service_templates ORDER BY template_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$template_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($template_list);
		return 1;
	}
	
	function return_contactgroup_member_list($group_id, &$member_list) {
		
		$member_list = array();
		$query = "SELECT * FROM nagios_contactgroup_membership WHERE contactgroup_id = '$group_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$member_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($member_list);
		return 1;
	}
	
	function return_hostgroup_member_list($hostgroup_id, &$member_list) {
		$member_list = array();
		
		$query = "select a.hostgroup_id, a.host_id FROM nagios_hostgroup_membership a, nagios_hosts b ";
		$query.= "WHERE a.hostgroup_id = '$hostgroup_id' and a.host_id = b.host_id order by b.host_name";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$member_list[] = $result->fields;
			$result->MoveNext();
		}
		
		$this->prepare_for_use($member_list);
		return 1;
	}

	function return_hostgroup_inherited_member_list($hostgroup_id, &$member_list) {
		$member_list = array();
		
		// Look at what templates associate themselves with this hostgroup
		$templates = array();
		$touched = array();
		
		$query = "SELECT * FROM nagios_hostgroup_template_membership WHERE hostgroup_id = '$hostgroup_id'";
		$result = $this->dbConnection->Execute( $query);
		while( !$result->EOF) {
			$templates[] = $result->fields['host_template_id'];
			$touched[] = $result->fields['host_template_id'];
			$result->MoveNext();
		}
		
		$added = array();
		while( count( $templates) > 0) {
			$templates = array_values( $templates);
			// Add all hosts
			$query = "SELECT $hostgroup_id AS 'hostgroup_id', host_id FROM nagios_hosts"
				." WHERE use_template_id=" . $templates[0];
			$result = $this->dbConnection->Execute( $query);
			
			while( !$result->EOF) {
				if( !isset( $added[$result->fields['host_id']])) {
					$member_list[] = $result->fields;
					$added[$result->fields['host_id']];
				}
				$result->MoveNext();
			}
			
			// check if any other templates inherit from this
			$query = "SELECT * FROM nagios_host_templates WHERE use_template_id = " . $templates[0];
			$result = $this->dbConnection->Execute( $query);
			
			while( !$result->EOF) {
				if( !isset( $touched[$result->fields['host_template_id']])) {
					$templates[] = $result->fields['host_template_id'];
					$touched[] = $result->fields['host_template_id'];
				}
				$result->MoveNext();
			}
			
			$touched[$templates[0]] = 1;
			
			unset( $templates[0]);
		}
		
		$this->prepare_for_use($member_list);
		return 1;
	}
	
	function return_servicegroup_member_list($servicegroup_id, &$member_list) {
		$member_list = array();
		
		$query = "select a.servicegroup_id, a.service_id from  nagios_servicegroup_membership a, nagios_services b, ";
		$query .= "nagios_hosts c WHERE servicegroup_id = '$servicegroup_id' and a.service_id = b.service_id and ";
		$query .= "b.host_id = c.host_id order by c.host_name, b.service_description";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$member_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($member_list);
		return 1;
	}	
	
	function return_broker_modules(&$module_list) {
		$module_list = array();
		$query = "SELECT * FROM nagios_broker_modules";
		$result = $this->dbConnection->Execute($query);
		while(!$result->EOF) {
			$module_list[] = $result->fields;
			$result->MoveNext();
		}
	}
	
	function add_broker_module($module_line) {
		$query = "INSERT INTO nagios_broker_modules(module_line) VALUES('$module_line')";
		$result = $this->dbConnection->Execute($query);
		return true;
	}
	
	function delete_broker_module($module_id) {
		$query = "DELETE FROM nagios_broker_modules WHERE module_id = '$module_id'";
		$result = $this->dbConnection->Execute($query);
		return true;
	}
	
	function return_host_contactgroups_list($host_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_host_contactgroups WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_service_contactgroups_list($service_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_service_contactgroups WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_escalation_contactgroups_list($escalation_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_escalation_contactgroups WHERE escalation_id = '$escalation_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_host_template_contactgroups_list($host_template_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_host_template_contactgroups WHERE host_template_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_service_template_contactgroups_list($service_template_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_service_template_contactgroups WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_service_template_servicegroups_list($service_template_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_servicegroup_template_membership WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_service_servicegroups_list($service_id, &$groups_list) {
		$groups_list = array();
		
		$query = "SELECT * FROM nagios_servicegroup_membership WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$groups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($groups_list);
		return 1;
	}
	
	function return_host_template_dependencies_list($host_template_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_dependencies WHERE host_template_id = '$host_template_id' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return true;
	}
	
	function return_host_template_escalations_list($host_template_id, &$escalations_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_escalations WHERE host_template_id = '$host_template_id' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$escalations_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $escalations_list);
		return true;	
	}
	
	function return_service_template_escalations_list($service_template_id, &$escalations_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_escalations WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$escalations_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $escalations_list);
		return true;	
	}
	
	function return_service_escalations_list($service_id, &$escalations_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_escalations WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$escalations_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $escalations_list);
		return true;	
	}
	
	function return_host_escalations_list($host_id, &$escalations_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_escalations WHERE host_id = '$host_id' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$escalations_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $escalations_list);
		return true;	
	}
	
	function return_service_template_dependencies_list($service_template_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_dependencies WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return true;	
	}
	
	function return_host_dependencies_list($host_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_dependencies WHERE host_id = '$host_id' AND service_id IS NULL";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return true;	
	}
	
	function return_service_dependencies_list($service_id, &$dependencies_list) {
		$dependencies_list = array();
		$query = "SELECT * FROM nagios_dependencies WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return true;	
	}
	
	function get_contact_list(&$contact_list) {
		$contact_list = array();

		$query = "SELECT contact_id, contact_name, alias, command_execution FROM nagios_contacts ORDER BY contact_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$contact_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $contact_list);
		return 1;
	}
	
	function get_host_list(&$host_list) {
		$host_list = array();
		// This will be REALLY scary for a system with lots of hosts
		
		$query = "SELECT host_id, host_name, alias, parents FROM nagios_hosts ORDER BY host_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$host_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $host_list);
		return 1;
	}
	
	function get_hostgroup_list(&$hostgroups_list) {
		$hostgroups_list = array();
		
		$query = "SELECT hostgroup_id, hostgroup_name, alias FROM nagios_hostgroups ORDER BY hostgroup_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$hostgroups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $hostgroup_list);
		return 1;
	}
	
	function get_servicegroup_list(&$servicegroups_list) {
		$servicegroups_list = array();
		
		$query = "SELECT servicegroup_id, servicegroup_name, alias FROM nagios_servicegroups ORDER BY servicegroup_name";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$servicegroups_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $servicegroups_list);
		return 1;
	}
	
	function get_services_list(&$services_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$services_list = array();
		
		$query = "SELECT service_id, service_description FROM nagios_services";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$services_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $services_list);
		return 1;
	}
	
	function get_hosts_services_list(&$services_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$services_list = array();
		
		$query = "SELECT a.service_id, a.service_description, b.host_name FROM nagios_services a, nagios_hosts b ";
		$query .= "where host_template_id Is NULL and a.host_id = b.host_id order by b.host_name, a.service_description";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$result->fields['display'] = "{$result->fields['host_name']}:{$result->fields['service_description']}";
			$services_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use( $services_list);
		
		return 1;
	}
	
	function get_dependencies_list(&$dependencies_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$dependencies_list = array();
		
		$query = "SELECT dependency_id FROM nagios_dependencies";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return 1;
	}

	function get_host_dependencies_list($host_id, &$dependencies_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$dependencies_list = array();
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE host_id = '$host_id' OR target_host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return 1;
	}
	
	function get_service_dependencies_list($service_id, &$dependencies_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$dependencies_list = array();
		
		$query = "SELECT dependency_id FROM nagios_dependencies WHERE service_id = '$service_id' OR target_service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$dependencies_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($dependencies_list);
		return 1;
	}
	
	function get_escalations_list(&$escalations_list) {
		// This will be a memory hog for those with a lot of servers+hosts
		$escalations_list = array();
		
		$query = "SELECT escalation_id FROM nagios_escalations";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$escalations_list[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($escalations_list);
		return 1;
	}
	
	function get_contacts_notification_commands($contact_id, &$command_list) {
		$command_list = array();
		
		$query = "SELECT contact_notification_command_id, command_id, notification_type FROM nagios_contacts_notification_commands WHERE contact_id = '$contact_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			if($result->fields['notification_type'] == 'host')
				$command_list['host'][] = $result->fields;
			else 
				$command_list['service'][] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($command_list);
		return 1;
	}
	
	function contact_has_notification_command($contact_id, $notification_command) {
		
		$query = "SELECT contact_notification_command_id  FROM nagios_contacts_notification_commands WHERE command_id = '${notification_command['command_id']}' AND contact_id = '$contact_id' AND notification_type = '${notification_command['notification_type']}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function contactgroup_has_member($contactgroup_id, $contact_id) {
		
		$query = "SELECT contact_id FROM nagios_contactgroup_membership WHERE contactgroup_id = '${contactgroup_id}' AND contact_id = '${contact_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_has_contactgroup($host_id, $contactgroup_id) {
		
		$query = "SELECT contactgroup_id FROM nagios_host_contactgroups WHERE contactgroup_id = '${contactgroup_id}' AND host_id = '${host_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_has_children($host_id) {
		$query = "SELECT count(*) FROM nagios_hosts WHERE parents = '$host_id'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->fields['count(*)']) {
			return false;
		}
		else {
			return true;
		}
		
	}
	
	function host_template_has_contactgroup($host_template_id, $contactgroup_id) {
		
		$query = "SELECT contactgroup_id FROM nagios_host_template_contactgroups WHERE contactgroup_id = '${contactgroup_id}' AND host_template_id = '${host_template_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function service_template_has_contactgroup($service_template_id, $contactgroup_id) {
		
		$query = "SELECT contactgroup_id FROM nagios_service_template_contactgroups WHERE contactgroup_id = '${contactgroup_id}' AND service_template_id = '${service_template_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function service_template_has_servicegroup($service_template_id, $servicegroup_id) {
		
		$query = "SELECT servicegroup_id FROM nagios_servicegroup_template_membership WHERE servicegroup_id = '${servicegroup_id}' AND service_template_id = '${service_template_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_has_service($host_id, $service_id) {
		
		$query = "SELECT host_id FROM nagios_host_services WHERE host_id = '${host_id}' AND service_id = '${service_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function hostgroup_has_service($hostgroup_id, $service_id) {
		
		$query = "SELECT hostgroup_id FROM nagios_hostgroup_services WHERE hostgroup_id = '$hostgroup_id' AND service_id = '${service_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	
	function service_has_contactgroup($service_id, $contactgroup_id) {
		
		$query = "SELECT contactgroup_id FROM nagios_service_contactgroups WHERE contactgroup_id = '${contactgroup_id}' AND service_id = '${service_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function escalation_has_contactgroup($escalation_id, $contactgroup_id) {
		
		$query = "SELECT contactgroup_id FROM nagios_escalation_contactgroups WHERE contactgroup_id = '${contactgroup_id}' AND escalation_id = '${escalation_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function service_has_servicegroup($service_id, $servicegroup_id) {
		
		$query = "SELECT servicegroup_id FROM nagios_servicegroup_membership WHERE servicegroup_id = '${servicegroup_id}' AND service_id = '${service_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_has_hostgroup($host_id, $hostgroup_id) {
		
		$query = "SELECT host_id FROM nagios_hostgroup_membership WHERE host_id = '${host_id}' AND hostgroup_id = '${hostgroup_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function host_template_has_hostgroup($host_template_id, $hostgroup_id) {
		
		$query = "SELECT host_template_id FROM nagios_hostgroup_template_membership WHERE host_template_id = '${host_template_id}' AND hostgroup_id = '${hostgroup_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function hostgroup_has_member ($hostgroup_id, $host_id) {
		
		$query = "SELECT host_id FROM nagios_hostgroup_membership WHERE hostgroup_id = '${hostgroup_id}' AND host_id = '${host_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function servicegroup_has_member ($servicegroup_id, $service_id) {
		
		$query = "SELECT service_id FROM nagios_servicegroup_membership WHERE servicegroup_id = '${servicegroup_id}' AND service_id = '${service_id}'";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function add_contacts_notification_command($contact_id, $notification_command) {
		$this->prepare_for_sql($notification_command);
		$query = "INSERT INTO nagios_contacts_notification_commands (";
		$query .= implode(", ", array_keys($notification_command));
		$query .= ") VALUES ('";
		$query .= implode("', '", array_values($notification_command));
		$query .= "')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	
	function add_contactgroup_member($contactgroup_id, $contact_id) {
		
		$query = "INSERT INTO nagios_contactgroup_membership (contactgroup_id, contact_id) VALUES ('$contactgroup_id', '$contact_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_hostgroup_member($hostgroup_id, $host_id) {
		if( $host_id == 0)
			return false;
		$query = "INSERT INTO nagios_hostgroup_membership (hostgroup_id, host_id) VALUES ('$hostgroup_id', '$host_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_hostgroup_template_member($hostgroup_id, $host_template_id) {
		
		$query = "INSERT INTO nagios_hostgroup_template_membership (hostgroup_id, host_template_id) VALUES ('$hostgroup_id', '$host_template_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_contactgroup($host_id, $contactgroup_id) {
		
		$query = "INSERT INTO nagios_host_contactgroups (host_id, contactgroup_id) VALUES ('$host_id', '$contactgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_template_contactgroup($host_template_id, $contactgroup_id) {
		
		$query = "INSERT INTO nagios_host_template_contactgroups (host_template_id, contactgroup_id) VALUES ('$host_template_id', '$contactgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_template_contactgroup($service_template_id, $contactgroup_id) {
		
		$query = "INSERT INTO nagios_service_template_contactgroups (service_template_id, contactgroup_id) VALUES ('$service_template_id', '$contactgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_host_template_hostgroup($host_template_id, $hostgroup_id) {
		$query = "INSERT INTO nagios_hostgroup_template_membership (host_template_id, hostgroup_id) VALUES ('$host_template_id', '$hostgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else
			return false;
	}
	
	function add_service_template_servicegroup($service_template_id, $servicegroup_id) {
		
		$query = "INSERT INTO nagios_servicegroup_template_membership (service_template_id, servicegroup_id) VALUES ('$service_template_id', '$servicegroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	}
	
	function add_service_contactgroup($service_id, $contactgroup_id) {
		
		$query = "INSERT INTO nagios_service_contactgroups (service_id, contactgroup_id) VALUES ('$service_id', '$contactgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	
	}
	
	function add_escalation_contactgroup($escalation_id, $contactgroup_id) {
		
		$query = "INSERT INTO nagios_escalation_contactgroups (escalation_id, contactgroup_id) VALUES ('$escalation_id', '$contactgroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	
	}
	
	function add_service_servicegroup($service_id, $servicegroup_id) {
		
		$query = "INSERT INTO nagios_servicegroup_membership (service_id, servicegroup_id) VALUES ('$service_id', '$servicegroup_id')";
		$result = $this->dbConnection->Execute($query);
		if($result)
			return true;
		else 
			return false;
	
	}
	
	function build_object_inheritance(&$dependentObject, $targetObjectTree) {
		// Build object inheritance
		if(isset($dependentObject['use'])) {
			// First we get to the end of the inheritance chain.
			$tmpObject = $dependentObject;
			$subcounter = 0;
			$useStack[$subcounter] = $tmpObject;
			while(isset($tmpObject['use'])) {
				$found = 0;
				// Now we have to find the dependency
				if(count($targetObjectTree)) {
					foreach($targetObjectTree as $loopObject) {
						if(isset($loopObject['name']) && $loopObject['name'] == $tmpLoopObject['use']) {
							$tmpObject = $loopObject;
							$found = 1;
						}
					}
				}
				if(!$found)
					unset($tmpObject['use']);	// Didn't find the top parent, so we'll toss it.
				$subcounter++;
				$useStack[$subcounter] = $tmpObject;
			}
			// At end of dependency list, start assigning values
			$numOfDependencies = count($useStack);
			$dependentObject = array();
			for($subcounter = ($numOfDependencies-1); $subcounter >= 0; $subcounter--)
				$dependentObject = array_merge($dependentObject, $useStack[$subcounter]);
			unset($useStack);
		}
		// Remove any use or registered items
		unset($dependentObject['use']);
		unset($dependentObject['name']);
		unset($dependentObject['register']);
	}
	
	function get_host_icon_image($host_id, &$host_icon_image) {
		
		$query = "SELECT icon_image FROM nagios_hosts_extended_info WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		if($result->fields['icon_image'] != '') {
			$host_icon_image = $this->prepare_for_sql($result->fields['icon_image']);
			return true;
		}
		else 
			return false;
	}
	
	function get_service_icon_image($service_id, &$service_icon_image) {
		
		$query = "SELECT icon_image FROM nagios_services_extended_info WHERE service_id = '$service_id'";
		$result = $this->dbConnection->Execute($query);
		if($result->fields['icon_image'] != '') {
			$service_icon_image = $this->prepare_for_sql($result->fields['icon_image']);
			return true;
		}
		else 
			return false;
	}
	
	
	function get_host_template_icon_image($host_template_id, &$host_template_icon_image) {
		
		$query = "SELECT icon_image FROM nagios_hosts_template_extended_info WHERE host_id = '$host_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($result->fields['icon_image'] != '') {
			$host_template_icon_image = $this->prepare_for_sql($result->fields['icon_image']);
			return true;
		}
		else 
			return false;
	}
	
	function get_service_template_icon_image($service_template_id, &$service_template_icon_image) {
		
		$query = "SELECT icon_image FROM nagios_services_template_extended_info WHERE service_template_id = '$service_template_id'";
		$result = $this->dbConnection->Execute($query);
		if($result->fields['icon_image'] != '') {
			$service_template_icon_image = $this->prepare_for_sql($result->fields['icon_image']);
			return true;
		}
		else 
			return false;
	}

	function return_services_list($host_id, &$member_list) {
		$member_list = array();
		
		$query = "SELECT service_description FROM nagios_services WHERE host_id = '$host_id'";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$member_list[] = $result->fields;
			$result->MoveNext();
		}
		
		$this->prepare_for_use($member_list);
		return 1;
	}

	function get_all_other_hosts_list($host_id, &$children_lists) {
		$children_lists = array();
		$query = "SELECT host_id, host_name, alias FROM nagios_hosts WHERE host_id != '$host_id' ORDER BY host_name ASC";
		$result = $this->dbConnection->Execute($query);
		while (! $result->EOF)
		{
			$children_lists[] = $result->fields;
			$result->MoveNext();
		}
		$this->prepare_for_use($children_lists);
		return 1;
	}

	function return_command_line($service_id) {
		$complete_command = array();
		$this->get_service_info($service_id, $tempService);
		if( isset( $tempService['check_command'])) {
			$complete_command = $this->return_command_name( $tempService['check_command']);
		} elseif( isset( $tempService['use_template_id'])) {
			$complete_command = $this->return_service_template_command( $tempService['use_template_id']);
		}

		if(is_array($complete_command))
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command[0] . "'";
		else
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command . "'";

		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_line']);
	}

	function return_host_command_line($host_id) {
		$complete_command = array();
		$this->get_host_info($host_id, $tempHost);
		if( isset( $tempHost['check_command'])) {
			$complete_command = $this->return_command_name( $tempHost['check_command']);
		} elseif( isset( $tempHost['use_template_id'])) {
			$complete_command = $this->return_host_template_command( $tempHost['use_template_id']);
		}
		
		if(is_array($complete_command))
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command[0] . "'";
		else
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command . "'";

		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_line']);
	}
	
	function return_host_template_command_line($host_template_id) {
		$complete_command = array();
		$complete_command = $this->return_host_template_command( $host_template_id );

		if(is_array($complete_command))
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command[0] . "'";
		else
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command . "'";

		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_line']);
	}	

	function return_service_template_command_line($service_template_id) {
		$complete_command = array();
		$complete_command = $this->return_service_template_command( $service_template_id );

		if(is_array($complete_command))
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command[0] . "'";
		else
			$query = "SELECT command_line FROM nagios_commands WHERE command_name = '" . $complete_command . "'";


		$result = $this->dbConnection->Execute($query);
		return $this->prepare_for_use($result->fields['command_line']);
	}
	
	function set_changes( $type, $to_change ) {
		
		$query = "INSERT INTO changes values ( '$type', '$to_change' )";
		$this->dbConnection->Execute($query);
		
	}
	
	function get_hosts_affected_by_service_change( $service_id ) {
		
		$query = "SELECT host_id, host_template_id FROM nagios_services WHERE service_id = $service_id";
		$result = $this->dbConnection->Execute($query);
		$values = $result->fields;

		if (isset($values['host_id'])) {
			$host_name = $this->return_host_name($values['host_id']);
			return $host_name;
		}else if (isset($values['host_template_id'])) {
			$hosts = array();
			$this->get_hosts_affected_by_host_template($values['host_template_id'], $hosts);
			$host_names = array();
			if (count($hosts)) {
				foreach ($hosts as $host_id) {
					$host_names[] = $this->return_host_name($host_id);
				}
			}
			return $host_names;
		}
	}
	
	function return_changes_to_export() {
		
		$query = "SELECT * from changes";
		$result = $this->dbConnection->Execute($query);
		$count = 0;
		while (! $result->EOF)
		{
			$changes = $result->fields;
			$parts = explode("#", $changes['to_change']);
			if ($changes['type'] == 'host') {
				print("Host <b>$parts[0]</b> changes to <b>$parts[1]</b>.<br>");
			}else{
				print("Service <b>$parts[1]</b> on Host <b>$parts[0]</b> changes to <b>$parts[2]</b>.<br>");
			}
			$result->MoveNext();
			$count++;
		}

		if (!$count)
			print("Nothing to do.<br>");		
	}
	
	function hostgroup_in_map( $hostgroup_id ) {
		$query = "SELECT * FROM nagios_hostgroups where hostgroup_id = '$hostgroup_id' and in_map = 1";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}		
	}
	
	function servicegroup_in_map( $servicegroup_id ) {
		$query = "SELECT * FROM nagios_servicegroups where servicegroup_id = '$servicegroup_id' and in_map = 1";
		$result = $this->dbConnection->Execute($query);	
		if(!$result->EOF) {
			return true;
		}
		else {
			return false;
		}		
	}

	function return_language_list(&$language_list) {

		$language_list = array();
		$query = "select * from languages where id=0";
		$result = $this->dbConnection->Execute($query);	
		while(!$result->EOF) {
			$tmp = array();
			$tmp['locale_name'] = $result->fields['lang'];
			$tmp['alias'] = $result->fields['description'];
			$language_list[] = $tmp; 
			$result->MoveNext();
		}
		$result->Close();		

		return true;
	}
	
	function get_locale_alias($language) {

		$language_list = array();
		$query = "select * from languages where id=0 and lang='$language'";
		$result = $this->dbConnection->Execute($query);	
		return $result->fields['description'];
		
	}
	
	function return_theme_list(&$theme_list) {
		
		$theme_list = array();
		$dh = opendir( "../seagull/modules/opinterface/templates/themes" );
		
		while( ($file = readdir($dh)) !== false ) {
			if (preg_match('/^[\.|CVS]+$/', $file)) { continue; }
			$theme_list[] = $file;			
		}
		closedir( $dh );
		
		sort($theme_list);
		
	}

}

?>
