{*?template charset=utf8?*}
{let gallery_limit=8
     gallery_pre_items=2
     gallery_post_items=2}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">

<head>
    <title>{$site.title} - {section name=Path loop=$module_result.path}{$Path:item.text}{delimiter} / {/delimiter}{/section}</title>

    <link rel="stylesheet" type="text/css" href={"stylesheets/core.css"|ezdesign} />
    <link rel="stylesheet" type="text/css" href={"stylesheets/admin.css"|ezdesign} />
    <link rel="stylesheet" type="text/css" href={"stylesheets/mycompany.css"|ezdesign} />

<!-- Javascript START -->

<script language="JavaScript">
<!--
{literal}
function OpenWindow ( URL, WinName, Features ) {
	popup = window.open ( URL, WinName, Features );
	if ( popup.opener == null ) {
		remote.opener = window;
	}
	popup.focus();
}
{/literal}

// -->
</script>

<!-- Javascript END -->

{* check if we need a http-equiv refresh *}
{section show=$site.redirect}
<meta http-equiv="Refresh" content="{$site.redirect.timer}; URL={$site.redirect.location}" />
{/section}

<!-- Meta information START -->

{section name=meta loop=$site.meta}
<meta name="{$meta:key}" content="{$meta:item}" />
{/section}

<meta name="MSSmartTagsPreventParsing" content="TRUE" />

<meta name="generator" content="eZ publish" />

<!-- Meta information END -->

</head>

<body>

<!-- Top box START -->

<img src={"toppmeny.gif"|ezimage} alt="" border="" USEMAP="#map" />

<map name="map">
<area shape="RECT" coords="0,2,72,23" href={"content/view/full/32/"|ezurl}>
<area shape="RECT" coords="75,2,142,25" href={"content/view/full/26/"|ezurl}>
<area shape="RECT" coords="145,2,217,23" href={"content/view/full/82/"|ezurl}>
<area shape="RECT" coords="221,1,283,23" href={"content/view/full/62/"|ezurl}>
</map>
<br />

{let folder_list=fetch(content,list,hash(parent_node_id,158,sort_by,array(array(priority))))}


<table width="700" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td colspan="2">
    <img src={"mycompanylogo.jpg"|ezimage} alt="My company - business" /></td>
</tr>
<tr>
    <td colspan="2" bgcolor="#e4eaf3">
    <table width="700" border="0" cellspacing="2" cellpadding="2">
    <tr>
{section name=Folder loop=$folder_list}
        <td align="center">
        &nbsp;<a class="small" href={concat("/content/view/full/",$Folder:item.node_id,"/")|ezurl}>{$Folder:item.name}</a>  <font size="2">&nbsp;</font>
        </td>
{/section}
        <td align="right">
	<input type="text" size="10"/>&nbsp;
        </td>
    </tr>
    </table>    
    </td>
</tr>
<tr>
    <td valign="top">
    &nbsp;
     {section name=Path loop=$module_result.path}
        {section show=$Path:item.url}
        <a class="small" href="{$Path:item.url}">{$Path:item.text}</a>
        {section-else}
	<span class="small">{$Path:item.text}</span>
        {/section}

        {delimiter}
        <span class="small">/</span>
        {/delimiter}
    {/section}
    <table width="100%" border="0" alt="" cellpadding="0" cellspacing="10">
    <tr>
        <td valign="top" width="100">
	Item 1<br />
	Item 2
        </td>

        <td valign="top">
	{$module_result.content}
        </td>
    </tr>
    </table>
    </td>
    <td width="204" valign="top">
    <table width="100%"  bgcolor="#e4eaf3" border="0" alt="" cellpadding="0" cellspacing="1">
    <tr>
        <td>
        <img width="204" height="116" src={"speed.jpg"|ezimage} alt="" /><br />
        </td>
    </tr>
    <tr>
        <td bgcolor="#e4eaf3">
	&nbsp;<b>News</b>
        </td>
    </tr>
    <tr>
        <td bgcolor="#ffffff">
	lings<br />
	lings<br />
	lings<br />
	lings<br />
	lings<br />
        </td>
    </tr>
    </td>
</tr>
</table>

</body>
</html>
{/let}
{/let}