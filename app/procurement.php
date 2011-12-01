<?php

function transformAllProcurements() {
	set_time_limit(3000);
	$list = getProcurementIds();
	echo "Found procurement data: ".count($list)." <br/>";

	$all="";
	echo "Transforming...<br/>";
	foreach ($list as $id) {
		$transformed = transformProcurementorReturn($id);
		if (strpos($id,"_")===false)
			$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		unset($transformed);
	}
	echo "<br/>";

	$all = "<Procurements>\n$all</Procurements>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/procurement-all.xsl",$all);
	storeModelFile("procurement-all.xml",$transformed);
	unset($transformed);

	echo "Procurement data transformed.<br/>";
	unset($list);
	unset($all);
	unset($transformed);
}

function transformProcurementorReturn($id) {
	if (isChanged("procurement/procurement_$id.xml")) {
		$transformed = transform("xsl/procurement.xsl",getRawFile("procurement/procurement_$id.xml"));
		storeModelFile("procurement/procurement_$id.xml",$transformed,false);
		echo ". ";
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("procurement/procurement_$id.xml");
	}
}
function loadProcurements() {
	set_time_limit(3000);

	echo "Loading procurement data ... <br/>";
	loadProcurementPeriods();
	echo "<br/>";

	echo "Loaded.<br/>";
}


/*_________________
	UTILS
*/

function setProcurementUnchangable($period) {
	setUnchangable("procurement/procurement_$period.xml");
}

function isProcurementChangable($period) {
	return isChangable("procurement/procurement_$period.xml");
}

function getProcurementIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/procurement/procurement*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/procurement/procurement_"),-4);
	return $res;
}

function loadProcurementPeriods() {
	$url = "http://www.parliament.bg/bg/publicprocurement/period/";
	$data = file_get_contents($url);
	preg_match_all("_/bg/publicprocurement/period/(2.*?)\"_ism",$data,$matches);
	if (count($matches)!=2)
		return "";

	$currentPeriods=array();	
	for ($i=1;$i>=-8;$i--)
		$currentPeriods[]=date("Y-n",strtotime("$i month"));
	$res=array();
	foreach ($matches[1] as $period) {
		$isCurrent=in_array($period,$currentPeriods);
		if ($isCurrent || isProcurementChangable($period)) {
			$url = "http://www.parliament.bg/bg/publicprocurement/period/$period";
			$data = file_get_contents($url);
			preg_match_all("_/bg/publicprocurement/ID/(.+?)\">.*?</a>.?\s?(.+?)</li>_ism",$data,$matches1);
			unset($data);
			if (count($matches1)!=3)
				continue;
			for ($i=0;$i<count($matches1[1]);$i++) 
				loadProcurement($matches1[1][$i],$matches1[2][$i]);
			if (!$isCurrent)
				setProcurementUnchangable($period);
			unset($matches1);
		} else
			echo "~ ";
		
	}
	unset($currentPeriods);
}

function loadProcurement($id, $date) {
	$url = "http://www.parliament.bg/bg/publicprocurement/ID/$id";
	$data = file_get_contents($url);
	preg_match("_<div\sclass=\"markframe\">(.*?)<div\sclass=\"markframe\">_ism",$data,$matches);
	if (count($matches)!=2)
		return;
	$data = $matches[1];
	$data = str_replace(array("<hr>","<br>","&nbsp;","\r","\n")," ",$data);
	$data = preg_replace("_\s+_ims"," ",$data);
	$data = "<div id='$id' date='$date'>$data";
	storeRawFile("procurement/procurement_$id.xml",$data);
	echo ". ";
	unset($matches);
	unset($data);
}
?>
