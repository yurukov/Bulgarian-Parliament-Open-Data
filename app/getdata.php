<?php
require_once "common.php";
require_once "mp.php";
require_once "absense.php";
require_once "bill.php";
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

} catch (ErrorException $e) {
    echo "Error:".$e->getMessage();
}
?>
