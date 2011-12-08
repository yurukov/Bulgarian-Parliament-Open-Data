<?php

function transformVoteList() { 
	global $datafolder;
	set_time_limit(3000);

	$list = glob("$datafolder/model/vote/vote_*.xml");
	echo "Found ".count($list)." xml vote files. <br/>\n";
	$all="";
	echo "Grouping vote files... <br/>\n";
	foreach ($list as $id) {
		$id=substr($id,strlen("$datafolder/model/"));
		$transformed = transform("xsl/vote-short.xsl",getModelFile($id));
		$all .= substr($transformed, strlen('<?xml version="1.0" encoding="UTF-8"?>')+1);
		echo ".";
		unset($transformed);
	}
	echo "<br/>\n";
	
	echo "Transforming list file.<br/>\n";
	$all="<PlenaryVoteList>$all</PlenaryVoteList>";
	$transformed = transform("xsl/vote-all.xsl",$all);
	storeModelFile("vote-all.xml",$transformed);

	echo "Done.<br/>\n";
	unset($list);
	unset($all);
	unset($transformed);
}

function transformVotes() {
	global $datafolder;
	set_time_limit(3000);

	$list = glob("$datafolder/raw/vote/iv*.csv");
	echo "Found ".count($list)." csv vote files. <br/>\n";
	echo "Transforming csv vote files... <br/>\n";
	for ($i=0;$i<count($list);$i++) {
		$id=substr($list[$i],strlen("$datafolder/raw/vote/iv"),-4);
		$data = transformCsv2Xml($id);
		storeRawFile("vote/iv$id.xml",$data);
		$transformed = transform("xsl/vote.xsl",$data);
		storeModelFile("vote/vote_$id.xml",$transformed);
		echo ".";
		unset($data);
		unset($transformed);
	}
	echo "<br/>\nDone.<br/>\n";
	unset($list);
}


function printVoteExcels() {
	global $datafolder;
	set_time_limit(3000);

	$data = getModelFile("plenaryst-all.xml");
	preg_match_all("_id=\"(\d*?)\"\sdate=\"\d*?/(\d*?)/(\d*?)\"_ims",$data,$matches);
	unset($data);
	if (count($matches)<4)
		exit;

	echo "Loading vote excel files... <br/>\n";
	for ($i=0;$i<count($matches[1]);$i++) 
		if (intval($matches[3][$i])>2009 || (intval($matches[3][$i])==2009 && intval($matches[2][$i])>6)) {
			$data1 = getModelFile("plenaryst/plenaryst_".$matches[1][$i].".xml");
			preg_match_all("_File type=\"xls\">\s*([^<]+)\s*</File_ims",$data1,$matches1);
			unset($data1);
			if (count($matches1)<2)
				continue;
			foreach ($matches1[1] as $link) 
				echo "$link\n";
			unset($matches1);
		}
	unset($matches);
	echo "<br/>\nLoaded.<br/>\n";
}


/*_________________
	UTILS
*/

function transformCsv2Xml($fileid) {
	
	$data = getRawFile("vote/iv$fileid.csv");
	$data = explode("\n",$data);
	$res = "";
	foreach ($data as $raw) {
		$raw = explode(",",$raw);
		$resr="";
		foreach ($raw as $cell)
			if ($cell=='' || $cell==null) 
				$resr.="<cell/>";
			else {
				if (substr($cell,0,1)=="\"")
					$cell = substr($cell,1);
				if (substr($cell,-1)=="\"")
					$cell = substr($cell,0,-1);
				$resr.="<cell>$cell</cell>";
			}
		$res.="<raw>$resr</raw>\n";
	}
	$res="<mpvote>$res</mpvote>";

	$data = getRawFile("vote/gv$fileid.csv");
	$data = explode("\n",$data);
	$res1 = "";
	foreach ($data as $raw) {
		$raw = explode(",",$raw);
		$resr="";
		foreach ($raw as $cell)
			if ($cell=='' || $cell==null) 
				$resr.="<cell/>";
			else {
				if (substr($cell,0,1)=="\"")
					$cell = substr($cell,1);
				if (substr($cell,-1)=="\"")
					$cell = substr($cell,0,-1);
				$resr.="<cell>$cell</cell>";
			}
		$res1.="<raw>$resr</raw>\n";
	}
	$res1="<groupvote>$res1</groupvote>";
	unset($data);
	return "<table id='$fileid'>".$res.$res1."</table>";
}

?>
