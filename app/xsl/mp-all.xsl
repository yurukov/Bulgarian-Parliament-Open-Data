<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/MPs">
		<xsl:copy>
			<xsl:apply-templates select="MP" >
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="MP">
		<xsl:copy>
			<xsl:apply-templates select="@*|Profile" />
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
