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



if (!is_file('../configuration.php'))
{
	exit('<html><body><h1>Configuration file not found !</h1></body></html>');
}
else
{
	require('../configuration.php');
}

//---------------------------------------------------------+

/**
 * Install Wizard Version
 */
define('WIZARDVERSION', 'v1.7.0');

/**
 * BGP VERSION LIST
 */
require('./inc/versions.php');

/**
 * SQL FUNCTIONS
 */
require('./inc/mysql.php');

//---------------------------------------------------------+

if (isset($_POST['task']))
{
	$task = $_POST['task'];
}

switch (@$task)
{
	case 'license':
		if ( isset($_POST['license']) )
		{
			if ($_POST['license'] == 'on')
			{
				header( "Location: index.php?step=one" );
				die();
			}
		}
		exit( "You must accept the terms of the license agreement." );
		break;

	default:
		break;
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Install and Update Script - BrightGamePanel</title>
		<!--Powered By Bright Game Panel-->
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- JS -->
			<script src="./bootstrap/js/jquery.js"></script>
			<script src="./bootstrap/js/bootstrap.js"></script>
		<!-- Style -->
			<link href="./bootstrap/css/bootstrap.css" rel="stylesheet">
			<style type="text/css">
			body {
				padding-top: 60px;
				padding-bottom: 40px;
			}
			</style>
			<link href="./bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
			<!--[if lt IE 9]>
			  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
			<![endif]-->
		<!-- Favicon -->
			<link rel="shortcut icon" href="./bootstrap/img/favicon.ico">
	</head>

	<body>
			<div class="navbar navbar-fixed-top">
				<div class="navbar-inner">
					<div class="container-fluid">
						<a class="brand" href="#">Bright Game Panel</a>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="page-header">
					<h1>Install and Update Script&nbsp;<small>Bright Game Panel <?php echo LASTBGPVERSION; ?></small></h1>
				</div>
				<ul class="breadcrumb">
<?php

//---------------------------------------------------------+

if (!isset($_GET['step'])) // Step == 'zero'
{
?>
					<li class="active">License</li>
<?php
}
else if ($_GET['step'] == 'one')
{
?>
					<li>
						<a href="index.php">License</a> <span class="divider">/</span>
					</li>
					<li class="active">Check Requirements</li>
<?php
}
else if ($_GET['step'] == 'two')
{
?>
					<li>
						<a href="index.php">License</a> <span class="divider">/</span>
					</li>
					<li>
						<a href="index.php?step=one">Check Requirements</a> <span class="divider">/</span>
					</li>
					<li class="active">Select Database Update</li>
<?php
}
else if ($_GET['step'] == 'three')
{
?>
					<li>
						<a href="index.php">License</a> <span class="divider">/</span>
					</li>
					<li>
						<a href="index.php?step=one">Check Requirements</a> <span class="divider">/</span>
					</li>
					<li>
						<a href="index.php?step=two">Select Database Update</a> <span class="divider">/</span>
					</li>
					<li class="active">Install Database</li>
<?php
}

//---------------------------------------------------------+

?>
				</ul>
<?php



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+



if (!isset($_GET['step'])) // Step == 'zero'
{
?>
				<div class="well">
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
				<form method="post" action="index.php">
					<input type="hidden" name="task" value="license" />
					<label class="checkbox">
						<input type="checkbox" name="license">&nbsp;I Accept the Terms of the License Agreement
					</label>
					<div style="text-align: center; margin-top: 19px;">
						<button type="submit" class="btn">Submit</button>
					</div>
				</form>
				<div class="modal fade" id="welcome">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>BrightGamePanel Install and Update Script</h3>
					</div>
					<div class="modal-body">
						<p class="lead">
							Welcome to BrightGamePanel,<br />
							a easy to use and powerful game control panel.
						</p>
						<br /><br />
						<small>Click on the button below to start the installation process.</small>
					</div>
					<div class="modal-footer">
						<a class="btn btn-primary" data-dismiss="modal" href="#">Go !</a>
					</div>
				</div>
				<script type="text/javascript">
				$(document).ready(function() {
					$('#welcome').modal('show')
				});
				</script>
<?php
}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+



else if ($_GET['step'] == 'one')
{
?>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Action</th>
							<th>Status</th>
							<th>Note</th>
						</tr>
					</thead>
					<tbody>
						<tr class="success">
							<td>Checking for CONFIGURATION file</td>
							<td><span class="label label-success">FOUND</span></td>
							<td></td>
						</tr>
<?php

	$versioncompare = version_compare(PHP_VERSION, '5.3.4');
	if ($versioncompare == -1)
	{
?>
						<tr class="error">
							<td>Checking your version of PHP</td>
							<td><span class="label label-important">FAILED (<?php echo PHP_VERSION; ?>)</span></td>
							<td>Upgrade to PHP 5.3.4 or greater</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking your version of PHP</td>
							<td><span class="label label-success"><?php echo PHP_VERSION; ?></span></td>
							<td></td>
						</tr>
<?php
	}
	unset($versioncompare);

?>
<?php

	if (ini_get('safe_mode'))
	{
?>
						<tr class="error">
							<td>Checking for PHP safe mode</td>
							<td><span class="label label-important">ON</span></td>
							<td>Please, disable safe mode !!!</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for PHP safe mode</td>
							<td><span class="label label-success">OFF</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('mysql'))
	{
?>
						<tr class="error">
							<td>Checking for MySQL extension</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>MySQL extension could not be found or is not installed. Please recompile your Apache with the MySQL extension included.</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for MySQL extension</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php

		$mysql_link = @mysql_connect(DBHOST,DBUSER,DBPASSWORD);
		if ($mysql_link == FALSE)
		{
?>
						<tr class="error">
							<td>Checking for MySQL server connection</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>Could not connect to MySQL: "<?php echo mysql_error(); ?>"</td>
						</tr>
<?php
			$error = TRUE;
		}
		else
		{
?>
						<tr class="success">
							<td>Checking for MySQL server connection</td>
							<td><span class="label label-success">SUCCESSFUL</span></td>
							<td></td>
						</tr>
<?php

			$mysql_database_link = @mysql_select_db(DBNAME);
			if ($mysql_database_link == FALSE)
			{
?>
						<tr class="error">
							<td>Checking for MySQL database</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>Could not connect to MySQL database: "<?php echo mysql_error(); ?>"</td>
						</tr>
<?php
				$error = TRUE;
			}
			else
			{
?>
						<tr class="success">
							<td>Checking for MySQL database</td>
							<td><span class="label label-success">SUCCESSFUL</span></td>
							<td></td>
						</tr>
<?php
			}
			mysql_close($mysql_link);
		}
	}

?>
<?php

	if (!function_exists('fsockopen'))
	{
?>
						<tr class="error">
							<td>Checking for FSOCKOPEN function</td>
							<td><span class="label label-important">FAILED</span></td>
							<td></td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for FSOCKOPEN function</td>
							<td><span class="label label-success">SUCCESSFUL</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!function_exists('mail'))
	{
?>
						<tr class="error">
							<td>Checking for MAIL function</td>
							<td><span class="label label-important">FAILED</span></td>
							<td></td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for MAIL function</td>
							<td><span class="label label-success">SUCCESSFUL</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('curl'))
	{
?>
						<tr class="error">
							<td>Checking for Curl extension</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>Curl extension is not installed. (<a href="http://php.net/curl">Curl</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for Curl extension</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('mbstring'))
	{
?>
						<tr class="error">
							<td>Checking for MBSTRING extension (LGSL - Used to show UTF-8 server and player names correctly)</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>mbstring extension is not installed. (<a href="http://php.net/mbstring">mbstring</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for MBSTRING extension (LGSL - Used to show UTF-8 server and player names correctly)</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('bz2'))
	{
?>
						<tr class="error">
							<td>Checking for BZIP2 extension (LGSL - Used to show Source server settings over a certain size)</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>BZIP2 extension is not installed. (<a href="http://php.net/bzip2">BZIP2</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for BZIP2 extension (LGSL - Used to show Source server settings over a certain size)</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('zlib'))
	{
?>
						<tr class="error">
							<td>Checking for ZLIB extension (LGSL - Required for America's Army 3)</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>ZLIB extension is not installed. (<a href="http://php.net/zlib">ZLIB</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for ZLIB extension (LGSL - Required for America's Army 3)</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('gd') && !extension_loaded('gd2'))
	{
?>
						<tr class="error">
							<td>Checking for GD extension (pChart Requirement)</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>GD / GD2 extensions are not installed. (<a href="http://php.net/book.image.php">GD</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for GD extension (pChart Requirement)</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!function_exists('imagettftext'))
	{
?>
						<tr class="error">
							<td>Checking for FreeType extension (securimage Requirement)</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>FreeType extension is not installed. (<a href="http://php.net/manual/en/image.installation.php">FreeType</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for FreeType extension (securimage Requirement)</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	if (!extension_loaded('simplexml'))
	{
?>
						<tr class="error">
							<td>Checking for SimpleXML extension</td>
							<td><span class="label label-important">FAILED</span></td>
							<td>SimpleXML extension is not installed. (<a href="http://php.net/simplexml">SimpleXML</a>).</td>
						</tr>
<?php
		$error = TRUE;
	}
	else
	{
?>
						<tr class="success">
							<td>Checking for SimpleXML extension</td>
							<td><span class="label label-success">INSTALLED</span></td>
							<td></td>
						</tr>
<?php
	}

?>
<?php

	$passphrase = file_get_contents("../.ssh/passphrase");
	if (preg_match('#isEmpty = TRUE;#', $passphrase))
	{
		if (is_writable("../.ssh/passphrase"))
		{
?>
						<tr class="success">
							<td>Checking for PASSPHRASE file CHMOD 0777 (.ssh/passphrase)</td>
							<td><span class="label label-success">OK</span></td>
							<td></td>
						</tr>
<?php
		}
		else
		{
?>
						<tr class="error">
							<td>Checking for PASSPHRASE file CHMOD 0777 (.ssh/passphrase)</td>
							<td><span class="label label-success">FAILED</span></td>
							<td></td>
						</tr>
<?php
			$error = TRUE;
		}
	}
	unset($passphrase);

?>
					</tbody>
				</table>
<?php

	if (isset($error))
	{
?>
				<div style="text-align: center;">
					<h3><b>Fatal Error(s) Found.</b></h3><br />
					<button class="btn" onclick="window.location.reload();">Check Again</button>
				</div>
<?php
	}
	else
	{
?>
				<div style="text-align: center;">
					<ul class="pager">
						<li>
							<a href="index.php?step=two">Next Step &rarr;</a>
						</li>
					</ul>
				</div>
<?php
	}

}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+



else if ($_GET['step'] == 'two')
{
?>
				<div class="well">
				<h2>Checking for existing databases . . . . .</h2>
<?php

	$mysql_link = @mysql_connect(DBHOST,DBUSER,DBPASSWORD);
	if (!$mysql_link)
	{
		exit('Could not connect to MySQL: '.mysql_error());
	}
	else
	{
		$mysql_database_link = mysql_select_db(DBNAME);
		if ($mysql_database_link == FALSE)
		{
			exit('Could not connect to MySQL database: '.mysql_error());
		}
		else
		{
			$tables = mysql_query('SHOW TABLES');
			$rowsTables = mysql_fetch_array($tables);

			if (!empty($rowsTables))
			{
				while ($rowsTables = mysql_fetch_array($tables))
				{
					if ($rowsTables[0] == DBPREFIX.'config')
					{
						$currentVersion = mysql_fetch_assoc(mysql_query( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" ));
					}
				}
			}

			mysql_close($mysql_link);
		}
	}

	if (isset($currentVersion))
	{
?>
				<div class="alert alert-block">
					<strong>FOUND !</strong> Tables exist in the database.<br />
					You can update your previous version of BrightGamePanel or perform a clean install <u>which will overwrite all data (BGP tables with the same prefix) in the database.</u><br />
					It is recommend you back up your database first.<br />
				</div>
				<h4>Current Version:</h4>&nbsp;<span class="label label-info"><?php echo $currentVersion['value']; ?></span><br /><br />
				<h4>Select Action :</h4><br />
				<form action="index.php" method="get">
					<input type="hidden" name="step" value="three" />
					<input name="version" type="radio" value="update" checked="checked" /><b>&nbsp;Update to the Last Version (<?php echo LASTBGPVERSION; ?>)</b><br /><br /><br />
					<input name="version" type="radio" value="full" /><b>&nbsp;<span class="label label-warning">Perform Clean Install</span>&nbsp;- Version <?php echo LASTBGPVERSION; ?></b><br /><br />
					<button type="submit" class="btn btn-primary">Install MySQL Database</button>
				</form>
				</div>
<?php
	}
	else
	{
?>
				<span class="label label-success">No tables found in the database</span><br /><br />
				<form action="index.php" method="get">
					<input type="hidden" name="step" value="three" />
					<input name="version" type="radio" value="full" checked="checked" /><b>&nbsp;Install BGP Version <?php echo LASTBGPVERSION; ?></b><br /><br />
					<button type="submit" class="btn btn-primary">Install MySQL Database</button>
				</form>
				</div>
<?php
	}

?>
				<div style="text-align: center;">
					<ul class="pager">
						<li>
							<a href="index.php?step=one">&larr; Previous Step</a>
						</li>
					</ul>
				</div>
<?php
}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+



else if ($_GET['step'] == 'three')
{

	switch (@$_GET['version'])
	{
		case 'full':

			//---------------------------------------------------------+

			$crypt_key = hash('sha512', md5(str_shuffle(time())));

			if (is_writable("../.ssh/passphrase"))
			{
				$handle = fopen('../.ssh/passphrase', 'w');
				fwrite($handle, $crypt_key);
				fclose($handle);
			}

			//---------------------------------------------------------+

			require("./sql/full.php");

			break;

		case 'update':

			$mysql_link = mysql_connect(DBHOST,DBUSER,DBPASSWORD);
			if (!$mysql_link)
			{
				exit(mysql_error());
			}
			else
			{
				$mysql_database_link = mysql_select_db(DBNAME);
				if ($mysql_database_link == FALSE)
				{
					echo "Could not connect to MySQL database";
				}
				else
				{
					$currentVersion = mysql_fetch_assoc(mysql_query( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" ));
					mysql_close($mysql_link);
				}
			}

			//---------------------------------------------------------+

			foreach($bgpVersions as $key => $value)
			{
				if ($value == $currentVersion['value']) // Version reference for the update
				{
					if ($key == end($bgpVersions))
					{
						break; // Already up-to-date
					}
					else
					{
						$i = $key; // Starting point for the update

						for ($i; $i < key($bgpVersions); $i++) // Loop in order to reach the last version
						{
							// Apply the update
							$sqlFile = './sql/';
							$sqlFile .= 'update_'.str_replace('.', '', $bgpVersions[$i]).'_to_'.str_replace('.', '', $bgpVersions[$i + 1]).'.php';

							require($sqlFile);
						}

						break; // Update finished
					}
				}
			}

			//---------------------------------------------------------+

			$mysql_link = mysql_connect(DBHOST,DBUSER,DBPASSWORD);
			if (!$mysql_link)
			{
				exit(mysql_error());
			}
			else
			{
				$mysql_database_link = mysql_select_db(DBNAME);
				if ($mysql_database_link == FALSE)
				{
					echo "Could not connect to MySQL database";
				}
				else
				{
					$currentVersion = mysql_fetch_assoc(mysql_query( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" ));
					mysql_close($mysql_link);
				}
			}

			if ($currentVersion['value'] != LASTBGPVERSION)
			{
				exit( "Update Error." );
			}

			//---------------------------------------------------------+

			break;

		default:
			exit('<h1><b>Error</b></h1>');
	}

	//---------------------------------------------------------+

?>
				<div class="well">
				<div class="alert alert-block">
					<strong>DELETE THE INSTALL FOLDER</strong><br />
					<?php echo getcwd(); ?>

				</div>
<?php
	if (@$_GET['version'] == 'full') // Full install case
	{
?>
				<h2>Install Complete!</h2>
				<legend>Login Information :</legend>
				Admin Username: <b>admin</b><br />
				Admin Password: <b>password</b><br />
				<hr>
				<i class="icon-share-alt"></i>&nbsp;<a href="../admin">@Admin Login</a>
				<hr>
				<div class="alert alert-error">
					<strong>Wait!</strong>
					Remember to change the admin username and password. If you have done a full installation, you have also to set PASSPHRASE CHMOD back to 0644 !
				</div>
<?php
	}
	else // Update Case
	{
?>
				<h2>Your system is now up-to-date.</h2>
				<legend>Changelog:</legend>
				<div style="width:auto;height:480px;overflow:scroll;overflow-y:scroll;overflow-x:hidden;">
<?php
		$log = fopen('CHANGELOG.txt', 'r');

		while ($rows = fgets($log))
		{
			echo $rows.'<br />';
		}

		fclose($log);
?>
				</div>
				<hr>
				<i class="icon-share-alt"></i>&nbsp;<a href="../admin">@Admin Login</a>
<?php
	}
?>
				<hr>
				<h1>Thanks for using Bright Game Panel :-)</h1>
				</div>
<?php
}



//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+



?>
				<hr>
				<footer>
					<div class="pull-left">
						Copyleft - 2012. Released Under <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPLv3</a>.<br />
						All Images Are Copyrighted By Their Respective Owners.
					</div>
					<div class="pull-right" style="text-align: right;">
						<a href="http://www.bgpanel.net/" target="_blank">Bright Game Panel</a><br />
						Install Script: <?php echo WIZARDVERSION; ?> - BGP: <?php echo LASTBGPVERSION; ?><br />
						Built with <a href="http://twitter.github.com/bootstrap/index.html" target="_blank">Bootstrap</a>.
					</div>
				</footer>
			</div><!--/container-->

			<!--Powered By Bright Game Panel-->

	</body>
</html>
