<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
	<xsl:output method="xml" indent="yes" encoding="UTF-8"/>

	<xsl:param name="id"/>

	<xsl:template match="/body">
		<SittingProgram>
			<xsl:attribute name="id">
				<xsl:value-of select="@idProgram"/>
			</xsl:attribute>
			<xsl:attribute name="date">
				<xsl:value-of select="@date"/>
			</xsl:attribute>
			<SittingProgramUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_p_',@idProgram,'.xml')"/></SittingProgramUrl>
			<OriginalDataUrl><xsl:value-of select="concat('http://www.parliament.bg/bg/plenaryprogram/ID/',@idProgram)"/></OriginalDataUrl>
			<xsl:if test="@idControl and not(@idControl='')">
				<PlenaryControl>
					<xsl:attribute name="id">
						<xsl:value-of select="@idControl"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_c_',@idControl,'.xml')"/>
				</PlenaryControl>
			</xsl:if>
			<xsl:if test="@idSitting and not(@idSitting='')">
				<PleanarySitting>
					<xsl:attribute name="id">
						<xsl:value-of select="@idSitting"/>
					</xsl:attribute>
					<xsl:value-of select="concat('http://parliament.yurukov.net/data/plenaryst/plenaryst_',@idSitting,'.xml')"/>
				</PleanarySitting>
			</xsl:if>
			<Title><xsl:value-of select="normalize-space(div/div[@class='marktitle']/text()[1])"/></Title>
			<DiscussionPoints>
				<xsl:for-each select="div/ol/li">
					<DiscussionPoint>
						<xsl:attribute name="order">
							<xsl:value-of select="position()"/>
						</xsl:attribute>
						<xsl:if test="a">
							<xsl:attribute name="recommendedByGroup">
								<xsl:value-of select="substring-before(substring-after(a/@href,'members/'),'/')"/>
							</xsl:attribute>
						</xsl:if>
						<Topic>
							<xsl:if test="substring(normalize-space(text()),1,1)=':'">
								<xsl:value-of select="normalize-space(substring-after(text(),':'))"/>
							</xsl:if>
							<xsl:if test="not(substring(normalize-space(text()),1,1)=':')">
								<xsl:value-of select="normalize-space(text())"/>
							</xsl:if>
						</Topic>
						<xsl:if test="ul/li">	
							<Bills>
								<xsl:for-each select="ul/li">
									<Bill>
										<xsl:variable name="billid" select="substring-before(substring-after(a/@href,'ID/'),'/')"/>
										<xsl:attribute name="id">
											<xsl:value-of select="$billid"/>
										</xsl:attribute>
										<BillName><xsl:value-of select="normalize-space(a)"/></BillName>
										<BillUrl><xsl:value-of select="concat('http://parliament.yurukov.net/data/bill/bill_',$billid,'.xml')"/></BillUrl>			
									</Bill>
								</xsl:for-each>
							</Bills>
						</xsl:if>
					</DiscussionPoint>
				</xsl:for-each>
				<xsl:for-each select="div//p[@align='justify']">
					<DiscussionPoint>
						<xsl:attribute name="order">
							<xsl:value-of select="position()"/>
						</xsl:attribute>
						<xsl:if test="em and substring(em/text(),1,9)='(Вносител'">
							<xsl:attribute name="recommendedBy">
								<xsl:value-of select="em/text()"/>
							</xsl:attribute>
						</xsl:if>
						<Topic>
							<xsl:value-of select="normalize-space(text())"/>
						</Topic>
					</DiscussionPoint>
				</xsl:for-each>
			</DiscussionPoints>
		</SittingProgram>
	</xsl:template>

</xsl:stylesheet>
