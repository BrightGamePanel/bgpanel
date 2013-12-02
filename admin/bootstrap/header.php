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



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}


?>
<!DOCTYPE html>
<html lang="<?php
			if (isset($_SESSION['adminlang'])) {
				echo substr($_SESSION['adminlang'], 0, 2);
			} else {
				echo substr(DEFAULT_LOCALE, 0, 2);
			}
			?>">
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
			<script src="../bootstrap/js/jquery.js"></script>
			<script src="../bootstrap/js/jquery.tablesorter.min.js"></script>
<?php
if (($page == 'scriptconsole') || ($page == 'utilitiesrcontool'))
{
	echo "\t\t\t<script src=\"../bootstrap/js/google-code-prettify/prettify.js\"></script>\r\n";
}
?>
			<script src="../bootstrap/js/jquery.lazyload.min.js"></script>
			<script src="../bootstrap/js/bootstrap.js"></script>
			<script src="../bootstrap/js/go-to-top.js"></script>
		<!-- Style -->
			<!-- Boostrap -->
			<link href="../bootstrap/css/<?php echo TEMPLATE; ?>" rel="stylesheet">
			<link href="../bootstrap/css/<?php echo formatTableSorter(); ?>" rel="stylesheet">
<?php
if (($page == 'scriptconsole') || ($page == 'utilitiesrcontool'))
{
	echo "\t\t\t<link href=\"../bootstrap/css/prettify.css\" rel=\"stylesheet\">\r\n";
}
?>
			<style type="text/css">
			body {
				padding-top: 60px;
				padding-bottom: 40px;
			}
			@media (max-width: 1023px) {
				.nav-collapse {
					overflow-y: auto;
				}
			}
			</style>
			<link href="../bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
			<link href="../bootstrap/css/go-to-top.css" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
			<!--[if lt IE 9]>
			  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
			<![endif]-->
		<!-- Favicon -->
			<link rel="shortcut icon" href="../bootstrap/img/favicon.ico">
	</head>


	<body>
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
					<div class="nav-collapse collapse navbar-responsive-collapse">
						<ul class="nav">
							<li <?php
	if ($tab == 0)
	{
		echo "class=\"active\"";
	}
?>>
								<a href="index.php"><i class="icon-home icon-white"></i>&nbsp;<?php echo T_('Home'); ?></a>
							</li>
							<li <?php
	if ($tab == 1)
	{
		echo "class=\"active\"";
	}
?>>
								<a href="client.php"><i class="icon-user icon-white"></i>&nbsp;<?php echo T_('Clients'); ?></a>
							</li>
							<li <?php
	if ($tab == 2)
	{
		echo "class=\"active\"";
	}
?>>
								<a href="server.php"><img src="../bootstrap/img/glyphicons_gamepad_white.png" width="14">&nbsp;<?php echo T_('Servers'); ?></a>
							</li>
							<li <?php
	if ($tab == 3)
	{
		echo "class=\"active\"";
	}
?>>
								<a href="box.php"><i class="icon-hdd icon-white"></i>&nbsp;<?php echo T_('Boxes'); ?></a>
							</li>
							<li class="dropdown <?php
	if ($tab == 4)
	{
		echo 'active';
	}
?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="icon-briefcase icon-white"></i>
									<?php echo T_('Utilities'); ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li class="nav-header"><?php echo T_('Tools'); ?></li>
									<li><a href="utilitieswebftp.php"><i class="icon-folder-open <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('WebFTP Tool'); ?></a></li>
									<li><a href="utilitiesrcontool.php"><i class="icon-globe <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Server RCON Tool'); ?></a></li>
									<li><a href="utilitieslog.php"><i class="icon-list-alt <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Activity Logs'); ?></a></li>
									<li><a href="utilitiesoptimize.php"><i class="icon-wrench <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Optimize Database'); ?></a></li>
									<li><a href="utilitiesversion.php"><i class="icon-certificate <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Version Check'); ?></a></li>
									<li class="nav-header"><?php echo T_('Scripts'); ?></li>
									<li class="dropdown-submenu">
										<a href="#"><i class="icon-forward <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Launcher'); ?></a>
										<ul class="dropdown-menu">
<?php

	/**
	 * Scripts: On-The-Fly Menu - Admin
	 *
	 * @note: JS is located in footer.php
	 */

	/**
	 * Processing Boxes
	 */
	if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box`" ) != 0)
	{
		$boxesNav = mysql_query( "SELECT `boxid`, `name` FROM `".DBPREFIX."box` ORDER BY `name`" );
		while ($rowsBoxesNav = mysql_fetch_assoc($boxesNav))
		{
?>
											<li class="dropdown-submenu">
												<a href="#"><i class="icon-hdd <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo htmlspecialchars($rowsBoxesNav['name'], ENT_QUOTES); ?></a>
												<ul class="dropdown-menu">
<?php

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
														<a href="#"><i class="icon-th-large <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo htmlspecialchars($rowsCategoriesNav['name'], ENT_QUOTES); ?></a>
														<ul class="dropdown-menu">
<?php
					/**
					 * Processing Scripts
					 */
					if (query_numrows( "SELECT `scriptid` FROM `".DBPREFIX."script` WHERE ( `boxid` = '".$rowsBoxesNav['boxid']."' AND `catid` = '".$rowsCategoriesNav['id']."' AND `status` = 'Active' ) " ) != 0)
					{
						$scriptsNav = mysql_query( "SELECT `scriptid`, `boxid`, `catid`, `name`, `panelstatus`, `type` FROM `".DBPREFIX."script` WHERE ( `boxid` = '".$rowsBoxesNav['boxid']."' AND `catid` = '".$rowsCategoriesNav['id']."' AND `status` = 'Active' ) ORDER BY `name`" );
						while ($rowsScriptsNav = mysql_fetch_assoc($scriptsNav))
						{
							if ($rowsScriptsNav['type'] == '0')
							{
?>
															<li>
																<a onclick="doScript('<?php echo $rowsScriptsNav['scriptid']; ?>', '<?php echo htmlspecialchars(addslashes($rowsScriptsNav['name']), ENT_QUOTES); ?>', 'launch')">
																	<i class="icon-arrow-right <?php echo formatIcon(); ?>"></i>
																	&nbsp;<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>&nbsp;
																	<span class="label label-inverse"><?php echo T_('Launch'); ?></span>
																</a>
															</li>
<?php
							}
							else if ($rowsScriptsNav['panelstatus'] == 'Stopped')
							{
?>
															<li>
																<a onclick="doScript('<?php echo $rowsScriptsNav['scriptid']; ?>', '<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>', 'start')">
																	<i class="icon-arrow-right <?php echo formatIcon(); ?>"></i>
																	&nbsp;<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>&nbsp;
																	<span class="label label-success"><?php echo T_('Start'); ?></span>
																</a>
															</li>
<?php
							}
							else if ($rowsScriptsNav['panelstatus'] == 'Started')
							{
?>
															<li>
																<a onclick="doScript('<?php echo $rowsScriptsNav['scriptid']; ?>', '<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>', 'stop')">
																	<i class="icon-arrow-right <?php echo formatIcon(); ?>"></i>
																	&nbsp;<?php echo htmlspecialchars($rowsScriptsNav['name'], ENT_QUOTES); ?>&nbsp;
																	<span class="label label-warning"><?php echo T_('Stop'); ?></span>
																</a>
															</li>
<?php
							}
						}
						unset($scriptsNav);

					}
					else
					{
						echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><a href=\"#\"><span class=\"label\"><i class=\"icon-warning-sign ".formatIcon()."\"></i>&nbsp;".T_('No Scripts Available')."</span></a></li>";
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
				echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<li><a href=\"#\"><span class=\"label\"><i class=\"icon-warning-sign ".formatIcon()."\"></i>&nbsp;".T_('No Categories Available')."</span></a></li>";
			}
?>

												</ul>
											</li>
<?php
		}
		unset($boxesNav);

	}
	else
	{
		echo "\t\t\t\t\t\t\t\t\t\t\t<li><a href=\"#\"><span class=\"label\"><i class=\"icon-warning-sign ".formatIcon()."\"></i>&nbsp;".T_('No Boxes Available')."</span></a></li>";
	}

?>

										</ul>
									</li>
									<li class="nav-header"><?php echo T_('Misc'); ?></li>
									<li><a href="utilitiesphpinfo.php"><i class="icon-info-sign <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('PHP Info'); ?></a></li>
									<li><a href="systemlicense.php"><i class="icon-info-sign <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('License Information'); ?></a></li>
								</ul>
							</li>
							<li class="dropdown <?php
	if ($tab == 5)
	{
		echo 'active';
	}
?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="icon-wrench icon-white"></i>
									<?php echo T_('Configuration'); ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li class="nav-header"><?php echo T_('General'); ?></li>
									<li><a href="configgeneral.php"><i class="icon-wrench <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Panel Settings'); ?></a></li>
									<li class="nav-header"><?php echo T_('Scripts'); ?></li>
									<li><a href="script.php"><i class="icon-cog <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Scripts'); ?></a></li>
									<li><a href="scriptcatmanage.php"><i class="icon-cog <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Categories'); ?></a></li>
									<li class="nav-header"><?php echo T_('Management'); ?></li>
									<li><a href="configadmin.php"><i class="icon-user <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Administrators'); ?></a></li>
									<li><a href="configgame.php"><i class="icon-cog <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Games'); ?></a></li>
									<li><a href="configgroup.php"><i class="icon-cog <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Groups'); ?></a></li>
									<li class="nav-header"><?php echo T_('Misc'); ?></li>
									<li><a href="configcron.php"><i class="icon-info-sign <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('Cron Settings'); ?></a></li>
									<li><a href="configapikey.php"><i class="icon-info-sign <?php echo formatIcon(); ?>"></i>&nbsp;<?php echo T_('API Key'); ?></a></li>
								</ul>
							</li>
						</ul>
						<ul class="nav pull-right">
<?php

	/**
	 * HEADER NOTIFICATIONS
	 */
	$headerNotificationsDataContent = '';

	if (MAINTENANCE == 1)
	{
		$headerNotificationsDataContent .= "<li>Maintenance Mode is Activated</li>";
	}

	if (!empty($headerNotificationsDataContent))
	{
?>
							<li>
								<a
								href="#"
								id="notificationsPopover"
								rel="popover"
								data-original-title="Notifications"
								data-content="<ul><?php echo $headerNotificationsDataContent; ?></ul>"
								>
								<i class="icon-exclamation-sign icon-white"></i>
								</a>
							</li>
<?php
	}

?>
							<li>
								<a href="#" id="clock" rel="tooltip" title="<?php echo date('l | F j, Y | H:i'); ?>"><i class="icon-time icon-white"></i></a>
							</li>
							<li>
								<a href="myaccount.php" id="me" rel="tooltip" title="<?php echo T_('My Account'); ?>"><i class="icon-user icon-white"></i></a>
							</li>
							<li style="padding-right: 8px;">
								<a href="process.php?task=logout" id="logout" rel="tooltip" title="<?php echo T_('Sign Out'); ?>"><i class="icon-off icon-white"></i></a>
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
	else if (!empty($rows['game'])) // Games
	{
		echo htmlspecialchars($rows['game'], ENT_QUOTES);
	}
	else // Clients, Administrators & MyAccount
	{
		echo htmlspecialchars($rows['firstname'], ENT_QUOTES).' '.htmlspecialchars($rows['lastname'], ENT_QUOTES);
	}
}

	?></small></h1>
			</div>
