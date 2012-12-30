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



$return = TRUE;


require("../configuration.php");
require("./include.php");


if (isset($_POST['task']))
{
	$task = mysql_real_escape_string($_POST['task']);
}
else if (isset($_GET['task']))
{
	$task = mysql_real_escape_string($_GET['task']);
}


/*
 * "SingleMode" is specific to "boxchart.php"
 * ( Only one box is parsed )
 *
 * Note: CONST(SINGLEBOXMODE) = boxid
 */

if (isset($_GET['singlemode']))
{
	if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".intval(mysql_real_escape_string($_GET['singlemode']))."'" ) == 0)
	{
		exit('Error: BoxID is invalid.');
	}
	else
	{
		define('SINGLEBOXMODE', intval(mysql_real_escape_string($_GET['singlemode'])));
	}
}


switch (@$task)
{

	// All boxes are parsed
	case 'box.day.players.multiple':

			/* Bright Game Panel inclusions */
			include("./pchart/box.day.process.php"); // Database information retrieval and useful vars computation
			include("./pchart/box.day.players.multiple.php"); // Process

		break;

	//------------------------------------------------------------------------------------------------------------+

	/*
	 * "SingleMode"
	 * ( Only one box is parsed )
	 */

	case 'box.day.players.single':

			include("./pchart/box.day.process.php");
			include("./pchart/box.day.players.single.php");

		break;

	//------------------------------------------------------------------------------------------------------------+

	/*
	 * CPU / RAM / LOADAVG
	 * If CONST(SINGLEBOXMODE) is defined, parse only one box
	 * Else, parse all boxes
	 */

	case 'box.day.cpu':

			include("./pchart/box.day.process.php");
			define('CHARTTYPE', 'cpu'); // Chart type
			include("./pchart/box.day.top.both.php");

		break;

	case 'box.day.ram':

			include("./pchart/box.day.process.php");
			define('CHARTTYPE', 'ram');
			include("./pchart/box.day.top.both.php");

		break;

	case 'box.day.loadavg':

			include("./pchart/box.day.process.php");
			define('CHARTTYPE', 'loadavg');
			include("./pchart/box.day.top.both.php");

		break;

	//------------------------------------------------------------------------------------------------------------+

	// All boxes are parsed
	case 'box.week.players.multiple':

			include("./pchart/box.week.process.php");
			include("./pchart/box.week.players.multiple.php");

		break;

	//------------------------------------------------------------------------------------------------------------+

	/*
	 * "SingleMode"
	 * ( Only one box is parsed )
	 */

	case 'box.week.players.single':

			include("./pchart/box.week.process.php");
			include("./pchart/box.week.players.single.php");

		break;

	//------------------------------------------------------------------------------------------------------------+

	/*
	 * CPU / RAM / LOADAVG
	 * If CONST(SINGLEBOXMODE) is defined, parse only one box
	 * Else, parse all boxes
	 */

	case 'box.week.cpu':

			include("./pchart/box.week.process.php");
			define('CHARTTYPE', 'cpu');
			include("./pchart/box.week.top.both.php");

		break;

	case 'box.week.ram':

			include("./pchart/box.week.process.php");
			define('CHARTTYPE', 'ram');
			include("./pchart/box.week.top.both.php");

		break;

	case 'box.week.loadavg':

			include("./pchart/box.week.process.php");
			define('CHARTTYPE', 'loadavg');
			include("./pchart/box.week.top.both.php");

		break;

	//------------------------------------------------------------------------------------------------------------+

	default:
		//Error Case
		$img = imagecreatefrompng('../bootstrap/img/nodata.png');

		header('Content-Type: image/png');

		imagepng($img);
		imagedestroy($img);
		die();
}

?>