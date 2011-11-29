<?php

function transformAllConsultants() {
	set_time_limit(3000);

	echo "Transforming...<br/>";
	foreach (array("mp","pgroup","pcommittee") as $type) {
		$transformed = transformConsultantsorReturn($type);
		unset($transformed);
	}
	echo "<br/>";
	echo "MP data transformed.<br/>";
}

function transformConsultantsorReturn($type) {
	if (isChanged("consultant/consultant_$type.xml")) {
		$transformed = transform("xsl/consultant.xsl",getRawFile("consultant/consultant_$type.xml"), array("type"=>$type));
		storeModelFile("consultant/consultant_$type.xml",$transformed);
		echo ". ";
		unset($otherIds);
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("consultant/consultant_$type.xml");
	}
}

function updateMPwithConsultants() {
	set_time_limit(3000);
	$consultantXml = getModelFile("consultant/consultant_mp.xml");
	$consultantD = new DOMDocument('1.0', 'utf-8');
	$consultantD->loadXML($consultantXml, LIBXML_NOWARNING | LIBXML_NOERROR);
	$xpath = new DOMXPath($consultantD);

	echo "Updating MP data with consultants. <br/>";
	$mpsC = $xpath->query('//Consultants');

	echo "Found consultants for ".$mpsC->length." MPs. Updating... <br/>";
	foreach ($mpsC as $consultants) {
		$id = $consultants->parentNode->getAttribute("id");

		$mpXml = getModelFile("mp/mp_$id.xml");
		$mpD = new DOMDocument('1.0', 'utf-8');
		$mpD->loadXML($mpXml, LIBXML_NOWARNING | LIBXML_NOERROR);
		$mpD->formatOutput=true;
		$xpath1 = new DOMXPath($mpD);

		$oldConsultants = $xpath1->query('//Consultants');
		foreach ($oldConsultants as $oldConsultantsNode)
			$oldConsultantsNode->parentNode->removeChild($oldConsultantsNode);
		
		$paNode = $xpath1->query('//Profile');
		$consultantsNew = $mpD->importNode($consultants, true);
		$paNode->item(0)->parentNode->insertBefore($consultantsNew, $paNode->item(0)->nextSibling);
	
		$mpXml = $mpD->saveXML();
		storeModelFile("mp/mp_$id.xml",$mpXml);
		echo ". ";

		unset($id);
		unset($mpXml);
		unset($mpD);
		unset($xpath1);
		unset($consultantsNew);
	}
	echo "<br/>";
	echo "Updated. <br/>";
}

function updatePGroupwithConsultants() {
	set_time_limit(3000);
	$consultantXml = getModelFile("consultant/consultant_pgroup.xml");
	$consultantD = new DOMDocument('1.0', 'utf-8');
	$consultantD->loadXML($consultantXml, LIBXML_NOWARNING | LIBXML_NOERROR);
	$xpath = new DOMXPath($consultantD);

	echo "Updating PGroup data with consultants. <br/>";
	$pgroupC = $xpath->query('//Consultants');

	echo "Found consultants for ".$pgroupC->length." groups. Updating... <br/>";
	foreach ($pgroupC as $consultants) {
		$id = $consultants->parentNode->getAttribute("id");

		$mpXml = getModelFile("pgroup/pgroup_$id.xml");
		$mpD = new DOMDocument('1.0', 'utf-8');
		$mpD->loadXML($mpXml, LIBXML_NOWARNING | LIBXML_NOERROR);
		$mpD->formatOutput=true;
		$xpath1 = new DOMXPath($mpD);

		$oldConsultants = $xpath1->query('//Consultants');
		foreach ($oldConsultants as $oldConsultantsNode)
			$oldConsultantsNode->parentNode->removeChild($oldConsultantsNode);
		
		$paNode = $xpath1->query('//Bills');
		$consultantsNew = $mpD->importNode($consultants, true);
		$paNode->item(0)->parentNode->insertBefore($consultantsNew, $paNode->item(0));
	
		$mpXml = $mpD->saveXML();
		storeModelFile("pgroup/pgroup_$id.xml",$mpXml);
		echo ". ";

		unset($id);
		unset($mpXml);
		unset($mpD);
		unset($xpath1);
		unset($consultantsNew);
	}
	echo "<br/>";
	echo "Updated. <br/>";
}

function updatePCommwithConsultants() {
	set_time_limit(3000);
	$consultantXml = getModelFile("consultant/consultant_pcommittee.xml");
	$consultantD = new DOMDocument('1.0', 'utf-8');
	$consultantD->loadXML($consultantXml, LIBXML_NOWARNING | LIBXML_NOERROR);
	$xpath = new DOMXPath($consultantD);

	echo "Updating PComm data with consultants. <br/>";
	$pgroupC = $xpath->query('//Consultants');

	echo "Found consultants for ".$pgroupC->length." groups. Updating... <br/>";
	foreach ($pgroupC as $consultants) {
		$id = $consultants->parentNode->getAttribute("id");

		$mpXml = getModelFile("pcomm/pcomm_$id.xml");
		$mpD = new DOMDocument('1.0', 'utf-8');
		$mpD->loadXML($mpXml, LIBXML_NOWARNING | LIBXML_NOERROR);
		$mpD->formatOutput=true;
		$xpath1 = new DOMXPath($mpD);

		$oldConsultants = $xpath1->query('//Consultants');
		foreach ($oldConsultants as $oldConsultantsNode)
			$oldConsultantsNode->parentNode->removeChild($oldConsultantsNode);
		
		$paNode = $xpath1->query('//Documents');
		$consultantsNew = $mpD->importNode($consultants, true);
		$paNode->item(0)->parentNode->insertBefore($consultantsNew, $paNode->item(0));
	
		$mpXml = $mpD->saveXML();
		storeModelFile("pcomm/pcomm_$id.xml",$mpXml);
		echo ". ";

		unset($id);
		unset($mpXml);
		unset($mpD);
		unset($xpath1);
		unset($consultantsNew);
	}
	echo "<br/>";
	echo "Updated. <br/>";
}


function loadAllConsultants() {
	echo "Loading consultants in current parliament... <br/>";
	$map = array("mp","pgroup","pcommittee");
	for ($i=1;$i<=3;$i++) {
		$url = "http://www.parliament.bg/bg/parliamentaryregister/$i";
		$data = file_get_contents($url);
		$start = strpos($data,"<table class=\"billsresult\">");
		$data = substr($data,$start,strpos($data,"</table>",$start)-$start+8);
		$data = str_replace("\"","'",$data);
		$data = str_replace(array("/bg/parliamentarycommittees/members/","/bg/parliamentarygroups/members/","/bg/MP/"),"",$data);
		$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n".$data;

		storeRawFile("consultant/consultant_".$map[$i-1].".xml",$data);
		echo ". ";		
	}
	echo "<br/>";
	echo "Done.<br/>";
}

/*_________________
	UTILS
*/



?>
