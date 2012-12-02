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
	Filename: objects.inc
	Description:
	The class definition for base objects
*/

abstract class baseObject {
	protected $myName;		// Needs to be the name of the object
	private $modules;
	
	function __construct() {
		// Dynamic loading of modules needs to go here.  Check in the 
		// $module_registry array
	}
	
	abstract public function init();
	abstract public function restart();
	
	protected function addModule($modObject) {
		$this->modules[] = $modObject;
	}
	
}
	
