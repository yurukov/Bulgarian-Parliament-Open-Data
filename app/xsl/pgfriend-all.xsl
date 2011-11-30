<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/GFriends">
		<FriendshipGroups>
			<xsl:apply-templates select="FriendshipGroup">
				<xsl:sort select="@id" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</FriendshipGroups>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="Members">
		<xsl:copy>
			<xsl:attribute name="count">
				<xsl:value-of select="count(MP)"/>
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
