<?php

function transformAllPGFriends() {
	set_time_limit(3000);
	$list = getPGFriendIds();
	echo "Found pgfriend data: ".count($list)." <br/>";

	$all="";
	echo "Transforming...<br/>";
	foreach ($list as $id) {
		$transformed = transformPGFriendorReturn($id);
		if (strpos($id,"_")===false)
			$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		unset($transformed);
	}
	echo "<br/>";

	$all = "<GFriends>\n$all</GFriends>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/pgfriend-all.xsl",$all);
	storeModelFile("pgfriend-all.xml",$transformed);
	unset($transformed);

	echo "PGFriend data transformed.<br/>";
	unset($list);
	unset($all);
	unset($transformed);
}

function transformPGFriendorReturn($id) {
	if (isChanged("pgfriend/pgfriend_$id.xml")) {
		$transformed = transform("xsl/pgfriend.xsl",getRawFile("pgfriend/pgfriend_$id.xml"));
		storeModelFile("pgfriend/pgfriend_$id.xml",$transformed,false);
		echo ". ";

		unset($otherIds);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("pgfriend/pgfriend_$id.xml");
	}
}

function loadPGFriend() {
	set_time_limit(3000);

	echo "Loading friendship group ids in current parliament... <br/>";

	$pgfIds = loadPGFriendList();
	if ($pgfIds===false)
		return;

	echo "Loading pgfriend members ... <br/>";
	$i=0;
	foreach($pgfIds as $id)
		if (loadPGFriendMembers($id))
			$i++;
	echo "<br/>";

	echo "Loaded.<br/>";
	unset($pgIds);
}

/*_________________
	UTILS
*/

function getPGFriendIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/pgfriend/pgfriend*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/pgfriend/pgfriend_"),-4);
	return $res;
}

function loadPGFriendList() {
	$url = "http://www.parliament.bg/bg/friendshipgroups";
	$data = file_get_contents($url);
	preg_match_all("_/bg/friendshipgroups/members/(\d+)\"_im",$data,$matches);
	
	if (count($matches)<2) {
		echo "Error loading the pgfriend list <br/>";
		unset($data);
		return false;
	}
	
	echo "Found friendship groups in current parliament: ".count($matches[1])." <br/>";	
	unset($data);
	return $matches[1];
}

function loadPGFriendMembers($id) {
	$url = "http://www.parliament.bg/bg/friendshipgroups/members/$id";
	$data = file_get_contents($url);
	$start = strpos($data,"<div class=\"markframe\">");
	$data = substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start);
	$data = str_replace("\"","'",$data);
	$data = preg_replace("_<input\stype='text'.*?/>|<script.*<\/script>_im","",$data);
	$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<FGroup>$data</FGroup>";
	storeRawFile("pgfriend/pgfriend_$id.xml",$data);
	echo ". ";
	return true;
}



?>
