<?php

function packData1() {
	global $datafolder;
	set_time_limit(9000);

	system("cd '$datafolder';zip -r -9 data.zip xsd -x *.gz; cd model; zip -r -9 ../data.zip * -x plenaryst/*");
	echo "<br/>\nDone";
}

function packData() {
	global $datafolder;
	set_time_limit(9000);

	$files=getFilesAll();
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

function getFilesAll() {
	global $datafolder;
	$res = getFiles("$datafolder/model");
	$res = array_merge($res,getFiles("$datafolder/xsd"));
	return $res;
}

function getFiles($location) {
	global $datafolder;
	$list = glob("$location/*");
	$res = array();
	for ($i=0;$i<count($list);$i++) 
		if (is_dir($list[$i]))
			$res = array_merge($res,getFiles($list[$i]));
		else
		if (substr($list[$i],-3)!=".gz")
			$res[]=$list[$i];
	return $res;
}

function createZip($files = array(),$destination = '') {
	global $datafolder;
	$valid_files = array();
	if(is_array($files))
		foreach($files as $file)
			if(file_exists($file) && strpos($file,"plenaryst/")==false && strpos($file,"vote/")==false)
				$valid_files[] = $file;

	if(count($valid_files)) {
		$zip = new ZipArchive();
		if($zip->open($destination,file_exists($destination) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
			return false;
		foreach($valid_files as $file)
			if (strpos($file,"$datafolder/model/")!==false)
				$zip->addFile($file,substr($file,strlen("$datafolder/model/")));
			else 
				$zip->addFile($file,substr($file,strlen("$datafolder/xsd/")));

    
		$zip->close();
    
		return file_exists($destination);
	} else
		return false;
}

?>
