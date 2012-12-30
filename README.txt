===================================================================
			Bright Game Panel - PHP Game Control Panel
			  by warhawk3407 (warhawk3407@gmail.com)
===================================================================

http://www.bgpanel.net/
Version 0.3.9 (Release 0 DEVELOPER BETA 5)
December 30th, 2012

===================================================================
						Terms of Use
===================================================================

By using BrightGamePanel, you declare that you have read LICENSING
conditions (see below) and you agree to respect all of them without
limitations.

===================================================================
							LICENSING
===================================================================

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.

===================================================================
							LIBRARIES
===================================================================

BrightGamePanel uses several GPL compliant libraries.
The following libraries are currently included into the panel :

- LGSL ( Live Game Server List ) by Richard Perry ( http://www.greycube.com/ )
- pChart 2.0 ( http://www.pchart.net/ )
- phpseclib 0.3.1 ( http://phpseclib.sourceforge.net/ )
- securimage 3.0.1 - January, 2012 ( http://www.phpcaptcha.org/ )

- Bootstrap 2.2.2 ( http://twitter.github.com/bootstrap/index.html )
- Bootswatch 2.2.2 ( http://bootswatch.com/ )
- google-code-prettify ( http://code.google.com/p/google-code-prettify/ )
- jQuery 1.8.3 ( http://jquery.com/ )
	* Lazy Load 1.8.3 ( http://www.appelsiini.net/projects/lazyload/ )
	* tablesorter 2.0.3 ( http://tablesorter.com/ )

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

===================================================================
					INSTALLATION INSTRUCTIONS
===================================================================

1. Unzip the contents of the .zip file to a folder on your computer.

2. Edit the SQL settings in the file 'configuration.php' (The file must be in UNIX format - I recommend to use Notepad++). It is located in the '/upload_me' folder.

3. Upload the entire contents of the folder '/upload_me' to your website in binary mode. It is recommended to change the name of the '/admin' folder.

4. CHMOD file '/.ssh/passphrase' to 0777.

5. Run the installation script at http://www.yourdomain.com/install/index.php

6. Once complete, delete the install folder from your web server.

7. CHMOD file '/.ssh/passphrase' to 0644.

8. Do not forget to schedule the cron job.

Enjoy BrightGamePanel !

NOTES:
IF CHARTS ARE NOT WORKING, TRY TO CHANGE CHMOD TO 0777 FOR '/admin/pcache' FOLDER.

===================================================================
						UPDATE INSTRUCTIONS
===================================================================

1. Remove all files (including the configuration.php file) from your website, both client and admin sides.
	*** IMPORTANT: SINCE V0.1.1, YOU HAVE TO KEEP INTACT ".ssh" DIR AT THE ROOT OF THE BRIGHT GAME PANEL INSTALL IF YOU ARE UPDATING TO A NEWER VERSION.

2. Edit the SQL settings in the file 'configuration.php'.

3. Upload the entire contents of the folder '/upload_me'.
	*** IMPORTANT: SINCE V0.1.1, DO NOT UPLOAD ".ssh" DIR OR IT WILL OVERWRITE YOUR PASSPHRASE.

4. CHMOD file '/.ssh/passphrase' to 0777 (USELESS IF YOU ARE UPDATING FROM V0.1.1).

5. Run the installation script at http://www.yourdomain.com/install/index.php
	|-> During the installation select "Update to the Last Version".

6. Once complete, delete the install folder from your web server.

7. CHMOD file '/.ssh/passphrase' to 0644 (USELESS IF YOU ARE UPDATING FROM V0.1.1).

Enjoy your updated version of BrightGamePanel !

NOTES:
IF CHARTS ARE NOT WORKING, TRY TO CHANGE CHMOD TO 0777 FOR '/admin/pcache' FOLDER.
