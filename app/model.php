<?php

$_changeable=array();

function fixXML($data) {
	$data = str_replace("<?xml version=\"1.0\" ?>","<?xml version=\"1.0\" encoding=\"UTF-8\" ?> ",$data);
	$data = preg_replace('/(="[^"><=]*?)"([^"><=]*?)"([^"><=]*?")/im','$1\'$2\'$3',$data);
	$data = preg_replace('/(?<!=)(?<!=)"(?!(\s|\/?>|[^>]*?<))/im','&quot;',$data);
	return $data;
}

function storeFile($name, $data) {
	global $datafolder;
	file_put_contents("$datafolder/$name",$data);
}

function storeRawFile($name, $data) {
	$data = fixXML($data);
	$olddata = getFile("raw/".$name);
	if ($olddata===false || $olddata!=$data)
		storeFile("raw/".$name, $data);
	unset($olddata);
}

function storeModelFile($name, $data, $formatXml=true) {
	if (substr($name,-4)=='.xml' && $formatXml)
		$data = formatXML($data);
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
		!file_exists("$datafolder/model/$name") ||
		!file_exists("$datafolder/raw/$name") ||
		filectime("$datafolder/raw/$name")>filectime("$datafolder/model/$name");
}

function isChangable($name) {
	global $_changeable;
	return !in_array($name,$_changeable);
}

function setUnchangable($name) {
	global $_changeable;
	if (isChangable($name))
		$_changeable[]=$name;
}

function initChangable() {
	global $datafolder,$_changeable;
	if (file_exists("$datafolder/raw/unchangable.csv"))
		$_changeable=file("$datafolder/raw/unchangable.csv", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function dumpChangable() {
	global $datafolder,$_changeable;
	file_put_contents("$datafolder/raw/unchangable.csv",implode("\n",$_changeable));
	unset($_changeable);
}

function getFile($name) {
	global $datafolder;
	if (!file_exists("$datafolder/$name"))
		return false;
	return file_get_contents("$datafolder/$name");
}

function getRawFile($name) {
	return getFile("raw/".$name);
}

function getModelFile($name) {
	return getFile("model/".$name);
}

function formatXML($data) {
	$xml = new DOMDocument('1.0', 'utf-8');
	$xml->loadXML($data);
	$xml->xmlStandalone=true;
	$xml->formatOutput=true;
	formatXMLNode($xml->firstChild);
	$res = $xml->saveXML();
	unset($xml);
	return $res;
}

function formatXMLNode($node) {
	if (!$node->hasChildNodes())
		return;
	$onlyText=true;
	$textNodes=array();
	foreach ($node->childNodes as $subnode)
		if ($subnode->nodeType!=XML_TEXT_NODE)
			$onlyText=false;
		else
			$textNodes[]=$subnode;
	if (!$onlyText)
		foreach ($textNodes as $textNode)
			$node->removeChild($textNode);		
	foreach ($node->childNodes as $subnode)
		formatXMLNode($subnode);
}

?>
