<?php

function transformAllPlenaryst() {
	set_time_limit(9000);
	$list = getPlenarystIds();
	echo "Found data for ".count($list)." pleanary sittings. <br/>\n";


	$all="";
	$all_c="";
	$all_p="";
	echo "Transforming...<br/>\n";
	foreach ($list as $id) {
		$transformed = transformPlenarystorReturn($id);

		if (strpos($id,"p")!==false) {
			$transformed1 = transform("xsl/plenaryst-p-short.xsl",$transformed);
			$all_p.=substr($transformed1, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		} else
		if (strpos($id,"c")!==false) {
			$transformed1 = transform("xsl/plenaryst-c-short.xsl",$transformed);
			$all_c.=substr($transformed1, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		} else {
			$transformed1 = transform("xsl/plenaryst-short.xsl",$transformed);
			$all.=substr($transformed1, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		}

		unset($transformed);
		unset($transformed1);
	}
	echo "<br/>\n";

	echo "Dumping complete lists.<br/>\n";
	$all = "<PleanarySittings>\n$all</PleanarySittings>";
	$transformed = transform("xsl/plenaryst-all.xsl",$all);
	storeModelFile("plenaryst-all.xml",$transformed);

	$all_c = "<PleanaryControls>\n$all_c</PleanaryControls>";
	$transformed = transform("xsl/plenaryst-c-all.xsl",$all_c);
	storeModelFile("plenaryst-c-all.xml",$transformed);

	$all_p = "<SittingPrograms>\n$all_p</SittingPrograms>";
	$transformed = transform("xsl/plenaryst-p-all.xsl",$all_p);
	storeModelFile("plenaryst-p-all.xml",$transformed);
	unset($transformed);

	echo "Pleanary sittings data transformed.<br/>\n";
	unset($list);
	unset($all);
	unset($all_c);
	unset($all_p);
}

function transformPlenarystorReturn($id) {
	if (isChanged("plenaryst/plenaryst_$id.xml")) {
		$template = "xsl/plenaryst.xsl";
		if (strpos($id,"p")!==false)
			$template = "xsl/plenaryst-p.xsl";
		else
		if (strpos($id,"c")!==false)
			$template = "xsl/plenaryst-c.xsl";

		$transformed = transform($template,cleanRawPlenaryst(getRawFile("plenaryst/plenaryst_$id.xml")), array('id'=>$id));
		storeModelFile("plenaryst/plenaryst_$id.xml",$transformed,false);
		echo ". ";
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("plenaryst/plenaryst_$id.xml");
	}
}

function loadAllPlenaryst() {
	set_time_limit(90000);

	echo "Loading plenary sittings, control and programs ... <br/>\n";
	initPlenarystChangable();
	loadPlenarystPeriods();
	dumpPlenarystChangable();
	echo "<br/>\n";

	echo "Loaded.<br/>\n";
}

/*_________________
	UTILS
*/

$_changeablePlenaryst=array();

function setPlenarystUnchangable($id) {
	global $_changeablePlenaryst;
	if (isPlenarystChangable($id))
		$_changeablePlenaryst[]=$id;
}

function isPlenarystChangable($id) {
	global $_changeablePlenaryst;
	return !in_array($id,$_changeablePlenaryst);
}

function initPlenarystChangable() {
	global $datafolder,$_changeablePlenaryst;
	if (file_exists("$datafolder/raw/unchangablePlenaryst.csv"))
		$_changeablePlenaryst=file("$datafolder/raw/unchangablePlenaryst.csv", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function dumpPlenarystChangable() {
	global $datafolder,$_changeablePlenaryst;
	file_put_contents("$datafolder/raw/unchangablePlenaryst.csv",implode("\n",$_changeablePlenaryst));
	unset($_changeablePlenaryst);
}

function cleanRawPlenaryst($data) {
	preg_match("_(<body[^>]*>)_im",$data,$matches);
	if (count($matches)!=2) {
		echo "Error parsing seating";
		return false;
	}
	$start=strpos($data,"<div class=\"markframe\">");
	$data=substr($data,$start,strpos($data,"<div class=\"markframe\">",$start+strlen("<div class=\"markframe\">"))-$start);
	$data=str_replace("<br>","<br/>",$data);
	$data=str_replace("</ul><li>","</ul></li><li>",$data);
	$data=str_replace("</ol>","</li></ol>",$data);
	$data=preg_replace("_(?<=\.)\s+<li>_sim","</li><li>",$data);
	$data=str_replace("&","&amp;",$data);
	$data=$matches[1].$data."</body>";
	unset($matches);
	return $data;
}

function getPlenarystIds() {
	global $datafolder;
	$res = array();
	$list = glob("$datafolder/raw/plenaryst/plenaryst*.xml");
	for ($i=0;$i<count($list);$i++)
		$res[] = substr($list[$i],strlen("$datafolder/raw/plenaryst/plenaryst_"),-4);
	unset($list);
	return $res;
}

function loadPlenarystPeriods() {

	$url = "http://www.parliament.bg/bg/plenaryst/period";
	$data = file_get_contents($url);
	preg_match_all("_/bg/plenaryst/period/(.*?)\"_ism",$data,$matches);
	if (count($matches)!=2)
		return "";

	$currentPeriods=array();	
	for ($i=1;$i>=-6;$i--)
		$currentPeriods[]=date("Y-n",strtotime("$i month"));
	$res=array();
	foreach ($matches[1] as $reportPeriod) {
		$isCurrent=in_array($reportPeriod,$currentPeriods);
		if ($isCurrent || isPlenarystChangable($reportPeriod)) {
			echo " $reportPeriod "; 

			$ids = loadPlenarystPeriod($reportPeriod);

			foreach ($ids as $id) 
				loadPlenaryst($id[0],$id[1],$id[2],$id[3]);
			if (!$isCurrent)
				setPlenarystUnchangable($reportPeriod);
			unset($ids);
		} else
			echo "~ ";
		
	}
	unset($currentPeriods);
}

function loadPlenarystPeriod($period) {
	$url = "http://www.parliament.bg/bg/plenaryst/period/$period";
	$data = file_get_contents($url);
	preg_match_all("_/bg/plenaryst/ID/(.*?)\".*?</a>,\s*([^<]*?)</li>_ism",$data,$matches);
	unset($data);
	if (count($matches)!=3)
		return array();
	
	$matches1=false;
	$matches2=false;
	if (intval(substr($period,0,4))>=2001) {
		$url = "http://www.parliament.bg/bg/plenaryprogram/period/$period";
		$data = file_get_contents($url);
		preg_match_all("_/bg/plenaryprogram/ID/(.*?)\".*?</a>,\s*([^<]*?)</li>_ism",$data,$matches1);
		unset($data);
		if (count($matches1)!=3)
			unset($matches1);

		$url = "http://www.parliament.bg/bg/parliamentarycontrol/period/$period";
		$data = file_get_contents($url);
		preg_match_all("_/bg/parliamentarycontrol/ID/(.*?)\".*?</a>,\s*([^<]*?)</li>_ism",$data,$matches2);
		unset($data);
		if (count($matches1)!=3)
			unset($matches2);
	}

	$res = array();
	for ($i=0;$i<count($matches[1]);$i++) {
		$idSitting=$matches[1][$i];
		$idProgram=false;
		$idControl=false;
		$date=$matches[2][$i];
		if ($matches1)
			for ($j=0;$j<count($matches1[1]);$j++) 
				if ($matches1[2][$j]==$date ||
					date("d/m/Y",strtotime(str_replace("/",".",$matches1[2][$j]))+3600*24)==$date ||
					date("d/m/Y",strtotime(str_replace("/",".",$matches1[2][$j]))+3600*24*2)==$date)
				{
					$idProgram=$matches1[1][$j];
					break;
				}
		if ($matches2)
			for ($j=0;$j<count($matches2[1]);$j++) 
				if ($matches2[2][$j]==$date) {
					$idControl=$matches2[1][$j];
					break;
				}					

		$res[]=array($idSitting, $idProgram, $idControl, $date);
	}

	unset($matches);
	unset($matches1);
	unset($matches2);
	return $res;	
}

function loadPlenaryst($idSitting, $idProgram, $idControl, $date) {
	$bodyReplace="<body idSitting='$idSitting' idProgram='$idProgram' idControl='$idControl' date='$date'>";
	
	$url = "http://www.parliament.bg/bg/plenaryst/ID/$idSitting";
	$data = file_get_contents($url);
	$data=str_replace("<body>",$bodyReplace,$data);
	storeRawFile("plenaryst/plenaryst_$idSitting.xml",$data);
	unset($data);
	echo ". ";

	if ($idProgram) {
		$url = "http://www.parliament.bg/bg/plenaryprogram/ID/$idProgram";
		$data = file_get_contents($url);
		$data=str_replace("<body>",$bodyReplace,$data);
		storeRawFile("plenaryst/plenaryst_p_$idProgram.xml",$data);
		unset($data);
		echo ". ";
	} 

	if ($idControl) {
		$url = "http://www.parliament.bg/bg/parliamentarycontrol/ID/$idControl";
		$data = file_get_contents($url);
		$data=str_replace("<body>",$bodyReplace,$data);
		storeRawFile("plenaryst/plenaryst_c_$idControl.xml",$data);
		unset($data);
		echo ". ";
	}
	unset($bodyReplace);
}

?>
