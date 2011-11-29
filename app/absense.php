<?php

function transformAllAbsense() {
	set_time_limit(3000);
	$list = getAbsense();

	$all="";

	echo "Transforming absenses for ".count($list)." months.<br/>";
	foreach ($list as $date) {
		$transformed = transformAbsenseorReturn($date);

		$transformed = substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		$all .= $transformed;

		unset($transformed);
	}	
	echo "<br/>";

	$all = "<AbsensesAll>\n$all</AbsensesAll>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/absense-all.xsl",$all);
	storeModelFile("absense-all.xml",$transformed);
	unset($transformed);

	echo "Transforming inverted list. <br/>";
	$transformed = transform("xsl/absense-invert.xsl",$all);
	storeModelFile("absense-invert.xml",$transformed);
	unset($transformed);

	echo "Absense data transformed.<br/>";
	unset($all);
	unset($current);
}

function transformAbsenseorReturn($date) {
	if (isChanged("absense/absense_$date.xml")) {
		$transformed = transform("xsl/absense.xsl",getRawFile("absense/absense_$date.xml"));
		storeModelFile("absense/absense_$date.xml",$transformed);
		echo ". ";
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("absense/absense_$date.xml");
	}
}

function updateMPwithAbsense() {
	set_time_limit(3000);
	$absenseXml = getModelFile("absense-invert.xml");
	$absenseD = new DOMDocument('1.0', 'utf-8');
	$absenseD->loadXML($absenseXml, LIBXML_NOWARNING | LIBXML_NOERROR);
	$xpath = new DOMXPath($absenseD);

	echo "Updating MP data with absense. <br/>";
	$mpsA = $xpath->query('//Absense');

	echo "Found absenses for ".$mpsA->length." MPs. Updating... <br/>";
	foreach ($mpsA as $absense) {
		$id = $absense->parentNode->getAttribute("id");

		$mpXml = getModelFile("mp/mp_$id.xml");
		$mpD = new DOMDocument('1.0', 'utf-8');
		$mpD->loadXML($mpXml, LIBXML_NOWARNING | LIBXML_NOERROR);
		$mpD->formatOutput=true;
		$xpath1 = new DOMXPath($mpD);

		$oldAbsense = $xpath1->query('//Absense');
		foreach ($oldAbsense as $oldAbsenseNode)
			$oldAbsenseNode->parentNode->removeChild($oldAbsenseNode);
		
		$paNode = $xpath1->query('//Profile');
		$absenseNew = $mpD->importNode($absense, true);
		$paNode->item(0)->parentNode->insertBefore($absenseNew, $paNode->item(0)->nextSibling);
	
		$mpXml = $mpD->saveXML();
		storeModelFile("mp/mp_$id.xml",$mpXml);
		echo ". ";

		unset($id);
		unset($mpXml);
		unset($mpD);
		unset($xpath1);
		unset($absenseNew);
	}
	echo "<br/>";
	echo "Updated. <br/>";
}

function loadAllAbsense() {
	set_time_limit(3000);
	date_default_timezone_set("Europe/Sofia");
	$date = date_create('now');
	$failcount=4;
	while ($failcount>0){
		$dateF=date_format($date,'Y-m');
		echo "Loading absenses for $dateF... ";
		if (!isAbsenseChangable($dateF)) {
			echo "skip.<br/>";
			$failcount--;
		} else 
		if (loadAbsense($dateF)) {
			echo "done.<br/>";	
			setAbsenseUnchangable($dateF);
			$failcount=4;
		} else {
			$failcount--;
			echo "failed.<br/>";	
		}
		date_modify($date, "-1 month");
	}
}

/*_________________
	UTILS
*/

function setAbsenseUnchangable($date) {
	setUnchangable("absense/absense_$date.xml");
}

function isAbsenseChangable($date) {
	return isChangable("absense/absense_$date.xml");
}

function getAbsense() {
	global $datafolder;
	$list = glob("$datafolder/raw/absense/absense*.xml");
	for ($i=0;$i<count($list);$i++)
		$list[$i]=substr($list[$i],strlen("$datafolder/raw/absense/absense_"),-4);
	sort($list);
	return $list;
}

function loadAbsense($date) {
	$url = "http://parliament.bg/export.php/bg/xml/absense/$date";
	$data = file_get_contents($url);

	if ($data==null || $data=="" ||
		strpos($data,"<PlenarySittings></PlenarySittings><CommitteeMeetings></CommitteeMeetings>")!==false ||
		strpos($data,"<schema></schema>")!==false)
	{
		unset($data);
		return false;	
	}

	storeRawFile("absense/absense_$date.xml",$data);
	unset($data);
	return true;		
}

?>
