<?php
/*
Plugin Name: Rombus Shortcodes
Plugin URI: https://rombus.in/
Description: This is an extended plugin to going along with the Rombus Premium WordPress Theme.
Author: Jitendra Kumar Sahoo
Author URI: http://www.mercenie.com
Version: 1.5.0
License: GNU GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check Premium
function rmbs_pgn_check_premium_RMS() {
	global $rm_options;
	$settings = get_option( 'rm_options', $rm_options );
	if( $settings['themelc']=='valid' && ( $settings['themexp']=='lifetime' || (strtotime( $settings['themexp'] ) > strtotime( date('Y-m-d H:i:s') )) ) ) {
		return true;
	} else {
		return false;
	}
}

if(rmbs_pgn_check_premium_RMS()) :
// Post Loop
function rmbs_pgn_m_postloop_shortcode($atts) {
	global $rm_options;
	$settings = get_option( 'rm_options', $rm_options );

	extract(shortcode_atts(array( 'class' => '','postcomb' => '','posttype' => '','taxonomytype' => '','taxonomylike' => '','fieldtype' => '','fieldlike' => '','termscomb' => '','termslike' => '','relation' => '','postperpage' => '','ignoresticky' => '','ordercomb' => '','orderby' => '','order' => '','metakey' => '','metatype' => '','metaquerycomb' => '','querykey' => '','querycompare' => '','querytype' => '','queryvalue' => ''), $atts));

	if($postcomb=='array') {$posttype = explode(',', $posttype);}
	if($termscomb=='array') {$termslike = explode(',', $termslike);}

	if($taxonomytype=='taxonomy') {
		$taxquery = array(
			'relation' => $relation,
		);
		$taxonomylike = explode('|', $taxonomylike);
		$fieldlike = explode('|', $fieldlike);
		$termslike = explode('|', $termslike);

		$i = 0;
		foreach ($taxonomylike as $key => $value) {
			$termslikearray = explode(',', $termslike[$i]);
			$arrayval = '';
			$arrayval = array(
				'taxonomy' => $taxonomylike[$i],
				'field' => $fieldlike[$i],
				'terms' => $termslikearray,
			);
			array_push($taxquery, $arrayval);
			$i++;
		}

		$args = array( 'post_type' => $posttype, 'tax_query' => $taxquery, 'posts_per_page' => $postperpage, 'ignore_sticky_posts' => $ignoresticky );
	} else {
		$args = array( 'post_type' => $posttype, $taxonomylike => $termslike, 'posts_per_page' => $postperpage, 'ignore_sticky_posts' => $ignoresticky );
	}

	if($ordercomb=='array') {
		$orderby = explode('|', $orderby);
		$order = explode('|', $order);

		$j = 0;
		$orderbyarray = array();
		foreach($orderby as $key => $value) {
			$orderbyj = $orderby[$j];
			$orderbyarray[$orderbyj] = $order[$j];
			$j++;
		}
		$args[orderby] = $orderbyarray;

		if (in_array('meta_value', $orderby) || in_array('meta_value_num', $orderby)) {
			$args[meta_key] = $metakey;
		}
	} else {
		$args[orderby] = $orderby;
		$args[order] = $order;

		if($orderby=='meta_value' || $orderby=='meta_value_num') {
			$args[meta_key] = $metakey;
		}
	}

	$metaqueryarray = array();
	$querykey = explode('|', $querykey);
	$querycompare = explode('|', $querycompare);
	$querytype = explode('|', $querytype);
	$queryvalue = explode('|', $queryvalue);
	$k = 0;
	foreach ($querykey as $key => $value) {
		$mqueryvalue = explode(',', $queryvalue[$k]);
		$mqueryarray = array(
			'key' => $querykey[$k],
			'compare' => $querycompare[$k],
			'type' => $querytype[$k],
			'value' => $mqueryvalue
		);
		array_push($metaqueryarray, $mqueryarray);
		$k++;
	}
	if(!empty($metaqueryarray)) {
		$args[meta_query] = $metaqueryarray;
	}

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while( $the_query->have_posts() ) {
			$the_query->the_post();
			$classes = '';
			if( ($the_query->current_post+1)%3 == '0' ) { $classes .= ' t-mr0'; }
			if( ($the_query->current_post+1)%2 == '0' ) { $classes .= ' s-mr0'; }

			$category = get_the_category();
			if ( has_post_thumbnail() ) {
			$thumbimg = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'mid-thumb');
			$printthumbimg = $thumbimg[0];
			} else {
				if( $settings['default_feat_image_url'] ) {
					$printthumbimg = $settings['default_feat_image_url'];
				} else {
					$printthumbimg = esc_url( get_template_directory_uri() ).'/images/post-default-image.png';
				}
			}
			$return .= '
			<!-- Selected Post -->
			<div class="item '.$class.' '.$classes.'" itemscope itemtype="http://schema.org/Article">
			  <div class="item-img">
			    <a href="'.get_the_permalink().'" title="'.get_the_title().'"><img src="'.$printthumbimg.'" width="300" height="200" alt="'.get_the_title().'" itemprop="image"/></a>
			  </div>
			          <div class="item-text">
			    <a itemprop="url" href="'.get_the_permalink().'" title="'.get_the_title().'" class="item-main-link"><h2 itemprop="name">'.get_the_title().'</h2></a>

			            <div class="ncadvc-cont">
			                  <span class="ncadvc-category"><a href="'.get_category_link($category[0]->term_id ).'">'.$category[0]->cat_name.'</a></span>
			                  <span itemprop="author" class="ncadvc-author"><a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">'.get_the_author().'</a></span>
			                  <span> - </span>
			                  <time itemprop="dateCreated" class="ncadvc-date" datetime="'.get_the_time(c).'">'.get_the_time(get_option('date_format')).'</time>
			                  <meta itemprop="interactionCount" content="UserComments:'.get_comments_number(get_the_ID()).'"/>
			                  <span class="ncadvc-comment">'.get_comments_number().'</span>
			                  <meta itemprop="interactionCount" content="UserPageVisits:'.rmbs_getPostViews(get_the_ID()).'"/>
			                  <span class="ncadvc-view">'.rmbs_getPostViews(get_the_ID()).'</span>
			                  <div style="clear:both;"></div>
			              </div>

			              <p class="item-para">'.strip_tags(get_the_excerpt()).'</p>
			          </div>
			</div>
			<!-- Selected Post -->
			';
		}
	}

	return '<div id="loopcont" class="loopcont">'.$return.'<div style="clear:both;"></div></div>';
}
add_shortcode('postloop', 'rmbs_pgn_m_postloop_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Carousel
function rmbs_pgn_rm_carousel_group( $atts, $content )
{
    extract(shortcode_atts(array(
			'class' => '',
            'itemnum' => '',
			'slidespeed' => '',
			'pagspeed' => '',
			'rewspeed' => '',
			'autoplay' => '',
			'stoponhover' => '',
			'nav' => '',
			'rewnav' => '',
			'scrollppage' => '',
			'pag' => '',
			'pagnum' => '',
			'lazyload' => '',
			'autoheight' => '',
			'transtyle' => ''
    ), $atts));

    $GLOBALS['carousel_count'] = 0;
    $GLOBALS['carousels'] = '';

    do_shortcode( $content );

	if( is_array( $GLOBALS['carousels'] ) ){
    	foreach( $GLOBALS['carousels'] as $k=>$content ){
			$contents[] = '<div '.( $content['id'] != '' ? 'id="'.$content['id'].'"' : '' ).' class="carousel '.$content['class'].'">'.$content['content'].'</div>';
    	}
			$six_digit_random_number = mt_rand(100000, 999999);
			$carousel_script = '
<script type="text/javascript">
$(document).ready(function() {
			$("#carousel'.$six_digit_random_number.'").owlCarousel({
			    items : '.$itemnum.',

			    slideSpeed : '.$slidespeed.',
			    paginationSpeed : '.$pagspeed.',
			    rewindSpeed : '.$rewspeed.',

			    autoPlay : '.$autoplay.',
			    stopOnHover : '.$stoponhover.',

			    navigation : '.$nav.',
			    rewindNav : '.$rewnav.',
			    scrollPerPage : '.$scrollppage.',

			    pagination : '.$pag.',
			    paginationNumbers: '.$pagnum.',

			    lazyLoad : '.$lazyload.',

			    autoHeight : '.$autoheight.',

			    transitionStyle : '.$transtyle.',
			});
});
</script>
			';

		$return = "\n".'<div id="carousel'.$six_digit_random_number.'" class="carousels '.$class.'">'.implode( "\n", $contents ).'</div>'."\n";
	}

    return $carousel_script.$return;
}
function rmbs_pgn_rm_carousel( $atts, $content )
{
    extract(shortcode_atts(array(
			'id' => '',
			'class' => ''
    ), $atts));

    $x = $GLOBALS['carousel_count'];

    $GLOBALS['carousels'][$x] = array( 'id' => $id, 'class' => $class, 'content' => do_shortcode($content) );

    $GLOBALS['carousel_count']++;
}
add_shortcode( 'carouselgroup', 'rmbs_pgn_rm_carousel_group' );
add_shortcode( 'carousel', 'rmbs_pgn_rm_carousel' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Content
function rmbs_pgn_rm_content_group( $atts, $content )
{
    extract(shortcode_atts(array(
	        'id' => '',
			'class' => '',
            'bgimageurl' => '',
			'colortype' => '',
			'headingtype' => '',
			'hweight' => '',
			'parafont' => '',
			'align' => '',
			'bgposition' => '',
			'bgopacity' => '',
			'leftrightpadd' => '',
			'topbottompadd' => '',
			'itemnumber' => '',
			'itemcol' => ''
    ), $atts));
	$opacity = '';
	if($bgopacity=='yes'){$opacity='<div class="fopacity"></div>';}

    $GLOBALS['content_count'] = 0;
    $GLOBALS['contents'] = '';

    do_shortcode( $content );

	if( is_array( $GLOBALS['contents'] ) ){
    	foreach( $GLOBALS['contents'] as $k=>$content ){
			$title = $para = $img = $pftext = '';
			if($parafont!==''){$pftext='style="font-size:'.$parafont.'px;"';}
			if($content['ticon']!=='none'){
				wp_enqueue_style( 'rm_font_awesome_css', get_template_directory_uri().'/styles/font-awesome.css', array(), wp_get_theme()->get( 'Version' ), 'all' );
			}
			if($content['title']!==''){$title='<'.$headingtype.' class="'.$headingtype.'ftitle '.$hweight.' '.$content['ticon'].'">'.$content['title'].'</'.$headingtype.'>';}
			if($content['url']!==''){$title='<a href="'.$content['url'].'" class="ftitlelink '.$content['titlecolor'].' '.$content['ticoncolor'].'">'.$title.'</a>';}
			if($content['imgurl']!==''){$img='<img class="fimg" src="'.$content['imgurl'].'" alt="" />';}
			if($content['content']!==''){$para='<div class="fpara" '.$pftext.'>'.$content['content'].'</div>';}
			$contents[] = '<div '.( $content['id'] != '' ? 'id="'.$content['id'].'"' : '' ).' class="fitem '.$content['animate'].' '.$content['class'].'">'.$img.$title.$para.'</div>';
    	}
		$return = "\n".'<div '.( $id != '' ? 'id="'.$id.'"' : '' ).' class="fullcontainer '.$colortype.' '.$align.' '.$bgposition.' '.$class.'" style="background-image: url('.$bgimageurl.');">'.$opacity.'<div class="fcontentholder"><div class="fcontent '.$itemnumber.' '.strtolower($leftrightpadd).' '.strtolower($topbottompadd).'" data-col="'.$itemcol.'">'.implode( "\n", $contents ).'<div class="fclear"></div></div></div></div>'."\n";
	}

    return $return;
}
function rmbs_pgn_rm_content( $atts, $content )
{
    extract(shortcode_atts(array(
            'title' => 'Content %d',
			'id' => '',
			'class' => '',
			'url' => '',
			'imgurl' => '',
			'titlecolor' => '',
			'ticoncolor' => '',
			'ticon' => '',
			'animate' => ''
    ), $atts));

    $x = $GLOBALS['content_count'];

    $GLOBALS['contents'][$x] = array( 'title' => sprintf( $title, $GLOBALS['content_count'] ), 'id' => $id, 'class' => $class, 'url' => $url, 'imgurl' => $imgurl, 'titlecolor' => $titlecolor, 'ticoncolor' => $ticoncolor, 'ticon' => $ticon, 'animate' => $animate, 'content' => do_shortcode($content) );

    $GLOBALS['content_count']++;
}
add_shortcode( 'contentgroup', 'rmbs_pgn_rm_content_group' );
add_shortcode( 'content', 'rmbs_pgn_rm_content' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Slide
function rmbs_pgn_rm_slide_group( $atts, $content )
{
    extract(shortcode_atts(array(
            'width' => '',
			'height' => '',
			'play' => '',
			'auto' => ''
    ), $atts));

    $GLOBALS['slide_count'] = 0;

    do_shortcode( $content );

	wp_enqueue_script( 'rm_jquery_slides_min_js', get_template_directory_uri().'/scripts/jquery.slides.min.js', array(), wp_get_theme()->get( 'Version' ), false );

	if( is_array( $GLOBALS['slides'] ) ){
    	foreach( $GLOBALS['slides'] as $k=>$slide ){
			$slides[] = '<img src="'.$slide['imgurl'].'" alt="'.$slide['imgalt'].'" />';
    	}
		$return = "\n".'<div class="slides">'.implode( "\n", $slides ).'</div><script>$(function() {$(".slides").slidesjs({width: '.$width.',height: '.$height.',play: {active: '.$play.',auto: '.$auto.',interval: 4000,swap: true}});});</script>'."\n";
	}

    return $return;
}
function rmbs_pgn_rm_slide( $atts, $content )
{
    extract(shortcode_atts(array(
            'title' => 'Content %d',
			'imgalt' => '',
			'imgurl' => ''
    ), $atts));

    $x = $GLOBALS['slide_count'];
    $GLOBALS['slides'][$x] = array( 'title' => sprintf( $title, $GLOBALS['slide_count'] ), 'imgalt' => $imgalt, 'imgurl' => $imgurl );

    $GLOBALS['slide_count']++;
}
add_shortcode( 'slidegroup', 'rmbs_pgn_rm_slide_group' );
add_shortcode( 'slide', 'rmbs_pgn_rm_slide' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Accordion
function rmbs_pgn_rm_accordion_group( $atts, $content )
{
    $GLOBALS['accordion_count'] = 0;

    do_shortcode( $content );

	if( is_array( $GLOBALS['accordions'] ) ){
    	foreach( $GLOBALS['accordions'] as $k=>$accordion ){
			$accordions[] = '<div class="accordion-section '.$accordion['active'].'"><a class="accordion-section-title" href="#accordion-'.$k.'">'.$accordion['title'].'</a><div id="accordion-'.$k.'" class="accordion-section-content acrd'.$accordion['open'].'">'.$accordion['content'].'</div></div>';
    	}
    	$return = "\n".'<div class="accordion">'.implode( "\n", $accordions ).'</div>'."\n";
	}
    return $return;
}
function rmbs_pgn_rm_accordion( $atts, $content )
{
    extract(shortcode_atts(array(
            'title' => 'Accordion %d',
			'active' => ''
    ), $atts));

    $x = $GLOBALS['accordion_count'];
	if($active=='active'){$open = 'open';}else{$open = 'close';}
    $GLOBALS['accordions'][$x] = array( 'title' => sprintf( $title, $GLOBALS['accordion_count'] ), 'active' => $active, 'open' => $open, 'content' =>  do_shortcode($content) );

    $GLOBALS['accordion_count']++;
}
add_shortcode( 'accordiongroup', 'rmbs_pgn_rm_accordion_group' );
add_shortcode( 'accordion', 'rmbs_pgn_rm_accordion' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Tab
function rmbs_pgn_rm_tab_group( $atts, $content )
{
	extract(shortcode_atts(array( 'type' => ''), $atts));
	$typeclass = '';
	if($type=='horizontal'){$typeclass = '';}
	if($type=='verticalleft'){$typeclass = 'verticaltabs';}
	if($type=='verticalright'){$typeclass = 'verticaltabs rightvtabs';}

    $GLOBALS['tab_count'] = 0;

    do_shortcode( $content );

	if( is_array( $GLOBALS['tabs'] ) ){
    	foreach( $GLOBALS['tabs'] as $k=>$tab ){
        	$tabs[] = '<li class="li'.$tab['active'].'"><a href="#tab'.$k.'">'.$tab['title'].'</a></li>';
        	$panes[] = '<div id="tab'.$k.'" class="tab tab'.$tab['active'].'">'.$tab['content'].'</div>';
    	}
    	$return = "\n".'<div class="tabs '.$typeclass.'"><ul class="tab-links">'.implode( "\n", $tabs ).'</ul>'."\n".'<div class="tab-content">'.implode( "\n", $panes ).'</div><div style="clear: both;"></div></div>'."\n";
	}
    return $return;
}
function rmbs_pgn_rm_tab( $atts, $content )
{
    extract(shortcode_atts(array(
            'title' => 'Tab %d',
			'active' => ''
    ), $atts));

    $x = $GLOBALS['tab_count'];
    $GLOBALS['tabs'][$x] = array( 'title' => sprintf( $title, $GLOBALS['tab_count'] ), 'active' => $active, 'content' =>  do_shortcode($content) );

    $GLOBALS['tab_count']++;
}
add_shortcode( 'tabgroup', 'rmbs_pgn_rm_tab_group' );
add_shortcode( 'tab', 'rmbs_pgn_rm_tab' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// List
function rmbs_pgn_rm_list_group( $atts, $content )
{
	extract(shortcode_atts(array( 'heading' => '', 'order' => '' ), $atts));

    $GLOBALS['list_count'] = 0;

    do_shortcode( $content );

	if( is_array( $GLOBALS['lists'] ) ){
		if($order=='ASC'){$show_count = 1;}
		if($order=='DESC'){$show_count = count($GLOBALS['lists']);}
    	foreach( $GLOBALS['lists'] as $k=>$list ){
        	$lists[] = '<div><'.$heading.'>'.$show_count.'. '.$list['title'].'</'.$heading.'>'.$list['content'].'</div>';

			if($order=='ASC'){$show_count += 1;}
			if($order=='DESC'){$show_count -= 1;}
    	}
    	$return = "\n".'<div class="lists">'.implode( "\n", $lists ).'</div>'."\n";
	}
    return $return;
}
function rmbs_pgn_rm_list( $atts, $content )
{
    extract(shortcode_atts(array(
            'title' => 'List %d'
    ), $atts));

    $x = $GLOBALS['list_count'];
    $GLOBALS['lists'][$x] = array( 'title' => sprintf( $title, $GLOBALS['list_count'] ), 'content' =>  do_shortcode($content) );

    $GLOBALS['list_count']++;
}
add_shortcode( 'listgroup', 'rmbs_pgn_rm_list_group' );
add_shortcode( 'list', 'rmbs_pgn_rm_list' );
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Ad
function rmbs_pgn_ad_shortcode($atts, $content = null) {
	global $rm_options;
	$settings = get_option( 'rm_options', $rm_options );
	extract(shortcode_atts(array( 'name' => '' ), $atts));
	$adsname = $name.'_show';
	$adcname = $name.'_code';

	if( $settings['all_ads'] == true && $settings[$adsname] == true) {
	return '<div class="ad-shortcode">'.$settings[$adcname].'</div>';
	}
}
add_shortcode('ad', 'rmbs_pgn_ad_shortcode');
endif;

// Quote
function rmbs_pgn_quote_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'position' => '' ), $atts));

	return '<p class="quote quote' . $position . '">'.do_shortcode($content).'</p>';
}
add_shortcode('quote', 'rmbs_pgn_quote_shortcode');

// Tips
function rmbs_pgn_tips_shortcode($atts, $content = null) {
	return '<p class="infobx tips default-text">'.do_shortcode($content).'</p>';
}
add_shortcode('tips', 'rmbs_pgn_tips_shortcode');

// Note
function rmbs_pgn_note_shortcode($atts, $content = null) {
	return '<p class="infobx note default-text">'.do_shortcode($content).'</p>';
}
add_shortcode('note', 'rmbs_pgn_note_shortcode');

// Warning
function rmbs_pgn_warning_shortcode($atts, $content = null) {
	return '<p class="infobx warning default-text">'.do_shortcode($content).'</p>';
}
add_shortcode('warning', 'rmbs_pgn_warning_shortcode');

if(rmbs_pgn_check_premium_RMS()) :
// Block
function block_shortcode($atts, $content = null) {
	return '<p class="infobx block default-text">'.do_shortcode($content).'</p>';
}
add_shortcode('block', 'rmbs_pgn_block_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Button
function rmbs_pgn_button_shortcode($atts, $content = null) {
	wp_enqueue_style( 'rm_font_awesome_css', get_template_directory_uri().'/styles/font-awesome.css', array(), wp_get_theme()->get( 'Version' ), 'all' );

	extract(shortcode_atts(array( 'url' => '','type' => '','color' => '','firstcolor' => '','secondcolor' => '','icon' => '','icontext' => '' ), $atts));

	$return = '';
	$six_digit_random_number = mt_rand(100000, 999999);

	if($color=='custom-color') {
		$return = '
<div style="display:none;"><style scoped>
.custom-color'.$six_digit_random_number.' {
	background-color: '.$firstcolor.' !important;
	border-color: '.$secondcolor.' !important;
	color: '.$secondcolor.' !important;
}
.custom-color'.$six_digit_random_number.':hover {
	background-color: '.$secondcolor.' !important;
	border-color: '.$secondcolor.' !important;
	color: '.($firstcolor==='transparent' ? '#ffffff' : $firstcolor).' !important;
}
</style></div>
';
		$color = $color.$six_digit_random_number;
	}

	if($url) {
		return $return.'<a href="'.$url.'" class="btn '.$type.' '.$color.' '.$icon.' '.$icontext.'">'.do_shortcode($content).'</a>';
	} else {
		return $return.'<div class="btn '.$type.' '.$color.' '.$icon.' '.$icontext.'">'.do_shortcode($content).'</div>';
	}
}
add_shortcode('button', 'rmbs_pgn_button_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Infobox
function rmbs_pgn_infobox_shortcode($atts, $content = null) {
	wp_enqueue_style( 'rm_font_awesome_css', get_template_directory_uri().'/styles/font-awesome.css', array(), wp_get_theme()->get( 'Version' ), 'all' );

	extract(shortcode_atts(array( 'type' => '','text' => '','icon' => '' ), $atts));

	return '<p class="infobx '.$type.' '.$text.' '.$icon.'">'.do_shortcode($content).'</p>';
}
add_shortcode('infobox', 'rmbs_pgn_infobox_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Code
function rmbs_pgn_encode_content($content) {
    $find_array = array( '&#91;', '&#93;' );
    $replace_array = array( '[', ']' );
    $content = preg_replace_callback( '|(.*)|isU', 'rmbs_pgn_pre_entities', trim( str_replace( $find_array, $replace_array, $content ) ) );
    $content = str_replace('#038;', '', $content);
    return $content;
}
function rmbs_pgn_pre_entities( $matches ) {
    return str_replace( $matches[1], htmlspecialchars( $matches[1]), $matches[0] );
}
function rmbs_pgn_code_shortcode($atts, $content = null) {
	global $code_name, $mode_name;

	$code_script_string = '';

	if($code_name!='codemirror') {
		$code_script_string .= '<script type="text/javascript" src="'.get_template_directory_uri().'/codemirror/lib/codemirror.js"></script>';
//		wp_enqueue_script('rm_codemirror_js', get_template_directory_uri().'/codemirror/lib/codemirror.js', array(), wp_get_theme()->get( 'Version' ), false);
	}
	wp_enqueue_style( 'rm_codemirror_css', get_template_directory_uri().'/codemirror/lib/codemirror.css', array(), wp_get_theme()->get( 'Version' ), 'all' );

	extract(shortcode_atts(array( 'title' => '','height' => '','linenumbers' => '','mode' => '','theme' => '' ), $atts));

	if($mode=='tiddlywiki' || $mode=='tiki') {
		wp_enqueue_style( 'rm_codemirror_'.$mode.'_css', get_template_directory_uri().'/codemirror/mode/'.$mode.'.css', array(), wp_get_theme()->get( 'Version' ), 'all' );
	}
	wp_enqueue_style( 'rm_codemirror_'.$theme.'_css', get_template_directory_uri().'/codemirror/theme/'.$theme.'.css', array(), wp_get_theme()->get( 'Version' ), 'all' );
	if($mode_name!=$mode) {
		$code_script_string .= '<script type="text/javascript" src="'.get_template_directory_uri().'/codemirror/mode/'.$mode.'.js"></script>';
//		wp_enqueue_script('rm_codemirror_'.$mode.'_js', get_template_directory_uri().'/codemirror/mode/'.$mode.'.js', array(), wp_get_theme()->get( 'Version' ), false);
		$mode_name = $mode;
	}

	$six_digit_random_number = mt_rand(100000, 999999);
	if($code_name!='codemirror') {
		$code_script_string .= '<script>
$(document).ready(function() {
$("pre.cdmrr").each(function() {
    var $this = $(this),
        $value = htmlspecialchars_decode($this.children("code").html());
		$mode = $this.data("mode");
		$theme = $this.data("theme");
		$line = $this.data("linenum");
	    $this.children("code").hide();
    var myCodeMirror = CodeMirror(this, {
		lineNumbers: $line,
        value: $value,
        mode: $mode,
		theme: $theme,
        readOnly: true
    });
});
});
</script>';
		$code_name = 'codemirror';
	}

	$content = str_replace('<br />', '', $content);
	$content = str_replace('<p>', '', $content);
	$content = str_replace('</p>', '', $content);

	return $code_script_string.'<div class="code-cont"><div class="sidebar-title code-title"><span>'.$title.'</span></div><div class="code-box" style="height:'.$height.'px;"><pre class="cdmrr" id="code'.$six_digit_random_number.'" data-mode="'.$mode.'" data-theme="'.$theme.'" data-linenum="'.$linenumbers.'" style="height:'.$height.'px;"><code>'.rmbs_pgn_encode_content($content).'</code></pre></div></div>';
}
add_shortcode('code', 'rmbs_pgn_code_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Vimeo
function rmbs_pgn_m_vimeo_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = 'width="'.$width.'"';
	if ($height) $show_height = 'height="'.$height.'"';

	return '<iframe src="http://player.vimeo.com/video/'.$show_id.'" '.$show_width.' '.$show_height.' frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
}
add_shortcode('vimeo', 'rmbs_pgn_m_vimeo_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Youtube
function rmbs_pgn_m_youtube_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => '', 'autoheight' => '' ), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = 'width:'.$width.'px;';
	if ($height) $show_height = 'height:'.$height.'px;';
	$ahcode = '';
	if ($autoheight=='on') { $ahcode = '<script type="text/javascript">
$(function() {
var div = document.getElementById("'.$show_id.'").clientWidth;
var ytwidth = (div*55.5)/100;
$("#'.$show_id.'").css("height", ytwidth);
});
</script>'; }

	return '<div class="youtube" id="'.$show_id.'" style="width:100%; '.$show_height.'"></div>'.$ahcode;
}
add_shortcode('youtube', 'rmbs_pgn_m_youtube_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Original Youtube
function rmbs_pgn_m_originalyoutube_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = ''.$width.'';
	if ($height) $show_height = ''.$height.'';

	return '<iframe width="'.$show_width.'" height="'.$show_height.'" src="https://www.youtube.com/embed/'.$show_id.'" frameborder="0" allowfullscreen></iframe>';
}
add_shortcode('originalyoutube', 'rmbs_pgn_m_originalyoutube_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Youtube with Intro
function rmbs_pgn_m_youtube_intro_shortcode($atts) {
	global $rm_options;
	$settings = get_option( 'rm_options', $rm_options );

	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = ''.$width.'';
	if ($height) $show_height = ''.$height.'';

	$six_digit_random_number = mt_rand(100000, 999999);

	return '
	<div id="player'.$six_digit_random_number.'"></div>
	<script src="http://www.youtube.com/player_api"></script>
	<script>

	    // create youtube player
	    var player;
	    function onYouTubePlayerAPIReady() {
	        player = new YT.Player("player'.$six_digit_random_number.'", {
	          height: "'.$show_height.'",
	          width: "'.$show_width.'",
	          videoId: "'.$settings[youtube_intro_one].'",
	          playerVars: {
	            "showinfo": 0
	          },
	          events: {
	            "onReady": onPlayerReady,
	            "onStateChange": onPlayerStateChange
	          }
	        });
	    }

	    // autoplay video
	    function onPlayerReady(event) {
	        event.target.playVideo();
	    }

	    // when video ends
	    var statechangecount = 0;
	    function onPlayerStateChange(event) {
	        if(event.data === 0) {
	            if(statechangecount === 0) {
	                player.loadVideoById("'.$show_id.'");
	                player.playVideo();
	                statechangecount++;
	            } else if(statechangecount === 1) {
	                player.loadVideoById("'.$settings[youtube_intro_two].'");
	                player.playVideo();
	                statechangecount++;
	            } else {
	                player.loadVideoById("'.$show_id.'");
	                player.playVideo();
	                player.stopVideo();
	            }
	        }
	    }
	</script>
	';
}
add_shortcode('youtubewithintro', 'rmbs_pgn_m_youtube_intro_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Slideshare
function rmbs_pgn_m_slideshare_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = 'width="'.$width.'"';
	if ($height) $show_height = 'height="'.$height.'"';

	return '<iframe src="http://www.slideshare.net/slideshow/embed_code/'.$show_id.'" '.$show_width.' '.$show_height.' frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC;border-width:1px 1px 0;margin-bottom:5px" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>';
}
add_shortcode('slideshare', 'rmbs_pgn_m_slideshare_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Scribd
function rmbs_pgn_m_scribd_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = 'width="'.$width.'"';
	if ($height) $show_height = 'height="'.$height.'"';

	return '<iframe class="scribd_iframe_embed" src="http://www.scribd.com/embeds/'.$show_id.'/content?start_page=1&view_mode=scroll" data-auto-height="false" data-aspect-ratio="1" scrolling="no" '.$show_width.' '.$show_height.' frameborder="0"></iframe>';
}
add_shortcode('scribd', 'rmbs_pgn_m_scribd_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// GooglePresentation
function rmbs_pgn_m_googlep_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '','width' => '','height' => ''), $atts));
	if ($id) $show_id = ''.$id.'';
	if ($width) $show_width = 'width="'.$width.'"';
	if ($height) $show_height = 'height="'.$height.'"';

	return '<iframe src="https://docs.google.com/presentation/embed?id='.$show_id.'&amp;start=false&amp;loop=false&amp;delayms=3000" frameborder="0" '.$show_width.' '.$show_height.' allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>';
}
add_shortcode('googlep', 'rmbs_pgn_m_googlep_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Recent Posts
function rmbs_pgn_recentposts_shortcode($atts) {
	extract(shortcode_atts(array( 'number' => '' ), $atts));

	$args = array( 'posts_per_page' => $number, 'ignore_sticky_posts' => 1 );
	$the_query = new WP_Query( $args );
	$return = '';
	while ( $the_query->have_posts() ) : $the_query->the_post();
		$return .= '<li><a href="'.get_the_permalink().'" title="'.__('Permalink to', 'rombus').' '.get_the_title().'"><h3>'.get_the_title().'</h3></a></li>';
	endwhile;
	wp_reset_query();
	return '<ul class="recentpost-shortcode">'.$return.'</ul>';
}
add_shortcode('recentposts', 'rmbs_pgn_recentposts_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Popular Posts
function rmbs_pgn_popularposts_shortcode($atts) {
	extract(shortcode_atts(array( 'number' => '' ), $atts));

	$args = array( 'orderby' => 'comment_count', 'posts_per_page' => $number, 'ignore_sticky_posts' => 1, 'year='.date('Y').'&w='.date('W') );
	$the_query = new WP_Query( $args );
	$return = '';
	while ( $the_query->have_posts() ) : $the_query->the_post();
		$return .= '<li><a href="'.get_the_permalink().'" title="'.__('Permalink to', 'rombus').' '.get_the_title().'"><h3>'.get_the_title().'</h3></a></li>';
	endwhile;
	wp_reset_query();
	return '<ul class="popularpost-shortcode">'.$return.'</ul>';
}
add_shortcode('popularposts', 'rmbs_pgn_popularposts_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Divider
function rmbs_pgn_divider_shortcode($atts) {
	return '<div class="divider"></div>';
}
add_shortcode('divider', 'rmbs_pgn_divider_shortcode');
endif;

if(rmbs_pgn_check_premium_RMS()) :
// Clear Float
function rmbs_pgn_clearfloat_shortcode($atts) {
	return '<div class="CF"></div>';
}
add_shortcode('CF', 'rmbs_pgn_clearfloat_shortcode');
endif;

// Fancy Gallery
function rmbs_pgn_fancygallery_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'type' => '' ), $atts));

	if(rmbs_pgn_check_premium_RMS()) :
	return '<div class="fancygallery '.$type.'">'.do_shortcode($content).'</div>';
	else :
	return do_shortcode($content);
	endif;
}
add_shortcode('fancygallery', 'rmbs_pgn_fancygallery_shortcode');
