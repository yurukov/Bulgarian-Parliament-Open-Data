<?php
ini_set('user_agent','Zashto taka be hora![yurukov.net]'); 
$datafolder="../data";
$force_export=true;
checkFolderStructure();

function fixXML($data) {
	$data = str_replace("<?xml version=\"1.0\" ?>","<?xml version=\"1.0\" encoding=\"UTF-8\" ?> ",$data);
	$data = preg_replace('/(="[^"><=]*?)"([^"><=]*?)"([^"><=]*?")/im','$1\'$2\'$3',$data);
	$data = preg_replace('/(?<!=)"(?!(\s|\/))/im','&quot;',$data);
	return $data;
}

function storeFile($name, $data) {
	global $datafolder;
	file_put_contents("$datafolder/$name",$data);
}

function storeRawFile($name, $data) {
	$olddata = getFile("raw/".$name);
	if ($olddata===false || $olddata!=$data)
		storeFile("raw/".$name, $data);
	unset($olddata);
}

function storeModelFile($name, $data) {
	if (!isChanged($name)) 
		return;
	//$data = formatXML($data);
	storeFile("model/".$name, $data);
	storeGzFile($name, $data);
}

function storeGzFile($name, $data) {
	global $datafolder;
	$fgz = fopen("$datafolder/gz/$name.gz", 'w');
	$zipped = gzencode($data, 9);
	fwrite ($fgz, $zipped);
	fclose ($fgz);
}

function isChanged($name) {
	global $force_export, $datafolder;
	return $force_export || 
		(file_exists("$datafolder/raw/$name") && 
		file_exists("$datafolder/gz/$name.gz") &&
		filectime("$datafolder/raw/$name")>filectime("$datafolder/gz/$name.gz"));
}

function getFile($name) {
	global $datafolder;
	if (!file_exists("$datafolder/$name"))
		return false;
	return file_get_contents("$datafolder/$name");
}

function getRawFile($name) {
	return fixXML(getFile("raw/".$name));
}

function getModelFile($name) {
	return getFile("model/".$name);
}

function formatXML($data) {
	$xml = new DOMDocument('1.0', 'utf-8');
	$xml->loadXML($data);
	$xml->xmlStandalone=true;
	$xml->formatOutput=true;
	return $xml->saveXML();
}

function checkFolderStructure() {
	checkFolder("raw");
	checkFolder("raw/mp");
	checkFolder("raw/absense");
	checkFolder("raw/bill");
	checkFolder("model");
	checkFolder("model/mp");
	checkFolder("model/absense");
	checkFolder("model/bill");
	checkFolder("gz");
	checkFolder("gz/mp");
	checkFolder("gz/absense");
	checkFolder("gz/bill");
}

function checkFolder($path) {
	global $datafolder;
	if (!file_exists("$datafolder/$path") || !is_dir("$datafolder/$path")) {
		echo "Creating data folder $path. <br/>";
		mkdir("$datafolder/$path");
	}
}

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

?>
