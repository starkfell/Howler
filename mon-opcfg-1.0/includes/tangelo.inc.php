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
Tangelo - A Nagios Configuration File Parser in PHP
Copyright (C) 2005  Taylor J. Dondich <tdondich at gmail.com>

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

	Requirements:
		PHP v5.x (For Object Orientated Approach)
		
	TODO:
		- Dependencies
		- Escalations
		- Service Groups

*/

final class Tangelo {
	public $mainConfig, $cgiConfig, $resourceConfig;	// To Hold the Configuration Information for both Main and CGI Configs
	private $errorMsg;			// To hold error messages (if caused)
	
	// Our data types
	public $contactgroups, $contacts, $hostgroups, $hosts, $services, $timeperiods, $commands, $hostextinfo, $serviceextinfo,
		$hostdependencies, $servicedependencies, $serviceescalations, $hostescalations, $servicegroups;
	// Our template holder
	public $templates;

	function __construct($mainConfigFile, $cgiConfigFile = NULL, $resourceFile = NULL) {

		global $sys_config;
		
		// Check if exist lock file
		$import_lock_file = "{$sys_config['web_dir']}/opcfg/import.lock";
		if (file_exists($import_lock_file)) {
			$this->errorMsg = "Another import process running. Can't continue.";
			return false;
		}

		$fh = fopen($import_lock_file, "w");
		fclose($fh);

		$mainConfig = array();		// An associative Array to hold the config values for the main file
		$cgiConfig = array();
		$templates = array();
		if($this->readConfigFile($mainConfigFile, $this->mainConfig) === false)
			return false;
		if($cgiConfigFile != NULL) {
				if($this->readConfigFile($cgiConfigFile, $this->cgiConfig) === false)
					return false;
		}
		if($resourceFile != NULL) {
				if($this->readConfigFile($resourceFile, $this->resourceConfig) === false)
					return false;
		}
	}
	/*
		readConfigFile($cfgFile, &$configArray);
		Reads a configuration file, and parses any additional files mentioned.  Any configuration parameters 
		found in this file are stored in the associative array $configArray
	*/
	private function readConfigFile($cfgFile, &$configArray) {	// This should only be called by our constructor
		if (($filePtr = @fopen($cfgFile, 'r')) == false) {
		    $this->errorMsg =  "Cannot Open Configuration File: $cfgFile";
		    return false;
		}
		while ($line = fgets($filePtr)) {		// Lines better not be over 1024 characters in length
		      if (preg_match('/^\s*(|#.*)$/', $line)) {
			      // We read a comment, so let's hop to the next line
			  continue;
		      }
		      if (preg_match('/^\s*cfg_file\s*=\s*(\S+)/', $line, $regs)) {
			if ($this->parseObjectFile($regs[1]) === false) {
			    return false;
			}
			continue;
		      }
		      if (preg_match('/^\s*cfg_file\s*=\s*(\S+)/', $line, $regs)) {
			if ($this->parseObjectFile($regs[1]) === false) {
			    return FALSE;
			}
			continue;
		      }
		      if (preg_match('/^\s*cfg_dir\s*=\s*(\S+)/', $line, $regs)) {
			if ($this->parseConfigDir($regs[1]) === false) {
			    return false;
			}
			continue;
		      }
		      if (preg_match('/^\s*resource_file\s*=\s*(\S+)/', $line, $regs)) {
			if ($this->readConfigFile($regs[1], $this->resourceConfig) === false) {
			    return false;
			}
			continue;
		      }			      
		      if (preg_match('/^\s*xedtemplate_config_file\s*=\s*(\S+)/', $line, $regs)) {
			if ($this->parseObjectFile($regs[1]) === false) {
			    return false;
			}
			continue;
		      }
		      if (preg_match('/^\s*([^=]+)\s*=\s*([^#;]+)\s*\n$/', $line, $regs)) {
		      			// EVENT BROKER MODULES
		      			if($regs[1] == "broker_module") {
		      				$configArray["broker_modules"][] = $regs[2];
		      			}
		      			else {		      			      	
				  		$configArray[$regs[1]] = $regs[2];
						// SF BUG# 1441729
						// resources are imported wrong
						// Solution: Also use the part before the = to check the resourcenumber
						
						/*
						
						if ( $regs[1] != "") {
							if (strstr ($regs[1], "\$USER")) {
								if (preg_match('/(\d+)/', $regs[1], $tempregs)) {
									$configArray[$regs[1]] = $tempregs[0];
								}
					  		$configArray[$regs[2]] = $regs[2];
							}
						}
						
						*/
		      			}
			continue;
		      }
		}
		return true;
	}
	private function parseConfigDir($configDir) {
		// Syntax check, if there is no trailing slash at the end of the dir name, let's add it
		if(!preg_match('/\/$/', $configDir))
			$configDir .= '/';
		// Function to go through a directory and iterate through all object config files and directories
		if(($dirPtr = @opendir($configDir)) == false) {
			$this->errorMsg = "Cannot Read From Configuration Directory: " . $configDir;
			return false;
		}
		// If we got here, no problems, let's go through the directory.  :)
	       while (($file = readdir($dirPtr)) !== false) {
		       if($file != '.' && $file != '..') {
			       if(preg_match('/.cfg$/', $file)) {
				       // Then this is a configuration file
				       if($this->parseObjectFile($configDir . $file) === false) {
					       return false;
				       }
				       continue;
			       }
			       if(filetype($configDir . $file) == 'dir') {
				       if($this->parseConfigDir($configDir . $file) === false) {
					       return false;
				       }
				       continue;
			       }
		       }		
	       }
	       closedir($dirPtr);
	}
	public function isError() {
		//print($this->errorMsg);		// No longer need to print error
		if($this->errorMsg != NULL) {
			return true;
		}
		else
			return false;
	}
	public function getError() {
		if($this->errorMsg != NULL)
			return $this->errorMsg;
		else
			return false;
	}
	
	function ParseObjectFile($filename) {
		$objectName = '';
		
		if ( ($fp = @fopen($filename, 'r')) == FALSE) {
		    $this->errormsg =  "Cannot open object file $filename";
		    return false;
		}
		while ($line = fgets($fp)) {
		    if (preg_match('/^\s*([|#;]+.*)$/', $line)) {
			continue;
		    }

			    		    
		    if (preg_match('/^\s*define\s+([^\s{]+).*$/', $line, $regs)) {
			$objectName = $regs[1];
			$tmpobject = array();
			continue;
		    }

		    if (preg_match('/^\s*}/', $line)) { //Completed object End curley bracket must be on it's own line
			switch($objectName) {
			case 'contactgroup':
				$this->contactgroups[] = $tmpobject;
			    break;
			case 'contact':
				$this->contacts[] = $tmpobject;
			    break;
			case 'host':
				$this->hosts[] = $tmpobject;
			    break;
			case 'hostgroup':
				$this->hostgroups[] = $tmpobject;
			    break;
			case 'timeperiod':
				$this->timeperiods[] = $tmpobject;
			    break;
			case 'command':
				$this->commands[] = $tmpobject;
			    break;
			case 'service':
				$this->services[] = $tmpobject;
			    break;
			case 'servicegroup':
				$this->servicegroups[] = $tmpobject;
			    break;
			case 'hostextinfo':
				$this->hostextinfo[] = $tmpobject;
			    break;
			case 'serviceextinfo':
				$this->serviceextinfo[] = $tmpobject;
			    break;
			case 'hostdependency':
				$this->hostdependencies[] = $tmpobject;
			    break;
			case 'servicedependency':
				$this->servicedependencies[] = $tmpobject;
				break;
			case 'hostescalation':
				$this->hostescalations[] = $tmpobject;
				break;
			case 'serviceescalation':
				$this->serviceescalations[] = $tmpobject;
				break;
			case 'servicegroup':
				$this->servicegroups[] = $tmpobject;
				break;
			} // switch
			$objectName = '';
			continue;
		    }		    
		    if (preg_match('/\s*([\S_]+_interval)\s+(\d*)/', $line, $regs)) {
                        $tmpobject[trim($regs[1])] = trim($regs[2]);
		        continue;
		    }
		    if (preg_match('/\s*(\S+)\s+(.*)$/', $line, $regs)) {  

			if (preg_match("/; .*$/", $regs[2]))
				$regs[2] = preg_replace("/; .*$/", "", $regs[2]);

		    	if($regs[1] != ";")
			    	$tmpobject[trim($regs[1])] = trim($regs[2]);
			continue;
		    }
		}
	}
	public function getMainConfig() {
		return $this->mainConfig;
	}
	public function getCGIConfig() {
		return $this->cgiConfig;
	}
	public function getResourceConfig() {
		return $this->resourceConfig;
	}
	public function buildInheritedObject(&$dependentObject, $targetObjectTree) {
		// Build object inheritance
		if(isset($dependentObject['use'])) {
			// First we get to the end of the inheritance chain.
			$tmpObject = $dependentObject;
			$subcounter = 0;
			$useStack[$subcounter] = $tmpObject;
			while(isset($tmpObject['use'])) {	// While we continue to have a use statement
				$found = 0;
				// Now we have to find the dependency
				if(count($targetObjectTree)) {
					foreach($targetObjectTree as $loopObject) {
						if(isset($loopObject['name']) && $loopObject['name'] == $tmpObject['use']) {
							// We found the dependency, assign it to tmpObject
							$tmpObject = $loopObject;
							$found = 1;
							break;
						}
					}
				}
				if(!$found) {
					unset($tmpObject['use']);	// Didn't find the top parent, so we'll toss it.
				}
				else {
					$subcounter++;
					$useStack[$subcounter] = $tmpObject;
				}
			}
			// At end of dependency list, start assigning values
			$numOfDependencies = count($useStack);
			$dependentObject = NULL;
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
	public function returnParsedConfig(&$builtContactGroups, &$builtContacts, &$builtHostGroups, &$builtHosts, &$builtServices, &$builtTimePeriods, &$builtCommands, &$builtHostExtended, &$builtServiceExtended) {
		$builtContactGroups = $builtContacts = $builtHostGroups = $builtHosts = $builtServices = $builtTimePeriods = $builtCommands = $builtHostExtended = $builtServiceExtended = NULL;
		$builtContactGroups = $builtContacts = $builtHostGroups = $builtHosts = $builtServices = $builtTimePeriods = $builtCommands = $builtHostExtended = $builtServiceExtended = NULL;
		if(count($this->contactgroups)) {
			foreach($this->contactgroups as $tempContactGroup) {	
				if(isset($tempContactGroup['register']) && $tempContactGroup['register'] == 0) {
					continue;
				}
				if(isset($tempContactGroup['use'])) {
					$this->buildInheritedObject($tempContactGroup, $this->contactgroups);
				}
				// We now have the completed object, let's put it into our system
				$tempContactGroup['members'] = explode(",", $tempContactGroup['members']);
				foreach($tempContactGroup['members'] as &$member)
					$member = trim($member);
				// Trimmed member list, and it's now in an array			
				$builtContactGroups[$tempContactGroup['contactgroup_name']] = $tempContactGroup;
			}
		}
		if(count($this->contacts)) {
			foreach($this->contacts as $tempContact) {
				if(isset($tempContact['register']) && $tempContact['register'] == 0) {
					continue;
				}
				if(isset($tempContact['use'])) {
					$this->buildInheritedObject($tempContact, $this->contacts);
				}
				// We have completed object, let's put into our system
				if(isset($tempContact['contactgroups'])) {
					$tempMembershipList = explode(",", $tempContact['contactgroups']);
					foreach($tempMembershipList as &$group) {
						$group = trim($group);
						if(!in_array($builtContactGroups[$group]['members']))
							$builtContactGroups[$group]['members'][] = $tempContact['contact_name'];
					}
					unset($tempContact['contactgroups']);
				}
				$builtContacts[$tempContact['contact_name']] = $tempContact;
			}
		}
		if(count($this->hostgroups)) {
			foreach($this->hostgroups as $tempHostGroup) {	
				if(isset($tempHostGroup['register']) && $tempHostGroup['register'] == 0) {
					continue;
				}
				if(isset($tempHostGroup['use'])) {
					$this->buildInheritedObject($tempHostGroup, $this->hostgroups);
				}
				// We now have the completed object, let's put it into our system
	
				if(isset($tempHostGroup['members'])) {
					$tempHostGroup['members'] = explode(",", $tempHostGroup['members']);			
					foreach($tempHostGroup['members'] as &$member) {
						$member = trim($member);
						if($member == '')
							unset($member);
					}
				}
				// Trimmed member list, and it's now in an array			
				$builtHostGroups[$tempHostGroup['hostgroup_name']] = $tempHostGroup;
			}
		}
		if(count($this->hosts)) {
			foreach($this->hosts as $tempHost) {
				if(isset($tempHost['register']) && $tempHost['register'] == 0) {
					continue;
				}
				if(isset($tempContact['use'])) {
					$this->buildInheritedObject($tempHost, $this->hosts);
				}
				// We have completed object, let's put into our system
				if(isset($tempHost['hostgroups'])) {
					$tempMembershipList = explode(",", $tempHost['hostgroups']);
					foreach($tempMembershipList as &$group) {
						$group = trim($group);
						if(!isset($builtHostGroups[$group]['members']) || !in_array($group, $builtHostGroups[$group]['members']))
							$builtHostGroups[$group]['members'][] = $tempHost['host_name'];
					}
					unset($tempHost['hostgroups']);
				}
				$builtHosts[$tempHost['host_name']] = $tempHost;
			}
		}
		// quick pass for the hostgroups time-saving tip of * in member list
		if(count($builtHostGroups)) {
			foreach($builtHostGroups as &$hostgroup) {
				if($hostgroup['members'][0] == '*') {
					$hostgroup['members'] = NULL;
					foreach($builtHosts as $host)
						$hostgroup['members'][] = $host['host_name'];
				}
			}
		}
		
		if(count($this->services)) {
			foreach($this->services as $tempService) {
				if(isset($tempService['register']) && $tempService['register'] == 0) {
					continue;
				}
				if(isset($tempService['use'])) {
					$this->buildInheritedObject($tempService, $this->services);
				}
				// We have completed object, let's put it into our system
				// We need to first check for hostgroup_name (s)
				if(isset($tempService['hostgroup_name'])) {
					$tempService['hostgroups'] = explode(",", $tempService['hostgroup_name']);
					foreach($tempService['hostgroups'] as &$group) {
						$group = trim($group);
						if(count($_SESSION['hostgroups'])) {
							if(count($_SESSION['hostgroups'][$group]['members'])) {
								foreach($_SESSION['hostgroups'][$group]['members'] as $host) {
									$builtServices[$host][$tempService['service_description']] = $tempService;
								}
							}
						}
					}
				}
				// Now let's check for multiple host_name entries
				if(isset($tempService['host_name'])) {
					$tempService['hosts'] = explode(",", $tempService['host_name']);
					foreach($tempService['hosts'] as &$host) {
						$host = trim($host);
						$builtServices[$host][$tempService['service_description']] = $tempService;
					}
				}
			}
		}
		
		if(count($this->timeperiods)) {
			foreach($this->timeperiods as $tempPeriod) {
				if(isset($tempPeriod['register']) && $tempPeriod['register'] == 0) {
					continue;
				}
				if(isset($tempPeriod['use'])) {
					$this->buildInheritedObject($tempPeriod, $this->timeperiods);
				}
				// We have completed object, let's put it into our system
				$builtTimePeriods[$tempPeriod['timeperiod_name']] = $tempPeriod;
			}
		}
		if(count($this->commands)) {
			foreach($this->commands as $tempCommand) {
				if(isset($tempCommand['register']) && $tempCommand['register'] == 0) {
					continue;
				}
				if(isset($tempCommand['use'])) {
					$this->buildInheritedObject($tempCommand, $this->commands);
				}
				// We have completed object, let's put it into our system
				$builtCommands[$tempCommand['command_name']] = $tempCommand;
			}
		}
		if(count($this->hostextinfo)) {
			foreach($this->hostextinfo as $tempExtended) {
				if(isset($tempExtended['register']) && $tempExtended['register'] == 0) {
					continue;
				}
				if(isset($tempExtended['use'])) {
					$this->buildInheritedObject($tempExtended, $this->hostextinfo);
				}
				// We have completed object, let's put it into our system
				$builtHostExtended[$tempExtended['host_name']] = $tempExtended;
			}
		}
		if(count($this->serviceextinfo)) {
			foreach($this->serviceextinfo as $tempExtended) {
				if(isset($tempExtended['register']) && $tempExtended['register'] == 0) {
					continue;
				}
				if(isset($tempExtended['use'])) {
					$this->buildInheritedObject($tempExtended, $this->serviceextinfo);
				}
				// We have completed object, let's put it into our system
				$builtServicesExtended[$tempExtended['host_name']][$tempExtended['service_description']] = $tempExtended;
			}
		}
		
	}
}




?>
