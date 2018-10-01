<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output media-type="text/html" method="html" omit-xml-declaration="yes" indent="no" encoding="utf-8"/>

<xsl:template match="video">
	<nav aria-label="breadcrumb" class="mt-5">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="{channel/@url}">
				<xsl:value-of select="channel/@title"/>
			</a></li>
			<li class="breadcrumb-item"><a href="{playlist/@url}">
				<xsl:value-of select="playlist/@title"/>
			</a></li>
			<li class="breadcrumb-item active" aria-current="page">
				<xsl:value-of select="title"/>
			</li>
		</ol>
	</nav>
	<h2 class="h1 mb-4">
		<xsl:value-of select="title"/>
	</h2>
	<div class="card" style="max-width:800px;">
		<div class="embed-responsive embed-responsive-16by9">
			<xsl:value-of select="player" disable-output-escaping="yes"/>
		</div>
		<div class="card-body">
			<xsl:apply-templates select="desc"/>
		</div>
	</div>
</xsl:template>

<xsl:template match="video/desc">
	<p class="card-text">
		<xsl:value-of select="text()" disable-output-escaping="yes"/>
	</p>
</xsl:template>

</xsl:stylesheet>