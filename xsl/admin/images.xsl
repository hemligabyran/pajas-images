<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<xsl:if test="/root/meta/controller">
				<xsl:call-template name="tab">
					<xsl:with-param name="href"      select="'images'" />
					<xsl:with-param name="text"      select="'List images'" />
				</xsl:call-template>

				<xsl:call-template name="tab">
					<xsl:with-param name="href"      select="'images/image'" />
					<xsl:with-param name="text"      select="'Add image'" />
					<xsl:with-param name="action"    select="'image'" />
					<xsl:with-param name="url_param" select="''" />
				</xsl:call-template>
			</xsl:if>
		</ul>
	</xsl:template>

	<xsl:template match="/">
		<xsl:if test="/root/content[../meta/action = 'index']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Images'" />
				<xsl:with-param name="h1"    select="'Images'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/content[../meta/action = 'image']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Image'" />
				<xsl:with-param name="h1"    select="'Image'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List Images -->
	<xsl:template match="content[../meta/action = 'index']">
		<xsl:for-each select="images/image">
			<a href="/admin/images/image?id={@id}" class="module" style="margin: 10px; border: solid 1px #ccc; padding: 5px; width: 156px; height: 200px;">
				<div style="height: 150px; width: 150px; padding: 3px; text-align: center;">
					<img src="/user_content/images/{filename}?maxheight=150&amp;maxwidth=150" alt="" style="border: none; margin-left: auto; margin-right: auto;" />
				</div>
				<p><xsl:value-of select="name" /></p>
			</a>
		</xsl:for-each>
	</xsl:template>

	<!-- Add or edit image -->
	<xsl:template match="content[../meta/action = 'image']">
		<form method="post" enctype="multipart/form-data" class="admin">
			<fieldset>
				<legend>
					<xsl:text>Image</xsl:text>
					<xsl:if test="/root/meta/url_param/id">#<xsl:value-of select="/root/meta/url_param/id" /></xsl:if>
				</legend>

				<xsl:if test="/root/meta/url_params/id">
					<p>Relative path: /user_content/images/<xsl:value-of select="formdata/field[@id = 'filename']" /></p>
				</xsl:if>

				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'name'" />
					<xsl:with-param name="label" select="'Name:'" />
				</xsl:call-template>

				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'filename'" />
					<xsl:with-param name="label" select="'Filename (if other than file):'" />
				</xsl:call-template>

				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'file'" />
					<xsl:with-param name="label" select="'File:'" />
					<xsl:with-param name="type"  select="'file'" />
				</xsl:call-template>

				<div style="clear: both;">
					<xsl:if test="/root/meta/url_params/id">
						<button class="longman negative" type="submit" name="action" value="rm">Delete</button>
					</xsl:if>
					<button class="longman positive" type="submit" name="action" value="save">Save</button>
				</div>

				<xsl:if test="/root/meta/url_params/id">
					<a href="/user_content/images/{formdata/field[@id = 'filename']}" style="display: block; clear: both; padding-top: 15px;">
						<img src="/user_content/images/{formdata/field[@id = 'filename']}?width=422" alt="" />
					</a>
				</xsl:if>

			</fieldset>
		</form>
	</xsl:template>

</xsl:stylesheet>