<?php

function packData() {
	global $datafolder;
	$files=getFiles();
	echo "Found ".count($files)." files.<br/>";

	$size=0;
	foreach ($files as $file)
		$size+=filesize($file);
	
	$destination="$datafolder/gz/data.zip";
	if (!createZip($files,$destination))
		echo "Failed to create the archive.<br/>";
	else
		echo "Reduced size from $size to ".filesize($destination).".<br/>";
}

function getFiles($location=false) {
	global $datafolder;
	if (!$location)
		$location = "$datafolder/model";
	$list = glob("$location/*");
	$res = array();
	for ($i=0;$i<count($list);$i++) 
		if (is_dir($list[$i]))
			$res = array_merge($res,getFiles($list[$i]));
		else
			$res[]=$list[$i];
	return $res;
}

function createZip($files = array(),$destination = '') {
	global $datafolder;
	$valid_files = array();
	if(is_array($files))
		foreach($files as $file)
			if(file_exists($file))
				$valid_files[] = $file;

	if(count($valid_files)) {
		$zip = new ZipArchive();
		if($zip->open($destination,file_exists($destination) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
			return false;
		foreach($valid_files as $file)
			$zip->addFile($file,substr($file,strlen("$datafolder/model/")));
    
		$zip->close();
    
		return file_exists($destination);
	} else
		return false;
}

?>
