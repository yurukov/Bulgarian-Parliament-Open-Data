<?php

function transformS($style, $doc, $param=false) {
	global $datafolder;
	$doc = "$datafolder/raw/$doc";
	$p = "";
	if ($param) {
		$p = "--param";
		foreach($param as $name=>$value)
			$p.=" $name '$value'";
	}
	$res = `xsltproc $p $style $doc 2>&1`;
	if (strpos($res,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>")===false)
		throw new ErrorException("Error while transforming: $res", 0, 1, "transform.php", 2);
	return $res;
}

function transform($style, $doc, $param=false) {
	
	$XML = new DOMDocument('1.0', 'utf-8'); 
	$XML->loadXML( $doc ); 

	$xslt = new XSLTProcessor(); 
	$XSL = new DOMDocument('1.0', 'utf-8'); 
	$XSL->load( $style ); 
	$xslt->importStylesheet( $XSL ); 

	if ($param)
		foreach($param as $name=>$value)
			$xslt->setParameter('', $name, $value);

	return $xslt->transformToXML( $XML ); 
}
	

?>
