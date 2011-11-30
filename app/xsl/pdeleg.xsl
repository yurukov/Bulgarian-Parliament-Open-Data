<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="date"/>

	<xsl:variable name="id" select="substring-after(/Deleg/div[1]/div/form/@action,'members/')"/>

	<xsl:template match="/Deleg">
		<ParliamentDelegation>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<xsl:attribute name="lastUpdate">
				<xsl:choose>
					<xsl:when test="$date=''">
						<xsl:value-of select="//update_max/@date"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$date"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="$date=''">
				<xsl:attribute name="active">1</xsl:attribute>
			</xsl:if>
			<PDelegationName><xsl:value-of select="div[1]/div[@class='articletitle']"/></PDelegationName>
			<ParliamentDelegationUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/pdeleg/pdeleg_',$id,'.xml')"/></ParliamentDelegationUrl>
			<ProfileUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/parliamentarydelegations/members/',$id)"/></ProfileUrl>
			<Members>
				<xsl:apply-templates select="div[1]/div[@class='MPBlock']">
					<xsl:sort select="div/a/img/@alt" data-type="text" order="ascending"/>
				</xsl:apply-templates>
			</Members>
			<Updates>
				<xsl:apply-templates select="//update[not($date=@date or ($date='' and //update_max/@date=@date))]">
					<xsl:sort select="concat(substring(@date,7),'-',substring(@date,4,2),'-',substring(@date,1,2))" data-type="text" order="descending"/>
				</xsl:apply-templates>
			</Updates>
		</ParliamentDelegation>
	</xsl:template>

	<xsl:template match="div[@class='MPBlock']">
		<MP>
			<xsl:variable name="id" select="substring(div/div/a[not(@class)]/@href,8)"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<FullName><xsl:value-of select="normalize-space(div/a/img/@alt)"/></FullName>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',$id,'.xml')"/></DataUrl>
		
			<Role>
				<xsl:attribute name="from">
					<xsl:value-of select="normalize-space(substring(normalize-space(div/div[@class='MPinfo']/br[1]/following-sibling::node()[1]),1,11))"/>
				</xsl:attribute>
				<xsl:variable name="to" select="normalize-space(substring(normalize-space(div/div[@class='MPinfo']/br[1]/following-sibling::node()[1]),13))"/>
				<xsl:if test="$to!='до момента'">
					<xsl:attribute name="to">
						<xsl:value-of select="$to"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="div/div[@class='MPinfo']/strong"/>
			</Role>
		</MP>
	</xsl:template>

	<xsl:template match="update">
		<Update>
			<xsl:attribute name="date">
				<xsl:value-of select="@date"/>
			</xsl:attribute>
			<xsl:variable name="date-file" select="concat(substring(@date,1,2),'.',substring(@date,4,2),'.',substring(@date,7))"/>
			<ParliamentDelegationUpdateUrl>
				<xsl:choose>
					<xsl:when test="@date=//update_max/@date">
						<xsl:value-of select="concat('http://parliament.yurukov.net/data/pdeleg/pdeleg_',$id,'.xml')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="concat('http://parliament.yurukov.net/data/pdeleg/pdeleg_',$id,'_',$date-file,'.xml')"/>
					</xsl:otherwise>
				</xsl:choose>
			</ParliamentDelegationUpdateUrl>
		</Update>
	</xsl:template>

</xsl:stylesheet>
