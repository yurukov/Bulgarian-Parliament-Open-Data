<?php

function transformAllBills() {
	set_time_limit(9000);
	$list = getBillIds();
	echo "Found data on ".count($list)." bills. <br/>";

	echo "Transforming...<br/>";
	$map=array();
	foreach ($list as $id) {
		$transformed = transformBillorReturn($id);

		preg_match_all("/<Signature>(.*)<\/Signature>/im",$transformed,$matches);
		if (!($matches==null || count($matches)<2 || count($matches[1])<1))
			$map[]=$matches[1][0]."\t$id";

		unset($transformed);
	} 
	echo "<br/>";

	echo "Dumping signature/id list. <br/>";
	storeModelFile("bill-signature.csv",implode("\n",$map));
	unset($transformed);

	echo "Bill data transformed.<br/>";
}

function transformBillorReturn($id) {
	if (isChanged("bill/bill_$id.xml")) {
		$transformed = transform("xsl/bill.xsl",getRawFile("bill/bill_$id.xml"), array("id"=>$id));
		storeModelFile("bill/bill_$id.xml",$transformed);
		echo ". ";
		return $transformed;
	} else {
		echo "~ ";	
		return getModelFile("bill/bill_$id.xml");
	}
}

function aggregateBills() {
	set_time_limit(9000);
	
	$list = getBillIds();
	echo "Found data on ".count($list)." bills. <br/>";

	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->formatOutput=true;
	$root = $doc->createElement('Bills');
	$doc->appendChild($root);

	echo "Merging...<br/>";
	foreach ($list as $id) {
		$billXml = getModelFile("bill/bill_$id.xml");
		if ($billXml=="")
			echo $id;
		$billD = new DOMDocument('1.0', 'utf-8');
		$billD->loadXML($billXml, LIBXML_NOWARNING | LIBXML_NOERROR);
		$bill = $billD->firstChild;

		$newbill = $doc->createElement('Bill');
		$newbill->setAttribute("id",$bill->getAttribute("id"));
		$name=$bill->getElementsByTagName("BillName")->item(0)->nodeValue;
		$name=str_replace(array("\n","\r")," ",$name);
		$newbill->appendChild($doc->createElement('BillName',$name));
		$newbill->appendChild($doc->createElement('Signature',$bill->getElementsByTagName("Signature")->item(0)->nodeValue));
		$newbill->appendChild($doc->createElement('Date',$bill->getElementsByTagName("Date")->item(0)->nodeValue));
		$newbill->appendChild($doc->createElement('DataUrl',$bill->getElementsByTagName("DataUrl")->item(0)->nodeValue));
		$root->appendChild($newbill);
	
		unset($name);
		unset($bill);
		unset($billD);
		unset($billXml);
		echo ". ";
	} 
	echo "<br/>";

	echo "Saving bill list. <br/>";

	$bills = $doc->saveXML();
	storeModelFile("bill-all.xml",$bills);
}

function loadAllBills() {
	set_time_limit(9000);
	echo "Loading max bill ids in current parliament... <br/>";

	$maxId = getMaxBillId();
	if (!$maxId)
		return;

	initBillChangable();

	echo "Loading bills... <br/>";
	$i=0;
	$j=0;
	$minId=-1;
	for ($id=5167; $id<=$maxId; $id++) {
		if ($id>5230 && $id<8311) {
			$id+=3080;
			continue;
		}

		if (!isBillChangable($id)) {
			$j++;
			echo "~ ";
		} else
		if (loadBill($id)) {
			if ($minId==-1)
				$minId=$id;
			$i++;
		}
	}
	echo "<br/>";
	dumpBillChangable();

	echo "Loaded $i out of $maxId. $j skipped. Min bill id is $minId.<br/>";

	unset($all);
}


/*_________________
	UTILS
*/

$_changeableBills=array();

function setBillUnchangable($id) {
	global $_changeableBills;
	if (isBillChangable($id))
		$_changeableBills[]=$id;
}

function isBillChangable($id) {
	global $_changeableBills;
	return !in_array($id,$_changeableBills);
}

function initBillChangable() {
	global $datafolder,$_changeableBills;
	if (file_exists("$datafolder/raw/unchangableBill.csv"))
		$_changeableBills=file("$datafolder/raw/unchangableBill.csv", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function dumpBillChangable() {
	global $datafolder,$_changeableBills;
	file_put_contents("$datafolder/raw/unchangableBill.csv",implode("\n",$_changeableBills));
	unset($_changeableBills);
}

function getBillIds() {
	global $datafolder;
	$list = glob("$datafolder/raw/bill/bill*.xml");
	for ($i=0;$i<count($list);$i++)
		$list[$i]=substr($list[$i],strlen("$datafolder/raw/bill/bill_"),-4);
	sort($list);
	return $list;
}

function getMaxBillId() {
	$url = "http://www.parliament.bg/rss.php?feed=bills&lng=bg";
	$data1 = file_get_contents($url);
	$url = "http://www.parliament.bg/rss.php?feed=desisions&lng=bg";
	$data2 = file_get_contents($url);
	preg_match_all("/bills\/ID\/(\d+)/im",$data1.$data2,$matches);

	if (count($matches)<2) {
		echo "Error loading the bill rss <br/>";
		unset($data1);
		unset($data2);
		return false;
	}

	$maxId=0;
	foreach ($matches[1] as $id)
		if ($maxId<$id)
			$maxId=$id;
	
	echo "Latest bill is with id: ".$maxId." <br/>";	
	unset($data1);
	unset($data2);
	return $maxId;
}

function loadBill($id) {
	$url = "http://www.parliament.bg/export.php/bg/xml/bills/$id";
	$data = file_get_contents($url);
	$data = trim($data);

	if ($data==null || $data=="" ||	strpos($data,"<schema></schema>")!==false)
	{
		setBillUnchangable($id);
		echo "| ";
		unset($data);
		return false;	
	}

	preg_match("_<Date\s*value=\"(.+?)\" />_im",$data,$matches);

	if (count($matches)!=2 || strtotime(str_replace("/",".",$matches[1]))<strtotime("-8 months")) {
		setBillUnchangable($id);
		echo "~";
	} 

	storeRawFile("bill/bill_$id.xml",$data);
	echo ". ";
	unset($data);
	return true;	
}	


?>
