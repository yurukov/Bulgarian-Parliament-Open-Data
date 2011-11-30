<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/sit">
		<ParliamentCommitteeSitting>
			<xsl:attribute name="id">
				<xsl:value-of select="@sitid"/>
			</xsl:attribute>
			<ParliamentCommittee>
				<xsl:attribute name="id">
					<xsl:value-of select="@id"/>
				</xsl:attribute>
				<PCommName><xsl:value-of select="div[1]/div[@class='marktitle']/text()[1]"/></PCommName>
				<ParliamentCommitteeUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/pcomm/pcomm_',@id,'.xml')"/></ParliamentCommitteeUrl>
			</ParliamentCommittee>
			<ParliamentCommitteeSittingUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/pcommsit/pcommsit_',@id,'_',@sitid,'.xml')"/></ParliamentCommitteeSittingUrl>
			<DateTime><xsl:value-of select="normalize-space(div[1]/div[@class='marktitle']/div[@class='dateclass']/node()[1])"/></DateTime>
			<Location><xsl:value-of select="normalize-space(div[1]/div[@class='marktitle']/div[@class='dateclass']/br[1]/following-sibling::node()[1])"/></Location>
			<DiscussionPoints>
				<xsl:apply-templates select="div[1]/div[@class='markcontent']/text()"/>
			</DiscussionPoints>
			<Transcript><xsl:value-of select="concat('http://www.parliament.bg',div[1]/ul/li/a[contains(@href,'steno/ID')]/@href)"/></Transcript>
			<Reports>
				<xsl:if test="contains(div[1]/ul/li/a/@href,'reports/ID')">
					<xsl:apply-templates select="div[1]/ul/li/a[contains(@href,'reports/ID')]"/>
				</xsl:if>
			</Reports>
			<AttendingMPs>
				<xsl:apply-templates select="div[1]/ol/li">
					<xsl:sort select="substring(a/@href,8)" data-type="number" order="ascending"/>
				</xsl:apply-templates>
			</AttendingMPs>
		</ParliamentCommitteeSitting>
	</xsl:template>

	<xsl:template match="div[@class='markcontent']/text()">
		<xsl:variable name="prevOrd" select="number(substring-before(preceding-sibling::text()[not(string(number(substring-before(.,'.')))='NaN')][1],'.'))"/>
		<xsl:variable name="currOrd" select="number(substring-before(.,'.'))"/>
		<xsl:if test="not(string($currOrd)='NaN') and (string($prevOrd)='NaN' or $prevOrd+1=$currOrd)">
			<DiscussionPoint>
				<xsl:attribute name="order">
					<xsl:value-of select="$currOrd"/>
				</xsl:attribute>
				<xsl:value-of select="normalize-space(substring-after(.,'.'))"/>
			</DiscussionPoint>
		</xsl:if>
	</xsl:template>

	<xsl:template match="ol/li">
		<MP>
			<xsl:variable name="id" select="substring(a/@href,8)"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<FullName><xsl:value-of select="normalize-space(a)"/></FullName>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',$id,'.xml')"/></DataUrl>
		</MP>
	</xsl:template>

	<xsl:template match="ul/li/a[contains(@href,'reports/ID')]">
		<Report>
			<xsl:variable name="id" select="substring-after(@href,'reports/ID/')"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<xsl:if test="//report[@id=$id]">
				<Title><xsl:value-of select="normalize-space(//report[@id=$id])"/></Title>
			</xsl:if>
			<ReportUrl><xsl:value-of select="concat('http://www.parliament.bg',@href)"/></ReportUrl>
		</Report>
	</xsl:template>
</xsl:stylesheet>
