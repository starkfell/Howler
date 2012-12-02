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
	Filename: output.inc
	Description:
	The class definition for the fruityOutput class
*/

class fruityOutput {
	private $additionalHeaderLinks = array();
	function addAdditionalHeaderLink($link, $desc) {
		$this->additionalHeaderLinks[] = array('link' => $link, 'desc' => $desc);
	}
	function getAdditionalHeaderLinks() {
		return $this->additionalHeaderLinks;
	}
}


function print_navbar($navbar) {
	?>
	<div class="navbar"><?php
	$numOfElements = count($navbar);
	for($counter = 0; $counter < $numOfElements; $counter++) {
		if($counter) {
			print(" | ");
		}
		?><a href="<?=$navbar[$counter]['url'];?>"><?=$navbar[$counter]['title'];?></a><?php
	}
}

function double_pane_form_window_start() {
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_form_window_finish() {
	?>
	</table>
	<?php
}

function double_pane_select_form_element_with_enabler_and_viewer($backgroundColor, $form_name, $element_name, $element_title, $element_description, $selectList, $select_value_field, $select_desc_field, $selected, $enabler_name, $checkbox_description) {
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td class="formcell">
		<b><?=$element_title;?>:</b> <?php print_select($element_name, $selectList, $select_value_field, $select_desc_field, $selected, ($_POST[$form_name.'_enablers'][$enabler_name]));?>
		
		<?php
		print("<input type=button value='View Images' onClick=\"javascript:popUp('" . $path_config['doc_root'] . "show_images.php')\">");
		?>

		</td>

		<td align="right" class="formcell">
		<input type="hidden" name="<?=$form_name;?>_enablers[<?=$enabler_name;?>]" value="<? if($_POST[$form_name.'_enablers'][$enabler_name]) print("1"); else print("0");?>" />
		<input type="checkbox" name="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" value="1" id="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" <? if($_POST[$form_name.'_enablers'][$enabler_name]) print("CHECKED");?> onclick="form_element_switch(document.<?=$form_name;?>.elements['<?=$element_name;?>'], document.<?=$form_name;?>.elements['<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]']); enabler_switch(document.<?=$form_name;?>.elements['<?=$form_name;?>_enablers[<?=$enabler_name;?>]']);" /><label for="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]"><b><?=$checkbox_description;?></b></label>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
		<?=$element_description;?><br />
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_select_form_element_with_enabler($backgroundColor, $form_name, $element_name, $element_title, $element_description, $selectList, $select_value_field, $select_desc_field, $selected, $enabler_name, $checkbox_description) {
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td class="formcell">
		<b><?=$element_title;?>:</b> <?php print_select($element_name, $selectList, $select_value_field, $select_desc_field, $selected, ($_POST[$form_name.'_enablers'][$enabler_name]));?><br />
		</td>
		<td align="right" class="formcell">
		<input type="hidden" name="<?=$form_name;?>_enablers[<?=$enabler_name;?>]" value="<? if($_POST[$form_name.'_enablers'][$enabler_name]) print("1"); else print("0");?>" />
		<input type="checkbox" name="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" value="1" id="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" <? if($_POST[$form_name.'_enablers'][$enabler_name]) print("CHECKED");?> onclick="form_element_switch(document.<?=$form_name;?>.elements['<?=$element_name;?>'], document.<?=$form_name;?>.elements['<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]']); enabler_switch(document.<?=$form_name;?>.elements['<?=$form_name;?>_enablers[<?=$enabler_name;?>]']);" /><label for="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]"><b><?=$checkbox_description;?></b></label>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
		<?=$element_description;?><br />
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_text_form_element_with_enabler($backgroundColor, $form_name, $element_name, $element_title, $element_description, $size, $maxlength, $value, $enabler_name, $checkbox_description) {
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td class="formcell">
		<b><?=$element_title;?>:</b> <input type="text" size="<?=$size;?>" maxlength="<?=$maxlength;?>" name="<?=$element_name;?>" value="<?=$value;?>" <?php if($_POST[$form_name.'_enablers'][$enabler_name] == 0) print("DISABLED");?>><br />
		</td>
		<td align="right" valign="top" class="formcell">
		<input type="hidden" name="<?=$form_name;?>_enablers[<?=$enabler_name;?>]" value="<? if($_POST[$form_name.'_enablers'][$enabler_name]) print("1"); else print("0");?>" />
		<input type="checkbox" name="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" value="1" id="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" <? if($_POST[$form_name.'_enablers'][$enabler_name]) print("CHECKED");?> onclick="form_element_switch(document.<?=$form_name;?>.elements['<?=$element_name;?>'], document.<?=$form_name;?>.elements['<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]']); enabler_switch(document.<?=$form_name;?>.elements['<?=$form_name;?>_enablers[<?=$enabler_name;?>]']);" /><label for="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]"><b><?=$checkbox_description;?></b></label>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
		<?=$element_description; ?><br />
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_text_form_element($backgroundColor, $form_name, $element_name, $element_title, $element_description, $size, $maxlength, $value) {
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
		<b><?=$element_title;?>:</b> <input type="text" size="<?=$size;?>" maxlength="<?=$maxlength;?>" name="<?=$element_name;?>" value="<?=$value;?>"><br />
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
		<?=$element_description; ?><br />
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_checkbox_group_form_element_with_enabler($backgroundColor, $form_name, $checkboxes, $element_title, $element_description, $enabler_name, $checkbox_description) {
	$numOfElements = count($checkboxes);
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="50%" class="formcell">
		<b><?=$element_title;?>:</b>
		</td>
		<td align="right" class="formcell">
		<input type="hidden" name="<?=$form_name;?>_enablers[<?=$enabler_name;?>]" value="<? if($_POST[$form_name.'_enablers'][$enabler_name]) print("1"); else print("0");?>" />
		<input type="checkbox" name="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" value="1" id="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" <? if($_POST[$form_name.'_enablers'][$enabler_name]) print("CHECKED");?> onclick="<?php
		// Gotta send multiple calls to javascript: form_element_switch
		for($counter = 0; $counter < $numOfElements; $counter++ ) {
			?>
			form_element_switch(document.<?=$form_name;?>.elements['<?=$checkboxes[$counter]['element_name'];?>'], document.<?=$form_name;?>.elements['<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]']);
			<?php
		}
		?>enabler_switch(document.<?=$form_name;?>.elements['<?=$form_name;?>_enablers[<?=$enabler_name;?>]']);" /><label for="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]"><b><?=$checkbox_description;?></b></label>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
			<table cellspacing="0" width="100%" border="0">
			<tr>
			<td valign="top" width="30%" class="formcell">
			<?php
			for($counter = 0; $counter < $numOfElements; $counter++) {
				?>
				<input type="checkbox" name="<?=$checkboxes[$counter]['element_name'];?>" <?php if($checkboxes[$counter]['checked'] == '1') print("CHECKED");?> value="<?=$checkboxes[$counter]['value'];?>" <?php if($_POST[$form_name.'_enablers'][$enabler_name] == 0) print("DISABLED");?>><?=$checkboxes[$counter]['element_title'];?><br />
				<?php
			}
			?>
			</td>
			<td class="formcell">
			<?=$element_description; ?><br />
			</td>
			</tr>
			</table>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

function double_pane_textarea_form_element_with_enabler($backgroundColor, $form_name, $element_name, $element_title, $element_description, $rows, $cols, $value, $enabler_name, $checkbox_description) {
	$numOfElements = count($checkboxes);
	?>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="50%" class="formcell">
		<b><?=$element_title;?>:</b>
		</td>
		<td align="right" class="formcell">
		<input type="hidden" name="<?=$form_name;?>_enablers[<?=$enabler_name;?>]" value="<? if($_POST[$form_name.'_enablers'][$enabler_name]) print("1"); else print("0");?>" />
		<input type="checkbox" name="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" value="1" id="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]" <? if($_POST[$form_name.'_enablers'][$enabler_name]) print("CHECKED");?> onclick="form_element_switch(document.<?=$form_name;?>.elements['<?=$element_name;?>'], document.<?=$form_name;?>.elements['<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]']);
		enabler_switch(document.<?=$form_name;?>.elements['<?=$form_name;?>_enablers[<?=$enabler_name;?>]']);" /><label for="<?=$form_name;?>_checkboxes[<?=$enabler_name;?>]"><b><?=$checkbox_description;?></b></label>
		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		</tr>
	<tr bgcolor="<?=$backgroundColor;?>">
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" class="formcell">
			<textarea name="<?=$element_name;?>" rows="<?=$rows;?>" cols="<?=$cols;?>" <?php if($_POST[$form_name.'_enablers'][$enabler_name] == 0) print("DISABLED");?>><?=$value;?></textarea><br />	
			<?=$element_description; ?><br />

		</td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<tr>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td colspan="2" height="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
		<td width="1" bgcolor="#aaaaaa"><img src="<?=$path_config['image_root'];?>dotclear.gif" height="1" width="1" /></td>
	</tr>
	<?php
}

