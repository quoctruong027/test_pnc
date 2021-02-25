<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	


# functions needed only for frontend ###########

# must have for large strings processing during minification
@ini_set('pcre.backtrack_limit', 5000000); 
@ini_set('pcre.recursion_limit', 5000000); 

# our own minification libraries
include_once($wpraiser_var_inc_lib . DIRECTORY_SEPARATOR . 'raisermin' . DIRECTORY_SEPARATOR . 'minify.php');

# php simple html
# https://sourceforge.net/projects/simplehtmldom/
define('MAX_FILE_SIZE', 2000000); # Process HTML up to 2 Mb
include_once($wpraiser_var_inc_lib . DIRECTORY_SEPARATOR . 'simplehtmldom' . DIRECTORY_SEPARATOR . 'simple_html_dom.php');

# PHP Minify [1.3.60] for CSS minification only
# https://github.com/matthiasmullie/minify
$wpraiser_var_inc_lib_matthiasmullie = $wpraiser_var_inc_lib . DIRECTORY_SEPARATOR . 'matthiasmullie' . DIRECTORY_SEPARATOR;
include_once($wpraiser_var_inc_lib_matthiasmullie . 'minify' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Minify.php');
include_once($wpraiser_var_inc_lib_matthiasmullie . 'minify' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'CSS.php');
include_once $wpraiser_var_inc_lib_matthiasmullie . 'minify'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'JS.php';
include_once $wpraiser_var_inc_lib_matthiasmullie . 'minify'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'Exception.php';
include_once $wpraiser_var_inc_lib_matthiasmullie . 'minify'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'Exceptions'. DIRECTORY_SEPARATOR .'BasicException.php';
include_once $wpraiser_var_inc_lib_matthiasmullie . 'minify'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'Exceptions'. DIRECTORY_SEPARATOR .'FileImportException.php';
include_once $wpraiser_var_inc_lib_matthiasmullie . 'minify'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'Exceptions'. DIRECTORY_SEPARATOR .'IOException.php';
include_once($wpraiser_var_inc_lib_matthiasmullie . 'path-converter' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ConverterInterface.php');
include_once($wpraiser_var_inc_lib_matthiasmullie . 'path-converter' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Converter.php');

################################################


# start buffering before template
function wpraiser_start_buffer() {
	if(wpraiser_can_cache() || wpraiser_can_minify_html() || wpraiser_can_minify_css() || wpraiser_can_minify_js() || wpraiser_can_process_lazyload() || wpraiser_can_process_cdn() ) {
		ob_start('wpraiser_process_page', 0, PHP_OUTPUT_HANDLER_REMOVABLE);
	}
}

# process html from wpraiser_end_buffer
function wpraiser_process_page($html) {
	
	# get globals
	global $wpraiser_settings, $wpraiser_cache_paths, $wpraiser_urls;
				
	# defaults
	$tvers = get_option('wpraiser_last_cache_update', '0');
	$now = time();
	$htmlpreloader = array();
	$htmlcssheader = array();
	$lp_css_last_ff_inline = '';
			
	# get html into an object
	# https://simplehtmldom.sourceforge.io/manual.htm
	$html_object = str_get_html($html, true, true, 'UTF-8', false, PHP_EOL, ' ');

	# return early if html is not an object, or overwrite html into an object for processing
	if (!is_object($html_object)) {
		return $html . '<!-- simplehtmldom failed to process the html -->';
	} else {
		$html = $html_object;
	}
		

	# process css, if not disabled ###############################
	if(wpraiser_can_minify_css()) {
					
		# defaults
		$wpr_styles = array();
		$wpr_styles_log = array();
		$enable_css_minification = false;
			
		# exclude styles and link tags inside scripts, no scripts or html comments
		$excl = array();
		foreach($html->find('script link[rel=stylesheet], script style, noscript style, noscript link[rel=stylesheet], comment') as $element) {
			$excl[] = $element->outertext;
		}

		# collect all styles, but filter out if excluded
		$allcss = array();
		foreach($html->find('link[rel=stylesheet], style') as $element) {
			if(!in_array($element->outertext, $excl)) {
				$allcss[] = $element;
			}
		}
					
		# merge and process
		foreach($allcss as $k=>$tag) {
			
			# css files only
			if($tag->tag == 'link' && isset($tag->href)) {
				
				# Remove "Link" HTML tags
				if(isset($wpraiser_settings['css']['remove_file']) && !empty($wpraiser_settings['css']['remove_file'])) {
					$arr = wpraiser_string_toarray($wpraiser_settings['css']['remove_file']);
					if(is_array($arr) && count($arr) > 0) {
						foreach ($arr as $e) { 
							if(stripos($tag->href, $e) !== false) {
								$tag->outertext = '';
								unset($allcss[$k]);
								continue 2;
							} 
						}
					}
				}
				
				# Ignore CSS files
				if(isset($wpraiser_settings['css']['ignore']) && !empty($wpraiser_settings['css']['ignore'])) {
					$arr = wpraiser_string_toarray($wpraiser_settings['css']['ignore']);
					if(is_array($arr) && count($arr) > 0) {
						foreach ($arr as $e) { 
							if(stripos($tag->href, $e) !== false) {
								unset($allcss[$k]);
								continue 2;
							} 
						}
					}
				}
			
			}
				
			# change the mediatype for files that are to be merged into the low priority css 
			if(isset($wpraiser_settings['css']['lowp_files']) && !empty($wpraiser_settings['css']['lowp_files'])) {
				$arr = wpraiser_string_toarray($wpraiser_settings['css']['lowp_files']);
				if(is_array($arr) && count($arr) > 0) {
					foreach ($arr as $e) { 
						if(stripos($tag->href, $e) !== false) {
							$tag->media = 'lowpriority';
							break;
						}
					} 
				}
			}
				
			# normalize mediatypes
			$media = 'all';
			if(isset($tag->media)) {
				$media = $tag->media;
				if ($media == 'screen' || $media == 'screen, print' || empty($media) || is_null($media) || $media == false) { 
					$media = 'all'; 
				}
			}
				
			# remove print mediatypes
			if(isset($wpraiser_settings['css']['remove_print']) && $wpraiser_settings['css']['remove_print'] == true && $media == 'print') {
				$tag->outertext = '';
				unset($allcss[$k]);
				continue;
			}

			# process css files
			if($tag->tag == 'link' && isset($tag->href)) {
				
				# default
				$css = '';
				
				# make sure we have a complete url
				$href = wpraiser_normalize_url($tag->href, $wpraiser_urls['wp_domain'], $wpraiser_urls['wp_home']);
				
				# get minification settings for files
				if(isset($wpraiser_settings['css']['min_files'])) {
					$enable_css_minification = $wpraiser_settings['css']['min_files'];
				}					
				
				# force minification on google fonts
				if(stripos($href, 'fonts.googleapis.com') !== false) {
					$enable_css_minification = true;
				}
				
				# download, minify, cache (no ver query string)
				$tkey = hash('sha1', $href);
				$css = wpraiser_get_transient($tkey);
				if ($css === false) {
					
					# open or download file, get contents
					$ddl = array();
					$ddl = wpraiser_maybe_download($href);
					
					# if success
					if(isset($ddl['content'])) {
						
						# contents
						$css = $ddl['content'];
					
						# minify
						$css = wpraiser_maybe_minify_css_file($css, $href, $enable_css_minification);

						# remove specific, minified CSS code
						if(isset($wpraiser_settings['css']['remove_code']) && !empty($wpraiser_settings['css']['remove_code'])) {
							$arr = wpraiser_string_toarray($wpraiser_settings['css']['remove_code']);
							if(is_array($arr) && count($arr) > 0) {
								foreach($arr as $str) {
									$css = str_replace($str, '', $css);
								}
							}
						}
						
						# handle import rules
						$css = wpraiser_replace_css_imports($css, $href);
							
						# trim code
						$css = trim($css);
						
						# size in bytes
						$fs = strlen($css);
						$ur = str_replace($wpraiser_urls['wp_home'], '', $href);
						$tkey_meta = array('fs'=>$fs, 'url'=>str_replace($wpraiser_cache_paths['cache_url_min'].'/', '', $ur), 'mt'=>$media);
							
						# save
						wpraiser_set_transient(array('uid'=>$tkey, 'date'=>$tvers, 'type'=>'css', 'content'=>$css, 'meta'=>$tkey_meta));

					}
				}
				
				# quick integrity check
				if(!empty($css) && $css != false) {
					
					# success, get final contents to array
					$wpr_styles[$media][] = $css;
					$wpr_styles_log[$media][] = $tkey;
					$tag->outertext = '';
					unset($allcss[$k]);
					continue;
										
				} else {
										
					# there is an error, so leave them alone
					$err = ''; if(isset($ddl['error'])) { $err = '<!-- '.$ddl['error'].' -->'; }
					$tag->outertext = PHP_EOL . $tag->outertext.$err . PHP_EOL;
					unset($allcss[$k]);
					continue;
										
				}
			
			}
		
	
			# process styles
			if($tag->tag == 'style' && !isset($tag->href)) {
			
				# default
				$css = '';
				
				# get minification settings for inline styles
				if(isset($wpraiser_settings['css']['min_inline'])) {
					$enable_css_minification = $wpraiser_settings['css']['min_inline'];
				}		
				
				# minify inline CSS
				$css = $tag->innertext;
				if($enable_css_minification) {
					$css = wpraiser_minify_css_string($css); 
				}
				
				# remove specific, minified CSS code
				if(isset($wpraiser_settings['css']['remove_code']) && !empty($wpraiser_settings['css']['remove_code'])) {
					$arr = wpraiser_string_toarray($wpraiser_settings['css']['remove_code']);
					if(is_array($arr) && count($arr) > 0) {
						foreach($arr as $str) {
							$css = str_replace($str, '', $css);
						}
					}
				}
				
				# handle import rules
				$css = wpraiser_replace_css_imports($css);
				
				# remove fonts and icons and collect for later
				$mff = array();
				preg_match_all('/(\@font-face)([^}]+)(\})/', $css, $mff);
				if(isset($mff[0]) && is_array($mff[0])) {
					foreach($mff[0] as $ff) {
						$css = str_replace($ff, '', $css);
						$lp_css_last_ff_inline.= $ff . PHP_EOL;
					}
				}
				
				# merge specific inlined styles
				if(isset($wpraiser_settings['css']['merge_style']) && !empty($wpraiser_settings['css']['merge_style'])) {
					$arr = wpraiser_string_toarray($wpraiser_settings['css']['merge_style']);
					if(is_array($arr) && count($arr) > 0) {
						foreach ($arr as $e) { 
							if(stripos($css, $e) !== false) {					
								$wpr_styles[$media][] = $css;
								$wpr_styles_log[$media][] = 'Inlined code with hash: ' . hash('sha1', $css);
								$tag->outertext = '';
								unset($allcss[$k]);
								continue 2;
							}
						} 
					}
				}
				
				# trim code
				$css = trim($css);
				
				# decide what to do with the inlined css
				if(empty($css)) {
					# delete empty style tags
					$tag->outertext = '';
					unset($allcss[$k]);
					continue;
				} else {
					# process inlined styles
					$tag->innertext = $css;
					unset($allcss[$k]);
					continue;
				}
			
			}
			
		}
			
		# generate merged css files, foreach mediatype
		if(is_array($wpr_styles) && count($wpr_styles) > 0) {
			
			# collect low priority process for last
			$lp_css_last = array();
			$lp_css_last_ff = '';
			
			# process other mediatypes
			foreach ($wpr_styles as $mediatype=>$css_process) {
				
				# skip lowpriority file
				if($mediatype == 'lowpriority') {
					$lp_css_last = $wpr_styles['lowpriority'];
					continue;
				}					
			
				# merge code, generate cache file paths and urls
				$file_css_code = implode('', $css_process);
				$css_uid = $tvers.'-'.hash('sha1', $file_css_code);
				$file_css = $wpraiser_cache_paths['cache_dir_min'] . DIRECTORY_SEPARATOR .  $css_uid.'.min.css';
				$file_css_url = $wpraiser_cache_paths['cache_url_min'].'/'.$css_uid.'.min.css';
				
				# remove fonts and icons from final css
				$mff = array();
				preg_match_all('/(\@font-face)([^}]+)(\})/', $file_css_code, $mff);
				if(isset($mff[0]) && is_array($mff[0])) {
					foreach($mff[0] as $ff) {
						$file_css_code = str_replace($ff, '', $file_css_code);
						$lp_css_last_ff.= $ff . PHP_EOL;
					}
				}
					
				# generate cache file
				clearstatcache();
				if (!file_exists($file_css)) {
					
					# prepare log
					$log = (array) array_values($wpr_styles_log[$mediatype]);
					$log_meta = array('loc'=>home_url(add_query_arg(NULL, NULL)), 'fl'=>$file_css_url, 'mt'=>$mediatype);
					
					# generate cache, write log
					if(!empty($file_css_code)) {
						wpraiser_save_log(array('uid'=>$file_css_url, 'date'=>$now, 'type'=>'css', 'meta'=>$log_meta, 'content'=>$log));
						wpraiser_save_file($file_css, $file_css_code);
					}
					
				}
					
				# preload and save for html implementation (with priority order prefix)
				if(!empty($file_css_code) && file_exists($file_css)) {
					$htmlpreloader['c_'.$css_uid] = '<link rel="preload" href="'.$file_css_url.'" as="style" media="'.$mediatype.'" />';
					$htmlcssheader['b_'.$css_uid] = '<link rel="stylesheet" href="'.$file_css_url.'" media="'.$mediatype.'" />';
				}
				
			}
		
			
			# process lowpriority css file
			if(!empty($lp_css_last) || !empty($lp_css_last_ff) || !empty($lp_css_last_ff_inline)) {
				
				# specific to lowpriority mediatype only
				$mediatype = 'lowpriority';
				
				# update log for extracted fonts
				if(!empty($lp_css_last_ff)) { 
					$wpr_styles_log[$mediatype][] = '[Size: '.str_pad(wpraiser_format_filesize(strlen($lp_css_last_ff)), 10,' ',STR_PAD_LEFT).']'."\t". 'Merged other font face rules with hash: ' . hash('sha1', $lp_css_last_ff);
					
					 
				}
				
				# update log for extracted inline styles
				if(!empty($lp_css_last_ff_inline)) { 
					$wpr_styles_log[$mediatype][] = '[Size: '.str_pad(wpraiser_format_filesize(strlen($lp_css_last_ff_inline)), 10,' ',STR_PAD_LEFT).']'."\t". 'Merged other font face rules with hash: ' . hash('sha1', $lp_css_last_ff_inline);
				}						
				
				# merge code, generate cache file paths and urls, append extracted font faces
				$file_css_code = implode(PHP_EOL, $lp_css_last).$lp_css_last_ff.$lp_css_last_ff_inline;
				$css_uid = $tvers.'-'.hash('sha1', $file_css_code);
				$file_css = $wpraiser_cache_paths['cache_dir_min'] . DIRECTORY_SEPARATOR .  $css_uid.'.min.css';
				$file_css_url = $wpraiser_cache_paths['cache_url_min'].'/'.$css_uid.'.min.css';
							
				# generate cache file
				clearstatcache();
				if (!file_exists($file_css)) {
					
					# prepare log
					$log = (array) array_values($wpr_styles_log[$mediatype]);
					$log_meta = array('loc'=>home_url(add_query_arg(NULL, NULL)), 'fl'=>$file_css_url, 'mt'=>$mediatype);
					
					# generate cache, write log
					if(!empty($file_css_code)) {
						wpraiser_save_log(array('uid'=>$file_css_url, 'date'=>$now, 'type'=>'css', 'meta'=>$log_meta, 'content'=>$log));
						wpraiser_save_file($file_css, $file_css_code);
					}
					
				}
					
				# lowpriority mediatype file
				if(!empty($file_css_code) && file_exists($file_css)) {
					$htmlcssheader['a_'.$css_uid] = '<script data-cfasync="false" id="wprlpcss">var a;wpruag()&&((a=document.getElementById("wprlpcss")).outerHTML='.wpraiser_escape_url_js("<link rel='stylesheet' href='". $file_css_url . "' media='all' />").');</script>'; # prepend
				}
				
			}
			
		}
	}
	
		
	
	# process js, if not disabled ###############################	
	if(wpraiser_can_minify_js()) {
		
		# defaults
		$scripts_duplicate_check = array();
		$enable_js_minification = false;
		$htmljscodeheader = array();
		$htmljscodedefer = array();
		$scripts_header = array();
		$scripts_footer = array();
		$scripts_header_log = array();
		$scripts_footer_log = array();			
		
		# get all scripts
		$allscripts = array();
		foreach($html->find('script') as $element) {
			$allscripts[] = $element;
		}
		
		# process all scripts
		if (is_array($allscripts) && count($allscripts) > 0) {
			foreach($allscripts as $k=>$tag) {
										
				# handle application/ld+json or application/json before anything else
				if(isset($tag->type) && ($tag->type == 'application/ld+json' || $tag->type == 'application/json')) {
					$tag->innertext = wpraiser_minify_microdata($tag->innertext);
					unset($allscripts[$k]);
					continue;
				}					
				
				
				# remove js files
				if(isset($tag->src)) {
					
					# remove js files
					if(isset($wpraiser_settings['js']['remove_scripts']) && !empty($wpraiser_settings['js']['remove_scripts'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['remove_scripts']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $a) { 
								if(stripos($tag->src, $a) !== false) {
									$tag->outertext = '';
									unset($allscripts[$k]);
									continue 2;
								} 
							}
						}
					}						
										
				# else remove inline scripts
				} else {
					
					# remove inlined scripts
					if(isset($wpraiser_settings['js']['js_remove_inlined']) && !empty($wpraiser_settings['js']['js_remove_inlined'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['js_remove_inlined']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $a) { 
								if(stripos($tag->innertext, $a) !== false) {
									$tag->outertext = '';
									unset($allscripts[$k]);
									continue 2;
								} 
							}
						}
					}
					
				}
				
				
				# upgrade jquery to version 3.x
				if(isset($wpraiser_settings['js']['upgrade_jquery']) && !empty($wpraiser_settings['js']['upgrade_jquery'])) {
					if (isset($tag->src) && $wpraiser_settings['js']['upgrade_jquery'] == true) {
						
						# replace jquery
						if(stripos($tag->src, '/jquery.js') !== false || stripos($tag->src, '/jquery.min.js') !== false) {
							$tag->src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js';
						}
						
						# upgrade jquery migrate
						if(stripos($tag->src, '/jquery-migrate.min.js') !== false || stripos($tag->src, '/jquery-migrate.js') !== false || stripos($tag->src, '/jquery-migrate-') !== false) {
							$tag->src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.3.0/jquery-migrate.min.js';
						}

					}
				}

				
				# process js files in the header, footer, or low priority
				if(isset($tag->src)) {
					
					# make sure we have a complete url
					$href = wpraiser_normalize_url($tag->src, $wpraiser_urls['wp_domain'], $wpraiser_urls['wp_home']);
					
					# get minification settings for files
					if(isset($wpraiser_settings['js']['min_files']) && !empty($wpraiser_settings['js']['min_files'])) {
						$enable_js_minification = $wpraiser_settings['js']['min_files'];
					}
					
					
					# JS files to hide (unmergeable)
					if(isset($wpraiser_settings['js']['thirdparty_hide']) && !empty($wpraiser_settings['js']['thirdparty_hide'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_hide']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $ac) { 
								if(stripos($tag->src, $ac) !== false) {
									
									# unique identifier
									$uid = hash('sha1', $tag->outertext);
									
									# remove exact duplicates, or replace transformed tag
									if(isset($scripts_duplicate_check[$uid])) {
										$tag->outertext = '';
									} else {
										$tag = wpraiser_wrap_script_inline($tag, 'hide'); # returns object
										$scripts_duplicate_check[$uid] = $uid;
									}					
									
									# mark as processed, unset and break inner loop
									unset($allscripts[$k]);
									continue 2;
									
								} 
							}
						}
					}
					
						
					# JS files to delay until user interaction (unmergeable)
					if(isset($wpraiser_settings['js']['thirdparty_delay']) && !empty($wpraiser_settings['js']['thirdparty_delay'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_delay']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $ac) { 
								if(stripos($tag->src, $ac) !== false) {
									
									# unique identifier
									$uid = hash('sha1', $tag->outertext);
									
									# remove exact duplicates, or replace transformed tag
									if(isset($scripts_duplicate_check[$uid])) {
										$tag->outertext = '';
									} else {
										$tag = wpraiser_wrap_script_inline($tag); # returns object
										$scripts_duplicate_check[$uid] = $uid;
									}					
									
									# mark as processed, unset and break inner loop
									unset($allscripts[$k]);
									continue 2;
									
								} 
							}
						}
					}
					
					# files to header
					if(isset($wpraiser_settings['js']['merge_files_header']) && !empty($wpraiser_settings['js']['merge_files_header'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['merge_files_header']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $aa) { 
								if(stripos($tag->src, $aa) !== false) {
									
									# download, minify, cache
									$tkey = hash('sha1', $href);
									$js = wpraiser_get_transient($tkey);
									if ($js === false) {
									
										# open or download file, get contents
										$ddl = array();
										$ddl = wpraiser_maybe_download($href);
										
										# if success
										if(isset($ddl['content'])) {
											
											# contents
											$js = $ddl['content'];
										
											# fixes "Does not use passive listeners to improve scrolling performance" on jquery
											$js = wpaiser_passive_listeners_fix($tag->src, $js);
												
											# minify, save and wrap
											$js = wpraiser_maybe_minify_js($js, $href, $enable_js_minification);
											$js = wpraiser_try_catch_wrap($js, $href);
											
											# merge but remove execution from inside merged files
											if(isset($wpraiser_settings['js']['thirdparty_merge_hiden']) && !empty($wpraiser_settings['js']['thirdparty_merge_hiden'])) {
												$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_merge_hiden']);
												if(is_array($arr) && count($arr) > 0) {
													foreach ($arr as $ac) { 
														if(stripos($tag->src, $ac) !== false) {
															$js = 'if(wpruag()){'.$js.'}';
														} 
													}
												}
											}
											
											# size in bytes
											$fs = strlen($js);
											$ur = str_replace($wpraiser_urls['wp_home'], '', $href);
											$tkey_meta = array('fs'=>$fs, 'url'=>str_replace($wpraiser_cache_paths['cache_url_min'].'/', '', $ur));
										
											# save
											wpraiser_set_transient(array('uid'=>$tkey, 'date'=>$tvers, 'type'=>'js', 'content'=>$js, 'meta'=>$tkey_meta));
																			
										}									
									}
									
									# quick integrity check
									if(!empty($js) && $js != false) {

										# collect and mark as done for html removal						
										$scripts_header[$tkey] = $js;
										$scripts_header_log[$tkey] = $tkey;
										
										# mark as processed, unset and break inner loop
										$tag->outertext = '';
										unset($allscripts[$k]);
										continue 2;
										
									} else {
										
										# there is an error, so leave them alone
										$err = ''; if(isset($ddl['error'])) { $err = '<!-- '.$ddl['error'].' -->'; }
										$tag->outertext = PHP_EOL . $tag->outertext.$err . PHP_EOL;
										unset($allscripts[$k]);
										continue 2;
										
									}
									
								} 
							}
						}
					}
					
					
					# files to footer
					if(isset($wpraiser_settings['js']['merge_files_footer']) && !empty($wpraiser_settings['js']['merge_files_footer'])) {
					$arr = wpraiser_string_toarray($wpraiser_settings['js']['merge_files_footer']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $ab) { 
								if(stripos($tag->src, $ab) !== false) {
										
									# download, minify, cache
									$tkey = hash('sha1', $href);
									$js = wpraiser_get_transient($tkey);
									if ($js === false) {

										# open or download file, get contents
										$ddl = array();
										$ddl = wpraiser_maybe_download($href);
										
										# if success
										if(isset($ddl['content'])) {
											
											# contents
											$js = $ddl['content'];
											
											# fixes "Does not use passive listeners to improve scrolling performance" on jquery
											$js = wpaiser_passive_listeners_fix($tag->src, $js);
												
											# minify, save and wrap
											$js = wpraiser_maybe_minify_js($js, $href, $enable_js_minification);
											$js = wpraiser_try_catch_wrap($js, $href);

											# merge but remove execution from inside merged files
											if(isset($wpraiser_settings['js']['thirdparty_merge_hiden']) && !empty($wpraiser_settings['js']['thirdparty_merge_hiden'])) {
												$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_merge_hiden']);
												if(is_array($arr) && count($arr) > 0) {
													foreach ($arr as $ac) { 
														if(stripos($tag->src, $ac) !== false) {
															$js = 'if(wpruag()){'.$js.'}';
														} 
													}
												}
											}											
										
											# size in bytes
											$fs = strlen($js);
											$ur = str_replace($wpraiser_urls['wp_home'], '', $href);
											$tkey_meta = array('fs'=>$fs, 'url'=>str_replace($wpraiser_cache_paths['cache_url_min'].'/', '', $ur));
										
											# save
											wpraiser_set_transient(array('uid'=>$tkey, 'date'=>$tvers, 'type'=>'js', 'content'=>$js, 'meta'=>$tkey_meta));
										
										}	
									}
									
									# quick integrity check
									if(!empty($js) && $js != false) {

										# collect and mark as done for html removal						
										$scripts_footer[$tkey] = $js;
										$scripts_footer_log[$tkey] = $tkey;
										
										# mark as processed, unset and break inner loop
										$tag->outertext = '';
										unset($allscripts[$k]);
										continue 2;
										
									} else {
										
										# there is an error, so leave them alone
										$err = ''; if(isset($ddl['error'])) { $err = '<!-- '.$ddl['error'].' -->'; }
										$tag->outertext = PHP_EOL . $tag->outertext.$err . PHP_EOL;
										unset($allscripts[$k]);
										continue 2;
										
									}
									
								} 
							}
						}
					}
				
					
					# if jquery is not being merged or removed, we need to make sure it loads earlier than the header file
					if(stripos($tag->src, '/jquery.js') !== false || stripos($tag->src, '/jquery.min.js') !== false) {
						$htmlpreloader['a_'.hash('sha1', $tag->src)] = '<link rel="preload" href="'.$tag->src.'" as="script" />';
						$htmljscodeheader['a_'.hash('sha1', $tag->src)] = "<script data-cfasync='false' src='".$tag->src."'></script>";
						$tag->outertext = '';
						unset($allscripts[$k]);
						continue;
					}
						
					# if jquery migrate is not being merged or removed, we need to make sure it loads earlier than the header file, but after jquery
					if(stripos($tag->src, '/jquery-migrate') !== false) {
						$htmlpreloader['a_'.$tag->src] = '<link rel="preload" href="'.$tag->src.'" as="script" />';
						$htmljscodeheader['b_'.hash('sha1', $tag->src)] =  "<script data-cfasync='false' src='".$tag->src."'></script>";
						$tag->outertext = '';
						unset($allscripts[$k]);
						continue;
					}
				
				}
					
					
				# process inlined scripts, merge to header, footer, or low priority
				if(!isset($tag->src)) {
				
					# default
					$js = '';
					
					# get minification settings for inlined scripts
					if(isset($wpraiser_settings['js']['min_inline'])) {
						$enable_js_minification = $wpraiser_settings['js']['min_inline'];
					}
					
					
					# minify inline scripts
					$js = $tag->innertext;
					$js = wpraiser_maybe_minify_js($js, null, $enable_js_minification);
					
					
					# inline scripts that need to wait until after the footer js file finishes loading
					if(isset($wpraiser_settings['js']['merge_files_footer']) && !empty($wpraiser_settings['js']['merge_files_footer'])) {
						if(isset($wpraiser_settings['js']['delay_inline_footer']) && !empty($wpraiser_settings['js']['delay_inline_footer'])) {
							$arr = wpraiser_string_toarray($wpraiser_settings['js']['delay_inline_footer']);
							if(is_array($arr) && count($arr) > 0) {
								foreach ($arr as $b) {
									if(stripos($js, $b) !== false || stripos($js, $b) !== false) {
										$js = 'window.addEventListener("load",function(){'.$js.'});';
										$tag->innertext = $js;
										unset($allscripts[$k]);
										continue 2;
									}
								}
							}
						}
					}
						
				
					# hide inline scripts (unmergeable)
					if(isset($wpraiser_settings['js']['thirdparty_hide']) && !empty($wpraiser_settings['js']['thirdparty_hide'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_hide']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $b) {
								if(stripos($js, $b) !== false || stripos($js, $b) !== false) {
									$js = 'if(wpruag()){'.$js.'}';
									$tag->innertext = $js;
									unset($allscripts[$k]);
									continue 2;
								}
							}
						}
					}
					
					
					# delay inline scripts until user interaction (unmergeable)
					if(isset($wpraiser_settings['js']['thirdparty_delay']) && !empty($wpraiser_settings['js']['thirdparty_delay'])) {
						$arr = wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_delay']);
						if(is_array($arr) && count($arr) > 0) {
							foreach ($arr as $b) {
								if(stripos($js, $b) !== false || stripos($js, $b) !== false) {
									$js = 'if(wpruag()){window.addEventListener("load",function(){c=setTimeout(b,5E3),d=["mouseover","keydown","touchmove","touchstart"];d.forEach(function(a){window.addEventListener(a,e,{passive:!0})});function e(){b();clearTimeout(c);d.forEach(function(a){window.removeEventListener(a,e,{passive:!0})})}function b(){'.$js.'};});}';
									$tag->innertext = $js;
									unset($allscripts[$k]);
									continue 2;
								}
							}
						}
					}
					
					
					# fallback to just minified code
					$tag->innertext = $js;
					unset($allscripts[$k]);
					continue;
				
				}
			}
		}
			

		# generate header merged scripts
		if(count($scripts_header) > 0) {

			# merge code, generate cache file paths and urls
			$fheader_code = implode('', $scripts_header);
			$js_header_uid = $tvers.'-'.hash('sha1', $fheader_code).'.header';
			$fheader = $wpraiser_cache_paths['cache_dir_min']  . DIRECTORY_SEPARATOR .  $js_header_uid.'.min.js';
			$fheader_url = $wpraiser_cache_paths['cache_url_min'].'/'.$js_header_uid.'.min.js';		

			# generate cache file
			clearstatcache();
			if (!file_exists($fheader)) {
					
				# prepare log
				$log = (array) array_values($scripts_header_log);
				$log_meta = array('loc'=>home_url(add_query_arg(NULL, NULL)), 'fl'=>$fheader_url);
				
				# generate cache, write log
				if(!empty($fheader_code)) {
					wpraiser_save_log(array('uid'=>$fheader_url, 'date'=>$now, 'type'=>'js', 'meta'=>$log_meta, 'content'=>$log));
					wpraiser_save_file($fheader, $fheader_code);
				}
			}
				
			# preload and save for html implementation (with priority order prefix)
			$htmlpreloader['b_'.$fheader_url] = '<link rel="preload" href="'.$fheader_url.'" as="script" />';
			$htmljscodeheader['b_'.$js_header_uid] = "<script data-cfasync='false' src='".$fheader_url."'></script>";
			
		}
			
		# generate footer merged scripts
		if(count($scripts_footer) > 0) {
				
			# merge code, generate cache file paths and urls
			$ffooter_code = implode('', $scripts_footer);
			$js_ffooter_uid = $tvers.'-'.hash('sha1', $ffooter_code).'.footer';
			$ffooter = $wpraiser_cache_paths['cache_dir_min']  . DIRECTORY_SEPARATOR .  $js_ffooter_uid.'.min.js';
			$ffooter_url = $wpraiser_cache_paths['cache_url_min'].'/'.$js_ffooter_uid.'.min.js';
				
			# generate cache file
			clearstatcache();
			if (!file_exists($ffooter)) {
					
				# prepare log
				$log = (array) array_values($scripts_footer_log);
				$log_meta = array('loc'=>home_url(add_query_arg(NULL, NULL)), 'fl'=>$ffooter_url);
											
				# generate cache, write log
				if(!empty($ffooter_code)) {
					wpraiser_save_log(array('uid'=>$ffooter_url, 'date'=>$now, 'type'=>'js', 'meta'=>$log_meta, 'content'=>$log));
					wpraiser_save_file($ffooter, $ffooter_code);
				}
			}
				
			# preload and save for html implementation (with priority order prefix)
			$htmlpreloader['d_'.$ffooter_url] = '<link rel="preload" href="'.$ffooter_url.'" as="script" />';
			$htmljscodedefer['a_'.$js_ffooter_uid] = "<script defer src='".$ffooter_url."'></script>";
					
		}
	}
		
	

	# process lazy loading, if not disabled ###############################	
	if(wpraiser_can_process_lazyload()) { 
	
		# process gravatar cache
		if(isset($wpraiser_settings['lazy']['enable_gravatar']) && $wpraiser_settings['lazy']['enable_gravatar'] == true) {
			$html = wpraiser_process_gravatar($html);
		}
		
		# iframes
		if(isset($wpraiser_settings['lazy']['enable_iframe']) && $wpraiser_settings['lazy']['enable_iframe'] == true) {
			$html = wpraiser_process_iframes($html);
		}
		
		# images
		if(isset($wpraiser_settings['lazy']['enable_img']) && $wpraiser_settings['lazy']['enable_img'] == true) {
			$html = wpraiser_process_images($html);
		}
		
		# background images
		if(isset($wpraiser_settings['lazy']['enable_bg']) && $wpraiser_settings['lazy']['enable_bg'] == true) {
			$html = wpraiser_process_images_bg($html);
		}
	
	}
	
	
	# process HTML minification, if not disabled ###############################	
	if(wpraiser_can_minify_html()) {		
			
		# Remove HTML comments and IE conditionals
		if(isset($wpraiser_settings['html']['remove_comments']) && $wpraiser_settings['html']['remove_comments'] == true) {
			foreach($html->find('comment') as $element) {
				 $element->outertext = '';
			}
		}
		
		# Remove generator tags
		if(isset($wpraiser_settings['html']['remove_generator']) && $wpraiser_settings['html']['remove_generator'] == true) {
			foreach($html->find('head meta[name=generator]') as $element) {
				 $element->outertext = '';
			}
		}
		
		# Remove shortlink tag
		if(isset($wpraiser_settings['html']['remove_shortlink']) && $wpraiser_settings['html']['remove_shortlink'] == true) {
			foreach($html->find('head link[rel=shortlink]') as $element) {
				 $element->outertext = '';
			}
		}
		
		# Remove resource hints
		if(isset($wpraiser_settings['html']['remove_hints']) && $wpraiser_settings['html']['remove_hints'] == true) {
			foreach($html->find('head link[rel=dns-prefetch], head link[rel=preconnect], head link[rel=prefetch], head link[rel=prerender]') as $element) {
				 $element->outertext = '';
			}
		}
		
		# Remove extra favicon sizes
		if(isset($wpraiser_settings['html']['remove_favicon']) && $wpraiser_settings['html']['remove_favicon'] == true) {
			
			# remove extra icons
			foreach($html->find('head meta[name*=msapplication], head link[rel=apple-touch-icon]') as $element) {
				 $element->outertext = '';
			}
			
			# allow the last link[rel=icon]
			$ic = array(); $ic = $html->find('head link[rel=icon]');
			$i = 1; $len = count($ic);
			if($len > 1) {
				foreach($ic as $element) {
					if ($i != $len) { $element->outertext = ''; } $i++; # delete except if last
				}
			}
		}
			
		# Remove RSD & WLW references
		if(isset($wpraiser_settings['html']['remove_rsd']) && $wpraiser_settings['html']['remove_rsd'] == true) {
			foreach($html->find('head link[rel=EditURI], head link[rel=preconnect], head link[rel=wlwmanifest]') as $element) {
				 $element->outertext = '';
			}
		}
			
		# Remove RSS feed references
		if(isset($wpraiser_settings['html']['remove_rssref']) && $wpraiser_settings['html']['remove_rssref'] == true) {
			foreach($html->find('head link[type=application/rss+xml]') as $element) {
				$element->outertext = '';
			}
		}
			
		# Remove REST API references
		if(isset($wpraiser_settings['html']['remove_restref']) && $wpraiser_settings['html']['remove_restref'] == true) {
			foreach($html->find('head link[rel=https://api.w.org/]') as $element) {
				 $element->outertext = '';
			}
		}
			
		# Remove oEmbed references
		if(isset($wpraiser_settings['html']['remove_oembed']) && $wpraiser_settings['html']['remove_oembed'] == true) {
			foreach($html->find('head link[type=application/json+oembed], head link[type=text/xml+oembed]') as $element) {
				 $element->outertext = '';
			}
		}
			
		# remove garbage
		if(isset($wpraiser_settings['html']['remove_garbage']) && !empty($wpraiser_settings['html']['remove_garbage'])) {
			$arr = wpraiser_string_toarray($wpraiser_settings['html']['remove_garbage']);
			if(is_array($arr) && count($arr) > 0) {
				foreach($html->find(implode(', ', $arr) ) as $element) {
					 $element->outertext = '';
				}
			}
		}
		
	}
	
	
	# build extra head and footer ###############################	
	
	# header and footer markers
	$hm = '<!-- h_preheader --><!-- h_header_function --><!-- h_cssheader --><!-- h_jsheader -->';
	$fm = '<!-- h_footer_lozad -->';
	
	# add our function to head
	if(wpraiser_can_minify_css() || wpraiser_can_minify_js() || wpraiser_can_process_lazyload()) { 
		$hm = wpraiser_add_header_function($hm);
	}
		
	# remove charset meta tag and collect it to first position
	if(!is_null($html->find('meta[charset]', 0))) {
		$hm = str_replace('<!-- h_preheader -->', $html->find('meta[charset]', 0)->outertext.'<!-- h_preheader -->', $hm);
		foreach($html->find('meta[charset]') as $element) { $element->outertext = ''; }
	}

	# add preload headers
	if(is_array($htmlpreloader)) {
		ksort($htmlpreloader); # priority
		$hm = str_replace('<!-- h_preheader -->', implode('', $htmlpreloader), $hm);
	}		
		
	# add stylesheets
	if(is_array($htmlcssheader) && count($htmlcssheader) > 0) {
		ksort($htmlcssheader); # priority
		$hm = str_replace('<!-- h_cssheader -->', implode('', $htmlcssheader).'<!-- h_cssheader -->', $hm);
	}
	
	# add header scripts
	if(is_array($htmljscodeheader) && count($htmljscodeheader) > 0) {
		ksort($htmljscodeheader); # priority
		$hm = str_replace('<!-- h_jsheader -->', implode('', $htmljscodeheader).'<!-- h_jsheader -->', $hm);
	}
		
	# add defer scripts
	if(is_array($htmljscodedefer) && count($htmljscodedefer) > 0) {
		ksort($htmljscodedefer); # priority
		$hm = str_replace('<!-- h_jsheader -->', implode('', $htmljscodedefer), $hm);
	}
		
	# add lozad and polyfill, if enabled
	if(wpraiser_can_process_lazyload()) { 
		$fm = wpraiser_add_lozad_and_polyfill($fm);
	}
		
	# cleanup leftover markers
	$hm = str_replace(
		  array('<!-- h_preheader -->', '<!-- h_header_function -->', '<!-- h_cssheader -->', '<!-- h_jsheader -->'), '', $hm); 
	$fm = str_replace('<!-- h_footer_lozad -->', '', $fm);
	
	
	# process cdn optimization, if not disabled ###############################	
	if(wpraiser_can_process_cdn()) { 
	
		# process cdn integration
		if(isset($wpraiser_settings['cdn']['enable']) && $wpraiser_settings['cdn']['enable'] == true && 
		isset($wpraiser_settings['cdn']['url']) && !empty($wpraiser_settings['cdn']['url'])) {
			$html = wpraiser_process_cdn($html); # content
			$hm = wpraiser_process_cdn($hm);     # head css and js files
		}	
	
	}
	
		
	# Save HTML and output page ###############################	
	
	# append header and footer, if available
	if(!is_null($html->find('head', 0)) && !is_null($html->find('body', -1))) {
		if(!is_null($html->find('head', 0)->first_child()) && !is_null($html->find('body', -1)->last_child())) {
			$html->find('head', 0)->first_child()->outertext = $hm . $html->find('head', 0)->first_child()->outertext;
			$html->find('body', -1)->last_child()->outertext = $html->find('body', -1)->last_child ()->outertext . $fm;
		}
	}	
	
	# convert html object to string
	$html = trim($html->save());
	
	# minify remaining HTML at the end, if enabled
	if(wpraiser_can_minify_html()) {
		$html = wpraiser_raisermin_html($html);
	}
	
	# save cache file
	if(wpraiser_can_cache() && isset($wpraiser_settings['cache']['enable_page'])) {
		wpraiser_save_cache_file($html, $wpraiser_settings);
	}
	
	# return html
	return $html;
	
}


