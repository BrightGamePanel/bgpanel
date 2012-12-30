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



$title = 'Admin Login';
$page = 'login';


require("../configuration.php");
require("./include.php");


//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

if (isset($_GET['task']))
{
	$task = mysql_real_escape_string($_GET['task']);
}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


include("./bootstrap/header.php");


if (MAINTENANCE == 1)
{
?>
			<div class="alert alert-block">
				<h4 class="alert-heading">Maintenance Mode</h4>
				The panel is currently undergoing scheduled maintenance.<br />
				Only <b>Super Administrators</b> are allowed to log in.
			</div>
<?php
}

if (!empty($_SESSION['lockout']) && ((time() - 60 * 10) < $_SESSION['lockout']))
{
?>
			<div class="alert alert-block">
				<h4 class="alert-heading">Too Many Incorrect Login Attempts</h4>
				Please wait 10 minutes before trying again.
			</div>
<?php
}
else
{
	if (@$task == 'password')
	{
		if (!empty($_SESSION['success']) && ($_SESSION['success'] == 'Yes'))
		{
?>
			<div class="alert alert-success">
				<a class="close" data-dismiss="alert">&times;</a>
				<h4 class="alert-heading">Password Sent</h4>
				Your password has been reset and emailed to you.
			</div>
<?php
		}
		else if (!empty($_SESSION['success']) && ($_SESSION['success'] == 'No'))
		{
?>
			<div class="alert alert-error">
				<a class="close" data-dismiss="alert">&times;</a>
				<h4 class="alert-heading">Fail!</h4>
				Your IP ("<?php echo $_SERVER['REMOTE_ADDR']; ?>") has been logged and admins notified of this failed attempt.
			</div>
<?php
			unset($_SESSION['success']);
		}
?>
			<div class="row">
				<div class="span4 offset4">
					<div class="well">
						<legend>Administrator Lost Password</legend>
						<form action="loginprocess.php" method="post">
						<input type="hidden" name="task" value="processpassword" />
							<label>Username :</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-user"></i></span>
								<input type="text" name="username" class="span3" placeholder="Login">
							</div>
							<label>Email :</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-envelope"></i></span>
								<input type="text" name="email" class="span3" placeholder="Email">
							</div>
							<label>&nbsp;</label>
							<img class="img-polaroid" id="captcha" src="../captcha/securimage_show.php" alt="CAPTCHA Image" />
							<button class="btn" type="button" onclick="document.getElementById('captcha').src = '../captcha/securimage_show.php?' + Math.random(); return false"><i class="icon-retweet"></i></button>
							<label></label>
							<div class="input-prepend">
								<span class="add-on">Captcha</span>
								<input type="text" name="captcha_code" class="span2">
							</div>
							<div style="text-align: center; margin-top: 24px;">
								<button type="submit" class="btn btn-block btn-inverse">Send Password</button>
							</div>
						</form>
						<ul class="pager">
							<li>
								<a href="login.php">Previous</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
<?php
	}
	else
	{
		if (isset($_SESSION['loginerror']))
		{
?>
			<div class="alert alert-error">
				<a class="close" data-dismiss="alert">&times;</a>
				<h4 class="alert-heading">Login Failed</h4>
				Your IP ("<?php echo $_SERVER['REMOTE_ADDR']; ?>") has been logged and admins notified of this failed attempt.
			</div>
<?php
			unset($_SESSION['loginerror']);
		}
?>
			<div class="row">
				<div class="span4 offset4">
					<div class="well">
						<div style="text-align: center; margin-bottom: 24px;">
							<img src="../bootstrap/img/logo.png" alt="Bright Game Panel Logo">
						</div>
						<legend>Administrator Login Form</legend>
						<form action="loginprocess.php" method="post">
							<input type="hidden" name="task" value="processlogin" />
							<input type="hidden" name="return" value="<?php
		if (isset($_GET['return']))
		{
			echo htmlspecialchars($_GET['return'], ENT_QUOTES);
		}
?>" />
							<label>Username :</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-user"></i></span>
								<input type="text" name="username" class="span3" <?php
		if (isset($_COOKIE['adminUsername']))
		{
			$cookie = htmlspecialchars($_COOKIE['adminUsername'], ENT_QUOTES);
			echo "value=\"{$cookie}\"";
			unset($cookie);
		}
		else
		{
			echo "placeholder=\"Login\"";
		}
?>>
							</div>
							<label>Password :</label>
							<div class="input-prepend">
								<span class="add-on"><i class="icon-lock"></i></span>
								<input type="password" name="password" class="span3" placeholder="Password">
							</div>
							<label class="checkbox">
								<input type="checkbox" name="rememberMe" checked="checked">Remember Me
							</label>
							<button class="btn btn-block btn-inverse" type="submit">Login</button>
						</form>
						<ul class="pager">
							<li>
								<a href="login.php?task=password">Forgot Password?</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
<?php
	}
}


include("./bootstrap/footer.php");
?>