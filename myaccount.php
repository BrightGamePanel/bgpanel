<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * LICENSE:
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @categories	Games/Entertainment, Systems Administration
 * @package		Bright Game Panel
 * @author		warhawk3407 <warhawk3407@gmail.com> @NOSPAM
 * @copyleft	2013
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */



$page = 'myaccount';
$tab = 9;
$isSummary = TRUE;
$return = 'myaccount.php';


require("configuration.php");
require("include.php");


$title = T_('My Account');


$rows = query_fetch_assoc( "SELECT * FROM `".DBPREFIX."client` WHERE `clientid` = '".$_SESSION['clientid']."' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#"><?php echo T_('Profile'); ?></a></li>
			</ul>
			<div class="well">
				<form method="post" action="process.php">
					<input type="hidden" name="task" value="myaccount" />
					<input type="hidden" name="clientid" value="<?php echo $_SESSION['clientid']; ?>" />
					<label><?php echo T_('First Name'); ?></label>
						<input type="text" name="firstname" class="span4" value="<?php echo htmlspecialchars($rows['firstname'], ENT_QUOTES); ?>">
					<label><?php echo T_('Last Name'); ?></label>
						<input type="text" name="lastname" class="span4" value="<?php echo htmlspecialchars($rows['lastname'], ENT_QUOTES); ?>">
					<label><?php echo T_('Email'); ?></label>
						<input type="text" name="email" class="span3" value="<?php echo htmlspecialchars($rows['email'], ENT_QUOTES); ?>">
					<label><?php echo T_('Username'); ?></label>
						<input type="text" name="username" class="span4" value="<?php echo htmlspecialchars($rows['username'], ENT_QUOTES); ?>">
					<label><?php echo T_('Password'); ?></label>
						<input type="password" name="password" class="span3" placeholder="">
					<label><?php echo T_('Password'); ?></label>
						<input type="password" name="password2" class="span3" placeholder="">
					<label><?php echo T_('Language'); ?></label>
						<select class="span2" name="language">
<?php
//---------------------------------------------------------+
foreach ($global_languages as $key => $value)
{
	if ($value == htmlspecialchars($rows['lang'], ENT_QUOTES))
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\" selected=\"selected\">".$key."</option>\r\n";
		echo $output;
	}
	else
	{
		$output = "\t\t\t\t\t\t\t<option value=\"".$value."\">".$key."</option>\r\n";
		echo $output;
	}
}
//---------------------------------------------------------+
?>						</select>
					<div style="text-align: center;">
						<ul class="pager">
							<li>
								<button type="submit" class="btn btn-primary"><?php echo T_('Save Changes'); ?></button>
							</li>
							<li>
								<button type="reset" class="btn"><?php echo T_('Cancel Changes'); ?></button>
							</li>
						</ul>
					</div>
				</form>
			</div>
<?php


include("./bootstrap/footer.php");
?>