<?php
require_once "common.php";
require_once "model.php";
require_once "zip.php";
require_once "mp.php";
require_once "absense.php";
require_once "bill.php";
require_once "consultant.php";
require_once "transform.php";

try {

//loadAllMPs();
//loadCurrentMPs();
//transformAllMPs();

//loadAllAbsense();
//transformAllAbsense();
//updateMPwithAbsense();

//loadAllBills();
//transformAllBills();
//aggregateBills();

//loadAllConsultants();
//transformAllConsultants();
//updateMPwithConsultants();

//packData();

} catch (ErrorException $e) {
    echo "Error:".$e->getMessage();
}
?>
