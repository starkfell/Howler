<?php

/*
 * Language Manager
 */

include_once('includes/config.inc');

// Print the header
print_header("Language Editor");

print("<br />\n");
print("<br />\n");

// Action handlers
if (isset($_REQUEST['request'])) {
	
	if ($_REQUEST['request'] == 'add_language') {
		
		if ($fruity->language_exists($_REQUEST['language_manage']['locale_name'])) {
			$status_msg = "A language with that name already exists!";
		} else {
			$fruity->add_language($_REQUEST['language_manage']);
			unset($_GET['language_add']);
			$status_msg = "Language added.";
		}
	} else if ($_REQUEST['request'] == 'modify_language') {
		
		$fruity->modify_language($_REQUEST['language_manage']);
		unset($_GET['language_id']);
		$status_msg = "Language modified.";

	} else if ($_REQUEST['request'] == 'delete') {

		$fruity->delete_language($_GET['language_id']);
		unset($_GET['language_id']);
		unset($_GET['delete']);
		$status_msg = "Language deleted.";
		
	}

}




if(isset($status_msg)) {
	print('<div align="center" class="statusmsg">' . $status_msg . '</div><br />' . "\n");
}

if (isset($_GET['language_add']) || isset($_GET['language_id'])) {
	
	if ($_GET['language_id'] != '')
		print_window_header("Modify a Language", "100%");
	else
		print_window_header("Add a Language", "100%");
	
	print('<form name="language_form" action="' . $path_config['doc_root'] . 'languages.php?language_add=1">' . "\n");
	
	if (isset($_GET['language_id'])) {
		print('<input type="hidden" name="request" value="modify_language" />' . "\n");
		print('<input type="hidden" name="language_manage[language_id]" value="' . $_GET['language_id'] . '">' . "\n");
	} else {
		print('<input type="hidden" name="request" value="add_language" />' . "\n");
	}
	
	print('<b>Locale Name:</b><br />' . "\n");
	print('<input type="text" name="language_manage[locale_name]" value="');
	if (isset($_GET['language_id'])) {
		print($fruity->get_locale_name($_GET['language_id']));
	}
	print('"><br /><br />' . "\n");
	
	print('<b>Alias:</b><br />' . "\n");
	print('<input type="text" name="language_manage[alias]" value="');
	if (isset($_GET['language_id'])) {
		print($fruity->get_locale_alias($_GET['language_id']));
	}
	print('"><br /><br />' . "\n");	
	
	if (isset($_GET['language_id'])) {
		
		print('<a href="' . $path_config['doc_root'] . 'languages.php?language_id=' . $_GET['language_id'] . '&request=delete">Delete</a>&nbsp;');
		print('<input type="submit" value="Modify Language" />&nbsp;');
		print('<a href="' . $path_config['doc_root'] . 'languages.php">Cancel</a>');
		
	} else {
		
		print('<input type="submit" value="Create Language" />&nbsp;');
		
	}

	print("\n");
	
	print('</form>' . "\n");
	
	print_window_footer();
	
	
} else {

	print_window_header("Language Listings", "100%");
	
	print('&nbsp;<a class="sublink" href="' . $path_config['doc_root'] . 'languages.php?language_add=1">Add A New Language</a><br />' . "\n");
	
	$language_list = array();
	$fruity->return_language_list($language_list);
	$numOfLanguages = count($language_list);
	
	if($numOfLanguages) {
		print("<br />\n");
		print('<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">' . "\n");
		print('<tr class="altTop">' . "\n");
		print('<td>Locale Name</td>' . "\n");
		print('<td>Alias</td>' . "\n");
		print('</tr>' . "\n");
		
		for($counter = 0; $counter < $numOfLanguages; $counter++) {
			
			if($counter % 2)
				print ('<tr class="altRow1">' . "\n");
			else 
				print ('<tr class="altRow2">' . "\n");
				
			print('<td height="20" class="altLeft">&nbsp;<a href="' . $path_config['doc_root'] . 'languages.php?language_id=' . $language_list[$counter]['language_id'] . '">' . $language_list[$counter]['locale_name'] . '</a></td>' . "\n");
			print('<td height="20" class="altRight">' . $language_list[$counter]['alias'] . '</td>' . "\n");
			print("</tr>\n");
		}
		
		print("</table>\n");

	} else {
		
		print("<br />\n");
		print('<div class="statusmsg">No Periods Exist</div>' . "\n");
		
	}	
	
	print_window_footer();
	
}

print_footer();

?>