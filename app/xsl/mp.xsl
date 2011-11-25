<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="id"/>
	<xsl:param name="current"/>
	<xsl:param name="otherids"/>

	<xsl:template match="/schema">
		<MP>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<xsl:attribute name="otherProfiles">
				<xsl:value-of select="$otherids"/>
			</xsl:attribute>
			<xsl:if test="$current">
				<xsl:attribute name="acting">1</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="@*|node()" />
		</MP>
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

	<xsl:template match="Profile">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
			<ProfileUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/MP/',$id)"/></ProfileUrl>
			<OriginalDataUrl><xsl:value-of select="concat('http://parliament.bg/export.php/bg/xml/MP/',$id)"/></OriginalDataUrl>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',$id,'.xml')"/></DataUrl>
			<Photo>
				<Photo_big><xsl:value-of select="concat('http://parliament.bg/images/Assembly/',$id,'.png')"/></Photo_big>
				<Photo_thumb><xsl:value-of select="concat('http://parliament.bg/images/Assembly/_thumb.',$id,'.png')"/></Photo_thumb>
			</Photo>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Names">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
			<FullName>
				<xsl:value-of select="concat(FirstName/@value,' ',SirName/@value,' ',FamilyName/@value)"/>
			</FullName>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Bill">
		<xsl:copy>
			<xsl:attribute name="id">
				<xsl:value-of select="substring(ProfileURL/@value,38)"/>
			</xsl:attribute>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Profession/@id|Language/@id|PreviosNA/@id">
		<xsl:attribute name="order">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>
