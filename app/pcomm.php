<?php

function transformAllPComms() {
	set_time_limit(3000);
	$list = getPCommIds();
	echo "Found pcomm data: ".count($list)." <br/>";

	$all="";
	$map=array();
	echo "Transforming...<br/>";
	foreach ($list as $id) {
		$transformed = transformPCommorReturn($id);
		if (strpos($id,"_")===false) {
			$all.=substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
			
			preg_match_all("/<PCommName>(.*)<\/PCommName>/im",$transformed,$matches);
			if ($matches==null || count($matches)<2 || count($matches[1])<1) {
				echo "Error reading pcomm $id <br/>";
				print_r($matches);	
				return;
			}
			$map[]=$matches[1][0]."\t$id";
		}
		unset($transformed);
	}
	echo "<br/>";

	$all = "<Comms>\n$all</Comms>";

	echo "Transforming complete list. <br/>";
	$transformed = transform("xsl/pcomm-all.xsl",$all);
	storeModelFile("pcomm-all.xml",$transformed);
	unset($transformed);

	echo "Dumping name/id list. <br/>";
	storeModelFile("pcomm-names.csv",implode("\n",$map));
	unset($transformed);

	echo "PComm data transformed.<br/>";
	unset($list);
	unset($map);
	unset($all);
	unset($transformed);
}

function transformPCommorReturn($id) {
	if (isChanged("pcomm/pcomm_$id.xml")) {
		$date = strpos($id,"_")!==false ? str_replace(".","/",substr($id,-10)) : "";
		$transformed = transform("xsl/pcomm.xsl",getRawFile("pcomm/pcomm_$id.xml"),array("date"=>$date));
		storeModelFile("pcomm/pcomm_$id.xml",$transformed,false);
		echo ". ";

		unset($date);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("pcomm/pcomm_$id.xml");
	}
}

function loadPComms() {
	set_time_limit(3000);

	echo "Loading pcomm ids in current parliament... <br/>";

	$pcIds = loadPCommList();
	if ($pcIds===false)
		return;

	echo "Loading pcomm members ... <br/>";
	foreach($pcIds as $type)
		foreach($type[1] as $id)
			loadPCommMembers($id,$type[0]);
	echo "<br/>";

	echo "Loaded.<br/>";
	unset($pcIds);
}

/*_________________
	UTILS
*/


function setPCommUnchangable($id) {
	if (strpos($id,"_")!==false)
		setUnchangable("pcomm/pcomm_$id.xml");
}

function isPCommChangable($id) {
	return isChangable("pcomm/pcomm_$id.xml");
}

function loadPCommList() {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees";
	$data = file_get_contents($url);
	$commParts = split("articletitle\">",$data);
	if (count($commParts)!=4) {
		echo "Error loading the pcomm list <br/>";
		unset($commParts);
		unset($data);
		return false;
	}

	$commParts[3] = substr($commParts[3],0,strpos($commParts[3],"<!-- End left column -->"));
	$commParts = array_slice($commParts,1);

	$res = array();
	$count = 0;
	foreach ($commParts as $part) {
		preg_match("_^(.*?)</div>_im",$part,$matches);
		$name = $matches[1];

		unset($matches);
		preg_match_all("_bg/parliamentarycommittees/members/(\d+)\"_im",$part,$matches);
		
		if (count($matches)<2) {
			echo "Error loading the pcomm list <br/>";
			unset($commParts);
			unset($count);
			unset($data);
			return false;
		}
		$res[] = array($name,$matches[1]);
		$count+= count($matches[1]);
	}
	
	echo "Found committees in current parliament: ".$count." <br/>";	
	unset($commParts);
	unset($count);
	unset($data);
	return $res;
}

function getPCommIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/pcomm/pcomm*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[]=substr($list[$i],strlen("$datafolder/raw/pcomm/pcomm_"),-4);
	return $res;
}

function loadPCommMembers($id, $type) {
	$data = loadPCommMembersDate($id);
	
	$map=array();		
	
	$commClose=false;
	preg_match("_name='ddate' size='12' value='(.*?)'/>_im",$data,$matches);
	if (count($matches)==2)
		$commClose=$matches[1];
	$data = preg_replace("_<input\stype='text'.*?/>_im","",$data);

	$dates = extractPCommUpdateDates($data,$commClose);	

	$date_active=false;
	for ($i=0;$i<count($dates);$i++) {
		$dataN = '';
		if (isPCommChangable("${id}_".str_replace("/",".",$dates[$i]))) {
			$dataN = loadPCommMembersDate($id,$dates[$i]);
			$dataN = preg_replace("_<input\stype='text'.*?/>_im","",$dataN);
		} else
			$dataN = getRawFile("pcomm/pcomm_${id}_".str_replace("/",".",$dates[$i]).".xml");

		if ($dataN==$data) {
			$date_active=$dates[$i];
			unset($dataN); 
			continue;
		}
		
		$map[$dates[$i]]=$dataN;
		$dates = array_merge($dates,array_diff(extractPCommUpdateDates($dataN,$commClose),$dates));
		unset($dataN);
	}	

	$dataCi = loadPCommInfo($id);
	$dataCd = loadPCommDoc($id);
	$dataCb = loadPCommBills($id);

	$updates = "<update date='".implode("'/><update date='",$dates)."'/>"; 
	if ($date_active)
		$updates = "<update_max date='".$date_active."'/>".$updates;
	if ($commClose)
		$updates = "<update_close date='".$commClose."'/>".$updates;
	$updates = "<type>".$type."</type>".$updates;
	$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Comm>".$data.$updates.$dataCi.$dataCd.$dataCb."</Comm>";
	storeRawFile("pcomm/pcomm_$id.xml",$data);
	echo ". ";

	foreach ($dates as $date)
		if (isset($map[$date])) {
			if (isPCommChangable("${id}_".str_replace("/",".",$date))) {
				$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<Comm>".$map[$date].$updates."</Comm>";
				storeRawFile("pcomm/pcomm_${id}_".str_replace("/",".",$date).".xml",$data);

				setPCommUnchangable("${id}_".str_replace("/",".",$date));

				echo ". ";
			} else
				echo "~ ";
		}
	unset($data);
	unset($map);
	unset($updates);
	return true;	
}

function loadPCommInfo($id) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/info";
	$data = file_get_contents($url);
	$start = strpos($data,"<div class=\"dateclass\">");
	if ($start===false) 
		return "";
	$data = trim(substr($data,$start+strlen("<div class=\"dateclass\">"),strpos($data,"</div>",$start)-$start-strlen("<div class=\"dateclass\">")));
	$data = "<div type='info'>".$data."</div>";
	echo ". ";
	return $data;
}

function loadPCommDoc($id) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/documents";
	$data = file_get_contents($url);
	$start = strpos($data,"<div class=\"articletitle1\">");
	if ($start===false) 
		return "";
	$data = trim(substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start));
	$data = "<div type='doc'>".$data;
	echo ". ";
	return $data;
}

function loadPCommBills($id) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id/bills";
	$data = file_get_contents($url);
	$start = strpos($data,"<div class=\"articletitle1\">");
	if ($start===false) 
		return "";
	$data = trim(substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start));
	$data = "<div type='bills'>".$data;
	echo ". ";
	return $data;
}

function loadPCommMembersDate($id,$date=false) {
	$url = "http://www.parliament.bg/bg/parliamentarycommittees/members/$id";
	$data = false;
	if ($date)
		$data = http_post($url, array("ddate"=>$date));
	else
		$data = file_get_contents($url);

	$start = strpos($data,"<div class=\"markframe\">");
	$data = substr($data,$start,strpos($data,"<!-- End left column -->",$start)-$start);
	$data = str_replace("\"","'",$data);
	$data = preg_replace("_|<script.*<\/script>_im","",$data);
	return $data;
}

function extractPCommUpdateDates($data,$commClose) {
	preg_match_all("_<\/strong><br\s?\/>\s*(.*?)\s*-\s*(.*?)\s<br\s*/>_ims",$data,$matches);
	$commCloseD = $commClose? strtotime(str_replace("/",".",$commClose)) : false;
	if (count($matches)==3) {
		foreach ($matches[2] as $date)
			if (strpos($date,"момента")===false) {
				$update = strtotime(str_replace("/",".",$date))+3600*24;
				if (!$commCloseD || $commCloseD>=$update)
					$matches[1][]=date("d/m/Y",$update);
			}
		$res = array_unique($matches[1]);
		sort($res);	
		return $res;
	}
	return array();
	
}


?>
