<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:variable name="id" select="substring-after(/FGroup/div[1]/div/form/@action,'members/')"/>

	<xsl:template match="/FGroup">
		<FriendshipGroup>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<PFriendshipGroupName><xsl:value-of select="div[1]/div[@class='articletitle']"/></PFriendshipGroupName>
			<FriendshipGroupUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/pgfriend/pgfriend_',$id,'.xml')"/></FriendshipGroupUrl>
			<ProfileUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/friendshipgroups/members/',$id)"/></ProfileUrl>
			<Members>
				<xsl:apply-templates select="div[1]/div[@class='MPBlock']">
					<xsl:sort select="div/a/img/@alt" data-type="text" order="ascending"/>
				</xsl:apply-templates>
			</Members>
		</FriendshipGroup>
	</xsl:template>

	<xsl:template match="div[@class='MPBlock']">
		<MP>
			<xsl:variable name="id" select="substring(div/div/a[not(@class)]/@href,8)"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<FullName><xsl:value-of select="normalize-space(div[@class='MPBlock_columns']/a/img/@alt)"/></FullName>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',$id,'.xml')"/></DataUrl>
			<Role><xsl:value-of select="div/div[@class='MPinfo']/strong"/></Role>
		</MP>
	</xsl:template>
</xsl:stylesheet>
