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



/* Create the pData object */
$MyData = new pData();



/**
 * We draw the graph
 */

$data = mysql_query( "SELECT `timestamp`, `boxids`, `players` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 + CRONDELAY))."' ORDER BY `id` ASC" );
$n = 0;
while ($rowsData = mysql_fetch_assoc($data)) //For each data
{

	/**
	 * Synchronization
	 */

	if (mysql_num_rows($data) != $numPointsPerDay) //Is there a gap in the graph ?
	{
		for ($i = $numPointsPerDay - $n; $i > -1; $i--)
		{
			$time = $lastTimestamp - (CRONDELAY * $i);

			if (date('H:i', $rowsData['timestamp']) != date('H:i', $time))
			{
				$points[$n] = VOID; //Fill in the gap with VOID key word
				$n++;
			}
			else
			{
				//Sync case
				$numPlayers = 0;

				$playersTable = explode(';', $rowsData['players']);
				foreach($playersTable as $value)
				{
					$numPlayers += $value;
				}

				$points[$n] = $numPlayers;

				break;
			}
		}
	}
	else
	{
		//Regular sync case
		$numPlayers = 0;

		$playersTable = explode(';', $rowsData['players']);
		foreach($playersTable as $value)
		{
			$numPlayers += $value;
		}

		$points[$n] = $numPlayers;
	}

	//---------------------------------------------------------+

	$n++;
}
unset($data, $n);

$MyData->addPoints($points, 'Players');

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

$MyData->setAxisName(0,'Players');

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/**
 * We draw the labels
 */

$k = 0;

for ($i = $numPointsPerDay; $i > -1; $i--)
{
	$time = $lastTimestamp - (CRONDELAY * $i);

	$cronTime[$i] = ''; //Default Value

	if (($k == $numPointsPerHour) || ($i == $numPointsPerDay) || ($i == 0)) //Labels
	{
		$cronTime[$i] = date('H:i', $time);

		$k = 0;
	}

	++$k;
}
unset($k);

$MyData->addPoints($cronTime, "Labels");

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

$MyData->setSerieDescription("Labels","Time");
$MyData->setAbscissa("Labels");

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/* Create the pChart object */
$myPicture = new pImage(1130,366,$MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,1129,365,array("R"=>0,"G"=>0,"B"=>0));

/* Write the chart title */
$myPicture->setFontProperties(array("FontName"=>"../libs/pchart/fonts/Forgotte.ttf","FontSize"=>11));
$myPicture->drawText(150,35,"Players (Past 24H)",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>"../libs/pchart/fonts/pf_arma_five.ttf","FontSize"=>6));

/* Define the chart area */
$myPicture->setGraphArea(60,40,1100,326);

/* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"DrawSubTicks"=>TRUE);
$myPicture->drawScale($scaleSettings);

/* Write the chart legend */
$myPicture->drawLegend(512,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/* Draw label lines */

for ($i = 0; $i < $numPointsPerDay; $i++)
{
	if ($cronTime[$i] != '')
	{
		$myPicture->drawXThreshold($cronTime[$i], array("ValueIsLabel"=>TRUE, "Alpha"=>24,"Ticks"=>4));
	}
}

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Draw the line chart */
$myPicture->drawAreaChart();

/* Write the chart boundaries */
$BoundsSettings = array("MaxDisplayR"=>237,"MaxDisplayG"=>23,"MaxDisplayB"=>48,"MinDisplayR"=>23,"MinDisplayG"=>144,"MinDisplayB"=>237);
$myPicture->writeBounds(BOUND_BOTH,$BoundsSettings);

/* Draw a line and a plot chart on top */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
$myPicture->drawLineChart();

/* Render the picture (choose the best way) */
$myPicture->autoOutput("./pcache/chart.playersday.png");

?>