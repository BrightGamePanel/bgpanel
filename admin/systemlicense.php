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



$page = 'systemlicense';
$tab = 4;
$return = 'systemlicense.php';


require("../configuration.php");
require("./include.php");


$title = T_('License Information');


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
<div class="well">
	<h3>Bright Game Panel Licensing (GNU General Public License)</h3>
	<div style="width:auto;height:480px;overflow:scroll;overflow-y:scroll;overflow-x:hidden;">
<?php
$license = fopen('../gpl-3.0.txt', 'r');

while ($rows = fgets($license))
{
	echo $rows.'<br />';
}

fclose($license);
?>
	</div>
</div>
<div class="well">
	<h3>HighSoft Non Commercial Licensing (CC BY-NC 3.0)</h3>
	<p>Bright Game Panel uses the Highcharts JS and Highstock JS libraries.</p>
	<br/>
	<p>Those libraries are developed by Highsoft. Highsoft is the owner of software products developed by Torstein Hønsi. Please, see <a href="http://highsoft.com/">http://highsoft.com/</a>.</p>
	<br/>
	<p>HighSoft software is licensed under the terms of the <a href="http://creativecommons.org/licenses/by-nc/3.0/">Creative Commons Attribution-NonCommercial 3.0 License</a>.</p>
	<br/>
	<p>
		You can use HighSoft software for free under the non-commercial license when you are:
		<ul>
			<li>A student, university or a public school</li>
			<li><a href="http://en.wikipedia.org/wiki/Non-profit_organization">A non-profit organisation</a></li>
			<li>Developing and testing applications using Highcharts/Highstock</li>
		</ul>
		Source editing is allowed.
	</p>
	<br/>
	<h6>HIGHSOFT SOFTWARE PRODUCT IS NOT FREE FOR COMMERCIAL USE.</h6>
	<br/>
	<p>More information at <a href="http://shop.highsoft.com/faq#non-commercial-redistribution">http://shop.highsoft.com/faq#non-commercial-redistribution</a>.</p>
</div>
<?php


include("./bootstrap/footer.php");
?>