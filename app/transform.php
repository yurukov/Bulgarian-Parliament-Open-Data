<?php

function transform($style, $doc, $param=false) {
	$XML = new DOMDocument(); 
	$XML->loadXML( $doc ); 

	$xslt = new XSLTProcessor(); 
	$XSL = new DOMDocument(); 
	$XSL->load( $style ); 
	$xslt->importStylesheet( $XSL ); 

	if ($param)
		foreach($param as $name=>$value)
			$xslt->setParameter('', $name, $value);

	return $xslt->transformToXML( $XML ); 
}
	

?>
