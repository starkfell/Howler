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
	Module Name: alwaysadmin
	Module Category: auth
	Module Description: 
	Simple authentication module for Fruity.  Will always
	return the username as being admin, and will always return 
	any rights as being available.
*/

define("MODULE_AUTH_ALWAYSADMIN_ENABLED", "true");

class module_auth_alwaysadmin extends Module {
	function __construct() {
		$this->setVersionInfo('alwaysadmin', 'Always authenticates as admin', 1, 0);
		$_SESSION['username'] = 'admin';
		$_SESSION['logged_in'] = 1;
	}
	function init() {
		// Nothing needed
	}
	function restart() {
		// Nothing needed
	}
	
	function login() {

		return true;
	}
	function hasPrivilege($privilege) {
		// Always has the rights to do *anything*
		return true;
	}
	function logout() {
		return true;	// Logging out always happens
	}
	function __destruct() {
		// No object destruction needed
	}
}

if(MODULE_AUTH_ALWAYSADMIN_ENABLED) {
	// Commented out until dynamic module loading
	//fruity->setAuthHandler(new module_auth_alwaysadmin);
}
?>
