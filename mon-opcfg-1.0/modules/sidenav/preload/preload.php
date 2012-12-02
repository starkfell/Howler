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
	Module Name: preload
	Module Category: sidenav
	Module Description: 
	Preloads the sidenav when first called.  Allows for up to 4 different
	  types of views: complete/partial preload and list by host/hostgroup.
	
*/

include_once(FRUITY_FS_ROOT . 'includes/TreeMenu/TreeMenu.php');

/*
	TODO:
	* "Snap to" when expanding on parts
	* Allow "FRUITY_DEFAULT_SIDENAV_GENERATION" to be changed via a GET setting
*/

define( "MODULE_SIDENAV_PRELOAD_ENABLED", "true");

// DEFINE:		FRUITY_DEFAULT_SIDENAV_GROUPING
// DESC:		Defines the default grouping behavior: host, or hostgroup
// POSSIBLE:	host - sorts by hosts, grouping parents; 
//			hostgroup - places all grouped hosts into groups, adding ungrouped hosts at the end
define( "FRUITY_DEFAULT_SIDENAV_GROUPING", "host");

// DEFINE:		FRUITY_DEFAULT_SIDENAV_GENERATION
// DESC:		Defines how to load the sidenav: all at once, or page-by-page
// POSSIBLE:	complete - loads the entire database and renders once
//			part - loads only "open groups" and refreshing the page when a group is opened
define( "FRUITY_DEFAULT_SIDENAV_GENERATION", "part");

class module_sidenav_preload extends Module {
	// Configuration Parameters
	private $treeType;
	private $treeGeneration;
	
	function __construct() {
		$this->setVersionInfo('preload', 'Sidebar Which Preloads Information For Faster Loading', 1, 0);
		$this->treeType = FRUITY_DEFAULT_SIDENAV_GROUPING;
		$this->treeGeneration = FRUITY_DEFAULT_SIDENAV_GENERATION;
		if( isset( $_GET['treeType']))
			$this->treeType = $_GET['treeType'];
		
		if( isset( $_GET['reload'])) {
			switch( $this->treeType) {
				case 'hostgroup':
					unset( $_SESSION['tempData']['trees']['hostgroup']);
					break;
				case 'host':
				default:
					unset( $_SESSION['tempData']['trees']['host']);
			}
			unset( $_GET['reload']);
		}
	}
	
	function __destruct() {
	}
	
	function init() {
		// does nothing
	}
	
	public function restart() {
		// Check for passed information via the URL
		
		if(isset($_GET['treeType'])) {
			$this->treeType = $_GET['treeType'];
		}
		else {
			$this->treeType = FRUITY_DEFAULT_SIDENAV_GROUPING;
		}
	}
	
	// Function:	buildHostTreeComplete
	// Description:	Recursive function to build the network layout in tree form for sidenav based on Host
	// Status:	Pending Testing
	
	private function buildHostTreeComplete($curhost, &$node) {
		global $fruity;
		// set our icons
		$icon         = 'plus.gif';
		$expandedIcon = 'minus.gif';
			$fruity->get_children_hosts_list($curhost, $childrenList);
		if($childrenList) {
			foreach($childrenList as $child) {
				$fruity->get_children_hosts_list($child['host_id'], $subChildrenList);
				if($subChildrenList) {	// This host has children hosts
					$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => '<b>'.$child['host_name'].'</b>', 'class' => 'treeNode', 'link' => 'hosts.php?host_id='. $child['host_id'], 'linkTarget' => 'rightHome')));
					$this->buildHostTreeComplete($child['host_id'], $tempNode);
				}
				else {
					$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => $child['host_name'], 'link' => 'hosts.php?host_id='. $child['host_id'], 'linkTarget' => 'rightHome')));
				}
			}
		}
	}
	
	// Function:	buildHostgroupTreeComplete
	// Description:	Function to build the network layout in tree form for sidenav based on Hostgroup
	// Status:	Pending Testing
	
	private function buildHostgroupTreeComplete( &$node) {
		global $fruity;
		// set our icons
		$icon         = 'plus.gif';
		$expandedIcon = 'minus.gif';
		$fruity->get_hostgroup_list( $hostgroupList);
		
		if( $hostgroupList) {
			$fruity->get_host_list( $tempHosts);
			$allHosts = array();
			$used = array();
			if(count($tempHosts)) {
				foreach( $tempHosts as $host) {
					$allHosts[$host['host_id']] = $host['host_name'];
				}
			}
			if(count($hostgroupList)) {
				foreach( $hostgroupList as $hostgroup) {
					$fruity->return_hostgroup_member_list( $hostgroup['hostgroup_id'], $memberList);
					$fruity->return_hostgroup_inherited_member_list( $hostgroup['hostgroup_id'], $inheritedList);
					
					if( $memberList || $inheritedList) {
						$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => '<b>'.$hostgroup['hostgroup_name'].'</b>', 'class' => 'treeNode', 'link' => 'hostgroups.php?hostgroup_id='.$hostgroup['hostgroup_id'], 'linkTarget' => 'rightHome')));
					}
					
					if( $memberList) {
						foreach( $memberList as $host) {
							$tempNode->addItem( new HTML_TreeNode(array('text' => $fruity->return_host_name( $host['host_id']), 'link' => 'hosts.php?host_id='.$host['host_id'], 'linkTarget' => 'rightHome')));
							unset( $allHosts[$host['host_id']]);
						}
					}
					
					if( $inheritedList) {
						foreach( $inheritedList as $host) {
							$tempNode->addItem( new HTML_TreeNode(array('text' => $fruity->return_host_name( $host['host_id']), 'link' => 'hosts.php?host_id='.$host['host_id'], 'linkTarget' => 'rightHome')));
							unset( $allHosts[$host['host_id']]);
						}
					}
					
					if( !$memberList && !$inheritedList) {
						$node->addItem(new HTML_TreeNode(array('text' => $hostgroup['hostgroup_name'], 'list' => 'hostgroups.php?hostgroup_id='.$hostgroup['hostgroup_id'], 'linkTarget' => 'rightHome')));
					}
				}
			}
			
			if(count($allHosts)) {
				foreach( $allHosts as $host_id=>$host) {
					$tempNode = &$node->addItem( new HTML_TreeNode(array('text' => $host, 'link' => 'hosts.php?host_id='.$host_id, 'linkTarget' => 'rightHome')));
				}
			}
		}
	}
	
	
	// Function:	buildHostTreePart
	// Description:	Recursive function to build the network layout in tree form for sidenav based on Host
	// Status:	Pending Testing
	
	private function buildHostTreePart( &$node) {
		$tempNodes = explode( ":", $_COOKIE['TreeMenuBranchStatus']);
		$this->_buildHostNode( $_SESSION['tempData']['trees']['host'], $node, $tempNodes);
	}
	
	private function _buildHostNode( $childrenList, &$node, $tempNodes, $depth=4) {
		$nodes = array();
		if(count($tempNodes)) {
			foreach( $tempNodes as $tnode) {
				$tempNode = explode( "_", $tnode);
				if( isset( $tempNode[$depth])) {
					$nodes[$tempNode[$depth]] = true;
				}
			}
		}
		
		if( count( $childrenList) > 0) {
			$nodeCount = 1;
			foreach($childrenList as $child) {
				if( count( $child['children']) > 0) {	// This host has children hosts
					$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => '<b>'.$child['host_name'].'</b>', 'class' => 'treeNode', 'link' => 'hosts.php?host_id='. $child['host_id'], 'linkTarget' => 'rightHome'), array( 'onexpand' => 'window.location="sidenav.php?treeType='.$this->treeType.'"')));
					
					if( $nodes[$nodeCount]) {
						$this->_buildHostNode($child['children'], $tempNode, $tempNodes, $depth+1);
					} else {
						$tempNode->addItem(new HTML_TreeNode(array('text' => 'Loading...')));
					}
				}
				else {
					$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => $child['host_name'], 'link' => 'hosts.php?host_id='. $child['host_id'], 'linkTarget' => 'rightHome')));
				}
				$nodeCount++;
			}
		}
	}
	
	
	// Function:	buildHostgroupTreePart
	// Description:	Function to build a partial network layout in tree form for sidenav based on Hostgroup
	// Status:	Pending Testing
	
	private function buildHostgroupTreePart( &$node) {
		// Check the cookie for which nodes should be expanded
		$nodes = array();
		$tempNodes = explode( ":", $_COOKIE['TreeMenuBranchStatus']);
		foreach( $tempNodes as $tnode) {
			$tempNode = explode( "_", $tnode);
			$nodes[$tempNode[4]] = true;
		}
		
		if( isset( $_SESSION['tempData']['trees']['hostgroup'])) {
			$nodeCount = 1;
			foreach( $_SESSION['tempData']['trees']['hostgroup'] as $hostgroup) {
				if( $hostgroup['hostgroup_id'] > 0) {
					if( count( $hostgroup['hosts']) > 0) {
						$tempNode = &$node->addItem(new HTML_TreeNode(array('text' => '<b>'.$hostgroup['hostgroup_name'].'</b>', 'class' => 'treeNode', 'link' => 'hostgroups.php?hostgroup_id='.$hostgroup['hostgroup_id'], 'linkTarget' => 'rightHome'), array('onexpand' => 'window.location="sidenav.php?treeType='.$this->treeType.'"')));
						
						if( $nodes[$nodeCount]) {
							foreach( $hostgroup['hosts'] as $host) {
								$tempNode->addItem( new HTML_TreeNode(array('text' => $host['host_name'], 'link' => 'hosts.php?host_id='.$host['host_id'], 'linkTarget' => 'rightHome')));
							}
						} else {
							$tempNode->addItem(new HTML_TreeNode(array('text' => 'Loading...')));
						}
					} else {
						$node->addItem(new HTML_TreeNode(array('text' => $hostgroup['hostgroup_name'], 'list' => 'hostgroups.php?hostgroup_id='.$hostgroup['hostgroup_id'], 'linkTarget' => 'rightHome')));
					}
				} else {
					foreach( $hostgroup['hosts'] as $host) {
						$tempNode = &$node->addItem( new HTML_TreeNode(array('text' => $host['host_name'], 'link' => 'hosts.php?host_id='.$host['host_id'], 'linkTarget' => 'rightHome')));
					}
				}
				
				$nodeCount++;
			}
		}
	}
	
	
	// Function:	preloadHostTree
	// Description:	Function to completly load the Host view of the sidenav
	// Status:	Pending Testing
	
	private function preloadHostTree() {
		global $fruity;
		// Empty tree
			$_SESSION['tempData']['trees']['host'] = array();

		
		$fruity->get_host_list( $hosts);
		
		// Doing a breadth-first arrangement here
		// Here is the first level (or 0th level, depending on how you feel today)
		$hostCount = count( $hosts);
		$nodeCount = 0;
		if( $hostCount > 0) {
			for( $i=0;$i<$hostCount;$i++) {
				// Take all hosts without a parent to make the top node
				if( is_null( $hosts[$i]['parents'])) {
					$_SESSION['tempData']['trees']['host'][$nodeCount] = array( 'host_id' => $hosts[$i]['host_id'], 'host_name' => $hosts[$i]['host_name'], 'children' => array());
					$nodeCount++;
					unset( $hosts[$i]);
				}
			}
		}
		
		// More hosts?  Recurse!
		if( count( $hosts) > 0) {
			$this->_preloadHostChildren( $hosts, $_SESSION['tempData']['trees']['host']);
		}
	}
	
	private function _preloadHostChildren( &$hosts, &$tree) {
		// Adding children to parents
		$numNodes = count( $tree);
		foreach( $hosts as $k=>$host) {
			// Iterate through all of the nodes and check for matches
			for( $n=0;$n<$numNodes;$n++) {
				// Check if this host matches
				if( $host['parents'] == $tree[$n]['host_id']) {
					// it's a match!!
					$tree[$n]['children'][] = array( 'host_id' => $host['host_id'], 'host_name' => $host['host_name'], 'children' => array());
					unset( $hosts[$k]);
					break;
				}
			}
		}
		
		// This level is done!
		// Check if there are more hosts; if so, delve deeper...
		if( count( $hosts) > 0) {
			for( $n=0;$n<$numNodes;$n++) {
				// Check if there are any children deeper than this level
				if( count( $tree[$n]['children']) > 0) {
					// Yep, there are -- recurse!!!
					// And suddenly, it becomes depth-first... bah!
					$this->_preloadHostChildren( $hosts, $tree[$n]['children']);
				}
				
				if( count( $hosts) == 0)
					break;
			}
		}
	}
	
	
	// Function:	preloadHostgroupTree
	// Description:	Function to completly load the Hostgroup view of the sidenav
	// Status:	Pending Testing
	
	private function preloadHostgroupTree() {
		if( !isset( $_SESSION['tempData']['trees']['hostgroup'])) {
			unset( $_SESSION['tempData']['trees']['hostgroup']);
			$_SESSION['tempData']['trees']['hostgroup'] = array();
			
			global $fruity;
			$fruity->get_hostgroup_list( $hostgroupList);
			
			if( $hostgroupList) {
				$fruity->get_host_list( $tempHosts);
				$allHosts = array();
				$used = array();
				foreach( $tempHosts as $host) {
					$allHosts[$host['host_id']] = $host['host_name'];
				}
				
				$nodeCount = 0;
				foreach( $hostgroupList as $hostgroup) {
					$fruity->return_hostgroup_member_list( $hostgroup['hostgroup_id'], $memberList);
					$_SESSION['tempData']['trees']['hostgroup'][$nodeCount] = array( 'hostgroup_id' => $hostgroup['hostgroup_id'], 'hostgroup_name' => $hostgroup['hostgroup_name'], 'hosts' => array());
					
					if( $memberList) {
						foreach( $memberList as $host) {
							$_SESSION['tempData']['trees']['hostgroup'][$nodeCount]['hosts'][] = array( 'host_id' => $host['host_id'], 'host_name' => $fruity->return_host_name( $host['host_id']));
							$used[$host['host_id']] = 1;
							//unset( $allHosts[$host['host_id']]);
						}
					}
					
					$fruity->return_hostgroup_inherited_member_list( $hostgroup['hostgroup_id'], $memberList);
					if( $memberList) {
						foreach( $memberList as $host) {
							$_SESSION['tempData']['trees']['hostgroup'][$nodeCount]['hosts'][] = array( 'host_id' => $host['host_id'], 'host_name' => $fruity->return_host_name( $host['host_id']));
							$used[$host['host_id']] = 1;
						}
					}
					
					$nodeCount++;
				}
				
				if( count( $allHosts) > 0) {
					$_SESSION['tempData']['trees']['hostgroup'][$nodeCount] = array( 'hostgroup_id' => 0, 'hostgroup_name' => '', 'hosts' => array());
					foreach( $allHosts as $host_id=>$host) {
						if( !isset( $used[$host_id])) {
							$_SESSION['tempData']['trees']['hostgroup'][$nodeCount]['hosts'][] = array( 'host_id' => $host_id, 'host_name' => $host);
						}
					}
				}
			}
		}
	}
	
	public function render() {
		global $path_config;
		global $sys_config;
		
		// Create our TreeMenu object.
		$menu = new HTML_TreeMenu();
		
		// Create our top level node for our TreeMenu.
		$topNode = new HTML_TreeNode(array('text' => "<b>".$sys_config['network_desc'] ."</b><br />", 'link' => 'hosts.php', 'linkTarget' => 'rightHome'));
		
		$link = array( "text" => "List by Hostgroup", "type" => "hostgroup");
		switch( $this->treeGeneration) {
			case 'part': {
				switch( $this->treeType) {
					case 'hostgroup':
						$this->preloadHostgroupTree();
						$this->buildHostgroupTreePart( $topNode);
						$link = array( "text" => "List by Host", "type" => "host");
						break;
					case 'host':
					default:
						$this->preloadHostTree();
						$this->buildHostTreePart( $topNode);
						$this->treeType = "host";
						break;
				}
			} break;
			case 'complete':
			default: {
				switch( $this->treeType) {
					case 'hostgroup':
						$this->buildHostgroupTreeComplete( $topNode);
						$link = array( "text" => "List by Host", "type" => "host");
						break;
					case 'host':
					default:
						$this->buildHostTreeComplete( 0, $topNode);
						$treeType = "host";
						break;
				}
			} break;
		}
		
		$menu->addItem($topNode);
		$treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => 'images', 'defaultClass' => 'treeNode'));
		
		print_blank_header("#C3C7D3");
?>
<script src="TreeMenu.js" language="JavaScript" type="text/javascript"></script>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td class="navbar" bgcolor="#C3C7D3">
	<table cellpadding="2" border="0">
	<tr>
	<td height="40" class="titlebar">Browser</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
</tr>
</table>
<table width="100%" bgcolor="#C3C7D3">
<tr>
<td width="100%">
<table width="100%">
<tr>
 <td><div class="small">[<a class="headerlink" href="<?=$path_config['doc_root'];?>sidenav.php?reload=1&treeType=<?=$this->treeType;?>">Reload</a>]</div></td>
 <td align="right" width="100%"><div class="small">[<a class="headerlink" href="<?=$path_config['doc_root'];?>sidenav.php?treeType=<?=$link['type'];?>"><?=$link['text'];?></a>]</div></td>
</tr>
</table>
</td>
</tr>
</table>
<table>
<tr>
<td bgcolor="#C3C7D3"><div class="small"><?$treeMenu->printMenu();?></div></td>
</tr>
</table>

<?php
		print_blank_footer();
	}
}

// $fruity->setSidebarHandler(new module_sidenav_preload);

?>
