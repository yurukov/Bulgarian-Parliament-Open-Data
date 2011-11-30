<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/CommSits">
		<ParliamentCommitteeSittings>
			<xsl:apply-templates select="//ParliamentCommittee[not(string(@id)=preceding::ParliamentCommittee/@id)]"/>
		</ParliamentCommitteeSittings>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="ParliamentCommittee">
	<xsl:variable name="id" select="@id"/>
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
			<Sittings>
				<xsl:apply-templates select="//ParliamentCommitteeSitting[ParliamentCommittee/@id=$id]" >
					<xsl:sort select="@id" data-type="number" order="ascending"/>
				</xsl:apply-templates>
			</Sittings>
		</xsl:copy>
	</xsl:template>


	<xsl:template match="ParliamentCommitteeSitting">
		<xsl:copy>
			<xsl:apply-templates select="@*" />
			<xsl:attribute name="discussionPoints">
				<xsl:value-of select="count(DiscussionPoints/DiscussionPoint)"/>
			</xsl:attribute>
			<xsl:attribute name="reports">
				<xsl:value-of select="count(Reports/Report)"/>
			</xsl:attribute>
			<xsl:attribute name="attendingMPs">
				<xsl:value-of select="count(AttendingMPs/MP)"/>
			</xsl:attribute>
			<xsl:if test="Transcript/text()">
				<xsl:attribute name="transcript">1</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="dateTime">
				<xsl:value-of select="DateTime"/>
			</xsl:attribute>
			<xsl:value-of select="ParliamentCommitteeSittingUrl" />
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
