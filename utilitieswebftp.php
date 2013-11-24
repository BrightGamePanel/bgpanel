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



$page = 'utilitieswebftp';
$tab = 4;
$return = 'utilitieswebftp.php';


require("./configuration.php");
require("./include.php");


$title = T_('WebFTP');


//---------------------------------------------------------+

if ( isset($_GET['go']) )
{
	$step = 'webftp';
}
else
{
	$step = 'start';
}

//---------------------------------------------------------+


switch ($step)
{

//------------------------------------------------------------------------------------------------------------+



	case 'start':


		include("./bootstrap/header.php");


		/**
		 * Notifications
		 */
		include("./bootstrap/notifications.php");


?>
			<div class="well">
				<div style="text-align: center; margin-top: 19px;">
					<button href="#" onclick="ajxp()" class="btn btn-primary btn-large">
						<img src="./bootstrap/img/ajxp.png" alt="AJXP"><br />
						<hr>
						<?php echo T_('New WebFTP Session'); ?>
					</button>
				</div>
				<div style="text-align: center; margin-top: 19px;">
					<ul class="pager">
						<li>
							<a href="index.php"><?php echo T_('Back to Home'); ?></a>
						</li>
					</ul>
				</div>
			</div>
			<script>
			function ajxp()
			{
				window.open('utilitieswebftp.php?go=true', 'AjaXplorer - files', config='width='+screen.width/1.5+', height='+screen.height/1.5+', fullscreen=yes, toolbar=no, location=no, directories=no, status=yes, menubar=no, scrollbars=yes, resizable=yes');
			}
			</script>
<?php

	break;



//------------------------------------------------------------------------------------------------------------+



	case 'webftp':

		// Redirect User to AJXP

		$pageURL = 'http';

		if (@$_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}

		$pageURL .= "://";

		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		$ajxpurl = substr( $pageURL, 0, -27 );
		$ajxpurl = $ajxpurl.'ajxp/';

		// App Bridge

		$ajxpurl = $ajxpurl.'bgp.sessionprocess.php?api_key='.API_KEY.'&login='.$_SESSION['clientusername'].'&password='.uniqid();

		// Log

		$message = 'New WebFTP Session: '.mysql_real_escape_string( $_SESSION['clientusername'] );
		query_basic( "INSERT INTO `".DBPREFIX."log` SET
			`message` = '".$message."',
			`name` = '".mysql_real_escape_string($_SESSION['clientfirstname'])." ".mysql_real_escape_string($_SESSION['clientlastname'])."',
			`ip` = '".$_SERVER['REMOTE_ADDR']."'
		" );

		// Redirect to Bridge

		header( 'Location: '.$ajxpurl );
		die();

	break;



//------------------------------------------------------------------------------------------------------------+

}


include("./bootstrap/footer.php");
?>
