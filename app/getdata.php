<?php
require_once "common.php";
require_once "db.php";
require_once "model.php";
require_once "zip.php";
require_once "mp.php";
require_once "absense.php";
require_once "bill.php";
require_once "consultant.php";
require_once "pgroup.php";
require_once "pcomm.php";
require_once "pcommsit.php";
require_once "pdeleg.php";
require_once "pgfriend.php";
require_once "procurement.php";
require_once "plenaryst.php";
require_once "vote.php";
require_once "transform.php";

header("Content-type: text/html; charset=utf-8");

try {

init();

if (isset($_GET['stage1'])) {

//	loadAllMPs();
	loadCurrentMPs();
	transformAllMPs();

} else if (isset($_GET['stage2'])) {

	loadAllAbsense();
	transformAllAbsense();
	updateMPwithAbsense();

} else if (isset($_GET['stage3'])) {

	loadPGroups();
	transformAllPGroups();
	loadPComms();
	transformAllPComms();
	loadPCommSits();
	transformAllPCommSits();
	loadPDeleg();
	transformAllPDelegs();
	loadPGFriend();
transformAllPGFriends();

} else if (isset($_GET['stage4'])) {

	loadAllConsultants();
	transformAllConsultants();
	updateMPwithConsultants();
	updatePGroupwithConsultants();
	updatePCommwithConsultants();

} else if (isset($_GET['stage5'])) {

	loadProcurements();
	transformAllProcurements();

} else if (isset($_GET['stage6'])) {

	loadAllBills();
	transformAllBills();
	aggregateBills();

} else if (isset($_GET['stage7'])) {

	loadAllPlenaryst();
	transformAllPlenaryst();

} else if (isset($_GET['stage8'])) {
	packData1();
}

//loadVoteExcels();
//transformVotes();
//transformVoteList();

//connectDB();
//sqlMPs();
//sqlVotes();
//mysql_close();

//packData();
//packData1();

destroy();

} catch (ErrorException $e) {
    echo "Error:".$e->getMessage()." in ".$e->getFile().":".$e->getLine()."\n";
}
?>
