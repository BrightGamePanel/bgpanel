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


?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php
		if (empty ($title))
		{
			echo htmlspecialchars(SITENAME, ENT_QUOTES);
		}
		else
		{
			echo $title.' - '.htmlspecialchars(SITENAME, ENT_QUOTES);
		}
		?></title>
		<!--Powered By Bright Game Panel-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Javascript -->
			<script src="./bootstrap/js/jquery.js"></script>
			<script src="./bootstrap/js/jquery.tablesorter.min.js"></script>
<?php
if (($page == 'scriptconsole') || ($page == 'utilitiesrcontool'))
{
	echo "\t\t\t<script src=\"./bootstrap/js/google-code-prettify/prettify.js\"></script>\r\n";
}
?>
			<script src="./bootstrap/js/bootstrap.js"></script>
		<!-- Style -->
			<!-- Boostrap -->
			<link href="./bootstrap/css/<?php echo TEMPLATE; ?>" rel="stylesheet">
			<link href="./bootstrap/css/<?php echo formatTableSorter(); ?>" rel="stylesheet">
<?php
if (($page == 'scriptconsole') || ($page == 'utilitiesrcontool'))
{
	echo "\t\t\t<link href=\"./bootstrap/css/prettify.css\" rel=\"stylesheet\">\r\n";
}
?>
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


	<body id="myBody">
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="#">Bright Game Panel</a>
<?php

/**
 * "Navigation Bar"
 */
if ($page != 'login')
{
?>
					<div class="nav-collapse">
						<ul class="nav">
							<li <?php
	if ($tab == 0)
	{
		echo "class=\"active\"";
	}
?>>
								<a href="index.php"><i class="icon-home icon-white"></i>&nbsp;Home</a>
							</li>
							<li class="dropdown <?php
	if ($tab == 2)
	{
		echo 'active';
	}
?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="icon-play icon-white"></i>
									Servers
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li class="nav-header">Game Servers</li>
<?php


	//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
	//We have to build the dropdown menu


	$groups = getClientGroups($_SESSION['clientid']);

	if ($groups != FALSE)
	{
		foreach($groups as $value)
		{
			if (getGroupServers($value) != FALSE)
			{
				$groupServers[] = getGroupServers($value); // Multi- dimensional array
			}
		}
	}

	// Build NEW single dimention array
	if (!empty($groupServers))
	{
		foreach($groupServers as $key => $value)
		{
			foreach($value as $subkey => $subvalue)
			{
				$servers[] = $subvalue;
			}
		}
		unset($groupServers);
	}

	if (!empty($servers))
	{
		foreach($servers as $key => $value)
		{
?>
									<li><a tabindex="-1" href="server.php?id=<?php echo $value['serverid']; ?>">#<?php echo $value['serverid']; ?> - <?php echo htmlspecialchars($value['name'], ENT_QUOTES); ?></a></li>
	<?php
		}
	}

	unset($groups, $servers);


	//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


?>
									<li class="divider"></li>
									<li class="nav-header">Voice Servers</li>
								</ul>
							</li>
							<li class="dropdown <?php
	if ($tab == 4)
	{
		echo 'active';
	}
?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="icon-briefcase icon-white"></i>
									Utilities
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li class="nav-header">Tools</li>
									<li><a tabindex="-1" href="utilitiesrcontool.php"><i class="icon-globe <?php echo formatIcon(); ?>"></i>&nbsp;Server RCON Tool</a></li>
									<li class="nav-header">Scripts</li>
									<li class="dropdown-submenu">
										<a tabindex="-1" href="#"><i class="icon-forward <?php echo formatIcon(); ?>"></i>&nbsp;Manage</a>
										<ul class="dropdown-menu">
<?php

	/**
	 * Scripts: On-The-Fly Menu - Client
	 *
	 * @date: 06/10/2012
	 */

	/**
	 * Processing Categories
	 */
	if (query_numrows( "SELECT `id` FROM `".DBPREFIX."scriptCat`" ) != 0)
	{
		$categoriesNav = mysql_query( "SELECT `id`, `name` FROM `".DBPREFIX."scriptCat` ORDER BY `name`" );
		while ($rowsCategoriesNav = mysql_fetch_assoc($categoriesNav))
		{
?>
											<li class="dropdown-submenu">
												<a tabindex="-1" href="#"><i class="icon-th-large <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo htmlspecialchars($rowsCategoriesNav['name'], ENT_QUOTES); ?></a>
												<ul class="dropdown-menu">
<?php
			/**
			 * Processing Scripts
			 */
			if (query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE `catid` = '".$rowsCategoriesNav['id']."' AND `status` = 'Active'" ) != 0)
			{
				$scriptsNav = mysql_query( "SELECT `scriptid`, `boxid`, `catid`, `groupid`, `name` FROM `".DBPREFIX."script` WHERE ( `catid` = '".$rowsCategoriesNav['id']."' AND `status` = 'Active' ) ORDER BY `name`" );
				while ($rowsScriptsNav = mysql_fetch_assoc($scriptsNav))
				{
					if (checkClientGroup($rowsScriptsNav['groupid'], $_SESSION['clientid']) != FALSE)
					{
?>
													<li>
														<a tabindex="-1" href="scriptsummary.php?id=<?php echo $rowsScriptsNav['scriptid']; ?>">
															<i class="icon-arrow-right <?php echo formatIcon(); ?>"></i>
															&nbsp;<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>&nbsp;
														</a>
													</li>
<?php
					}
				}
				unset($scriptsNav);

			}
			else
			{
				echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<li><a tabindex=\"-1\" href=\"#\"><span class=\"label\"><i class=\"icon-warning-sign ".formatIcon()."\"></i>&nbsp;No Scripts Available</span></a></li>\r\n";
			}
?>
												</ul>
											</li>
<?php
		}
		unset($categoriesNav);
	}
	else
	{
		echo "\t\t\t\t\t\t\t\t\t\t\t<li><a tabindex=\"-1\" href=\"#\"><span class=\"label\"><i class=\"icon-warning-sign ".formatIcon()."\"></i>&nbsp;No Categories Available</span></a></li>\r\n";
	}

?>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
						<ul class="nav pull-right">
							<li>
								<a href="#" id="clock" rel="tooltip" title="" data-original-title="<?php echo date('l | F j, Y | H:i'); ?>"><i class="icon-time icon-white"></i></a>
							</li>
							<li>
								<a href="#myBody" id="gototop" rel="tooltip" title="Back to Top"><i class="icon-arrow-up icon-white"></i></a>
							</li>
							<li>
								<div class="btn-group">
									<a class="btn btn-inverse dropdown-toggle <?php
	if ($tab == 9)
	{
		echo 'active';
	}
?>" data-toggle="dropdown" href="#">
										<img src="./bootstrap/img/icon-me-white.png" alt="me">
									</a>
									<ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dLabel">
										<li class="nav-header"><?php echo htmlspecialchars($_SESSION['clientusername'], ENT_QUOTES); ?></li>
										<li>
											<a tabindex="-1" href="myaccount.php">
												<i class="icon-edit <?php echo formatIcon(); ?>"></i>&nbsp;My Account
											</a>
										</li>
										<li class="divider"></li>
										<li>
											<a tabindex="-1" href="process.php?task=logout">
												<i class="icon-off <?php echo formatIcon(); ?>"></i>&nbsp;Sign Out
											</a>
										</li>
									</ul>
								</div>
							</li>
						</ul>
					</div><!--/.nav-collapse -->
<?php
}

/**
 * End of "Navigation Bar"
 */

?>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="page-header">
				<h1><?php echo $title; ?>&nbsp;<small><?php

if (isset($isSummary))
{
	if (!empty($rows['name'])) // Boxes, Servers, Scripts & Groups
	{
		echo htmlspecialchars($rows['name'], ENT_QUOTES);
	}
	else // Clients, Administrators & MyAccount
	{
		echo htmlspecialchars($rows['firstname'], ENT_QUOTES).' '.htmlspecialchars($rows['lastname'], ENT_QUOTES);
	}
}

?></small></h1>
			</div>
