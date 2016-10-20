<?php
/***
| OnDaemonDay.hook.php for phpMyAdmin on ZPanelX
| Written By: VJ (VJftw @ ZPanel Forums)
***/
require_once __DIR__.'/../code/VJftwTools.php';
	
function phpMyAdminUpdate() {
	Global $zdbh;
	$sentoraRoot = ctrl_options::GetSystemOption('sentora_root');
	$tempDir = ctrl_options::GetSystemOption('temp_dir');
	$phpMyAdminDir = $sentoraRoot."etc/apps/phpmyadmin/";

	// Check PHP and DB versions - skip update if below requirements - TGates
	$mysqlVersion = $zdbh->query('select version()')->fetchColumn();
	if ((version_compare(phpversion(), '5.5.0', '>=')) && (version_compare($mysqlVersion, '5.5.0', '>=')))
	{
		// Open the phpMyAdmin Directory and search for the RELEASE-DATE-#.#.# file
		$phpMyAdminDirHandle = opendir($phpMyAdminDir);
		while ((false !== ($file = readdir($phpMyAdminDirHandle))) && empty($match)) {
			preg_match('/RELEASE-DATE-\d+(?:\.\d+)+/', $file, $match);
		}
			
		if (!empty($match[0])) {
			$filePieces = explode('-', $match[0]);
			$phpMyAdminVersionCurrent = $filePieces[2];
			echo "Current phpMyAdmin Version: \t".$phpMyAdminVersionCurrent.fs_filehandler::NewLine();
		}
		
		// Determine the most up to date version of phpMyAdmin
		// Links updated by TGates
		$phpMyAdminDownloadsPage = file_get_contents("https://www.phpmyadmin.net/downloads/");
		preg_match('/phpMyAdmin \d+(?:\.\d+)+/', $phpMyAdminDownloadsPage, $match);
		if (!empty($match[0])) {
			$stringPieces = explode(' ', $match[0]);
			$phpMyAdminVersionNew = $stringPieces[1];
			echo "Newest phpMyAdmin Version: \t".$phpMyAdminVersionNew.fs_filehandler::NewLine();
		}
		
		if (!isset($phpMyAdminVersionNew)) {
			echo "Please notify VJftw @ ZPanel Forums: Newest phpMyAdmin Version not found.".fs_filehandler::NewLine();
		}
		// otherwise an update is possible. 
		else if (!(isset($phpMyAdminVersionCurrent)) || ($phpMyAdminVersionNew != $phpMyAdminVersionCurrent)) {
			echo "\tBacking up phpMyAdmin Configuration.".fs_filehandler::NewLine();
			// backup the config file
			$phpMyAdminConfig = file_get_contents($phpMyAdminDir."config.inc.php");
			
			echo "\tDownloading new phpMyAdmin.".fs_filehandler::NewLine();
			// download the file from phpmyadmin.net
			// links updated by TGates
			file_put_contents($tempDir."phpMyAdmin.zip", file_get_contents("https://files.phpmyadmin.net/phpMyAdmin/".$phpMyAdminVersionNew."/phpMyAdmin-".$phpMyAdminVersionNew."-all-languages.zip"));
			
			echo "\tExtracting phpMyAdmin.".fs_filehandler::NewLine();
			$phpMyAdminZip = new ZipArchive;
			$res = $phpMyAdminZip->open($tempDir."phpMyAdmin.zip");
			if ($res === TRUE) {
				$phpMyAdminZip->extractTo($tempDir."phpMyAdmin/");
				$phpMyAdminZip->close();
			}
			
			echo "\tRemoving old phpMyAdmin.".fs_filehandler::NewLine();
			removeDirectoryContents($phpMyAdminDir);
			
			echo "\tCopying new phpMyAdmin.".fs_filehandler::NewLine();
			$handle = opendir($tempDir."phpMyAdmin/");
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..")
					$phpMyAdminSrc = $entry;
			}
			closedir($handle);
			CopyDirectoryContents($tempDir."phpMyAdmin/".$phpMyAdminSrc, $phpMyAdminDir);
			RemoveDirectoryContents($tempDir."phpMyAdmin/");
			rmdir($tempDir."phpMyAdmin/");
			unlink($tempDir."phpMyAdmin.zip");
			
			// remove settings/setup folder - TGates
			RemoveDirectoryContents($phpMyAdminDir."setup/");
			rmdir($phpMyAdminDir."setup/");

			// remove examples folder - TGates
			RemoveDirectoryContents($phpMyAdminDir."examples/");
			rmdir($phpMyAdminDir."examples/");

			
			echo "\tRewrite configuration.".fs_filehandler::NewLine();
			file_put_contents($phpMyAdminDir."config.inc.php", $phpMyAdminConfig);
			
			echo "\tDone.".fs_filehandler::NewLine();
		} else {
			echo "No Update required.".fs_filehandler::NewLine();
		}
	} else {
		echo "PHP or MySQL below minimum requirements. No update available." . fs_filehandler::NewLine();
	}
}
	
/**
	Start Maintenance
*/
echo fs_filehandler::NewLine() . "phpMyAdmin Maintenance" . fs_filehandler::NewLine();
echo "Checking for phpMyAdmin Update." . fs_filehandler::NewLine();
phpMyAdminUpdate();
echo "End of phpMyAdmin Maintenance" . fs_filehandler::NewLine();