<?php

function transformAllMPs() {
	set_time_limit(3000);
	$list = getMPIds();
	echo "Found MP data: ".count($list)." <br/>";

	$currentmpIds = loadMPList();
	if ($currentmpIds===false)
		return;

	echo "Transforming...<br/>";
	$all="";
	$current="";
	$map=array();
	foreach ($list as $id) {
		$is_current=in_array($id,$currentmpIds);
		$transformed = transformMPorReturn($id, $is_current);

		$transformed = substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		$all .= $transformed;
		if ($is_current)
			$current .= $transformed;

		preg_match_all("/<FullName>(.*)<\/FullName>/im",$transformed,$matches);
		if ($matches==null || count($matches)<2 || count($matches[1])<1) {
			echo "Error reading mp $id <br/>";
			print_r($matches);	
			return;
		}
		$map[]=$matches[1][0]."\t$id\t".($is_current?1:0);

		unset($is_current);
		unset($transformed);
		unset($matches);
		
	}
	echo "<br/>";

	$all = "<MPs>\n$all</MPs>";
	$current = "<MPs>\n$current</MPs>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/mp-all.xsl",$all);
	storeModelFile("mp-all.xml",$transformed);
	unset($transformed);

	echo "Transforming current list. <br/>";
	$transformed = transform("xsl/mp-all.xsl",$current);
	storeModelFile("mp-current.xml",$transformed);
	unset($transformed);

	echo "Dumping name/id list. <br/>";
	storeModelFile("mp-names.csv",implode("\n",$map));
	unset($transformed);

	echo "MP data transformed.<br/>";
	unset($all);
	unset($current);
}

function transformMPorReturn($id, $is_current) {
	if (isChanged("mp/mp_$id.xml")) {
		$otherIds = implode(",",getOtherMPIds($id));
		$transformed = transform("xsl/mp.xsl",getRawFile("mp/mp_$id.xml"), array("id"=>$id, "current"=>$is_current, "otherids"=> $otherIds));
		storeModelFile("mp/mp_$id.xml",$transformed);
		echo ". ";
		unset($otherIds);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("mp/mp_$id.xml");
	}
}

function loadAllMPs() {
	set_time_limit(3000);
	echo "Loading MP ids in current parliament... <br/>";

	$mpIds = loadMPList();
	if ($mpIds===false)
		return;

	$maxId = 0;
	foreach($mpIds as $id)
		if ($id>$maxId)
			$maxId=$id;

	echo "Max MP id found: $maxId <br/>";

	echo "Loading MP data... <br/>";
	$i=0;
	for ($id=1; $id<=$maxId;$id++)
		if (loadMP($id))
			$i++;
	echo "<br/>";

	echo "Loaded $i out of $maxId MPs.<br/>";
	unset($all);
}

function loadCurrentMPs() {
	set_time_limit(3000);

	echo "Loading MP ids in current parliament... <br/>";

	$mpIds = loadMPList();
	if ($mpIds===false)
		return;

	echo "Loading MP data... <br/>";
	$i=0;
	foreach($mpIds as $id)
		if (loadMP($id))
			$i++;
	echo "<br/>";

	echo "Loaded $i MPs.<br/>";
	unset($mpIds);
}


/*_________________
	UTILS
*/


function getOtherMPIds($id) {
	$map = getModelFile("mp-names.csv");
	$map = explode("\n",$map);
	$name="";
	for ($i=0;$i<count($map);$i++) {
		$map[$i]=explode("\t",$map[$i]);
		if ($id==$map[$i][1])
			$name = $map[$i][0];
	}	
	$res = array();
	for ($i=0;$i<count($map);$i++)
		if ($name==$map[$i][0] && $id!=$map[$i][1])
			$res[]=$map[$i][1];
	sort($res);
	return $res;
}

function getMPIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/mp/mp*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/mp/mp_"),-4);
	return $res;
}


function loadMPList() {
	$url = "http://www.parliament.bg/bg/MP";
	$data = file_get_contents($url);
	preg_match_all("/\/bg\/MP\/(\d+)/im",$data,$matches);
	
	if (count($matches)<2) {
		echo "Error loading the MP List <br/>";
		return false;
	}
	
	echo "Found MPs in current parliament: ".count($matches[1])." <br/>";	
	return $matches[1];
}

function loadMP($id) {
	$url = "http://parliament.bg/export.php/bg/xml/MP/$id";
	$data = file_get_contents($url);
	$data = trim($data);

	if ($data==null || $data=="" ||
		strpos($data,"<schema><Profile></schema>")!==false ||
		strpos($data,"<schema></schema>")!==false)
	{
		echo "| ";
		unset($data);
		return false;	
	}

	storeRawFile("mp/mp_$id.xml",$data);
	echo ". ";
	unset($data);
	return true;	
}	

?>
