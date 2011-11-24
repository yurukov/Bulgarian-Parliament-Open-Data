<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/schema">
		<Absenses>
			<xsl:apply-templates select="@*|node()" />
		</Absenses>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Date">
		<xsl:copy>
			<xsl:variable name="timestamp" select="node()[position()=1]"/>
			<xsl:attribute name="timestamp">
				<xsl:value-of select="concat(substring($timestamp,7),'-',substring($timestamp,4,2),'-',substring($timestamp,1,2))"/>
			</xsl:attribute>
			<xsl:attribute name="original">
				<xsl:value-of select="$timestamp"/>
			</xsl:attribute>			
			<xsl:apply-templates select="@*|node()[position()>1]" />
		</xsl:copy>
	</xsl:template>
	<xsl:template match="MP">
		<xsl:copy>
			<xsl:variable name="id" select="substring(MPURL/@value,32)"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<FullName>
				<xsl:value-of select="MPName/@value" />
			</FullName>
			<ProfileUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/MP/',$id)"/></ProfileUrl>
			<DataUrl><xsl:value-of select="concat('http://yurukov.net/parliament/data1/mp/mp_',$id,'.xml')"/></DataUrl>
		</xsl:copy>
	</xsl:template>


</xsl:stylesheet>
