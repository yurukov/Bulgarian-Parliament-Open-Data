<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/AbsensesAll">
		<Absenses>
			<PlenarySittings>
				<xsl:apply-templates select="//PlenarySittings/Date">
					<xsl:sort select="concat(substring(@timestamp,7),'-',substring(@timestamp,4,2),'-',substring(@timestamp,1,2))" data-type="text" order="ascending"/>
				</xsl:apply-templates>
			</PlenarySittings>
			<CommitteeMeetings>
				<xsl:apply-templates select="//CommitteeMeetings/Date">
					<xsl:sort select="concat(substring(@timestamp,7),'-',substring(@timestamp,4,2),'-',substring(@timestamp,1,2))" data-type="text" order="ascending"/>
				</xsl:apply-templates>
			</CommitteeMeetings>
		</Absenses>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Date">
		<xsl:copy>
			<xsl:apply-templates select="@*"/>
			<xsl:apply-templates select="MP">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
