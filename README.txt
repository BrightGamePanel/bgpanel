===================================================================
			Bright Game Panel - PHP Game Control Panel
			  by warhawk3407 (warhawk3407@gmail.com)
===================================================================

http://www.bgpanel.net/
Version 0.4.7 (Release 0 DEVELOPER BETA 9)
November 24th, 2013

===================================================================
							Terms of Use
===================================================================

By using Bright Game Panel, you declare that you have read and understood
LICENSING conditions (see below) and you agree to respect all of them
without limitations.

===================================================================
							LICENSING
===================================================================

-------------------------------------------------------------------
Bright Game Panel Licensing (GNU General Public License)
-------------------------------------------------------------------

Bright Game Panel is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

-------------------------------------------------------------------
HighSoft Non Commercial Licensing (CC BY-NC 3.0)
-------------------------------------------------------------------

Bright Game Panel uses the Highcharts JS and Highstock JS libraries.

Those libraries are developed by Highsoft. Highsoft is the owner of software products
developed by Torstein Hønsi. Please, see <http://highsoft.com/>.

Highcharts JS and Highstock JS libraries provided in this package are licensed under
the terms of the Creative Commons Attribution-NonCommercial 3.0 License.
Please, see <http://creativecommons.org/licenses/by-nc/3.0/>.

You can use HighSoft software for free under the non-commercial license when you are:
	- A student, university or a public school
	- A non-profit organisation
	- Developing and testing applications using Highcharts/Highstock

Source editing is allowed.

HIGHSOFT SOFTWARE PRODUCT IS NOT FREE FOR COMMERCIAL USE.

More information at <http://shop.highsoft.com/faq#non-commercial-redistribution>.

===================================================================
							LIBRARIES
===================================================================

Bright Game Panel uses several GPL compliant libraries.
The following libraries are currently included into the panel :

- AjaXplorer 5 ( 5.0.2__2013-08-20__d717183490 ) by Charles Du Jeu ( http://ajaxplorer.info/ )
- LGSL ( Live Game Server List ) by Richard Perry ( http://www.greycube.com/ )
- pChart 2.1.3 ( http://www.pchart.net/ )
- phpseclib build-364 ( http://phpseclib.sourceforge.net/ )
- securimage 3.5.1 (June 22, 2013) ( http://www.phpcaptcha.org/ )
- php-gettext 1.0.11 ( https://launchpad.net/php-gettext/ )

- Bootstrap 2.3.2 ( http://getbootstrap.com/2.3.2/ )
- Bootswatch 2.3.2 ( http://bootswatch.com/ )
- google-code-prettify ( http://code.google.com/p/google-code-prettify/ )
- jQuery 1.9.1 ( http://jquery.com/ )
	* Lazy Load 1.8.5 ( http://www.appelsiini.net/projects/lazyload/ )
	* tablesorter 2.0.3 ( http://tablesorter.com/ )
	* Highcharts JS: Highstock 1.3.4 (2013-08-02) ( http://www.highcharts.com/ )

===================================================================
						WEB SERVER REQUIREMENTS
===================================================================

1. Linux OS
2. PHP Version 5.3.4 or Greater
3. PHP Safe Mode Disabled
4. MySQL Database
5. Curl Extension
6. FSOCKOPEN Function
7. MAIL Function
8. MBSTRING Extension
9. BZIP2 Extension
10. ZLIB Extension
11. GD Extension
12. FreeType Extension
13. SimpleXML Extension
14. DOM Xml Enabled (AJXP)
15. MCrypt Enabled

===================================================================
						INSTALLATION INSTRUCTIONS
===================================================================

1. Unzip the contents of the .zip file to a folder on your computer.

2. Edit the SQL settings in the file 'configuration.php' (The file must be in UNIX format - I recommend to use Notepad++). It is located in the '/upload_me' folder.

3. Upload the entire contents of the folder '/upload_me' to your website in binary mode. It is recommended to change the name of the '/admin' folder.

4. CHMOD file '/.ssh/passphrase' to 0777.

5. Make the '/ajxp/data/' folder writeable by the server
	|-> For example:
		"chown -R www-data /ajxp/data/"
		"chmod -R 0777 /ajxp/data/"

6. Run the installation script at http://www.yourdomain.com/install/index.php

7. Once complete, delete the install folder from your web server.

8. CHMOD file '/.ssh/passphrase' to 0644.

9. Do not forget to schedule the cron job.

Enjoy BrightGamePanel !

===================================================================
						UPDATE INSTRUCTIONS
===================================================================

0. READ SPECIFIC INFORMATION RELATIVE TO A VERSION UPGRADE. IT IS WRITTEN IN THE 'README UPDATE' FILE ( IF THIS FILE DOESN'T EXIST, IGNORE THIS STEP ).
	|-> Filename syntax: 'README-version-UPDATE.txt'

1. Remove all files (including the configuration.php file) from your website, both client and admin sides.
	*** IMPORTANT ***
	- YOU HAVE TO KEEP INTACT '/.ssh' DIRECTORY AT THE ROOT OF THE BRIGHT GAME PANEL INSTALL IF YOU ARE UPDATING TO A NEWER VERSION !

2. Unzip the contents of the .zip file to a folder on your computer.

3. Edit the SQL settings in the file '/upload_me/configuration.php'.

4. Delete the folder '.ssh' located in '/upload_me'.

5. Upload the entire contents of the folder '/upload_me'.
	*** IMPORTANT ***
	- DO NOT UPLOAD '.ssh' FOLDER ( IT SHOULD HAS BEEN DELETED AT STEP 4 ), OTHERWISE IT WILL OVERWRITE YOUR PASSPHRASE, MAKING BGP UNUSABLE !
	- DO NOT FORGET TO UPLOAD '.version' FOLDER IN ORDER TO OVERWRITE THE FILE '/.version/version.xml' !

6. CHMOD file '/.ssh/passphrase' to 0777.

7. Make the '/ajxp/data/' folder writeable by the server
	|-> For example:
		"chown -R www-data /ajxp/data/"
		"chmod -R 0777 /ajxp/data/"

8. Run the installation script at http://www.yourdomain.com/install/index.php
	|-> During the installation select "Update to the Last Version".

9. Once complete, delete the install folder from your web server.

10. CHMOD file '/.ssh/passphrase' to 0644.

Enjoy your updated version of Bright Game Panel !
