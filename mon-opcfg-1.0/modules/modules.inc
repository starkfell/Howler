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
	Filename: modules/modules.inc
	Description:
	Maintains what modules to load
*/

abstract class Module {
	private $modname;
	private $moddesc;
	private $majorver;
	private $minorver;
	
	// Function to set module name information
	public function getModName() {
		return $this->modname;
	}
	
	public function getModDesc() {
		return $this->moddesc;
	}
	
	public function getVersion() {
		$versionInfo = array('major' => $this->majorver, 'minor' => $this->minorver);
	}
	
	// Function to set versioning information
	final protected function setVersionInfo($modname, $moddesc, $major, $minor) {
		$this->modname = $modname;
		$this->moddesc = $moddesc;
		$this->majorver = (int)$major;
		$this->minorver = (int)$minor;
	}
	
	abstract public function init();
	abstract public function restart();
}

/*
	Module Type:	Authentication
	Used By:		[none]
	Provides:		1) Security wrapper for Fruity
				2) $module_auth->hasAccess( $tag) for access validation
*/
require_once FRUITY_FS_ROOT . "modules/auth/alwaysadmin/alwaysadmin.php";

/*
	Module Type:	Sidenav
	Used By:		sidenav.php
	Provides:		$module_sidenav->render() to render the Sidenav
*/
require_once FRUITY_FS_ROOT . "modules/sidenav/preload/preload.php";

/*
	Module Type:	Search
	Used By:		header.php, search.php
	Provides:		1) $module_search->renderBasicSearch() for a "small", "in-line" search form
				2) $module_search->renderAdvancedSearch() for a full-page search form
				3) $module_search->renderResults() to display all results
*/
require_once FRUITY_FS_ROOT . "modules/search/simple/simple.php";

