<?php

$plugin['name'] = 'soo_modified';
$plugin['version'] = '0.1.0';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Tags for article modification dates';
$plugin['type'] = 0; 

@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

function soo_if_modified ( $atts, $thing )
{
	assert_article();
	
	extract(lAtts(array(
		'format'	=>  '',
	), $atts));
	
	if ( ! $format )
	{
		global $id, $c, $pg, $dateformat, $archive_dateformat;
		$format = ( $id or $c or $pg ) ? $archive_dateformat : $dateformat;		
	}
	
	global $thisarticle;
	
	$modified = safe_strftime($format, $thisarticle['modified']);
	$posted = safe_strftime($format, $thisarticle['posted']);
	
	return parse(EvalElse($thing, $modified != $posted));
}

function soo_if_modified_author ( $atts, $thing )
{
	assert_article();
	global $thisarticle;
	
	extract(lAtts(array(
		'id'	=>  '',
		'name'	=>  '',
	), $atts));
	
	if ( $name )
	{
		if (!$authorid = fetch('name','txp_users','RealName', doSlash($name)))
			trigger_error(gTxt('tag_error') . ' ' . __FUNCTION__ . ' ' 
				. gTxt('unknown_author', array('name' => $name)));
	}
	elseif ( $id )
	{
		if ( ! safe_count('txp_users', 'name = "' . doSlash($id) . '"') )
			trigger_error(gTxt('tag_error') . ' ' . __FUNCTION__ . ' ' 
				. gTxt('unknown_author', array('name' => $id)));
		$authorid = $id;
	}
	else
		$authorid = $thisarticle['authorid'];
	
	$mod_id = fetch('LastModID','textpattern','ID', $thisarticle['thisid']);
	
	return parse(EvalElse($thing, $mod_id == $authorid));
}

function soo_modified_author ( $atts )
{
	assert_article();
	global $thisarticle;
	extract(lAtts(array(
		'fullname'	=>  1,
	), $atts));
	
	$author_id = fetch('LastModID','textpattern','ID', $thisarticle['thisid']);
	
	return $fullname ? get_author_name($author_id) : $author_id;
}

# --- END PLUGIN CODE ---

if (0) {
?>
<!-- CSS SECTION
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
div#sed_help pre {padding: 0.5em 1em; background: #eee; border: 1px dashed #ccc;}
div#sed_help h1, div#sed_help h2, div#sed_help h3, div#sed_help h3 code {font-family: sans-serif; font-weight: bold;}
div#sed_help h1, div#sed_help h2, div#sed_help h3 {margin-left: -1em;}
div#sed_help h2, div#sed_help h3 {margin-top: 2em;}
div#sed_help h1 {font-size: 2.4em;}
div#sed_help h2 {font-size: 1.8em;}
div#sed_help h3 {font-size: 1.4em;}
div#sed_help h4 {font-size: 1.2em;}
div#sed_help h5 {font-size: 1em;margin-left:1em;font-style:oblique;}
div#sed_help h6 {font-size: 1em;margin-left:2em;font-style:oblique;}
div#sed_help li {list-style-type: disc;}
div#sed_help li li {list-style-type: circle;}
div#sed_help li li li {list-style-type: square;}
div#sed_help li a code {font-weight: normal;}
div#sed_help li code:first-child {background: #ddd;padding:0 .3em;margin-left:-.3em;}
div#sed_help li li code:first-child {background:none;padding:0;margin-left:0;}
div#sed_help dfn {font-weight:bold;font-style:oblique;}
div#sed_help .required, div#sed_help .warning {color:red;}
div#sed_help .default {color:green;}
</style>
# --- END PLUGIN CSS ---
-->
<!-- HELP SECTION
# --- BEGIN PLUGIN HELP ---
 <div id="sed_help">

 <div id="toc">

h2. Contents

* "Overview":#overview
* "soo_modified_author":#soo_modified_author
* "soo_if_modified":#soo_if_modified
* "soo_if_modified_author":#soo_if_modified_author
* "History":#history

 </div>

h1. soo_modified

h2(#overview). Overview

Inspired by the now-orphaned "ob1_modified":http://forum.textpattern.com/viewtopic.php?id=3710 plugin, *soo_modified* includes tags for dealing with article modification information. These supplement the basic function offered by the core "modified":http://textbook.textpattern.net/wiki/index.php?title=modified tag.

h2(#soo_modified_author). soo_modified_author

Output the name of the author who most recently modified the current article.

pre. <txp:soo_modified_author />

h3. Attributes

* @fullname@ _(boolean)_ Whether to output the author's full name or id. %(default)Default% 1, output the author's full name.

h2(#soo_if_modified). soo_if_modified

Conditional tag that compares the current article's modification date to its posted date.

pre. <txp:soo_if_modified>
...
<txp:else />
...
</txp:soo_if_modified>

h3. Attributes

* @format@ _("strftime() format string":http://us3.php.net/manual/en/function.strftime.php)_ Date format to use for comparing the dates. %(default)Defaults% to site preference for date format in the current context.

By using a date format that doesn't include the time of day, this tag will evaluate to true only if the last-modification stamp is on a different day than the posted stamp.

h2(#soo_if_modified_author). soo_if_modified_author

Conditional tag that compares the author who most recently modified the current article to the article's author, or to the specified author.

pre. <txp:soo_if_modified_author>
...
<txp:else />
...
</txp:soo_if_modified_author>

h3. Attributes

If neither attribute is used, the tag will compare the author who last modified the article to the author who posted it.

* @id@ _(Txp author id)_ Txp ID of author to compare. %(default)Default% empty.
* @name@ _(Txp author id)_ Full name of author to compare. (This must exactly match a full name from the txp_users table.) %(default)Default% empty.

h2(#history). Version History

h3. 0.1.0 (2010/12/31)

* Initial release
* @soo_modified_author@
* @soo_if_modified@
* @soo_if_modified_author@


 </div>
# --- END PLUGIN HELP ---
-->
<?php
}

?>