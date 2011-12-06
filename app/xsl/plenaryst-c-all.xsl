<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/PleanaryControls">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" >
				<xsl:sort select="concat(substring(@date,7),'-',substring(@date,4,2),'-',substring(@date,1,2))" data-type="text" order="descending"/>
			</xsl:apply-templates>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
