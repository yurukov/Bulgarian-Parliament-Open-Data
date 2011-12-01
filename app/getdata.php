<?php
require_once "common.php";
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
require_once "transform.php";

echo "da, ama ne.<br/>";

try {

init();

//loadAllMPs();
//loadCurrentMPs();
//transformAllMPs();

//loadAllAbsense();
//transformAllAbsense();
//updateMPwithAbsense();

//loadPGroups();
transformAllPGroups();

//loadPComms();
//transformAllPComms();

//loadPCommSits();
//transformAllPCommSits();

//loadPDeleg();
//transformAllPDelegs();

//loadPGFriend();
//transformAllPGFriends();

//loadAllConsultants();
//transformAllConsultants();
//updateMPwithConsultants();
//updatePGroupwithConsultants();
//updatePCommwithConsultants();

//loadProcurements();
transformAllProcurements();

//loadAllBills();
//transformAllBills();
//aggregateBills();

//packData();

destroy();

} catch (ErrorException $e) {
    echo "Error:".$e->getMessage();
}
?>
