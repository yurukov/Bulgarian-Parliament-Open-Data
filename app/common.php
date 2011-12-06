<?php
//ini_set('user_agent','Zashto taka be hora![yurukov.net]'); 
ini_set('user_agent','Mozilla/5.0 (Windows NT 6.1; rv:10.0.1) Gecko/20100101 Firefox/10.0.1'); 

set_error_handler('handleError');
$datafolder="../data";
$force_export=false;

function checkFolderStructure() {
	$aspects = array("raw","model","gz");
	$models = array("mp","absense","bill","consultant","pgroup","pcomm","pcommsit","pdeleg","pgfriend","procurement","plenaryst");

	foreach ($aspects as $aspect) {
		checkFolder($aspect);
		foreach ($models as $model) 
			checkFolder("$aspect/$model");
	}
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

function http_post ($url, $data)
{
    $data_url = http_build_query ($data);
    $data_len = strlen ($data_url);

    return file_get_contents ($url, false, stream_context_create (array ('http'=>array ('method'=>'POST'
            , 'header'=>"Connection: close\r\nContent-Length: $data_len\r\nContent-type: application/x-www-form-urlencoded\r\n"
            , 'content'=>$data_url
            ))));
}

function init() {
	checkFolderStructure();
	initChangable();
}

function destroy() {
	dumpChangable();
}

?>
