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
 * @version		(Release 0) DEVELOPER BETA 5
 * @link		http://www.bgpanel.net/
 */



$title = 'Cron Settings';
$page = 'configcron';
$tab = 5;
$return = 'configcron.php';


require("../configuration.php");
require("./include.php");


include("./bootstrap/header.php");


?>
			<div class="alert alert-info">
				<h4 class="alert-heading">Tip :</h4>
				To enable server monitoring, set up the cron job to run every <?php echo (CRONDELAY / 60); ?> minutes.<br />
				More information at: <a target="_blank" href="http://wiki.bgpanel.net/doku.php?id=wiki:setting_up_cron_job"><b><u>Setting Up Cron Job</u></b></a>
			</div>
			<legend>Create the following Cron Job using PHP:</legend>
			<div>
				<pre style="text-align: center;"><?php echo '*/'.(CRONDELAY / 60).' * * * * php -q '.substr(@$_SERVER['SCRIPT_FILENAME'], 0, @strrpos(@$_SERVER['SCRIPT_FILENAME'], "/")).'/cron.php > /dev/null 2>&1'; ?></pre>
			</div>
<?php


include("./bootstrap/footer.php");
?>