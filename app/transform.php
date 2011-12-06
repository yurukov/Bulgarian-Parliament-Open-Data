<?php

$_templates = array();

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
	global $_templates;

	$XML = new DOMDocument('1.0', 'utf-8'); 
	$XML->loadXML( $doc ); 
	if (!isset($_templates[$style])) {
		$_templates[$style] = new DOMDocument('1.0', 'utf-8'); 
		$_templates[$style]->load( $style ); 
	}
	$xslt = new XSLTProcessor(); 
	$xslt->importStylesheet( $_templates[$style] ); 

	if ($param)
		foreach($param as $name=>$value)
			$xslt->setParameter('', $name, $value);

	$res = $xslt->transformToXML( $XML ); 

	unset($XML);
	unset($xslt);
	return $res;
}
	

?>
