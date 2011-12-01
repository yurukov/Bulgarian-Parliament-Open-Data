<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/div">
		<PublicProcurement>
			<xsl:attribute name="id">
				<xsl:value-of select="@id"/>
			</xsl:attribute>
			<Title><xsl:value-of select="normalize-space(div[@class='marktitle'])"/></Title>
			<PublishDate><xsl:value-of select="@date"/></PublishDate>
			<xsl:if test="ul[@class='frontList']/li[starts-with(text(),'Процедура')]">
				<Procedure><xsl:value-of select="normalize-space(substring-after(ul[@class='frontList']/li[starts-with(text(),'Процедура')],':'))"/></Procedure>
			</xsl:if>
			<xsl:if test="ul[@class='frontList']/li[contains(text(),'Държавен вестник')]">
				<StateGazetteIssue><xsl:value-of select="normalize-space(substring-after(ul[@class='frontList']/li[contains(text(),'Държавен вестник')],':'))"/></StateGazetteIssue>
			</xsl:if>
			<xsl:if test="ul[@class='frontList']/li[contains(text(),'Регистъра')]">
				<ProcurementRegistryNumber><xsl:value-of select="normalize-space(substring-after(ul[@class='frontList']/li[contains(text(),'Регистъра')],':'))"/></ProcurementRegistryNumber>
			</xsl:if>
			<Description>
				<xsl:for-each select="div[@class='markcontent']//text()">
					<xsl:value-of select="."/>
				</xsl:for-each>
			</Description>
		</PublicProcurement>
	</xsl:template>

</xsl:stylesheet>
