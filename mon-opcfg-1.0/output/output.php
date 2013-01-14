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
* output.php
* Modified: 2/14/05
* Description:
* Output library
*/

// Let's create the enable/disable select list
$enable_list[] = array("values" => 0, "text" => "Disable");
$enable_list[] = array("values" => 1, "text" => "Enable");


function print_redirect($redirect = "", $redirect_sec = 0, $redirect_text = "") {
	global $config;
	?>
	<HTML>
	<HEAD>
	<META CONTENT="<?php echo $redirect_sec; ?>;url=<?php echo $redirect; ?>" http-equiv="refresh">
	<TITLE></TITLE>
	</HEAD>
	<BODY>
	<?php echo $redirect_text; ?>
	</BODY>
	</HTML>
	<?php
}

function print_error($err_code, $err_message = "NULL", $terminate = 0) {
	global $output_config;
	?>
	An Internal Error Has Occurred
	
	<b>Error Code: </b><?=$err_code;?><br />
	<?php
	if($err_message != "NULL") {

		print("<b>" . $err_message . "</b><br />");

	}

	?>

	<p>

	<b>Please contact <?=$config['admin']['name'];?> at:

	<a href="mailto://<?=$config['admin']['email'];?>">

	<?=$config['admin']['email'];?></a></b><br />

	<p>

	<?

	if($terminate > 0)

		print("Due to the severity of this error, the page has cancelled loading.<br />");

	print(" ");

	if($terminate > 0)

		die();

	else

		return 0;

}

function print_tree($hostNavArray, $depth) {
	global $output_config, $path_config;
	$numOfEntries = count($hostNavArray);
	if($output_config['use_frames'] == 0)
		$link = $_SERVER['PHP_SELF'];
	else
		$link = 'sidenav.php';
		
	for($counter = 0; $counter < $numOfEntries; $counter++) {
		?>
		<tr>
		<td height="20" valign="top">
		<font face="<?=$output_config['font_face'];?>" size="1">
		<?php
		// If there is depth to the category (based on how deep in the tree it is category wise, print blank space)
		if($depth > 0)
			print("<img src=\"".$path_config['image_root']."navspacer.gif\" height=\"9\" width=\"".($depth * 9)."\" alt=\"\" />");
		// If there are no sub-categories expanded yet, but it's a branch, then print the arrow to provide expansion ability.
		if(($hostNavArray[$counter]["children"] == NULL) && ($hostNavArray[$counter]["leaf"] == 0)) {
			?><a href="<?=$link;?>?request=OPEN&host_id=<?=$hostNavArray[$counter]["host_id"];?>"><img src="<?=$path_config['image_root'];?>plus.gif" border="0" align="middle" height="9" width="9" alt="+"/></a><?php
		}
		// If there are categories expanded, then let's go ahead and provide a way to collapse the branch.
		if(($hostNavArray[$counter]["children"] <> NULL) && $hostNavArray[$counter]["leaf"] == 0) {
			?><a href="<?=$link;?>?request=CLOSE&host_id=<?=$hostNavArray[$counter]["host_id"];?>"><img src="<?=$path_config['image_root'];?>minus.gif" width="9" align="middle" height="9" border="0" alt="-" /></a><?php
		}
		// If the category is simply a leaf, then let's just print a nav spacer (same size as the collapse/expand arrows)
		if($hostNavArray[$counter]["leaf"] == 1) {
			?><img src="<?=$path_config['image_root'];?>navspacer.gif" width="9" height="9" align="middle" alt="" /><?
		}
		?>&nbsp;<a class="headerlink" href="hosts.php?host_id=<?=$hostNavArray[$counter]["host_id"];?>" target="main"><?php if($hostNavArray[$counter]["leaf"] == 0) print("<b>"); ?><?=$hostNavArray[$counter]["name"];?><?php if($hostNavArray[$counter]["leaf"] == 0) print("</b>"); ?></a></td>
		</tr>
		<?php
		// If there are sub categories at this point, recursive call while increasing the depth
		if($hostNavArray[$counter]["children"] <> NULL)
			print_tree($hostNavArray[$counter]["children"], ($depth+1));
	}
}



function print_window_header($header, $width, $alignment = "center") {
	global $output_config;
	global $path_config;
	?>
	<table <?php if($width != NULL) { ?>width="<?=$width;?>"<?php }?> cellspacing="0" cellpadding="0" align="<?=$alignment;?>">
	<tr>
		<td width="1" bgcolor="#CCCCCC"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td height="1" bgcolor="#CCCCCC"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#CCCCCC"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td class="navbar" bgcolor="#C3C7D3">
		<table cellpadding="2" border="0">
		<tr>
		<td class="windowtitlebar"><?=$header;?></td>
		</tr>
		</table>
		</td>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td height="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#CCCCCC"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td bgcolor="#f9f9f9">
			<table width="100%" border="0">
			<tr>
				<td class="description">
	<?php
}

function print_window_footer() {
	global $path_config;
	?>
				</td>
			</tr>
			</table>
		</td>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#CCCCCC"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td height="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>	
	</table>
	<?php
}
	


// Used if frames not used
function print_header($header, $marginwidth = 0, $alignment = "center") {
	global $output_config;
	global $path_config;
	global $sys_config;
	?>
	<html>
	<head>
	<title><?=$sys_config['name'];?><?php if($header) print(" - " . $header);?></title>

	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/yahoo/yahoo-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/dom/dom-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/event/event-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/animation/animation-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/connection/connection-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/dragdrop/dragdrop-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/element/element-beta-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/button/button-min.js"></script>
	<script type="text/javascript" src="<?=$path_config['doc_root'];?>includes/yui/build/container/container-min.js"></script>

	<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>includes/yui/build/container/assets/skins/sam/container.css"> 
	<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>includes/yui/build/button/assets/skins/sam/button.css"> 

	<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>style/used.css">
	</head>
	
	<body bgcolor="#C3C7D3" marginheight="0" marginwidth="<?=$marginwidth;?>" leftmargin="0" topmargin="0" class="yui-skin-sam">
	<script language="javascript">
	function form_element_switch(element, checkbox) {
		if(checkbox.checked) {
			element.readOnly = false;
			element.disabled = false;
		}
		else {
			element.readOnly = true;
			element.disabled = true;
		}
	}
	
	function enabler_switch(enabler) {
		if(enabler.value == '0') {
			enabler.value = '1';
		}
		else {
			enabler.value = '0';
		}
	}
	
	function confirmDelete(msg) {
		
		// A little trick...
		msg = msg || 0;
		
		if (msg == 0) {
			return confirm("Do you really want to delete this Object?");
		} else {
			return confirm("This object is part of the following report(s):\n\n" + msg + "\n\nDo you really want to delete this Object?");
		}
		
  }

	function popUp(URL) {
		day = new Date();
		id = day.getTime();
		eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600');");
	}

	function enableFieldCheckbox( fieldName , checkName ) {
					
		var field = document.getElementsByName(fieldName);
		var check = document.getElementsByName(checkName);
					
		if (field[0].disabled == false) {
			field[0].disabled = true;
			check[0].value = 1;
		}else{
			field[0].disabled = false;
			check[0].value = 0;
		}
	}

        function removeSpaces(string) {
                var tstring = "";
                string = '' + string;
                splitstring = string.split(" ");
                for(i = 0; i < splitstring.length; i++)
                tstring += splitstring[i];
                return tstring;
        }

	function showDuplicateHostDialog() {

		// Prepare cloning panel
		var loading = new YAHOO.widget.Panel("duplicatingHost", { close: false, fixedcenter : true, width : "30em", constraintoviewport:true, visible : false, modal : true });
		loading.render();

		// Define various event handlers for Dialog
		var handleSubmit = function() {
			loading.show();
			this.submit();
		};

		var handleCancel = function() {
			this.cancel();
		};

		var handleSuccess = function(o) {
			loading.hide();
			document.getElementsByName('new_host_name')[0].value = "";
			document.getElementsByName('new_description')[0].value = "";
			document.getElementsByName('new_address')[0].value = "";
			var response = o.responseText;
			var split = new Array();
			split = response.split("!");
			alert(split[0]);
			if (split[1] !== "0") {
				window.location = "<?=$path_config['doc_root'];?>hosts.php?host_id=" + split[1];
				parent.sidenav.location.reload();
			}
		};

		var handleFailure = function(o) {
			loading.hide();
			alert("Submission failed: " + o.status);
		};

		// Instantiate the Dialog
		var dialog = new YAHOO.widget.Dialog("duplicateHost", 
								{ width : "30em",
								  fixedcenter : true,
								  visible : false, 
								  modal : true, 
								  close: false,
 								  draggable:false,  
								  constraintoviewport : true,
								  buttons : [ { text:"Submit", handler:handleSubmit, isDefault:true },
									      { text:"Cancel", handler:handleCancel } ]
								});

		// Wire up the success and failure handlers
		dialog.callback = { success: handleSuccess,
							     failure: handleFailure };
	
		// Render the Dialog
		dialog.render();

		YAHOO.util.Event.addListener("duplicateHostButton", "click", dialog.show, dialog, true);

	}
	
	function showCreateTemplateDialog() {

		// Prepare creating template panel
		var loading = new YAHOO.widget.Panel("creatingTemplate", { close: false, fixedcenter : true, width : "30em", constraintoviewport:true, visible : false, modal : true });
		loading.render();

		// Define various event handlers for Dialog
		var handleSubmit = function() {
			loading.show();
			this.submit();
		};

		var handleCancel = function() {
			this.cancel();
		};

		var handleSuccess = function(o) {
			loading.hide();
			document.getElementsByName('new_template_name')[0].value = "";
			document.getElementsByName('new_template_description')[0].value = "";
			var response = o.responseText;
			var split = new Array();
			split = response.split("!");
			alert(split[0]);
			if (split[1] !== "0") {
				window.location = "<?=$path_config['doc_root'];?>host_templates.php?host_template_id=" + split[1];
				parent.sidenav.location.reload();
			}
		};

		var handleFailure = function(o) {
			loading.hide();
			alert("Submission failed: " + o.status);
		};

		// Instantiate the Dialog
		var dialog = new YAHOO.widget.Dialog("createTemplate", 
								{ width : "30em",
								  fixedcenter : true,
								  visible : false, 
								  modal : true, 
								  close: false,
 								  draggable:false,  
								  constraintoviewport : true,
								  buttons : [ { text:"Submit", handler:handleSubmit, isDefault:true },
									      { text:"Cancel", handler:handleCancel } ]
								});

		// Wire up the success and failure handlers
		dialog.callback = { success: handleSuccess,
							     failure: handleFailure };
	
		// Render the Dialog
		dialog.render();

		YAHOO.util.Event.addListener("createTemplateButton", "click", dialog.show, dialog, true);

	}

	// Export JS functions
	function startExport() {
	
		var URL = "<?=$path_config['doc_root'];?>ajax_export.php?action=start_export";
		YAHOO.util.Connect.asyncRequest('GET', URL, { success: function(data) {
				alert(data.responseText);
			}}
		);
	
	}
	
	function updateRun() {
		var URL = "<?=$path_config['doc_root'];?>ajax_export.php?action=show_run_log";
		YAHOO.util.Connect.asyncRequest('GET', URL, { success: function(data) {
				YAHOO.util.Dom.get('run_output').innerHTML = data.responseText;
			}}
		);
	
	}
	
	function updateLog() {
		var URL = "<?=$path_config['doc_root'];?>ajax_export.php?action=show_log";
		YAHOO.util.Connect.asyncRequest('GET', URL, { success: function(data) {
				YAHOO.util.Dom.get('log_output').innerHTML = data.responseText;
			}}
		);
	
	}
	
	function showLoader() {
	
		var img = YAHOO.util.Dom.get('loader');
		var div = YAHOO.util.Dom.get('run_output');
	
		if (div.innerHTML == "No export process running" || div.innerHTML == "") {
			img.style.display = "none";
		} else {
			img.style.display = "block";
		}
	
	}

	/*
	 * Change some invalid chars from str
	 * received as parameter and return the
	 * changed string
	 */
	function changeCharCode(str) {
	
		var repName = "";
		for(i=0; i<str.length; i++) {
			var character = str.charAt(i);
			var code = str.charCodeAt(i);
			
			if(code >= 224 && code <= 230) {
			      repName += "a";
			}
			else if(code >= 232 && code <= 235) {
			      repName += "e";
			}
			else if(code >= 236 && code <= 239) {
			      repName += "i";
			}
			else if(code >= 240 && code <= 246) {
			      repName += "o";
			}
			else if(code >= 249 && code <= 253) {
			      repName += "u";
			}
			else if(code == 231) {
			      repName += "c";
			}
			else if(code == 199) {
			      repName += "C";
			}
			else if(code >= 192 && code <= 198 ) {
			      repName += "A";
			}
			else if(code >= 200 && code <= 203 ) {
			      repName += "E";
			}
			else if(code >= 204 && code <= 207 ) {
			      repName += "I";
			}
			else if(code >= 210 && code <= 214 ) {
			      repName += "O";
			}
			else if(code >= 217 && code <= 220 ) {
			      repName += "U";
			}
			else if(character == " ") {
					repName += "_";
			}else if(  	(code >= 97 && code <= 122)  ||
			        		(code >= 65 && code <= 90)   ||
			        		(code >= 48 && code <= 57)   ||
			        		(character == "\n") || 
							(character == "_" ) ||
							(character == "-" ) ||
							(character == "/" ) ) {
			
			      repName += character;
			}
	
		}
	
		return(repName);
	}
	
	</script>
	<table height="100%" width="100%" cellspacing="0" cellpadding="0" align="<?=$alignment;?>">
	<tr>
		<td height="1" width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td class="navbar" bgcolor="#C3C7D3">
		<table cellpadding="2" border="0">
		<tr>
		<td height="40" class="titlebar"><?=$header;?></td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td height="1" width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#000000"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td valign="top" bgcolor="#eeeeee">
			<table border="0" width="100%">
			<tr>
				<td valign="top" class="description">
	<?php
}

function print_frame_header($fruity) {
	global $fruity;	
	global $output_config;
	global $path_config;
	global $sys_config;
	
	if($fruity->isSideBarIncluded()) {
		$linktarget = "rightHome";
	}
	else {
		$linktarget = "main";
	}
	?>
<html>
<head>
<title><?=$sys_config['name'];?><?php if($header) print(" - " . $header);?></title>

<!-- OpCss -->
<LINK REL='stylesheet' TYPE='text/css' HREF='<?=$path_config['doc_root'];?>style/common.css'>
<LINK REL='stylesheet' TYPE='text/css' HREF='<?=$path_config['doc_root'];?>style/opservices.css'>
<LINK REL='stylesheet' TYPE='text/css' HREF='<?=$path_config['doc_root'];?>style/avail.css'>

</head>

<body bgcolor="#F0F1F5" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
<table height="100%" width="100%" cellspacing="0" cellpadding="0">
	<tr bgcolor="#F0F1F5">
		<td height="1" colspan="2" class="navbar" bgcolor="#F0F1F5" valign="center" >
			<table cellspacing="2" border="0">
			<tr>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="welcome.php">Home</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="main.php">Main Config</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="cgi.php">CGI Config</a></td>
				<td>|</td>
<!--				<td><a target="<?=$linktarget;?>" class="headerlink" href="languages.php">Languages</a></td>				
				<td>|</td> -->
				<td><a target="<?=$linktarget;?>" class="headerlink" href="resources.php">Resources</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="commands.php">Commands</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="timeperiods.php">Time Periods</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="contacts.php">Contacts</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="contactgroups.php">Contact Groups</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="templates.php">Templates</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>hosts.php">Hosts</a></td>
				<td>|</td>
                                <td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>add_multiple_hosts.php">Add Multiple Hosts</a></td>
                                <td>|</td>
                                <td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>update_host_doc_search.php">Host Documentation</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>hostgroups.php">Host Groups</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>servicegroups.php">Service Groups</a></td>
				<td>|</td>
				<td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>new_export.php">Export</a></td>
				<!-- <td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$path_config['doc_root'];?>export.php">Export</a></td> -->
				<?php
				$additionalLinks = $fruity->getOutputHandler()->getAdditionalHeaderLinks();
				$numOfLinks = count($additionalLinks);
				for($counter = 0; $counter < $numOfLinks; $counter++) {
					?>
					<td>|</td>
					<td><a target="<?=$linktarget;?>" class="headerlink" href="<?=$additionalLinks[$counter]['link'];?>"><?=$additionalLinks[$counter]['desc'];?></a></td>
					<?php
				}
				?>
			</tr>
			</table>
		</td>
		<td class="headerright" align="right" valign="center">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td><img src="<?=$path_config['image_root'];?>dotclear.gif" height="2" width="1" /></td>
				</tr>
				<tr>
					<td>
					<?
						if($fruity->isSearchIncluded()) {
							$fruity->searchRender();
						}
					?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
	<?php
}

function print_footer() {
	global $output_config;
	global $path_config;
	?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	</body>
	</html>
	<?php
}

function print_select($name, $list, $index, $index_desc, $selected = NULL, $enabled = 1) {
	$numOfElements = count($list);
	?>
	<select name="<?=$name;?>" <? if(!$enabled) print("DISABLED");?>>
		<?php
		for($counter = 0; $counter < $numOfElements; $counter++) {
			?>
			<option <?php if($selected == $list[$counter][$index]) print("SELECTED");?> value="<?=$list[$counter][$index];?>"><?=$list[$counter][$index_desc];?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function print_blank_header($bgcolor = "#ffffff", $marginwidth = 0, $title = NULL) {
	global $output_config;
	global $path_config;
	?>
	<html>
	<head>
	<title><?=$title;?></title>
	<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>style/used.css">
	</head>
	<body style="background-color: <?=$bgcolor;?>" marginwidth="<?=$marginwidth;?>" marginheight="0" topmargin="0" leftmargin="0" rightmargin="0"><?php
}

function print_blank_footer() {
	?>
	</body>
	</html>
	<?php
}


function print_list($listItems, $listKeys, $sortBy, $width = "100%") {
	$numOfItems = $listItems;
	?>
	<table width="<?=$width;?>" cellspacing="0" cellpadding="0" border="0">
	<?php
	for($counter = 0; $counter < $numOfItems; $counter++) {
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
		<td><?=$listItems[$counter][$listKeys[0]['key_name']]?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}

function print_command( $check_command) {
	$count = count( $check_command);
	if( $count == 0)
		print "[none]";
	else {
		print $check_command[0];
		for( $i=1;$i<$count;$i++) {
			print "<span class=\"bang\" style=\"font-weight: bold; font-size: 16px;\">!</span>" . $check_command[$i];
		}
	}
}
