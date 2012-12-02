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
	login.php
	Login page for Fruity.  Redirected to if session becomes invalid
*/

include_once('../../../includes/config.inc');


// Form Processing
if(isset($_POST['request'])) {
	foreach($_POST['login'] as $key=>$value)
		$_SESSION['tempData']['login'][$key] = $value;
	if($_POST['request'] == 'login') {
		if($_SESSION['tempData']['login']['username'] == '') {
			$status_msg = "Username cannot be blank";
		}
		else {
			if(!user_exists($_SESSION['tempData']['login']['username'])) {
				$status_msg = "Username does not exist";
			}
			else {
				if(!login_user($_SESSION['tempData']['login']['username'], $_SESSION['tempData']['login']['password'])) {
					$status_msg = "Login failed.  Check password and try again.";
				}
				else {
					// We have logged in, session variables set, redirect
					header("Refresh: 0; URL=index.php");
					die();
				}
			}
		}
	}
}


// Let's first put in required javascript
?>
<script type="text/javascript">       
<!--
// Remove frames if needed
if (self.location.href != top.location.href) {
top.location.href = self.location.href;
}
// -->
</script>
<?php
print_blank_header("#dddddd","0", $sys_config['name'] . " Login");
?>

<br />
<br />
<div align="center">
	<?php
	print_window_header($sys_config['name'] . " Login", "400", "center");
	?>
	<br />
	<br />
	<?php
	if($fruity->ErrorMsgSet()) {
		?>
		<div align="center" class="statusmsg"><?=$fruity->ErrorMsg(1);?></div><br />
		<?php
	}
	?>
		<form name="loginForm" action="<?=AUTH_MODULES_URL_PATH;?>dbauth/login.php" method="post">
		<input type="hidden" name="__dbauth_request" value="login" />
		<table width="90%" align="center" border="0">
		<tr>
			<b>Username:</b> <input type="text" name="__dbauth_login[username]" maxlength="255" size="12"><br />
			Enter your username to the Fruity system.  This value is not case sensitive.
			<br />
			<br />
			<b>Password:</b> <input type="password" name="__dbauth_login[password]" maxlength="255" size="12"><br />
			Enter your password to the Fruity system.  This value is case sensitive.
		</tr>
		<tr>
			<td><input type="submit" value="Login" /></td>
		</tr>
		</table>
		</form>
		<br />
		<?php
	print_window_footer();
	?>
</div>
<br />
<br />
<?php
print_blank_footer();
?>