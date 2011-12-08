<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/PlenaryVotes">
		<xsl:copy>
			<xsl:apply-templates select="@*" />
			<xsl:attribute name="votingPoints">
				<xsl:value-of select="count(VotingPoints/VotingPoint)"/>
			</xsl:attribute>
			<xsl:attribute name="presentMPs">
				<xsl:value-of select="count(MPVotes/MPVote[.//@present='1'])"/>
			</xsl:attribute>
			<xsl:attribute name="allMPs">
				<xsl:value-of select="count(MPVotes/MPVote)"/>
			</xsl:attribute>
			<xsl:value-of select="OriginalUrl"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>	

</xsl:stylesheet>
