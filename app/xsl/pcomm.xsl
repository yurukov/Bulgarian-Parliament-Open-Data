<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="date"/>

	<xsl:variable name="id" select="substring(/Comm/div[1]/div/form/@action,37)"/>

	<xsl:template match="/Comm">
		<ParliamentCommittee>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<xsl:attribute name="type">
				<xsl:value-of select="type"/>
			</xsl:attribute>
			<xsl:attribute name="update">
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
			<PCommName><xsl:value-of select="div[1]/div[@class='articletitle']"/></PCommName>
			<ParliamentCommitteeUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/pcomm/pcomm_',$id,'.xml')"/></ParliamentCommitteeUrl>
			<ProfileUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/parliamentarycommittees/members/',$id)"/></ProfileUrl>
			<Contact>
				<Email><xsl:value-of select="normalize-space(div[@type='info']/a)"/></Email>
				<Address><xsl:value-of select="normalize-space(div[@type='info']/br[1]/following-sibling::node()[1])"/></Address>
				<Telephone><xsl:value-of select="normalize-space(div[@type='info']/br[2]/following-sibling::node()[1])"/></Telephone>
				<ContactPerson><xsl:value-of select="normalize-space(div[@type='info']/br[3]/following-sibling::node()[1])"/></ContactPerson>
			</Contact>
			<Members>
				<xsl:apply-templates select="div[1]/div[@class='MPBlock']">
					<xsl:sort select="div/a/img/@alt" data-type="text" order="ascending"/>
				</xsl:apply-templates>
			</Members>
			<Documents>
				<xsl:apply-templates select="div[@type='doc']/div[@class='MProw']"/>
			</Documents>
			<Bills>
				<xsl:apply-templates select="div[@type='bills']/div[@class='MProw']"/>
			</Bills>
			<Updates>
				<xsl:apply-templates select="//update[not($date=@date or ($date='' and //update_max/@date=@date))]">
					<xsl:sort select="concat(substring(@date,7),'-',substring(@date,4,2),'-',substring(@date,1,2))" data-type="text" order="descending"/>
				</xsl:apply-templates>
			</Updates>
		</ParliamentCommittee>
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
			<ParliamentGroupUpdateUrl>
				<xsl:choose>
					<xsl:when test="@date=//update_max/@date">
						<xsl:value-of select="concat('http://parliament.yurukov.net/data/pgroup/pgroup_',$id,'.xml')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="concat('http://parliament.yurukov.net/data/pgroup/pgroup_',$id,'_',$date-file,'.xml')"/>
					</xsl:otherwise>
				</xsl:choose>
			</ParliamentGroupUpdateUrl>
		</Update>
	</xsl:template>

	<xsl:template match="div[@type='doc']/div[@class='MProw']">
		<Document>
			<xsl:attribute name="uploaded">
				<xsl:value-of select="normalize-space(div[@class='dateclass'])"/>
			</xsl:attribute>
			<Name>
				<xsl:value-of select="a"/>
			</Name>
			<DataUrl><xsl:value-of select="concat('http://www.parliament.bg',a/@href)"/></DataUrl>
		</Document>
	</xsl:template>

	<xsl:template match="div[@type='bills']/div[@class='MProw']">
		<Bill>
			<xsl:variable name="id" select="substring(a/@href,14)"/>
			<xsl:attribute name="id">
				<xsl:value-of select="$id"/>
			</xsl:attribute>
			<Name>
				<xsl:value-of select="a"/>
			</Name>
			<DataUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/bill/bill_',$id,'.xml')"/></DataUrl>
		</Bill>
	</xsl:template>

</xsl:stylesheet>
