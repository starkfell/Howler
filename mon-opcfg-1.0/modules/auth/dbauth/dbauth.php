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
	Module Name: dbauth
	Module Category: auth
	Module Description: 
	Provides authentication against a database backend using adodb
	Table names are prefixed with dbauth_
	
*/

define("MODULE_AUTH_DBAUTH_ENABLED", "false");

class module_auth_dbauth extends Module {
	// Configuration parameters
	private $dbHost;
	private $dbUsername;
	private $dbPasswor;
	private $dbDatabase ;
	private $dbServ;	// Database driver to use
	private $dbConnection;

	function __construct() {
		$this->setVersionInfo('dbauth', 'Authenticates Against a Database', 1, 0);
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
	function init() {
		global $fruity;
		// Setup Additional Links
		$fruity->getOutputHandler()->addAdditionalHeaderLink(AUTH_MODULES_URL_PATH . "dbauth/logout.php", "Logout");
	}
	function restart() {
		if(!$_SESSION['logged_in']) {
			$this->dbConnection = ADONewConnection($this->dbServ);
			$this->dbConnection->PConnect($this->dbHost, $this->dbUsername,
								$this->dbPassword,$this->dbDatabase);
			if(!$this->dbConnection->IsConnected()) {
				print("DBAUTH Failure: Unable to connect to auth database.  Please check your dbauth configuration.");
				die();
			}	
			$this->dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
			$this->login();
		}
	}
	function login() {
		global $fruity;
		global $path_config;
		if($_SERVER['PHP_SELF'] == AUTH_MODULES_URL_PATH . "dbauth/login.php") {
			// We're at our module's login page, let's see if there's anything to process.
			if(isset($_POST['__dbauth_request'])) {
				foreach($_POST['__dbauth_login'] as $key=>$value)
					$_SESSION['tempData']['__dbauth_login'][$key] = $value;
				if($_POST['__dbauth_request'] == 'login') {
					if($_SESSION['tempData']['__dbauth_login']['username'] == '') {
						 $fruity->setErrorMsg("Username cannot be blank");
					}
					else {
						if(!$this->user_exists($_SESSION['tempData']['__dbauth_login']['username'])) {
							$fruity->setErrorMsg("Username does not exist");
						}
						else {
							if(!$this->login_user($_SESSION['tempData']['__dbauth_login']['username'], $_SESSION['tempData']['__dbauth_login']['password'])) {
								$fruity->setErrorMsg("Login failed.  Check password and try again.");
							}
							else {
								// We have logged in, session variables set, redirect back to fruity's main page
								header("Refresh: 0; URL=". $path_config['doc_root'] . "index.php");
								die();
							}
						}
					}
				}
			}
			
			
		}
		else {
			// We're going to redirect to this module's login page
			header("Location: " . AUTH_MODULES_URL_PATH . "dbauth/login.php");
			die();
		}
	}
	function hasPrivilege($privilege) {
		// Always has the rights to do *anything*  Needs to be fleshed out.
		return true;
	}
	function logout() {
		session_destroy();
		return true;	// Logging out always happens, no real clean up here.
	}
	
	function user_exists($username) {
		$query = "SELECT username FROM dbauth_users WHERE LCASE(username) = LCASE('".$username."')";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF)
			return 0;
		else
			return 1;
	}
	function login_user($user, $pass) {
		$query = "SELECT user_id, username,password FROM dbauth_users WHERE (LCASE(username) = LCASE('$user')) AND password = MD5('$pass')";
		$result = $this->dbConnection->Execute($query);
		if($result->EOF) {
			return false;
		}
		else {
			// If we got here, user is validated
			$_SESSION['username'] = $result->fields["username"];
			$_SESSION['logged_in'] = '1';
			return true;	// USER VERIFIED
		}
	}
	
	
	function __destruct() {
		// No object destruction needed
	}
}

if(MODULE_AUTH_DBAUTH_ENABLED) {
	//$fruity->setAuthHandler(new module_auth_dbauth);
}
?>
