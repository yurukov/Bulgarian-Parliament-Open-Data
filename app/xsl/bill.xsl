<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="id"/>

	<xsl:template match="/schema">
		<Bill>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<xsl:apply-templates select="@*|node()" />
			<OriginalDataUrl><xsl:value-of select="concat('http://www.parliament.bg/export.php/bg/xml/bills/',$id)"/></OriginalDataUrl>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/bill/bill_',$id,'.xml')"/></DataUrl>
		</Bill>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="node()[@value]">
		<xsl:copy>
			<xsl:apply-templates select="@*[local-name()!='value']" />
			<xsl:value-of select="normalize-space(@value)"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Committees/CommitteeName">
		<Committee>
			<Name>
				<xsl:apply-templates select="@*|node()" />
			</Name>
			<xsl:apply-templates select="following-sibling::Role[1]" />
		</Committee>
	</xsl:template>

	<xsl:template match="Chronology/Date">
		<Event>
			<xsl:copy>
				<xsl:apply-templates select="@*|node()" />
			</xsl:copy>
			<xsl:apply-templates select="following-sibling::Status[1]" />
		</Event>
	</xsl:template>

	<xsl:template match="Committees">
		<xsl:apply-templates select="@*|CommitteeName" />
	</xsl:template>

	<xsl:template match="Chronology">
		<xsl:apply-templates select="@*|Chronology" />
	</xsl:template>

	<xsl:template match="Importer/@id">
		<xsl:attribute name="order">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>
