<?php
	/*	Game Install Script
	Every directory is gameserver path relative,
	ExternalFiles are cached if they aren't temporal and saved in home of SSH User,
	use repo:/
	or  game:/
	functions:
		download(url,destination);
		makeDir(directory);
		unTar(filename,extractpath); 
		unZip(filename,extractpath); 
		rsync(frompath,topath);
		destroyDir(path); 
		execute(file);
		delete(file);
	------
	*/
	
	/****** PRELOAD ******/
	$this->setMode('preload');
	$this->download('http://files.sa-mp.com/samp03asvr_R4.tar.gz','repo:/');
	$this->unTar('repo:/samp03asvr_R4.tar.gz','repo:/');	
	$this->delete('repo:/samp03asvr_R4.tar.gz');
	/****** INSTALL ******/
	$this->setMode('install');
	$this->rsync('repo:/samp03/*','game:/');
	/****** UPDATE ******/ 
	/* Just tell our gameinstaller we have a gaminstall script loaded */
	$this->gameset = true;
?>