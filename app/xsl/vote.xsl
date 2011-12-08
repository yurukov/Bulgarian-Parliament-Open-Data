<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/table">
		<PlenaryVotes>
			<xsl:attribute name="date">
				<xsl:value-of select="substring-before(substring-after(groupvote/raw[2]/cell[1],' на '),' ')"/>
			</xsl:attribute>
			<xsl:attribute name="time">
				<xsl:value-of select="substring-after(substring-after(groupvote/raw[2]/cell[1],' на '),' ')"/>
			</xsl:attribute>
			<IndividualVotesOriginalUrl><xsl:value-of select="concat('http://www.parliament.bg/pub/StenD/iv',@id,'.xls')"/></IndividualVotesOriginalUrl>
			<GroupVotesOriginalUrl><xsl:value-of select="concat('http://www.parliament.bg/pub/StenD/gv',@id,'.xls')"/></GroupVotesOriginalUrl>
			<OriginalUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/vote/vote_',@id,'.xml')"/></OriginalUrl>
			<VotingPoint>
				<xsl:apply-templates select="groupvote/raw[substring(cell/text(),1,5)='Номер']"/>
			</VotingPoint>
			<MPVote>
				<xsl:apply-templates select="mpvote/raw[position()>2 and cell[2]]"/>
			</MPVote>
		</PlenaryVotes>
	</xsl:template>

	<xsl:template match="groupvote/raw">
		<VotingPoint>
			<xsl:attribute name="point">
				<xsl:value-of select="substring-before(substring-after(cell[1],'Номер ('),')')"/>
			</xsl:attribute>
			<xsl:attribute name="date">
				<xsl:value-of select="substring-before(substring-after(cell[1],' на '),' ')"/>
			</xsl:attribute>
			<xsl:attribute name="time">
				<xsl:value-of select="substring(substring-after(substring-after(cell[1],' на '),' '),1,5)"/>
			</xsl:attribute>
			<xsl:if test="substring(cell[1],1,9)='Номер (1)'">
				<xsl:text>Регистрация</xsl:text>
			</xsl:if>
			<xsl:if test="not(substring(cell[1],1,9)='Номер (1)')">
				<xsl:value-of select="substring-after(cell[1],'по тема ')"/>
			</xsl:if>
		</VotingPoint>
	</xsl:template>

	<xsl:template match="mpvote/raw">
		<MPVote>
			<xsl:attribute name="id">
				<xsl:value-of select="cell[3]"/>
			</xsl:attribute>
			<xsl:attribute name="pgroup">
				<xsl:value-of select="cell[4]"/>
			</xsl:attribute>
			<Name><xsl:value-of select="cell[1]"/></Name>
			<Votes>
				<xsl:for-each select="cell[position()>4 and .!='']">
					<Vote>
						<xsl:attribute name="point">
							<xsl:value-of select="position()"/>
						</xsl:attribute>
						<xsl:if test="position()=1">
							<xsl:attribute name="registration">1</xsl:attribute>
							<xsl:if test=".='П'">
								<xsl:attribute name="present">1</xsl:attribute>
							</xsl:if>
							<xsl:if test="not(.='П')">
								<xsl:attribute name="present">0</xsl:attribute>
							</xsl:if>
						</xsl:if>
						<xsl:if test="not(position()=1)">
							<xsl:attribute name="voted">
								<xsl:value-of select="."/>
							</xsl:attribute>
						</xsl:if>
					</Vote>
				</xsl:for-each>
			</Votes>
		</MPVote>
	</xsl:template>

</xsl:stylesheet>
