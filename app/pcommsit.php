<?php

function transformAllPCommSits() {
	set_time_limit(3000);
	$list = getPCommSitIds();
	echo "Found sitings data for ".count($list)." pcomms. <br/>";

	echo "Transforming...<br/>";
	$all="";
	foreach ($list as $commId=>$sits) {
		$allC="";
		foreach ($sits as $id) {
			$transformed = transformPCommSitorReturn($id);
			$allC.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
			unset($transformed);
		}
		$allC = "<CommSits>\n$allC\n</CommSits>";		
		$transformed = transform("xsl/pcommsit-comm.xsl",$allC);
		storeModelFile("pcommsit/pcommsit_$commId.xml",$transformed);
		$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		echo ". ";

		unset($transformed);
		unset($allC);
	}
	echo "<br/>";

	$all = "<CommSits>\n$all</CommSits>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/pcommsit-all.xsl",$all);
	storeModelFile("pcommsit-all.xml",$transformed);
	unset($transformed);

	echo "PComm sit data transformed.<br/>";
	unset($list);
	unset($all);
}

function transformPCommSitorReturn($id) {
	if (isChanged("pcommsit/pcommsit_$id.xml")) {
		$transformed = transform("xsl/pcommsit.xsl",getRawFile("pcommsit/pcommsit_$id.xml"));
		storeModelFile("pcommsit/pcommsit_$id.xml",$transformed,false);
		echo ". ";
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("pcommsit/pcommsit_$id.xml");
	}
}

function loadPCommSits() {
	set_time_limit(3000);

	echo "Loading pcomm ids in current parliament... <br/>";

	$pcIds = getPCommIds();

	echo "Loading pcomm sittings, reports and transcripts ... <br/>";
	foreach($pcIds as $id)
		if (strpos($id,"_")===false)
			loadPCommSitPeriods($id);
	echo "<br/>";

	echo "Loaded.<br/>";
	unset($pcIds);
}


/*_________________
	UTILS
*/

function setPCommSitUnchangable($id) {
	if (strpos($id,"_")!==false)
		setUnchangable("pcommsit/pcommsit_$id.xml");
}

function isPCommSitChangable($id) {
	return isChangable("pcommsit/pcommsit_$id.xml");
}

function getPCommSitIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/pcommsit/pcommsit*.xml");
	for ($i=0;$i<count($list);$i++) {
		$id = substr($list[$i],strlen("$datafolder/raw/pcommsit/pcommsit_"),-4);
		$commId = substr($id,0,strpos($id,"_"));
		if (!isset($res["$commId"]))
			$res["$commId"]=array($id);
		else
			$res["$commId"][]=$id;
	}
	unset($list);
	return $res;
}

function loadPCommSitPeriods($id) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/sittings";
	$data = file_get_contents($url);
	preg_match_all("_sittings/period/(.*?)\"_ism",$data,$matches);
	if (count($matches)!=2)
		return "";

	$currentPeriods=array();	
	for ($i=1;$i>=-3;$i--)
		$currentPeriods[]=date("Y-n",strtotime("$i month"));
	$res=array();
	foreach ($matches[1] as $reportPeriod) {
		$isCurrent=in_array($reportPeriod,$currentPeriods);
		if ($isCurrent || isPCommSitChangable("${id}_$reportPeriod")) {
			$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/sittings/period/$reportPeriod";
			$data = file_get_contents($url);
			preg_match_all("_/sittings/ID/(.*?)\"_ism",$data,$matches1);
			unset($data);
			if (count($matches1)!=2)
				continue;
			foreach ($matches1[1] as $sitId) 
				loadPCommSit($id,$sitId);
			if (!$isCurrent)
				setPCommSitUnchangable("${id}_$reportPeriod");
			unset($matches1);
		} else
			echo "~ ";
		
	}
	unset($currentPeriods);
}

function loadPCommSit($id, $sitId) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/sittings/ID/$sitId";
	$data = file_get_contents($url);
	preg_match("_<div\sclass=\"markframe\">(.*?class=\"markframe\".*?)<div\sclass=\"markframe\">_ism",$data,$matches);
	if (count($matches)!=2)
		return;
	$data = $matches[1];
	unset($matches);

	preg_match_all("_/reports/ID/(.*?)\"_ism",$data,$matches);
	if (count($matches)!=2)
		return;
	foreach ($matches[1] as $reportId) {
		$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/reports/ID/$reportId";
		$dataR = file_get_contents($url);
		preg_match("_<div\sclass=\"markcontent\">.*?<br\s?/>\s*(.*?)\s*<hr\s?/>_ism",$dataR,$matches1);
		if (count($matches1)!=2)
			continue;
		$data.="<report id='$reportId'>Доклад ".$matches1[1]."</report>";		
		unset($matches1);
	}
	$data="<sit id='$id' sitid='$sitId'>$data</sit>";
	storeRawFile("pcommsit/pcommsit_${id}_${sitId}.xml",$data);
	echo ". ";
	unset($data);
}


?>
