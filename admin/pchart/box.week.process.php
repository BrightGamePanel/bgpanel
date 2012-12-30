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



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}



/**
 * We have to find the last update timestamp
 */

$data = query_fetch_assoc( "SELECT `id`, `timestamp` FROM `".DBPREFIX."boxData` ORDER BY `id` DESC LIMIT 1" );
$lastTimestamp = $data['timestamp'];

/**
 * We have to find some values
 */

$numPointsPerHour = (3600 / CRONDELAY);
$numPointsPerDay = $numPointsPerHour * 24;
$numPointsPerWeek = $numPointsPerDay * 7;

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+
//We check if we can draw the graph

$numData = mysql_num_rows(mysql_query( "SELECT `timestamp` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 + CRONDELAY))."'" ));
if ($numData < $numPointsPerHour)
{
	//Error: No Data or Insufficient Data (week_process)
	$img = imagecreatefrompng('../bootstrap/img/nodata.png');

	header('Content-Type: image/png');

	imagepng($img);
	imagedestroy($img);
	die();
}

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/* pChart library inclusions */
include("../libs/pchart/class/pData.class.php");
include("../libs/pchart/class/pDraw.class.php");
include("../libs/pchart/class/pImage.class.php");

?>