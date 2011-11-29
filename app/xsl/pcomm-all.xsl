<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/Comms">
		<ParliamentCommittees>
			<xsl:apply-templates select="ParliamentCommittee">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</ParliamentCommittees>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Members|Bills|Documents">
		<xsl:copy>
			<xsl:attribute name="count">
				<xsl:value-of select="count(MP|Bill|Document)"/>
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
