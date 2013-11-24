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



$page = 'boxchart';
$tab = 3;
$isSummary = TRUE;

if ( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	exit('Error: BoxID error.');
}

$boxid = $_GET['id'];
$return = 'boxchart.php?id='.urlencode($boxid);


require("../configuration.php");
require("./include.php");


$title = T_('Box Charts');

$boxid = mysql_real_escape_string($_GET['id']);


if (query_numrows( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."'" ) == 0)
{
	exit('Error: BoxID is invalid.');
}


$rows = query_fetch_assoc( "SELECT `name` FROM `".DBPREFIX."box` WHERE `boxid` = '".$boxid."' LIMIT 1" );


include("./bootstrap/header.php");


/**
 * Notifications
 */
include("./bootstrap/notifications.php");


?>
			<ul class="nav nav-tabs">
				<li><a href="boxsummary.php?id=<?php echo $boxid; ?>"><?php echo T_('Summary'); ?></a></li>
				<li><a href="boxprofile.php?id=<?php echo $boxid; ?>"><?php echo T_('Profile'); ?></a></li>
				<li><a href="boxip.php?id=<?php echo $boxid; ?>"><?php echo T_('IP Addresses'); ?></a></li>
				<li><a href="boxserver.php?id=<?php echo $boxid; ?>"><?php echo T_('Servers'); ?></a></li>
				<li class="active"><a href="boxchart.php?id=<?php echo $boxid; ?>"><?php echo T_('Charts'); ?></a></li>
				<li><a href="boxgamefile.php?id=<?php echo $boxid; ?>"><?php echo T_('Game File Repositories'); ?></a></li>
				<li><a href="#" onclick="ajxp()"><?php echo T_('WebFTP'); ?></a></li>
				<li><a href="boxlog.php?id=<?php echo $boxid; ?>"><?php echo T_('Activity Logs'); ?></a></li>
			</ul>
			<div id="charts">
<?php

if (query_numrows( "SELECT `timestamp`, `cache` FROM `".DBPREFIX."boxData` WHERE `timestamp` >= '".(time() - (60 * 60 * 24 * 7 * 4 + CRONDELAY))."'" ) != 0)
{
?>
				<script>
				// Chart Containers
				var players;
				var top;
				var bw_usage;
				var bw_consumption;

				$(document).ready(function() {
					//------------------------------------------------------------------------------------------------------------+
					/**
					 * PLAYERS
					 */
					//------------------------------------------------------------------------------------------------------------+
					$(function() {
						$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=boxplayers&boxid=<?php echo $boxid; ?>', function(data) {
							players = new Highcharts.StockChart({
								chart : {
									renderTo : 'players'
								},

								title : {
									text : 'Players'
								},

								xAxis: {
									gapGridLineWidth: 0
								},

								rangeSelector : {
									buttons : [{
										type : 'day',
										count : 1,
										text : '1D'
									}, {
										type : 'week',
										count : 1,
										text : '1W'
									}, {
										type : 'month',
										count : 1,
										text : '1M'
									}, {
										type : 'all',
										count : 1,
										text : 'All'
									}],
									selected : 0,
									inputEnabled : false
								},

								series : [{
									name : 'Players',
									type : 'area',
									data : data,
									threshold : null,
									gapSize: 5,
									tooltip : {
										valueDecimals : 2
									},
									fillColor : {
										linearGradient : {
											x1: 0,
											y1: 0,
											x2: 0,
											y2: 1
										},
										stops : [[0, Highcharts.getOptions().colors[0]], [1, 'rgba(254,254,254,254)']]
									}
								}]
							});
						});
					});

					//------------------------------------------------------------------------------------------------------------+
					/**
					 * TOP
					 */
					//------------------------------------------------------------------------------------------------------------+
					$(function() {
						var seriesOptions = [],
							yAxisOptions = [],
							seriesCounter = 0,
							names = ['CPU', 'RAM', 'LoadAVG'],
							colors = ['#2f7ed8', '#910000', '#8bbc21'];

						$.each(names, function(i, name) {
							$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=box'+ name.toLowerCase() +'&boxid=<?php echo $boxid; ?>', function(data) {

								seriesOptions[i] = {
									name: name,
									data: data,
									color: colors[i]
								};

								// As we're loading the data asynchronously, we don't know what order it will arrive. So
								// we keep a counter and create the chart when all the data is loaded.
								seriesCounter++;

								if (seriesCounter == names.length) {
									// create the chart when all data is loaded
									top = new Highcharts.StockChart({
										chart: {
											renderTo: 'top'
										},

										title : {
											text : 'System Monitor'
										},

										rangeSelector: {
											buttons : [{
												type : 'day',
												count : 1,
												text : '1D'
											}, {
												type : 'week',
												count : 1,
												text : '1W'
											}, {
												type : 'month',
												count : 1,
												text : '1M'
											}, {
												type : 'all',
												count : 1,
												text : 'All'
											}],
											selected : 0,
											inputEnabled : true
										},

										navigator: {
											enabled : false
										},

										yAxis: {
											labels: {
												formatter: function() {
													return this.value + '%';
												}
											},
											plotLines: [{
												value: 0,
												width: 2,
												color: 'silver'
											}]
										},

										tooltip: {
											pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> %<br/>',
											valueDecimals: 2
										},

										series: seriesOptions
									});
								}
							});
						});
					});

					//------------------------------------------------------------------------------------------------------------+
					/**
					 * BANDWIDTH USAGE
					 */
					//------------------------------------------------------------------------------------------------------------+
					$(function() {
						var seriesOptions = [],
							yAxisOptions = [],
							seriesCounter = 0,
							names = ['RX Usage', 'TX Usage'],
							colors = ['#2f7ed8', '#910000', '#8bbc21'];

						$.each(names, function(i, name) {
							$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=boxbw'+ name.toLowerCase() +'&boxid=<?php echo $boxid; ?>', function(data) {

								seriesOptions[i] = {
									name: name,
									data: data,
									color: colors[i]
								};

								seriesCounter++;

								if (seriesCounter == names.length) {
									bw_usage = new Highcharts.StockChart({
										chart: {
											renderTo: 'bw_usage'
										},

										title : {
											text : 'Bandwidth Statistics'
										},

										rangeSelector: {
											buttons : [{
												type : 'day',
												count : 1,
												text : '1D'
											}, {
												type : 'week',
												count : 1,
												text : '1W'
											}, {
												type : 'month',
												count : 1,
												text : '1M'
											}, {
												type : 'all',
												count : 1,
												text : 'All'
											}],
											selected : 0,
											inputEnabled : true
										},

										yAxis: {
											labels: {
												formatter: function() {
													return this.value + 'MB/s';
												}
											},
											plotLines: [{
												value: 0,
												width: 2,
												color: 'silver'
											}]
										},

										tooltip: {
											pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> MB/s<br/>',
											valueDecimals: 2
										},

										series: seriesOptions
									});
								}
							});
						});
					});

					//------------------------------------------------------------------------------------------------------------+
					/**
					 * BANDWIDTH CONSUMPTION
					 */
					//------------------------------------------------------------------------------------------------------------+
					$(function() {
						var seriesOptions = [],
							yAxisOptions = [],
							seriesCounter = 0,
							names = ['RX Consumption', 'TX Consumption'],
							colors = ['#2f7ed8', '#910000', '#8bbc21'];

						$.each(names, function(i, name) {
							$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=boxbw'+ name.toLowerCase() +'&boxid=<?php echo $boxid; ?>', function(data) {

								seriesOptions[i] = {
									type: 'column',
									name: name,
									data: data,
									color: colors[i],
									dataGrouping: {
										approximation: 'sum',
										units: [[
											'minute',
											[1, 2, 5, 10, 15, 30]
										], [
											'hour',
											[1, 2, 3, 4, 6, 8, 12]
										], [
											'day',
											[1]
										], [
											'week',
											[1]
										], [
											'month',
											[1]
										]]
									}
								};

								seriesCounter++;

								if (seriesCounter == names.length) {
									bw_consumption = new Highcharts.StockChart({
										chart: {
											renderTo: 'bw_consumption',
											alignTicks: false
										},

										title : {
											text : 'Bandwidth Consumption'
										},

										rangeSelector: {
											buttons : [{
												type : 'day',
												count : 1,
												text : '1D'
											}, {
												type : 'week',
												count : 1,
												text : '1W'
											}, {
												type : 'month',
												count : 1,
												text : '1M'
											}, {
												type : 'all',
												count : 1,
												text : 'All'
											}],
											selected : 0,
											inputEnabled : true
										},

										yAxis: {
											labels: {
												formatter: function() {
													return this.value + 'GB';
												}
											}
										},

										tooltip: {
											pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> GB<br/>',
											valueDecimals: 2
										},

										series: seriesOptions
									});
								}
							});
						});
					});

					//------------------------------------------------------------------------------------------------------------+
					/**
					 * TOTAL BANDWIDTH CONSUMPTION
					 */
					//------------------------------------------------------------------------------------------------------------+
					$(function() {
						var seriesOptions = [],
							yAxisOptions = [],
							seriesCounter = 0,
							names = ['Total RX Consumption', 'Total TX Consumption'],
							colors = ['#2f7ed8', '#910000', '#8bbc21'];

						$.each(names, function(i, name) {
							$.getJSON('api.boxdata.json.php?api_key=<?php echo API_KEY; ?>&task=boxbw'+ name.toLowerCase() +'&boxid=<?php echo $boxid; ?>', function(data) {

								seriesOptions[i] = {
									name: name,
									data: data,
									color: colors[i],
									dataGrouping: {
										approximation: 'sum',
										units: [[
											'minute',
											[1, 2, 5, 10, 15, 30]
										], [
											'hour',
											[1, 2, 3, 4, 6, 8, 12]
										], [
											'day',
											[1]
										], [
											'week',
											[1]
										], [
											'month',
											[1]
										]]
									}
								};

								seriesCounter++;

								if (seriesCounter == names.length) {
									bw_consumption_total = new Highcharts.StockChart({
										chart: {
											renderTo: 'bw_consumption_total'
										},

										title : {
											text : 'Total Bandwidth Consumption'
										},

										rangeSelector: {
											buttons : [{
												type : 'day',
												count : 1,
												text : '1D'
											}, {
												type : 'week',
												count : 1,
												text : '1W'
											}, {
												type : 'month',
												count : 1,
												text : '1M'
											}, {
												type : 'all',
												count : 1,
												text : 'All'
											}],
											selected : 0,
											inputEnabled : true
										},

										yAxis: {
											labels: {
												formatter: function() {
													return this.value + 'GB';
												}
											}
										},

										tooltip: {
											pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> GB<br/>',
											valueDecimals: 2
										},

										series: seriesOptions
									});
								}
							});
						});
					});

				});
				</script>
				<script src="../bootstrap/js/highstock.js"></script>
				<script src="../bootstrap/js/modules/exporting.js"></script>

				<div id="players"></div>
				<hr>
				<div id="top"></div>
				<hr>
				<div id="bw_usage"></div>
				<hr>
				<div id="bw_consumption"></div>
				<hr>
				<div id="bw_consumption_total"></div>

<?php
}
else
{
?>
				<img class="nodata" data-original="../bootstrap/img/nodata.png" src="../bootstrap/img/wait.gif" style="display: inline; padding-left: 20px;">
				<script>
				// delayed rendering
				$(document).ready(function() {
					$("img.nodata").lazyload();
				});
				</script>
<?php
}

?>
			</div><!-- /charts -->
			<script>
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php


include("./bootstrap/footer.php");
?>