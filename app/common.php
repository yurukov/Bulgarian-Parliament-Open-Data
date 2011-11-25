<?php
ini_set('user_agent','Zashto taka be hora![yurukov.net]'); 
$datafolder="../data";
$force_export=false;
checkFolderStructure();

function checkFolderStructure() {
	checkFolder("raw");
	checkFolder("raw/mp");
	checkFolder("raw/absense");
	checkFolder("raw/bill");
	checkFolder("raw/consultant");
	checkFolder("model");
	checkFolder("model/mp");
	checkFolder("model/absense");
	checkFolder("model/bill");
	checkFolder("model/consultant");
	checkFolder("gz");
	checkFolder("gz/mp");
	checkFolder("gz/absense");
	checkFolder("gz/bill");
	checkFolder("gz/consultant");
}

function checkFolder($path) {
	global $datafolder;
	if (!file_exists("$datafolder/$path") || !is_dir("$datafolder/$path")) {
		echo "Creating data folder $path. <br/>";
		mkdir("$datafolder/$path");
	}
}

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

?>
