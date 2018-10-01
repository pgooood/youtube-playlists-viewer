<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output media-type="text/html" method="html" omit-xml-declaration="yes" indent="no" encoding="utf-8"/>

<xsl:variable name="rowSize" select="3"/>

<xsl:template match="channel">
	<h1 class="mb-4 mt-5">
		<xsl:value-of select="@title"/>
		<xsl:text>&#160;</xsl:text>
		<small><a href="{@youtube-url}" target="_blank"><i class="fab fa-youtube"></i></a></small>
	</h1>
	<div class="row">
		<xsl:apply-templates select="playlist"/>
	</div>
</xsl:template>

<xsl:template match="channel/playlist">
	<!-- group by 3 per line -->
	<xsl:variable name="ps" select="count(preceding-sibling::playlist)"/>
	<xsl:if test="$ps &gt; 0 and $ps mod $rowSize = 0">
		<xsl:text disable-output-escaping="yes">&lt;/div&gt;&lt;div class="row"&gt;</xsl:text>
	</xsl:if>

	<div class="col-md-{12 div $rowSize}">
		<div class="card mb-3 shadow-sm">
			<xsl:apply-templates select="img"/>
			<div class="card-body">
				<xsl:apply-templates select="title | desc"/>
			</div>
		</div>
	</div>
</xsl:template>

<xsl:template match="channel/playlist/img">
	<a href="{parent::*/@url}">
		<img class="card-img-top" src="{@src}" alt="{parent::*/title}"/>
	</a>
</xsl:template>

<xsl:template match="channel/playlist/title">
	<h6 class="card-title">
		<a href="{parent::*/@url}">
			<xsl:value-of select="text()"/>
		</a>
	</h6>
</xsl:template>

<xsl:template match="channel/playlist/desc">
	<p class="card-text max-height-3">
		<xsl:value-of select="text()" disable-output-escaping="yes"/>
	</p>
</xsl:template>

</xsl:stylesheet>