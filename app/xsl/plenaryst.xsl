<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="id"/>

	<xsl:template match="/body">
		<PleanarySitting>
			<xsl:attribute name="id">
				<xsl:value-of select="@idSitting"/>
			</xsl:attribute>
			<xsl:attribute name="date">
				<xsl:value-of select="@date"/>
			</xsl:attribute>
			<xsl:if test="substring(normalize-space(div/div[@class='marktitle']/text()[3]),1,7)='Открито'">
				<xsl:attribute name="time">
					<xsl:value-of select="concat(substring-before(substring-after(div/div[@class='marktitle']/text()[3],'в '),','),':',substring-before(substring-after(div/div[@class='marktitle']/text()[3],','),' ч'))"/>
				</xsl:attribute>
			</xsl:if>
			<PleanarySittingUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_',@idSitting,'.xml')"/></PleanarySittingUrl>
			<OriginalDataUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/plenaryst/ID/',@idSitting)"/></OriginalDataUrl>
			<xsl:if test="@idProgram and not(@idProgram='')">
				<SittingProgram>
					<xsl:attribute name="id">
						<xsl:value-of select="@idProgram"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_p_',@idProgram,'.xml')"/>
				</SittingProgram>
			</xsl:if>
			<xsl:if test="@idControl and not(@idControl='')">
				<PlenaryControl>
					<xsl:attribute name="id">
						<xsl:value-of select="@idControl"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_c_',@idControl,'.xml')"/>
				</PlenaryControl>
			</xsl:if>
			<Title><xsl:value-of select="normalize-space(div/div[@class='marktitle']/text()[1])"/></Title>
			<xsl:if test="div/div[@class='marktitle']/a[@class='example7']">
				<Video><xsl:value-of select="concat('http://parliament.yurukov.net',div/div[@class='marktitle']/a[@class='example7']/@href)"/></Video>
			</xsl:if>
			<VoteDocuments>
				<xsl:apply-templates select="div/ul[@class='frontList']/li[not(a/text()=following::li/a/text())]"/>
			</VoteDocuments>
			<Transcript>
				<xsl:for-each select="div/div[@class='markcontent']/text()">
					<xsl:if test="not(normalize-space(.)='')">
						<xsl:value-of select="concat(normalize-space(.),'&#10;')"/>
					</xsl:if>
				</xsl:for-each>
			</Transcript>
		</PleanarySitting>
	</xsl:template>

	<xsl:template match="div/ul[@class='frontList']/li">
		<Document>
			<xsl:variable name="name" select="a/text()"/>
			<Name><xsl:value-of select="normalize-space($name)"/></Name>
			<xsl:for-each select="preceding::li[a/text()=$name] | current()">
				<File>
					<xsl:attribute name="type">
						<xsl:value-of select="substring(a/@href,21)"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net',a/@href)"/>
				</File>
			</xsl:for-each>
		</Document>
	</xsl:template>

</xsl:stylesheet>
