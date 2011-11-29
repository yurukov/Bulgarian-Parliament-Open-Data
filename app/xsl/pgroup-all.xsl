<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/Groups">
		<ParliamentGroups>
			<xsl:apply-templates select="ParliamentGroup">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</ParliamentGroups>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Members|Bills">
		<xsl:copy>
			<xsl:attribute name="count">
				<xsl:value-of select="count(MP|Bill)"/>
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
