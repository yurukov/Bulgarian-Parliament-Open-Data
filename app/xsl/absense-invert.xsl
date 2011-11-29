<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/AbsensesAll">
		<MPs>
			<xsl:apply-templates select="//MP[not(string(@id)=preceding::MP/@id)]">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</MPs>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="MP">
		<xsl:variable name="id" select="string(@id)"/>
		<MP>
			<xsl:apply-templates select="@*|node()" />
			<Absense>
				<PlenarySittings>
					<xsl:apply-templates select="//PlenarySittings/Date[MP/@id=$id]">
						<xsl:sort select="concat(substring(@timestamp,7),'-',substring(@timestamp,4,2),'-',substring(@timestamp,1,2))" data-type="text" order="ascending"/>
					</xsl:apply-templates>
				</PlenarySittings>
				<CommitteeMeetings>
					<xsl:apply-templates select="//CommitteeMeetings/Date[MP/@id=$id]">
						<xsl:sort select="concat(substring(@timestamp,7),'-',substring(@timestamp,4,2),'-',substring(@timestamp,1,2))" data-type="text" order="ascending"/>
					</xsl:apply-templates>
				</CommitteeMeetings>
			</Absense>
		</MP>
	</xsl:template>
	
	<xsl:template match="Date">
		<xsl:copy>
			<xsl:apply-templates select="@*" />
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
