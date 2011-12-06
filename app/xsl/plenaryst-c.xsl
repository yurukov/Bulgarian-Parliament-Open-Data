<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="id"/>

	<xsl:template match="/body">
		<PleanaryControl>
			<xsl:attribute name="id">
				<xsl:value-of select="@idControl"/>
			</xsl:attribute>
			<xsl:attribute name="date">
				<xsl:value-of select="@date"/>
			</xsl:attribute>
			<PleanaryControlUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_c_',@idControl,'.xml')"/></PleanaryControlUrl>
			<OriginalDataUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/parliamentarycontrol/ID/',@idControl)"/></OriginalDataUrl>
			<xsl:if test="@idProgram and not(@idProgram='')">
				<SittingProgram>
					<xsl:attribute name="id">
						<xsl:value-of select="@idProgram"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_p_',@idProgram,'.xml')"/>
				</SittingProgram>
			</xsl:if>
			<xsl:if test="@idSitting and not(@idSitting='')">
				<PleanarySitting>
					<xsl:attribute name="id">
						<xsl:value-of select="@idSitting"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_',@idSitting,'.xml')"/>
				</PleanarySitting>
			</xsl:if>
			<Title><xsl:value-of select="normalize-space(div/div[@class='marktitle']/text()[1])"/></Title>
			<Questions>
				<xsl:for-each select="div/div[@class='markcontent']/text()">
					<xsl:if test="not(normalize-space(.)='')">
						<xsl:value-of select="concat(normalize-space(.),'&#10;')"/>
					</xsl:if>
				</xsl:for-each>
			</Questions>
		</PleanaryControl>
	</xsl:template>

</xsl:stylesheet>
