<?php

function connectDB() {
	$link = mysql_connect('localhost', 'yurukov1_parl', ')]*4s~;%&foH');
	if (!$link) {
	    die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("yurukov1_parliament",$link);
	mysql_query("SET NAMES 'utf8'");
}

function sqlMPs() {
	global $datafolder;
	set_time_limit(3000);

	$mplist = glob("$datafolder/model/mp/mp*.xml");

	mysql_query("delete from mp");
	mysql_query("delete from absence");
	mysql_query("delete from consultants");
	mysql_query("delete from mp_bills");
	mysql_query("delete from speech");
	mysql_query("delete from parlimentary_control");
	

	foreach ($mplist as $mppath) {
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->load($mppath);
		$xpath = new DOMXPath($xml);

		$mp = $xpath->query("/MP")->item(0);
		$mpid = intval($mp->getAttribute("id"));
		$actingId=$mpid;
		if ($mp->getAttribute("acting")!="1") {
			$otherProfiles = $mp->getAttribute("otherProfiles");
			$otherProfiles = explode(",",trim($otherProfiles));
			if (count($otherProfiles)>0 && $otherProfiles[count($otherProfiles)-1]!="")
				$actingId = $otherProfiles[count($otherProfiles)-1];
		}
		$firstName = sqlClean($xpath->query("/MP/Profile/Names/FirstName")->item(0)->nodeValue);
		$sirName = sqlClean($xpath->query("/MP/Profile/Names/SirName")->item(0)->nodeValue);
		$familyName = sqlClean($xpath->query("/MP/Profile/Names/FamilyName")->item(0)->nodeValue);
		$dateOfBirth = translateDate($xpath->query("/MP/Profile/DateOfBirth")->item(0)->nodeValue);
		$placeOfBirth = sqlClean($xpath->query("/MP/Profile/PlaceOfBirth")->item(0)->nodeValue);
		$profession = sqlClean(getListAsString($xpath->query("/MP/Profile/Profession/Profession")));
		$language = sqlClean(getListAsString($xpath->query("/MP/Profile/Language/Language")));
		$scienceDegree = sqlClean(getListAsString($xpath->query("/MP/Profile/ScienceDegree/ScienceDegree")));
		$maritalStatus = sqlClean($xpath->query("/MP/Profile/MaritalStatus")->item(0)->nodeValue);
		$politicalForce = sqlClean($xpath->query("/MP/Profile/PoliticalForce")->item(0)->nodeValue);
		$constituency = sqlClean($xpath->query("/MP/Profile/Constituency")->item(0)->nodeValue);
		$email = sqlClean($xpath->query("/MP/Profile/E-mail")->item(0)->nodeValue);
		$website = sqlClean($xpath->query("/MP/Profile/Website")->item(0)->nodeValue);
		

		$sql = "INSERT INTO mp (id, actingId, firstName, sirName, familyName, dateOfBirth, placeOfBirth".
			", profession, language, scienceDegree, maritalStatus, politicalForce, constituency, email, website)".
			" VALUES ($mpid,$actingId,'$firstName','$sirName','$familyName','$dateOfBirth','$placeOfBirth','$profession','$language',".
			"'$scienceDegree','$maritalStatus','$politicalForce','$constituency','$email','$website')";

		mysql_query($sql) or die(mysql_error());
		
		$absenses = $xpath->query("/MP/Absense/PlenarySittings/Date");
		foreach ($absenses as $absense) {
			$date = translateDate($absense->getAttribute("timestamp"));
			$sql = "INSERT INTO absence (mpId, date, type) VALUES ($mpid,'$date','PlenarySittings')";
			mysql_query($sql) or die(mysql_error());
		}
		$absenses = $xpath->query("/MP/Absense/CommitteeMeetings/Date");
		foreach ($absenses as $absense) {
			$date = translateDate($absense->getAttribute("timestamp"));
			$sql = "INSERT INTO absence (mpId, date, type) VALUES ($mpid,'$date','CommitteeMeetings')";
			mysql_query($sql) or die(mysql_error());
		}

		$consultants = $xpath->query("/MP/Consultants/Consultant");
		foreach ($consultants as $consultant) {
			$name = sqlClean($consultant->childNodes->item(1)->nodeValue);
			$education = sqlClean($consultant->childNodes->item(3)->nodeValue);
			$field = sqlClean($consultant->childNodes->item(5)->nodeValue);
			$sql = "INSERT INTO consultants (id, idType, name, education, field) VALUES ($mpid,'mp','$name','$education','$field')";
			mysql_query($sql) or die(mysql_error());
		}

		$bills = $xpath->query("/MP/Bills/Bill");
		foreach ($bills as $bill) {
			$billId = sqlClean($bill->getAttribute("id"));
			$sql = "INSERT INTO mp_bills (mpid, billId) VALUES ($mpid,$billId)";
			mysql_query($sql) or die(mysql_error());
		}

		$speeches = $xpath->query("/MP/Speeches/Speech");
		foreach ($speeches as $speech) {
			$topic = sqlClean($speech->childNodes->item(1)->nodeValue);
			$type = sqlClean($speech->childNodes->item(3)->nodeValue);
			$date = translateDate($speech->childNodes->item(5)->nodeValue);
			$sql = "INSERT INTO speech(mpId, topic, type, date) VALUES ($mpid,'$topic','$type','$date')";
			mysql_query($sql) or die(mysql_error());
		}

		$questions = $xpath->query("/MP/ParliamentaryControl/Question");
		foreach ($questions as $question) {
			$about = sqlClean($question->childNodes->item(1)->nodeValue);
			$to = sqlClean($question->childNodes->item(3)->nodeValue);
			$date = translateDate($question->childNodes->item(5)->nodeValue);
			$sql = "INSERT INTO parlimentary_control (mpId, about, `to`, date) VALUES ($mpid,'$about','$to','$date')";
			mysql_query($sql) or die(mysql_error());
		}

		echo ". ";

		unset($xml);
		unset($xpath);
		unset($absenses);
		unset($mp);
		unset($absenses);
		unset($consultants);
		unset($bills);
		unset($speeches);
		unset($questions);
	}
}

function sqlVotes() {
	global $datafolder;
	set_time_limit(3000);


	$mplist = glob("$datafolder/model/vote/vote*.xml");

//	mysql_query("delete from vote");
//	mysql_query("delete from voting_topic");
	foreach ($mplist as $mppath) {
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->load($mppath);
		$xpath = new DOMXPath($xml);
		
		$sql = array();
		$votingpoints = $xpath->query("/PlenaryVotes/VotingPoints/VotingPoint");
		foreach ($votingpoints as $votingpoint) {
			$point = intval($votingpoint->getAttribute("point"));
			$date = translateDate($votingpoint->getAttribute("date"));
			$time = sqlClean($votingpoint->getAttribute("time"));
			$topic = sqlClean($votingpoint->nodeValue);
			$sql[]="('$date',$point,'$time','$topic')";
		}
		if (count($sql)==0) {
			die("Error in $mppath");
		}
		$sql = "REPLACE INTO voting_topic(date, point, time, topic) VALUES ".implode(",",$sql);
		mysql_query($sql) or die("$sql  ".mysql_error());
		unset($sql);

		$voteNode = $xpath->query("/PlenaryVotes")->item(0);
		$date = translateDate($voteNode->getAttribute("date"));
		$mpVotes = $xpath->query("/PlenaryVotes/MPVotes/MPVote");
		foreach ($mpVotes as $mpVote) {
			$sql = array();
			$mpId = intval($mpVote->getAttribute("id"));
			$present = $xpath->query("/PlenaryVotes/MPVotes/MPVote[@id='$mpId']/Votes/Vote[@registration='1'][last()]")->item(0)->getAttribute("present")=='1'?"true":"false";
			$votes = $xpath->query("/PlenaryVotes/MPVotes/MPVote[@id='$mpId']/Votes/Vote[not(@registration='1')]");
			foreach ($votes as $vote) {
				$point = intval($vote->getAttribute("point"));
				$voted = sqlClean($vote->getAttribute("voted"));
				$sql[]="($mpId,'$date',$point,$present,'$voted')";
			}
			if (count($sql)==0)
				$sql[]="($mpId,'$date',1,$present,null)";
			$sql = "REPLACE INTO vote(mpId, date, point, present, voted) VALUES ".implode(",",$sql);
			mysql_query($sql) or die(mysql_error());
			unset($sql);
		}
		
		echo ". ";
		
		unset($xml);		
		unset($xpath);		
		unset($votingpoints);
		unset($voteNode);
		unset($mpVotes);
	}
	echo "done";
}

function sqlClean($input) {
	return trim(str_replace("\\","\\\\",str_replace(array("'","`"),"\"",$input)));
}

function getListAsString($nodeList) {
	$res = array();
	foreach ($nodeList as $node)
		$res[] = $node->nodeValue;
	return implode(",",$res);
}

function translateDate($text) {
	if (strpos($text,"'")!==false)
		return;
	if (strpos($text,"/")==2 || strpos($text,"-")==2 || strpos($text,".")==2)
		return substr($text,6,4)."-".substr($text,3,2)."-".substr($text,0,2);
	if (strpos($text,"/")==4)
		return str_replace(array("/","."),"-",$text);
}


?>
