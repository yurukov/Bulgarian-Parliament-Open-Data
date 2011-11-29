<?php

function transformAllPGroups() {
	set_time_limit(3000);
	$list = getPGroupIds();
	echo "Found pgroup data: ".count($list)." <br/>";

	$all="";
	$map=array();
	echo "Transforming...<br/>";
	foreach ($list as $id) {
		$transformed = transformPGrouporReturn($id);
		if (strpos($id,"_")===false) {
			$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
			
			preg_match_all("/<PGroupName>(.*)<\/PGroupName>/im",$transformed,$matches);
			if ($matches==null || count($matches)<2 || count($matches[1])<1) {
				echo "Error reading pgroup $id <br/>";
				print_r($matches);	
				return;
			}
			$map[]=$matches[1][0]."\t$id";
		}
		unset($transformed);
	}
	echo "<br/>";

	$all = "<Groups>\n$all</Groups>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/pgroup-all.xsl",$all);
	storeModelFile("pgroup-all.xml",$transformed);
	unset($transformed);

	echo "Dumping name/id list. <br/>";
	storeModelFile("pgroup-names.csv",implode("\n",$map));
	unset($transformed);

	echo "PGroup data transformed.<br/>";
	unset($list);
	unset($map);
	unset($all);
	unset($transformed);
}

function transformPGrouporReturn($id) {
	if (isChanged("pgroup/pgroup_$id.xml")) {
		$date = strpos($id,"_")!==false ? str_replace(".","/",substr($id,-10)) : "";
		$transformed = transform("xsl/pgroup.xsl",getRawFile("pgroup/pgroup_$id.xml"),array("date"=>$date));
		storeModelFile("pgroup/pgroup_$id.xml",$transformed,false);
		echo ". ";

		unset($otherIds);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("pgroup/pgroup_$id.xml");
	}
}

function loadPGroups() {
	set_time_limit(3000);

	echo "Loading pgroup ids in current parliament... <br/>";

	$pgIds = loadPGroupList();
	if ($pgIds===false)
		return;

	echo "Loading pgroup members ... <br/>";
	$i=0;
	foreach($pgIds as $id)
		if (loadPGroupMembers($id))
			$i++;
	echo "<br/>";

	echo "Loaded.<br/>";
	unset($pgIds);
}

/*_________________
	UTILS
*/

function setPGroupUnchangable($id) {
	if (strpos($id,"_")!==false)
		setUnchangable("pgroup/pgroup_$id.xml");
}

function isPGroupChangable($id) {
	return isChangable("pgroup/pgroup_$id.xml");
}

function loadPGroupList() {
	$url = "http://www.parliament.bg/bg/parliamentarygroups";
	$data = file_get_contents($url);
	preg_match_all("_bg/parliamentarygroups/members/(\d+)\"_im",$data,$matches);
	
	if (count($matches)<2) {
		echo "Error loading the pgroup list <br/>";
		unset($data);
		return false;
	}
	
	echo "Found groups in current parliament: ".count($matches[1])." <br/>";	
	unset($data);
	return $matches[1];
}

function getPGroupIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/pgroup/pgroup*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/pgroup/pgroup_"),-4);
	return $res;
}

function loadPGroupMembers($id) {
	$data = loadPGroupMembersDate($id);
	
	$map=array();		
	
	$dates = extractPGroupUpdateDates($data);	
	$date_active=false;
	for ($i=0;$i<count($dates);$i++) {
		$dataN = '';
		if (isPGroupChangable("${id}_".str_replace("/",".",$dates[$i]))) 
			$dataN = loadPGroupMembersDate($id,$dates[$i]);
		else
			$dataN = getRawFile("pgroup/pgroup_${id}_".str_replace("/",".",$dates[$i]).".xml");

		if ($dataN==$data) {
			$date_active=$dates[$i];
			unset($dataN); 
			continue;
		}
		
		$map[$dates[$i]]=$dataN;
		$dates = array_merge($dates,array_diff(extractPGroupUpdateDates($dataN),$dates));
		unset($dataN);
	}	

	$dataB = loadPGroupBills($id);

	$updates = "<update date='".implode("'/><update date='",$dates)."'/>"; 
	if ($date_active)
		$updates = "<update_max date='".$date_active."'/>".$updates;
	$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Group>".$data.$updates.$dataB."</Group>";
	storeRawFile("pgroup/pgroup_$id.xml",$data);
	echo ". ";

	foreach ($dates as $date)
		if (isset($map[$date])) {
			if (isPGroupChangable("${id}_".str_replace("/",".",$date))) {
				$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Group>".$map[$date].$updates."</Group>";
				storeRawFile("pgroup/pgroup_${id}_".str_replace("/",".",$date).".xml",$data);

				setPGroupUnchangable("${id}_".str_replace("/",".",$date));

				echo ". ";
			} else
				echo "~ ";
		}
	unset($data);
	unset($map);
	unset($updates);
	return true;	
}

function loadPGroupBills($id) {
	$url = "http://www.parliament.bg/bg/parliamentarygroups/members/$id/bills";
	$data = file_get_contents($url);
	$start = strpos($data,"<div class=\"articletitle1\">");
	if ($start===false) 
		return "";
	$data = trim(substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start));
	$data = "<div type='bills'>".$data;
	return $data;
}

function loadPGroupMembersDate($id,$date=false) {
	$url = "http://www.parliament.bg/bg/parliamentarygroups/members/$id";
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

function extractPGroupUpdateDates($data) {
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
