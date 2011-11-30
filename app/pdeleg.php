<?php

function transformAllPDelegs() {
	set_time_limit(3000);
	$list = getPDelegIds();
	echo "Found pdeleg data: ".count($list)." <br/>";

	$all="";
	echo "Transforming...<br/>";
	foreach ($list as $id) {
		$transformed = transformPDelegorReturn($id);
		if (strpos($id,"_")===false)
			$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		unset($transformed);
	}
	echo "<br/>";

	$all = "<Deleg>\n$all</Deleg>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/pdeleg-all.xsl",$all);
	storeModelFile("pdeleg-all.xml",$transformed);
	unset($transformed);

	echo "PDeleg data transformed.<br/>";
	unset($list);
	unset($all);
	unset($transformed);
}

function transformPDelegorReturn($id) {
	if (isChanged("pdeleg/pdeleg_$id.xml")) {
		$date = strpos($id,"_")!==false ? str_replace(".","/",substr($id,-10)) : "";
		$transformed = transform("xsl/pdeleg.xsl",getRawFile("pdeleg/pdeleg_$id.xml"),array("date"=>$date));
		storeModelFile("pdeleg/pdeleg_$id.xml",$transformed,false);
		echo ". ";

		unset($otherIds);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("pdeleg/pdeleg_$id.xml");
	}
}

function loadPDeleg() {
	set_time_limit(3000);

	echo "Loading deleg ids in current parliament... <br/>";

	$pgIds = loadPDelegList();
	if ($pgIds===false)
		return;

	echo "Loading pdeleg members ... <br/>";
	$i=0;
	foreach($pgIds as $id)
		if (loadPDelegMembers($id))
			$i++;
	echo "<br/>";

	echo "Loaded.<br/>";
	unset($pgIds);
}

/*_________________
	UTILS
*/

function setPDelegUnchangable($id) {
	if (strpos($id,"_")!==false)
		setUnchangable("pdeleg/pdeleg_$id.xml");
}

function isPDelegChangable($id) {
	return isChangable("pdeleg/pdeleg_$id.xml");
}

function getPDelegIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/pdeleg/pdeleg*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/pdeleg/pdeleg_"),-4);
	return $res;
}

function loadPDelegList() {
	$url = "http://www.parliament.bg/bg/parliamentarydelegations";
	$data = file_get_contents($url);
	preg_match_all("_parliamentarydelegations/members/(\d+)\"_im",$data,$matches);
	
	if (count($matches)<2) {
		echo "Error loading the pdeleg list <br/>";
		unset($data);
		return false;
	}
	
	echo "Found delegations in current parliament: ".count($matches[1])." <br/>";	
	unset($data);
	return $matches[1];
}

function loadPDelegMembers($id) {
	$data = loadPDelegMembersDate($id);
	
	$map=array();		
	
	$dates = extractPDelegUpdateDates($data);	
	$date_active=false;
	for ($i=0;$i<count($dates);$i++) {
		$dataN = '';
		if (isPDelegChangable("${id}_".str_replace("/",".",$dates[$i]))) 
			$dataN = loadPDelegMembersDate($id,$dates[$i]);
		else
			$dataN = getRawFile("pdeleg/pdeleg_${id}_".str_replace("/",".",$dates[$i]).".xml");

		if ($dataN==$data) {
			$date_active=$dates[$i];
			unset($dataN); 
			continue;
		}
		
		$map[$dates[$i]]=$dataN;
		$dates = array_merge($dates,array_diff(extractPDelegUpdateDates($dataN),$dates));
		unset($dataN);
	}	

	$updates = "<update date='".implode("'/><update date='",$dates)."'/>"; 
	if ($date_active)
		$updates = "<update_max date='".$date_active."'/>".$updates;
	$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Deleg>".$data.$updates."</Deleg>";
	storeRawFile("pdeleg/pdeleg_$id.xml",$data);
	echo ". ";

	foreach ($dates as $date)
		if (isset($map[$date])) {
			if (isPDelegChangable("${id}_".str_replace("/",".",$date))) {
				$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Deleg>".$map[$date].$updates."</Deleg>";
				storeRawFile("pdeleg/pdeleg_${id}_".str_replace("/",".",$date).".xml",$data);

				setPDelegUnchangable("${id}_".str_replace("/",".",$date));

				echo ". ";
			} else
				echo "~ ";
		}
	unset($data);
	unset($map);
	unset($updates);
	return true;	
}

function loadPDelegMembersDate($id,$date=false) {
	$url = "http://www.parliament.bg/bg/parliamentarydelegations/members/$id";
	$data = false;
	if ($date)
		$data = http_post($url, array("ddate"=>$date));
	else
		$data = file_get_contents($url);

	$start = strpos($data,"<div class=\"markframe\">");
	$data = substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start);
	$data = str_replace("\"","'",$data);
	$data = preg_replace("_<input\stype='text'.*?/>|<script.*<\/script>_im","",$data);
	return $data;
}

function extractPDelegUpdateDates($data) {
	preg_match_all("_<\/strong><br\s?\/>\s*(.*?)\s*-\s*(.*?)\s<br\s*/>_ims",$data,$matches);
	if (count($matches)==3) {
		foreach ($matches[2] as $date)
			if (strpos($date,"момента")===false)
				$matches[1][]=date("d/m/Y",strtotime(str_replace("/",".",$date))+3600*24);
		$res = array_unique($matches[1]);
		sort($res);	
		return $res;
	}
	return array();	
}

?>
