<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# rewrite assets to cdn
function wpraiser_process_cdn($html) {
	
	# settings
	global $wpraiser_settings, $wpraiser_urls;
	
	# html must be an object
	if (!is_object($html)) {
		$nobj = 1;
		$html = str_get_html($html, true, true, 'UTF-8', false, PHP_EOL, ' ');
	}
	
	# images
	if(isset($wpraiser_urls['wp_domain']) && isset($wpraiser_settings['cdn']['url']) && isset($wpraiser_settings['cdn']['integration'])) {
		if(!empty($wpraiser_settings['cdn']['url']) && !empty($wpraiser_urls['wp_domain']) && !empty($wpraiser_settings['cdn']['integration'])) {
			$arr = wpraiser_string_toarray($wpraiser_settings['cdn']['integration']);
			if(is_array($arr) && count($arr) > 0) {
				foreach($html->find(implode(', ', $arr) ) as $elem) {
					
					# preserve some attributes but replace others
					if (is_object($elem) && isset($elem->attr)) {

						# get all attributes
						foreach ($elem->attr as $key=>$val) {
							
							# skip href attribute for links
							if($key == 'href' && stripos($elem->outertext, '<a ') !== false) { continue; }
							
							# skip certain attributes							
							if(in_array($key, array('id', 'class', 'action'))) { continue; }

							# replace other attributes
							$elem->{$key} = str_replace('//'.$wpraiser_urls['wp_domain'], '//'.$wpraiser_settings['cdn']['url'], $elem->{$key});
							$elem->{$key} = str_replace('\/\/'.$wpraiser_urls['wp_domain'], '\/\/'.$wpraiser_settings['cdn']['url'], $elem->{$key});
							
						}
						
					}

				}
			}
		}
	}
	
	
	# add CDN support to Styles, CSS and JS files
	if(isset($wpraiser_settings['cdn']['enable']) && $wpraiser_settings['cdn']['enable'] == true && 
	isset($wpraiser_settings['cdn']['url']) && !empty($wpraiser_settings['cdn']['url'])) {
		
		# css
		if(isset($wpraiser_settings['cdn']['enable_css']) && $wpraiser_settings['cdn']['enable_css'] == true) {
			
			# scheme + site url
			$fcdn = str_replace($wpraiser_urls['wp_domain'], $wpraiser_settings['cdn']['url'], $wpraiser_urls['wp_home']);
			
			# replace inside styles
			foreach($html->find('style') as $elem) {
				
				# fetch
				$css = $elem->outertext;
				
				# known replacements
				$css = str_ireplace('url(/wp-content/', 'url('.$fcdn.'/wp-content/', $css);
				$css = str_ireplace('url("/wp-content/', 'url("'.$fcdn.'/wp-content/', $css);
				$css = str_ireplace('url(\'/wp-content/', 'url(\''.$fcdn.'/wp-content/', $css);
				
				# save
				$elem->outertext = $css;
			
			}
			
			# replace link stylesheets
			foreach($html->find('link[rel=stylesheet], link[rel=preload]') as $elem) {
				if(isset($elem->href)) {
					$elem->href = str_replace($wpraiser_urls['wp_home'], $fcdn, $elem->href);
				}			
			}
		}
		
		# js
		if(isset($wpraiser_settings['cdn']['enable_js']) && $wpraiser_settings['cdn']['enable_js'] == true) {
			
			# replace script files
			foreach($html->find('script') as $elem) {
				
				# inline, escaped scripts
				if(!isset($elem->src) && !empty($elem->innertext) && stripos($elem->innertext, '/cache\/wpraiser\/min\/') !== false) {
					$elem->innertext = str_replace('\/\/'.$wpraiser_urls['wp_domain'], '\/\/'.$wpraiser_settings['cdn']['url'], $elem->innertext);
				}
				
				# js files
				if(isset($elem->src) && stripos($elem->src, $wpraiser_urls['wp_domain']) !== false) {
					$elem->src = str_replace('//'.$wpraiser_urls['wp_domain'], '//'.$wpraiser_settings['cdn']['url'], $elem->src);
				}
			}
				
		}
	}	
	
	
	# exclude CDN rewrite by uri path
	if(isset($wpraiser_settings['cdn']['skip_asset']) && !empty($wpraiser_settings['cdn']['skip_asset'])) {
		
		# get exclusions and proceed	
		$arr = advc_wpraiser_string_toarray($wpraiser_settings['cdn']['skip_asset']);
		if(is_array($arr)) {
		
			# get all urls that have been rewritten
			$find = array('link[rel=stylesheet], link[rel=preload], script[src]');
			$arr = wpraiser_string_toarray($wpraiser_settings['cdn']['integration']);
			if(is_array($arr) && count($arr) > 0) { $find = array_merge($find, $arr); }
			
			# list of replaced urls
			$newurls = array();
			
			# loop through all CDN replaced elements
			foreach($html->find(implode(', ', $find)) as $e) {
				
				# get all attributes
				if (is_object($e) && isset($e->attr)) {
					foreach ($e->attr as $key=>$v) {
						
						# only with cdn url
						if(stripos($key, $wpraiser_settings['cdn']['url']) !== false) {
							
							# undo cdn replacements on exclusion
							foreach ($arr as $a) {
							
								# exact match
								if($key == $a) { 
									$e->{$key} = str_replace('//'.$wpraiser_settings['cdn']['url'], '//'.$wpraiser_urls['wp_domain'], $e->{$key});
								}
								
								# match start and end
								if(substr($a, -1) == '*' && substr($a, 1) == '*') {
									if(stripos($key, trim($a, '*')) !== false) { 
										$e->{$key} = str_replace('//'.$wpraiser_settings['cdn']['url'], '//'.$wpraiser_urls['wp_domain'], $e->{$key});
									}
								} 
													
								# match start only
								if(substr($a, -1) == '*' && substr($a, 1) != '*') {
									if(substr($key, 0, strlen(trim($a, '*'))) == trim($a, '*')) { 
										$e->{$key} = str_replace('//'.$wpraiser_settings['cdn']['url'], '//'.$wpraiser_urls['wp_domain'], $e->{$key});
									}
								}
													
								# match ending only
								if(substr($a, -1) != '*' && substr($a, 1) == '*') {
									if(substr($key, -strlen(trim($a, '*'))) == trim($a, '*')) { 
										$e->{$key} = str_replace('//'.$wpraiser_settings['cdn']['url'], '//'.$wpraiser_urls['wp_domain'], $e->{$key});
									}
								}
							}							
							
						}
					
					}
				}
			}

		}
	}
	
	
	# convert html object to string, only when needed
	if(isset($nobj) && $nobj == 1) {
		$html = trim($html->save());
	}
	
	# return
	return $html;
}


#############################################################
# lazy load for images and youtube/vimeo iframes with lozad.js
#############################################################

# lazy load iframes and process responsive videos
function wpraiser_process_iframes($html) {
	
	# settings
	global $wpraiser_settings;
	
	# defaults
	$excl = array();
	$iframesclass = 'lazyloadiframe';
	
	# do not process under these situations
	$wpraiser_no_iframe_container = get_option('wpraiser_no_iframe_container');
	
	# get all exclusions
	if(isset($wpraiser_settings['lazy']['iframe_exc']) && !empty($wpraiser_settings['lazy']['iframe_exc'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['lazy']['iframe_exc']);
		foreach($html->find(implode(', ', $arr)) as $elem) {
			$excl[] = $elem->outertext;
		}
	}
	
	# get all iframes with src attributes that are not in the exclusions list 
	$iframes = array();
	foreach($html->find('iframe[src]') as $elem) {
		if(!in_array($elem->outertext, $excl)) {
			$iframes[] = $elem;
		}
	}
	
	# iframes
	foreach($iframes as $iframe) {
		
		# get tag
		$tag = $iframe->outertext;
				
		# skip if src attribute is escaped
		if(stripos($iframe->src, '\/') !== false) { continue; }
		
		# add lazy load class
		if(!isset($iframe->class) || (isset($iframe->class) && stripos($iframe->class, $iframesclass) === false)) {
			$iframe->class = trim($iframe->class . ' '.$iframesclass);
		}
		
		# iframes should always have an title attribute
		if(!isset($iframe->title) || (isset($iframe->title) && empty($iframe->title))) {
			$iframe->title = 'content';
		}
		
		# process src
		if(isset($iframe->src)) {
			$iframe->{'data-src'} = $iframe->src;
			$iframe->src = 'about:blank';
		}
		
		# height and width to inline style
		if(isset($iframe->height) && isset($iframe->width)) {
			$iframe->style = 'width:'.$iframe->width.';height:'.$iframe->height.';';
			unset($iframe->height);
			unset($iframe->width);
		}
		
		# add responsive 16:9 div wrapper
		if(isset($wpraiser_settings['lazy']['video_wrap_on']) && $wpraiser_settings['lazy']['video_wrap_on'] == true) {
			if(isset($wpraiser_settings['lazy']['video_wrap']) && !empty($wpraiser_settings['lazy']['video_wrap'])) {
				$arr = wpraiser_string_toarray($wpraiser_settings['lazy']['video_wrap']);
				foreach ( $arr as $rv ) {
					if(stripos($iframe->{'data-src'}, $rv) !== false) { 
						$iframe->class = str_ireplace('lazyloadiframe', 'lazyloadvideo', $iframe->class);
					}
				}
			}
		}
	}

	return $html;
}



# lazy load images
function wpraiser_process_images($html) {
	
	# settings
	global $wpraiser_settings;
	
	# get all exclusions
	$excl = array();
	if(isset($wpraiser_settings['lazy']['img_exc']) && !empty($wpraiser_settings['lazy']['img_exc'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['lazy']['img_exc']);
		$arr = array_merge($arr, array('picture img[src]')); # exclude picture elements for now
		foreach($html->find(implode(', ', $arr)) as $elem) {
			$excl[] = $elem->outertext;
		}
	}
	
	# get all images with src attribute that are not inside the exclusions list and not base64 encoded
	$imgs = array();
	foreach($html->find('img[src]') as $elem) {
		if(!in_array($elem->outertext, $excl) && stripos($elem->src, 'data:image') === false) {
			$imgs[] = $elem;
		}
	}
	
	# images
	foreach($imgs as $image) {
				
		# skip if src attribute is escaped
		if(stripos($image->src, '\/') !== false) { continue; }
		
		# images should always have an alt attribute
		if(!isset($image->alt) || (isset($image->alt) && empty($image->alt))) {
			$image->alt = 'image';
		}		
		
		# strip html tags from descriptions, titles and remove other garbage
		$cleanup = array('title', 'description', 'alt', 'data-image-description');
		foreach ($cleanup as $c) {
			if(isset($image->{$c})) { 
				$image->{$c} = htmlspecialchars(trim(strip_tags($image->{$c})), ENT_QUOTES); 
			}
		}	

		# remove exif data and image meta attributes
		if(isset($image->{'data-image-meta'})) {
			unset($image->{'data-image-meta'});
		}
		
		# remove default loading attribute
		if(isset($image->{'loading'})) {
			unset($image->{'loading'});
		}
				
		# process the src attribute
		if(isset($image->src)) {
			$image->{'data-src'} = $image->src;
			$image->src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		}
		
		# process images with the srcset attribute
		if(isset($image->srcset)) {
			$image->{'data-srcset'} = $image->srcset;
			unset($image->srcset);
		}
		
		# compare srcset with width attribute, and return the closest image size url
		if(isset($image->width) && is_numeric($image->width) && isset($image->{'data-srcset'}) && !empty($image->{'data-srcset'})) { 
		
			# closest
			$url = wpraiser_get_closest_image_size_from_srcset($image->{'data-srcset'}, $image->width);
			
			# not false or empty
			if($url !== false && !empty($url)) {
				if(isset($image->{'data-srcset'})) { unset($image->{'data-srcset'}); }
				if(isset($image->sizes)) { unset($image->sizes); }
				$image->{'data-src'} = $url;
			}
	
		}
		
		# add lazy load class
		if(!isset($image->class) || (isset($image->class) && stripos($image->class, 'lazyload') === false)) {
			$image->class = trim($image->class . ' lazyload');
		}
	
	}
	
	
	# handle picture elements here
	
	# get all exclusions again
	$excl = array();
	if(isset($wpraiser_settings['lazy']['img_exc']) && !empty($wpraiser_settings['lazy']['img_exc'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['lazy']['img_exc']);
		foreach($html->find(implode(', ', $arr)) as $elem) {
			$excl[] = $elem->outertext;
		}
	}

	# get all picture elements with src attribute that are not inside the exclusions 
	$pictures = array();
	foreach($html->find('picture') as $elem) {
		if(!in_array($elem->outertext, $excl)) {
			$pictures[] = $elem;
		}
	}
	
	# pictures
	foreach($pictures as $picture) {
		
		# must have or skip
		if(is_null($picture->find('img[src]')) || !isset($picture->find('img[src]', -1)->src)) {
			$picture->outertext = $picture->outertext;
			continue;
		}
		
		# skip data urls or escaped urls
		if(stripos($picture->find('img[src]', -1)->src, 'data:image') === false && 
		   stripos($picture->find('img[src]', -1)->src, '\/') !== false) {
			continue;
		}
				
		# alt from first img tag
		if(isset($picture->find('img[src]', -1)->alt) && !empty($picture->find('img[src]', -1)->alt)) {
			$imgalt = $picture->find('img[src]', -1)->alt; 
		} else {
			$imgalt = 'picture'; 
		}
		
		# get image url from first img tag
		$imgurl = $picture->find('img[src]', -1)->src;
		
		# add lazy load class to picture tag
		if(!isset($picture->class) || (isset($picture->class) && stripos($picture->class, 'lazyload') === false)) {
			$picture->class = trim($picture->class . ' lazyload');
		}
		
		# add minimum height to picture tag, for style correction during lazy loading
		if(!isset($picture->style) || (isset($picture->style) && stripos($picture->style, 'min-height') === false)) {
			$picture->style = trim($picture->style . ' display:block;min-height:1rem;');
		}
		
		# adjust the alt attribute
		if(!isset($picture->{'data-alt'}) || (isset($picture->{'data-alt'}) && empty($picture->{'data-alt'}))) {
			$picture->{'data-alt'} = $imgalt;
		}	
		
		# IE 11 compatibility, if available
		if(!isset($picture->{'data-iesrc'}) || (isset($picture->{'data-iesrc'}) && empty($picture->{'data-iesrc'}))) {
			$picture->{'data-iesrc'} = $imgurl;
		}
		
		# images inside picture elements
		foreach ($picture->find('img[src]') as $img) {
		
			# remove default src from img
			if(isset($img->src)) {
				$img->{'data-src'} = $img->src;
				$img->src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
			}
			
			# remove srcset from img
			if(isset($img->srcset)) {
				$img->{'data-srcset'} = $img->srcset;
				unset($img->srcset);
			}
			
			# remove sizes from img
			if(isset($img->sizes)) {
				$img->{'sizes'} = $img->sizes;
				unset($img->sizes);
			}
				
			# remove default loading attribute
			if(isset($img->loading)) {
				unset($img->loading);
			}
			
			# compare srcset with width attribute, and return the closest image size url
			if(isset($img->width) && is_numeric($img->width) && isset($img->{'data-srcset'}) && !empty($img->{'data-srcset'})) { 
			
				# closest
				$url = wpraiser_get_closest_image_size_from_srcset($img->{'data-srcset'}, $img->width);
				
				# not false or empty
				if($url !== false && !empty($url)) {
					if(isset($img->{'data-srcset'})) { unset($img->{'data-srcset'}); }
					if(isset($img->sizes)) { unset($img->sizes); }
					$img->{'data-src'} = $url;
				}
			
			}
			
		}
		
		# remove empty picture sources inside and simplify
		foreach ($picture->find('source') as $pict) {
				
			# remove empty
			if(!isset($pict->srcset) || (isset($picture->srcset) && empty($picture->srcset))) {
				$pict->outertext = '';
			}
			
			# simplify src
			if(stripos($pict->srcset, ' 1x, http') !== false) {
				$pict->srcset = substr($pict->srcset, 0, stripos($pict->srcset, ' 1x, http'));
			}
			
		}
		
		# if there is no sources available, just show normal image
		if( is_null($picture->find('source')) || 
		   (is_array($picture->find('source')) && strlen(implode('', $picture->find('source'))) == 0)) { 
			foreach ($picture->find('img[src]') as $img) {
				
				# add lazy load class
				if(!isset($img->class) || (isset($img->class) && stripos($img->class, 'lazyload') === false)) {
					$img->class = trim($img->class . ' lazyload');
				}
				
				# replace
				$picture->outertext = $img->outertext;
				
			}
		}
	 
	}
	
	# return
	return $html;
}


# lazy load background images
function wpraiser_process_images_bg($html) {
	
	# settings
	global $wpraiser_settings;

	# defaults
	$excl = array();
		
	# get all exclusions
	if(isset($wpraiser_settings['lazy']['bg_exc']) && !empty($wpraiser_settings['lazy']['bg_exc'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['lazy']['bg_exc']);
		foreach($html->find(implode(', ', $arr)) as $elem) {
			$excl[] = $elem->outertext;
		}
	}
	
	# get background images that are not inside the exclusions
	$imgs = array();
	foreach($html->find('section[style*=background], section[style*=background-image], div[style*=background], div[style*=background-image], span[style*=background], span[style*=background-image], a[style*=background], a[style*=background-image]') as $elem) {
		if(!in_array($elem->outertext, $excl)) {
			$imgs[] = $elem;
		}
	}
	
	# images
	foreach($imgs as $image) {
			
		# get tag
		$tag = $image->outertext;
			
		# split background image to data-background-image and preserve remaining style attributes
		preg_match('~background(-image)?\s*:(.*?)(;|$)(.*?)~iu', $image->style, $bg); 
				
		# set attributes, only if something to process and only if it's a valid image format
		if(isset($bg[0]) && !empty($bg[0])) {
			if(stripos($bg[0], '.jpg') !== false || stripos($bg[0], '.jpeg') !== false || stripos($bg[0], '.png') !== false || stripos($bg[0], '.gif') !== false || stripos($bg[0], '.webp') !== false) {
			
				# extract image url
				preg_match('#url\([\'"]?(.*(jpg|png|gif|jpeg))#iu', $bg[0], $match);
				if(isset($match[1]) && !empty($match[1])) {
				
					# add new attribute
					$image->{'data-background-image'} = trim($match[1]);
					
					# set style
					$image->style = str_ireplace($bg[0], '', $image->style);
					
					# add lazy load class
					if(!isset($image->class) || (isset($image->class) && stripos($image->class, 'lazyload') === false)) {
						$image->class = trim($image->class . ' lazyload');
					}
					
				}

			}
		}	
	}
	
	return $html;
}



# process gravatar cache
function wpraiser_process_gravatar($html) {
	
	# settings
	global $wpraiser_urls;
	
	# get all gravatar images
	$imgs = array();
	foreach($html->find('img[src*=secure.gravatar.com]') as $elem) {
		if(stripos($elem->src, 'secure.gravatar.com/avatar/') !== false) {
			$imgs[] = $elem;
		}
	}
		
	# images
	foreach($imgs as $image) {
		
		# remove lazyload class
		if(isset($image->class)) {
			if(stripos($image->class, 'lazyload') !== false) {
				$image->class = trim(str_replace('lazyload', '', $image->class));
			}
		}
		
		# images should always have an alt attribute
		if(!isset($image->alt) || (isset($image->alt) && empty($image->alt))) {
			$image->alt = 'gravatar';
		}
		
		# unset the data-srcset attribute
		if(isset($image->srcset)) {
			unset($image->srcset);
		}
		
		# unset the data-srcset attribute
		if(isset($image->{'data-srcset'})) {
			unset($image->{'data-srcset'});
		}
		
		# cache the thumbnail locally
		if(isset($image->src)) {
			$gravatar = wpraiser_download_images($image->src);
			if($gravatar !== false) { 
				$image->src = $gravatar;
			}
		}

	}
	
	return $html;
}


# add lazy load library
function wpraiser_add_lozad_and_polyfill($html) { 

# settings
global $wpraiser_settings;

# must have
if(!isset($wpraiser_settings['lazy']['enable_img']) || !isset($wpraiser_settings['lazy']['enable_bg']) || !isset($wpraiser_settings['lazy']['enable_iframe'])) { return $html; }

# must have at least one option active
if($wpraiser_settings['lazy']['enable_img'] != true && $wpraiser_settings['lazy']['enable_bg'] != true && $wpraiser_settings['lazy']['enable_iframe'] != true) { return $html; }

# defaults
$polyfill = '';

# Load Polyfill for IE ?
if(isset($wpraiser_settings['lazy']['enable_polyfill']) && $wpraiser_settings['lazy']['enable_polyfill'] == true) {
	$polyfill = '<script data-cfasync="false">if(window.navigator.userAgent.match(/(MSIE|Trident)/)){document.write("<script crossorigin=\"anonymous\" src=\"https:\/\/polyfill.io\/v3\/polyfill.min.js?flags=gated&features=Object.assign%2CIntersectionObserver\"><\/script>");}</script>';
}

# lozad.js - v1.16.0 - 2020-09-06
$lozad = <<<EOF
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.lozad=e()}(this,function(){"use strict";
var g="undefined"!=typeof document&&document.documentMode,f={rootMargin:"0px",threshold:0,load:function(t){if("picture"===t.nodeName.toLowerCase()){var e=t.querySelector("img"),r=!1;null===e&&(e=document.createElement("img"),r=!0),g&&t.getAttribute("data-iesrc")&&(e.src=t.getAttribute("data-iesrc")),t.getAttribute("data-alt")&&(e.alt=t.getAttribute("data-alt")),r&&t.append(e)}if("video"===t.nodeName.toLowerCase()&&!t.getAttribute("data-src")&&t.children){for(var a=t.children,o=void 0,i=0;i<=a.length-1;i++)(o=a[i].getAttribute("data-src"))&&(a[i].src=o);t.load()}t.getAttribute("data-poster")&&(t.poster=t.getAttribute("data-poster")),t.getAttribute("data-src")&&(t.src=t.getAttribute("data-src")),t.getAttribute("data-srcset")&&t.setAttribute("srcset",t.getAttribute("data-srcset"));var n=",";if(t.getAttribute("data-background-delimiter")&&(n=t.getAttribute("data-background-delimiter")),t.getAttribute("data-background-image"))t.style.backgroundImage="url('"+t.getAttribute("data-background-image").split(n).join("'),url('")+"')";else if(t.getAttribute("data-background-image-set")){var d=t.getAttribute("data-background-image-set").split(n),u=d[0].substr(0,d[0].indexOf(" "))||d[0];
u=-1===u.indexOf("url(")?"url("+u+")":u,1===d.length?t.style.backgroundImage=u:t.setAttribute("style",(t.getAttribute("style")||"")+"background-image: "+u+"; background-image: -webkit-image-set("+d+"); background-image: image-set("+d+")")}t.getAttribute("data-toggle-class")&&t.classList.toggle(t.getAttribute("data-toggle-class"))},loaded:function(){}};function A(t){t.setAttribute("data-loaded",!0)}var m=function(t){return"true"===t.getAttribute("data-loaded")},v=function(t){var e=1<arguments.length&&void 0!==arguments[1]?arguments[1]:document;return t instanceof Element?[t]:t instanceof NodeList?t:e.querySelectorAll(t)};return function(){var r,a,o=0<arguments.length&&void 0!==arguments[0]?arguments[0]:".lozad",t=1<arguments.length&&void 0!==arguments[1]?arguments[1]:{},e=Object.assign({},f,t),i=e.root,n=e.rootMargin,d=e.threshold,u=e.load,g=e.loaded,s=void 0;"undefined"!=typeof window&&window.IntersectionObserver&&(s=new IntersectionObserver((r=u,a=g,function(t,e){t.forEach(function(t){(0<t.intersectionRatio||t.isIntersecting)&&(e.unobserve(t.target),m(t.target)||(r(t.target),A(t.target),a(t.target)))})}),{root:i,rootMargin:n,threshold:d}));for(var c,l=v(o,i),b=0;b<l.length;b++)(c=l[b]).getAttribute("data-placeholder-background")&&(c.style.background=c.getAttribute("data-placeholder-background"));return{observe:function(){for(var t=v(o,i),e=0;e<t.length;e++)m(t[e])||(s?s.observe(t[e]):(u(t[e]),A(t[e]),g(t[e])))},triggerLoad:function(t){m(t)||(u(t),A(t),g(t))},observer:s}}});
EOF;

# initialize, iframes on scroll or after 5 seconds
$lozad.= <<<EOF
document.addEventListener("DOMContentLoaded",function(){const observer=lozad(".lazyload",{rootMargin: '200px 0px'});observer.observe();});
if(wpruag()){var c=setTimeout(b,5E3),d=["mouseover","keydown","touchmove","touchstart"];d.forEach(function(a){window.addEventListener(a,e,{passive:!0})});function e(){b();clearTimeout(c);d.forEach(function(a){window.removeEventListener(a,e,{passive:!0})})}function b(){lozad(".lazyloadvideo",{rootMargin:"200px 0px",loaded:function(a){a.setAttribute("width","100%");a.style.height=.5625*a.offsetWidth+"px"}}).observe();lozad(".lazyloadiframe",{rootMargin:"200px 0px"}).observe();};}
EOF;

# build and return
return str_replace('<!-- h_footer_lozad -->', $polyfill.'<script data-cfasync="false">'.$lozad.'</script>', $html);

}


