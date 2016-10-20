<?php
/***
| VJftwTools.php
| Written By: VJ (VJftw @ ZPanel Forums) 
| Provides required functions for some modules
***/

// Declare functions (check if they exist to prevent duplicate functions being declared.
if (!function_exists("CopyDirectoryContents"))
{
	function CopyDirectoryContents($src, $dest)
	{
		$path = realpath($src);
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object)
		{
			$startsAt = substr(dirname($name), strlen($src));
			mkNewDir($dest.$startsAt);
			if(is_writable($dest.$startsAt) and $object->isFile())
			{
				copy((string)$name, $dest.$startsAt.DIRECTORY_SEPARATOR.basename($name));
				chmod($dest.$startsAt.DIRECTORY_SEPARATOR.basename($name), 0777);
			}
		}
	}
}
	
if (!function_exists("mkNewDir"))
{
	function mkNewDir($folder) {
		if(!is_dir($folder)) {
			mkdir($folder, 0777);
			chmod($folder, 0777);
		}
	}
}

if (!function_exists("removeDirectoryContents"))
{
	function removeDirectoryContents($directory)
	{
		// set the directory slash implementation for Windows if required.
		if (sys_versions::ShowOSPlatformVersion() == "Windows")
			$l = '\\';
		else
			$l = '/';
		$files = dir($directory);
		while ($file = $files->read())
		{
			if ($file != '.' && $file != '..')
			{			
				if (is_dir($directory.$file))
				{
					removeDirectoryContents($directory.$file.$l);
					rmdir($directory.$file);
				}
				else
					unlink($directory.$file);
			}
		}
		$files->close();
	}
}