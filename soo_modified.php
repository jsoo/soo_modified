<?php
$plugin['version'] = '0.1.2';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Tags for article modification dates';
$plugin['type'] = 0; 
$plugin['allow_html_help'] = 1;

if (! defined('txpinterface')) {
    global $compiler_cfg;
    @include_once('config.php');
    @include_once($compiler_cfg['path']);
}

# --- BEGIN PLUGIN CODE ---

if(class_exists('\Textpattern\Tag\Registry')) {
    Txp::get('\Textpattern\Tag\Registry')
        ->register('soo_if_modified')
        ->register('soo_if_modified_author')
        ->register('soo_modified_author')
        ;
}

function soo_if_modified ($atts, $thing)
{
    assert_article();
    
    extract(lAtts(array(
        'format'    =>  '',
    ), $atts));
    
    if (! $format) {
        global $id, $c, $pg, $dateformat, $archive_dateformat;
        $format = ($id or $c or $pg) ? $archive_dateformat : $dateformat;     
    }
    
    global $thisarticle;
    
    $modified = safe_strftime($format, $thisarticle['modified']);
    $posted = safe_strftime($format, $thisarticle['posted']);
    
    return parse($thing, $modified != $posted);
}

function soo_if_modified_author ($atts, $thing)
{
    assert_article();
    global $thisarticle;
    
    extract(lAtts(array(
        'id' => '',
        'name' => '',
    ), $atts));
    
    static $users = array();

    if ($name and (! $authorid = array_search($name, $users))) {
        $user_id = fetch('name','txp_users', 'RealName', doSlash($name));
        if ( ! $user_id ) {
            trigger_error(gTxt('tag_error').' '.__FUNCTION__.' ' 
                .gTxt('unknown_author', array('name' => $name)));
            $user_id = 'unknown';
        }
        $authorid = $users[$user_id] = $name;
    } elseif ($id and (! $authorid = array_search($id, $users))) {
        if (! safe_count('txp_users', 'name = "'.doSlash($id).'"')) {
            trigger_error(gTxt('tag_error').' '.__FUNCTION__.' ' 
                .gTxt('unknown_author', array('name' => $id)));
        }
        $authorid = $users[$id] = $id;
    } else {
        $authorid = $thisarticle['authorid'];
    }
    
    $mod_id = _soo_modified_author_id($thisarticle['thisid']);
    
    return parse($thing, $mod_id == $authorid);
}

function soo_modified_author ($atts)
{
    assert_article();
    global $thisarticle;
    extract(lAtts(array(
        'fullname' => 1,
    ), $atts));
    
    $author_id = _soo_modified_author_id($thisarticle['thisid']);
    
    return $fullname ? get_author_name($author_id) : $author_id;
}

function _soo_modified_author_id ($article_id)
{
    static $mod_ids = array();
    if (empty($mod_ids[$article_id])) {
        $mod_ids[$article_id] = fetch('LastModID','textpattern','ID', $article_id);
    }
    return $mod_ids[$article_id];
}

# --- END PLUGIN CODE ---

?>
