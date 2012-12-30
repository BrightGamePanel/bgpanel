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



$title = 'PHP Info';
$page = 'utilitiesphpinfo';
$tab = 4;
$return = 'utilitiesphpinfo.php';


require("../configuration.php");
require("./include.php");


include("./bootstrap/header.php");


?>
<div class="well">
	<div style="width:auto;height:480px;overflow:scroll;overflow-y:scroll;overflow-x:hidden;">
<?php

/**
 * php at SPAMMENOT dot tof2k dot com 10-Sep-2006 03:32
 * http://php.net/manual/fr/function.phpinfo.php
 * "obtain a phpinfo without headers (and css)"
 */

ob_start();
phpinfo();
$info = ob_get_contents();
ob_end_clean();
$info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);

echo "\r\n<!--PHP Info-->\r\n";
echo $info;
echo "\r\n<!--END : PHP Info-->\r\n";
?>
	</div>
</div>
<?php


include("./bootstrap/footer.php");
?>