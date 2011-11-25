<?php

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
