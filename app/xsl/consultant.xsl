<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="type"/>

	<xsl:template match="table">
		<ParliamentConsultants>
			<xsl:apply-templates select="//td[@colspan='3']">
				<xsl:sort select="a/@href" data-type="number" order="ascending"/>
			</xsl:apply-templates>
		</ParliamentConsultants>
	</xsl:template>

	<xsl:template match="td[@colspan='3']">
		<xsl:choose>
	  		<xsl:when test="$type='mp'">
				<MP>
					<xsl:attribute name="id">
						<xsl:value-of select="a/@href"/>
					</xsl:attribute>
					<FullName><xsl:value-of select="normalize-space(a)"/></FullName>
					<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',a/@href,'.xml')"/></DataUrl>
					<Consultants>
						<xsl:variable name="f" select="parent::tr/following-sibling::tr[@class]/following-sibling::tr"/>
						<xsl:apply-templates select="parent::tr/following-sibling::tr[not(@class or .=$f)]" />
					</Consultants>
				</MP>
	  		</xsl:when>
	  		<xsl:when test="$type='pgroup'">
				<PGroup>
					<xsl:attribute name="id">
						<xsl:value-of select="a/@href"/>
					</xsl:attribute>
					<FullName><xsl:value-of select="normalize-space(a)"/></FullName>
					<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/mp/mp_',a/@href,'.xml')"/></DataUrl>
					<Consultants>
						<xsl:variable name="f" select="parent::tr/following-sibling::tr[@class]/following-sibling::tr"/>
						<xsl:apply-templates select="parent::tr/following-sibling::tr[not(@class or .=$f)]" />
					</Consultants>
				</PGroup>
	  		</xsl:when>
	  		<xsl:when test="$type='pcommittee'">
				<PCommittee>
					<xsl:attribute name="id">
						<xsl:value-of select="a/@href"/>
					</xsl:attribute>
					<FullName><xsl:value-of select="normalize-space(a)"/></FullName>
					<Consultants>
						<xsl:variable name="f" select="parent::tr/following-sibling::tr[@class]/following-sibling::tr"/>
						<xsl:apply-templates select="parent::tr/following-sibling::tr[not(@class or .=$f)]" />
					</Consultants>
				</PCommittee>
	  		</xsl:when>
	  		<xsl:otherwise/>
		</xsl:choose>
		
	</xsl:template>

	<xsl:template match="tr">
		<Consultant>
			<Name><xsl:value-of select="normalize-space(td[1]/node()[2])"/></Name>
			<Education><xsl:value-of select="normalize-space(td[2])"/></Education>
			<Field><xsl:value-of select="normalize-space(td[3])"/></Field>
		</Consultant>
	</xsl:template>

</xsl:stylesheet>
