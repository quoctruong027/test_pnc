<?php
if (!defined('ABSPATH')) {
    die;
}
?>
<div class="wrap guaven_woos_admin_container">
<div id="icon-options-general" class="icon32"><br></div><h2>WooCommerce Search Engine
  <span style="float:right"><a class="button" href="?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php">Analytics</a> </span>

  <?php
  if (get_option('guaven_woos_support_expired')=='2') {
    echo '  <span style="float:right"> <a style="background:red;color:white" class="button" href="https://codecanyon.net/item/woocommerce-search-box/15685698">Renew Expired Support</a></span>';
  }
  else {
    echo '<span style="float:right"> <a class="button" href="https://goo.gl/forms/hh9J7y9JtKMOpAjx2" target="_blank">Request a Feature</a></span>
    <span style="float:right"> <a style="background:#008ec2;color:white"
    class="button" href="https://guaven.com/contact/?fr=settingspage&purchase_code='.get_option('guaven_woos_purchasecode').'">Get Support</a></span>';
  }
  ?>
  </h2>
<?php
settings_errors();
?>

<form action="" method="post" name="settings_form">
<?php
wp_nonce_field('guaven_woos_nonce', 'guaven_woos_nonce_f');
?>

<h3>Cache re/builder</h3>

<p>
This button generates the needed cached data based on your products by using parameters below.</p>
<?php
$guaven_woos_rebuild_via = get_option("guaven_woos_rebuild_via");
if (defined('W3TC') and $guaven_woos_rebuild_via == 'db') {
    echo '<p style="color:blue">It seems you are using W3 Total Cache which blocks rebuilding process by default (due to its Object Cache feature).
Please go to "Data Building" tab and choose "Rebuild via Filesystem" option for "Rebuild the cache via" setting.
</p>';
}
?>
<div style="height:30px">
<input type="button" class="rebuilder  gws_rebuilder inputrebuilder button button-primary" value="Rebuild the Cache <?php
echo $this->get_current_language_code()!=''?' - '.$this->get_current_language_code():''; ?>" style="float:left"></div>

<div style="font-weight: bold;font-size:14px;background:#00a747;color:white;margin-top:10px;display:none;clear:both;padding: 10px" id="result_field"></div>

<br>
<div class="tab">
  <button class="tablinks" id="guaven_woos_tablink_live" onclick="openSettingTab(event, 'guaven_woos_tab_live');return false;">Live Search</button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_backend');return false;">Backend Search</button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_admin');return false;">Data Building</button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_advanced');return false;">Advanced Settings</button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_updates');return false;">Getting Updates</button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_faq');return false;">FAQ</button>
</div>

<div id="guaven_woos_tab_live" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>

      <tr valign="top">
      <th scope="row" class="titledesc">Smart Search</th>
      <td scope="row">

      <p>
      <label>
              <input name="guaven_woos_corr_act" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_corr_act"), 'checked');
?>>
              Automatic Correction feature    (usually should be checked) </label>
      <br>
      <small>For example, if a user types <i>ifone</i> instead of <i>iphone</i>, or <i>kidshoe</i> instead of <i>Kids Shoes</i> this feature will understand him/her and will suggest
      corresponding products.</small></p>
      <br>
      <p>
      <label>
              Show suggestions by autocorrected key if there are
      <input name="guaven_woos_whentypo" type="number" step="1"  id="guaven_woos_whentypo"
      value="<?php
echo get_option("guaven_woos_whentypo") != '' ? ((int) get_option("guaven_woos_whentypo")) : 10;
?>" class="small-text"> or fewer suggestions for original input.
      </label>
    </p><br>

      <p>
      <label>
              Show suggestion after
      <input name="guaven_woos_min_symb_sugg" type="number" step="1" min="1" id="guaven_woos_min_symb_sugg"
      value="<?php
echo (int) get_option("guaven_woos_min_symb_sugg");
?>" class="small-text"> characters entered by a visitor.
      </label>
      </p>
<br>
      <p>
      <label>
              The maximal number of suggestions:
      <input name="guaven_woos_maxres" type="number" step="1" min="1" id="guaven_woos_maxres" value="<?php
echo (int) get_option("guaven_woos_maxres");
?>" class="small-text">

      </label>
      </p>

      </td> </tr>


  <tr valign="top">
  <th scope="row" class="titledesc">Initial texts</th>
  <td scope="row">


  <p>

  <label>
Initial help message to the visitor when he/she focuses on search area:
  <input name="guaven_woos_showinit_t" type="text" id="guaven_woos_showinit_t"
  value='<?php
echo $this->kses(get_option("guaven_woos_showinit_t"));
?>' class="small-text" style="width:500px"
  placeholder='F.e: Type here any product name you want: f.e. iphone, samsung etc.'>
  </label>
  </p><br>
  <p>
  <label>
  "No match" text
  <input name="guaven_woos_showinit_n" type="text" id="guaven_woos_showinit_n"
  value='<?php
echo $this->kses(get_option("guaven_woos_showinit_n"));
?>' class="" style="width:500px"
  placeholder='No any products found...'>
  </label>
  </p>
  <br>
  <p>
  <label>
  "Show all results" link text below live results
  <input name="guaven_show_all_text" type="text" id="guaven_show_all_text"
  value='<?php
echo esc_attr(get_option("guaven_show_all_text"));
?>' class="" style="width:500px"
  placeholder='Show all results...'>
  </label>
  <br>
  <small>Leave empty if you don't want it to appear</small>
  </p>


      </td>
  </tr>

    <tr valign="top">
    <th scope="row" class="titledesc">Trending products</th>
    <td scope="row">
    <p>
    <label>
    Show  <input name="guaven_woos_data_trend_num"  class="small-text" type="number"  value="<?php
echo intval(get_option("guaven_woos_data_trend_num"));
?>">
    trending products in search suggestion box (will be shown when the cursor is in the search box, but the user has not pressed enter yet. </label>
  </p><small>Type 0(zero) if you don't want to use this block yet. Also, note that trending data isn't being collected while you don't use this block. </small>
    <br>  <br>
    <p>
    <label>
          Title text for "Trending Products" block:
    <input name="guaven_woos_trendt" type="text" id="guaven_woos_trendt"
    value="<?php
echo $this->kses(get_option("guaven_woos_trendt"));
?>">
    </label>
    <small>(f.e.  Trending products.) </small>
    </p>
    <br>
    <p>
    <label>
          "Trending Products" criterions:
    Trending data should be built on data for the latest
    <input name="guaven_woos_trend_days" type="number" id="guaven_woos_trend_days"  value="<?php
echo (int) get_option("guaven_woos_trend_days");
?>" class="small-text">
    days and refreshed each
    <input name="guaven_woos_trend_refresh" type="number" id="guaven_woos_trend_refresh"  value="<?php
echo (int) (get_option("guaven_woos_trend_refresh"));
?>" class="small-text">
     minutes.
   </label><br>
    <small>Recommended default values are "3" and "10" which mean 3 days and 10 minutes. </small>
    </p>
    </td> </tr>


    <tr valign="top">
    <th scope="row" class="titledesc">Featured products
    </th>
    <td scope="row">
    <p>
    <label>ID numbers of featured products:
    <input name="guaven_woos_pinneds" type="text" id="guaven_woos_pinneds"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinneds"));
?>">
    </label>
    <small>(Comma-separated: f.e.  12,23,1,34. Leave empty if you don't want to use this yet) </small>
  </p><br>
    <p>
    <label>Term ID numbers of featured categories:
    <input name="guaven_woos_pinneds_cat" type="text" id="guaven_woos_pinneds_cat"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinneds_cat"));
?>">
    </label>
    <small>(Comma-separated term_IDs. Leave empty if you don't want to use this yet) </small>
    </p>

    <br>
    <p>
    <label>
          Title text for this block:

    <input name="guaven_woos_pinnedt" type="text" id="guaven_woos_pinnedt"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinnedt"));
?>">
    </label>
    <small>(f.e.  Featured products.) </small>
    </p>
    </td> </tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Personal "Recently Viewed Products"</th>
  <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_ispers" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_ispers"), 'checked');
?>>
          Enable cookie-based personalized initial suggestions (will be shown when cursor is in the search box, but user has not pressed enter yet)</label>
  </p>

  <br>
  <p>
  <label>
        Title text for personalized initial suggestions:

  <input name="guaven_woos_perst" type="text" id="guaven_woos_perst"
  value="<?php
echo $this->kses(get_option("guaven_woos_perst"));
?>">
  </label>
  <small>(e.g.  Recently viewed products.) </small>
  </p>


  <p>
  <label>
        Max number of personal suggestions:

  <input name="guaven_woos_persmax" type="number" id="guaven_woos_persmax"
  value="<?php
echo (int) get_option("guaven_woos_persmax");
?>">
  </label>
  <small>(default is 5) </small>
  </p>

  </td> </tr>



  <tr valign="top">
  <th scope="row" class="titledesc">Show this below when "not found" appears</th>
  <td scope="row">

  <p>
  <label>
          <input name="guaven_woos_nomatch_pops" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_nomatch_pops"), 'checked');
?>>
          Show the most popular products below when "no match" (not found) message appears?</label>
  </p>


  <br>
  <p>
  <label>
        Meta key name for product popularity:

  <input name="guaven_woos_popsmkey" type="text" id="guaven_woos_popsmkey"
  value="<?php
echo esc_attr(get_option("guaven_woos_popsmkey"));
?>">
  </label>
  <small>(f.e. total_sales, view_count, views etc. You should check your products custom fields if you don't know its exact name) </small>
  </p>
  <br>
  <p>
  <label>
        Max number of popular products:

  <input name="guaven_woos_popsmax" type="number" id="guaven_woos_popsmax"
  value="<?php
echo (int) get_option("guaven_woos_popsmax");
?>">
  </label>
  <small>(default is 5) </small>
  </p>
  </td></tr>


    <tr valign="top">
    <th scope="row" class="titledesc">FullScreen Mobile Search</th>
    <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_mobilesearch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_mobilesearch"), 'checked');
?>>
          Enable Full-Screen Mobile Search Popup </label>
  </p>
  <small>If you enable this, then all search fields of your website will be turned into full-screen simple search form (for mobile devices only)</small>
</td></tr>

<tr valign="top">
<th scope="row" class="titledesc">Smart expressions </th>
<td scope="row">
<p>
<label> <input name="guaven_woos_simple_expressions" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_simple_expressions"), 'checked');?>>
Enable Smart Expressions with prices </label>
<br>
<small>If you enable this, then live search would recognize expressions like "smartphones under 100 usd, smartphones under $100, smartphones around 100$" etc.</small>
</p>
<br>
<p>
<label>
Comma separated values for "under,around,above" segmentators.
<input name="guaven_woos_expression_segments" type="text" id="guaven_woos_expression_segments"
value="<?php echo esc_attr(get_option("guaven_woos_expression_segments")); ?>">
</label>
<small>default value is under,around,above </small>
</p>
<br>
<p>
<label>
Your currency spelling: <br>
Singular: <input name="guaven_woos_expression_spell_s" type="text" id="guaven_woos_expression_spell_s"
value="<?php echo esc_attr(get_option("guaven_woos_expression_spell_s")); ?>" placeholder="f.e. dollar">
Plural: <input name="guaven_woos_expression_spell_p" type="text" id="guaven_woos_expression_spell_p"
value="<?php echo esc_attr(get_option("guaven_woos_expression_spell_p")); ?>" placeholder="f.e. dollars"><br>
</label>
<small>by default our engine understands abreviation and currency symbol (f.e. USD and $, GBP and £ and so on). But you can also set oftently used spellings (f.e. dollar)  </small>
</p>

</td></tr>


<tr valign="top">
<th scope="row" class="titledesc">Show found categories</th>
<td scope="row">

<p>
<label>
<input name="guaven_woos_catsearch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_catsearch"), 'checked');
?>>
Show categories/taxonomies block in search results?</label>
</p>
<br>
<p>
<label>
      Max number of shown taxonomies:

<input name="guaven_woos_catsearchmax" type="number" id="guaven_woos_catsearchmax"
value="<?php
echo (int) get_option("guaven_woos_catsearchmax");
?>">
</label>
<small>(default is 5) </small>
</p>
<br>
 <p>
<label>
  Shown taxonomies:
<input name="guaven_woos_shown_taxonomies" type="text" id="guaven_woos_shown_taxonomies"
value="<?php
echo esc_attr(get_option("guaven_woos_shown_taxonomies"));
?>">
</label>
<small>default value is product_cat. If you want to use several taxonomies, type their names comma-separated. </small>
</p>
</td></tr>


  </tbody> </table>

</div>


<div id="guaven_woos_tab_backend" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>
  <tr valign="top">
  <th scope="row" class="titledesc">Backend Search</th>
  <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '');
?>>
          Don't affect default search results of my theme's search page </label>
  </p>
<br>
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '1');
?>>
          Deprecated: Display live smart results in the Theme's Search Results Page (replaces default search results of your theme.
          It doesn't change UI of the theme's search results page)
<br>  <small>This option works on <b>cookie</b> based algorithm - only for the websites with <=1000 products</small>
        </label>
  </p>

  <br>
    <p>
    <label>
            <input name="guaven_woos_backend" type="radio" value="3" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '3');
?>>
            <b>Recommended: </b>Display  live smart results in the Theme's Search Results Page (replaces default search results of your theme.
            It doesn't change UI of the theme's search results page)
  <br>  <small>This option works on <b>WP Transient</b> based algorithm </small>
          </label>
    </p>

<br>
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="2" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '2');
?>>
          Replace my theme's search results page with "SSSS" (Standalone Simple and Smart Search) module by this plugin.
<br> <small>To use this option you need to create a new page with <i><?php
echo home_url("/search-results");
?></i> URL and to put this shortcode to its content:
  <i>[woo_search_standalone]</i></small>
         </label>
  </p>

  </td></tr>
</tbody>
</table>


</div>

<div id="guaven_woos_tab_admin" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>


  <tr valign="top">
  <th scope="row" class="titledesc">Search by more data</th>
  <td scope="row">

  <div>
    <input name="guaven_woos_wootags" type="hidden" id="guaven_woos_wootags"
    value='<?php
$gws_wootags = explode(",", get_option("guaven_woos_wootags"));
echo esc_attr(implode(",", $gws_wootags));
?>'>

  <dl class="dropdown"> <dt><a href="#"><span class="hida">Search by WooCommerce attributes and taxonomies</span> <p class="multiSel"></p> </a></dt><dd>
    <div class="mutliSelect">
              <ul>
                  <?php
$this->attribute_checkboxes($gws_wootags);
?>
             </ul>
          </div>
      </dd>
  </dl>
  <br>
  <p>
  <label>
  Search by custom taxonomies and custom attributes (Comma-separated names)



  <input name="guaven_woos_customtags" type="text" id="guaven_woos_customtags"
  value='<?php
echo esc_attr(get_option("guaven_woos_customtags"));
?>' class="small-text" style="width:500px"
  placeholder='F.e: product_tag,product_vendor etc.'>
</label>
</div>
  <small>Custom attribute name should start with pa_. If any questions, just write to our support</small>
</p>

  <p>
<br>    <label>
  Search by custom post fields (Comma-separated names of meta_keys you want to be indexed)
  <input name="guaven_woos_customfields" type="text" id="guaven_woos_customfields"
  value='<?php
echo esc_attr(get_option("guaven_woos_customfields"));
?>' class="small-text" style="width:500px"
  placeholder='F.e: _wc_average_rating,_stock_status etc.'>
  </label>
  </p>
  <small>If you enter here some meta key fields, the search suggestion algorithm will include their data to search metadata.
  (e.g. you have a bookstore, you add _book_author field here. And then when a visitor types the name of the author in the search box, his/her
  books will be suggested with a normal title. )</small>

  <p>
<br>  <label>
          <input name="guaven_woos_add_shortdescription_too" type="checkbox" value="1" class="tog"
          <?php
echo checked(get_option("guaven_woos_add_shortdescription_too"), 'checked');
?>>
          Search by Product Short Description (not recommended for most cases)   </label>
  </p>
  <small>Although short descriptions will be hidden in search suggestions, the plugin will give the results based on short descriptions.<br>
  Check this only if it is very important for your store.
  </small>

  <p>
<br>  <label>
          <input name="guaven_woos_add_description_too" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_add_description_too"), 'checked');
?>>
          Search by Product Full Description (not recommended for most cases)   </label>
  </p>
  <small>Although descriptions will be hidden in search suggestions, the plugin will give the results based on descriptions.<br>
  Check this only if it is very important for your store.
  </small>
</td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc">Cache Size</th>
  <td scope="row">




  <p>
  <label>
          Maximum numbers of products in cached data.
  <input name="guaven_woos_maxprod" type="number" step="1000" min="1000" id="guaven_woos_maxprod"
  value="<?php
echo (int) get_option("guaven_woos_maxprod");
?>" class="small-text"> (defaul is 10000).
  </label>
  </p>

    </td>
  </tr>



  <tr valign="top">
  <th scope="row" class="titledesc">How to rebuild</th>
  <td scope="row">


  <?php
$guaven_woos_autorebuild = get_option("guaven_woos_autorebuild");
?>
 <p>
  <label>
  Do the Cache Auto-Rebuild after each time when you edit any product / show manual rebuilder button in admin top bar:
  <select name="guaven_woos_autorebuild">
  <option value="b1a0" <?php
echo selected($guaven_woos_autorebuild, 'b1a0');
?>>Enable top rebuild button / disable auto-rebuild</option>
  <option value="b1a1" <?php
echo selected($guaven_woos_autorebuild, 'b1a1');
?>>Enable top rebuild button / enable auto-rebuild</option>
  <option value="b0a1" <?php
echo selected($guaven_woos_autorebuild, 'b0a1');
?>>Disable top rebuild button / enable auto-rebuild</option>
  <option value="b0a0" <?php
echo selected($guaven_woos_autorebuild, 'b0a0');
?>>Disable top rebuild button / disable auto-rebuild</option>
  </select>
  </p>
  <br>

<label>Rebuild with cron jobs</label>:
  <code>
  php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?>
 </code>
<br>
For WPML websites:
<code>
php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?> LANGUAGE_CODE
</code>
<br>
For Multisite websites:
<code>
  php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?> 0 SUBSITE_ID
 </code>
<br>
<small>In some servers you might need to use "/usr/local/bin/php ..." prefix instead of "php ...".</small>
<br>
<p><br>
<label>
Rebuild the cache via
<select name="guaven_woos_rebuild_via">
<option value="db" <?php
echo selected($guaven_woos_rebuild_via, 'db');
?>>Rebuild via Database</option>
<option value="fs" <?php
echo selected($guaven_woos_rebuild_via, 'fs');
?>>Rebuild via Filesystem</option>
</select>
</p>
<small>If you choose filesystem, then temporary rebuilding data would be stored in filesystem. Otherwise, it would be stored in database table.
  In some servers there are strict database data size limits which don't allow rebuilding process to be finished. That's why, recommended option is "FileSystem".
  You should choose "Database" option only if there is writing permission problem in the /plugins directory of your filesystem.</small>
<br>

  </td></tr>


    <tr valign="top">
    <th scope="row" class="titledesc">Which products will appear</th>
    <td scope="row">
    <p>
    <label>
            <input name="guaven_woos_nostock" type="checkbox" value="1" class="tog" <?php
if (get_option("guaven_woos_nostock") != '') {
    echo 'checked="checked"';
}
?>>
            Include out of stock products     </label>
    </p>

    <br>
    <p>
    <label>
            <input name="guaven_woos_removehiddens" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_removehiddens"), 'checked');
?>>
            Hide "Catalog visibility = hidden" products at live search box</label>
    </p>

      <br>
    <p>
    <label>
            <input name="guaven_woos_removefilters" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_removefilters"), 'checked');
?>>Remove all filters by 3rd party plugins while fetching products for cache rebuilding  (not necessary in most cases)</label>
    </p>

    <br>
    <p>
    <label>
      Variations in search process:
      <select name="guaven_woos_variation_skus">
        <option value="" <?php
echo selected(get_option("guaven_woos_variation_skus"), '');
?>>Show parent product of variables</option>
        <option value="1" <?php
echo selected(get_option("guaven_woos_variation_skus"), 1);
?>>
          Show parent product of variables + search by variation meta data</option>
        <option value="2" <?php
echo selected(get_option("guaven_woos_variation_skus"), 2);
?>>Show all variations apart from each other, hide main product itself</option>

            </select>
           </label>
    </p>

    <br>
    <p>
    <label>
            Order by products by
    <input name="guaven_woos_customorder" type="text"  id="guaven_woos_customorder"
    value="<?php
echo esc_attr(get_option("guaven_woos_customorder"));
?>">
    </label>
    <small>Supported formats: default WP orders such as <i>date</i>, <i>title</i>, <i>ID</i>, <i>ID ASC</i>,<i>title DESC</i> etc. 
    And any meta fields <i>meta:total_sales</i>, 
    <i>metanum:view_count</i>, <i>metanum:view_count DESC</i>, <i>meta:authorname DESC</i> etc...</small>
    </p>

    <br>
    <p>
    <label>

   <input name="guaven_woos_disablerelevancy" type="checkbox" value="1" class="tog" <?php echo checked(get_option("guaven_woos_disablerelevancy"), 'checked');?>>
   Disable relevancy in ordering
 </label><br>
    <small>If you uncheck this setting (default and recommended state is unchecked state), search results would appear ordered by "relevancy (first priority) + the field you set above (second priority)".
      If you check this setting, then search results would appear directly ordered by the field you set in the field above.</small>
    </p>

      </td>
    </tr>



  </tbody></table>
</div>

<div id="guaven_woos_tab_advanced" class="tabcontent">


  <table class="form-table" id="box-table-a">
  <tbody>


    <tr valign="top">
    <th scope="row" class="titledesc">Transliterated search</th>
    <td scope="row">

    <p>
    <label> Transliterated data should be generated:
    <select name="guaven_woos_translit_data">
      <option value="" <?php
echo selected(get_option("guaven_woos_translit_data"), '');
?>>at endusers' browser sessions </option>
      <option value="1" <?php
echo selected(get_option("guaven_woos_translit_data"), '1');
?>>in cache rebuilding process (as pre-saved data) </option>
      <option value="-1" <?php
echo selected(get_option("guaven_woos_translit_data"), '-1');
?>>nowhere - disable search by transliterated data - default</option>
    </select>
    </p>
    <small>1 st option: at endusers' browser - transliterated data would be generated in each user session - recommended for the websites with < 1000 products;
      <br>2nd option: in cache rebuilding - transliterated cache data would be generated during cache rebuild, at once, for all;
      <br>3rd option: disable by default - no any transliteration data would be used in search process - recommended for English language websites; </small>
    </td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc">Customization</th>
  <td scope="row">

  <p>Custom CSS for plugin elements (don't use style tag, just directly put custom CSS code) </p>

   <textarea name="guaven_woos_custom_css" id="guaven_woos_custom_css" class="large-text code" rows="3"><?php
echo esc_attr(stripslashes(get_option("guaven_woos_custom_css")));
?></textarea>

  <br>
  <p>Custom JS (don't use script tag, just directly put custom JavaScript code) </p>
   <textarea name="guaven_woos_custom_js" id="guaven_woos_custom_js" class="large-text code" rows="3"><?php
echo esc_attr(stripslashes(get_option("guaven_woos_custom_js")));
?></textarea>

<br><br>
<p>
<label>
Search box selector:
<input name="guaven_woos_selector" type="text" id="guaven_woos_selector"
value='<?php
echo esc_attr(stripslashes(get_option("guaven_woos_selector")));
?>' class="small-text" style="width:300px" placeholder=''>
</label>
<br>
<small>Default selector is <code>[name="s"]</code>. You can change it if you want to exclude some search forms.</small>
</p>
<br>
<p>
<label>
Category filter selector of the search box.:
<input name="guaven_woos_filter_selector" type="text" id="guaven_woos_filter_selector"
value='<?php
echo esc_attr(stripslashes(get_option("guaven_woos_filter_selector")));
?>' class="small-text" style="width:300px" placeholder=''>
</label>
<br>
<small>If your theme's search form has its own category drop-down filter, then our live search box will consider its actual value.
To make it work you just need to enter selector name of that drop-down filter. (#ID, .CLASS, [name="its_name"]) </small>
</p>
<br>

<p>
<label>
Increased Memory Limit for Backend-side processes:
<input name="guaven_woos_memory_limit" type="text" id="guaven_woos_memory_limit"
value='<?php echo (int)get_option("guaven_woos_memory_limit");?>' class="small-text" style="width:300px" placeholder='f.e. 512M'>
</label>
<br>
<small>You can can set higher value for this field and let our admin & background processes to work faster. This setting is available just for this plugin
  and it doesn't affect the website's memory limit.<br>
  Recommended value: 512M or 1024M </small>
</p>



  </td> </tr>



  <tr valign="top">
  <th scope="row" class="titledesc">Exclude products</th>
  <td scope="row">
  <p>
  <label>
  Comma-separated IDs of product <b>categories</b> which should be excluded from the search
  <input name="guaven_woos_excluded_cats" type="text" id="guaven_woos_excluded_cats"
  value='<?php
echo esc_attr(get_option("guaven_woos_excluded_cats"));
?>' class="small-text" style="width:300px"
  placeholder=''>
  </label>
  </p>

  <p>
  <label>
  Comma-separated <b>product</b> IDs which should be excluded from the search
  <input name="guaven_woos_excluded_prods" type="text" id="guaven_woos_excluded_prods"
  value='<?php
echo esc_attr(get_option("guaven_woos_excluded_prods"));
?>' class="small-text" style="width:300px"
  placeholder=''>
  </label>
  </p>

  </td></tr>







  <tr valign="top">
  <th scope="row" class="titledesc">Synonym list </th>
  <td scope="row">
  <p>Put your product related synonyms there. Our search algorythm will take it into account.  </p>

   <textarea name="guaven_woos_synonyms" id="guaven_woos_synonyms" class="large-text code" rows="2"><?php
echo $this->kses(get_option("guaven_woos_synonyms"));
?></textarea>
  <br /><code>Each pair should be in A-B format, Comma-separated. For example: car-auto, lbs-pound, footgear-shoes. If you want to use "-" inside any word, use _ instead.
  F.e.  casing-t_shirt</code>
  </td> </tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Ignore them </th>
  <td scope="row">
  <p>Put your commonly used strings here if you want them to be skipped by our search engine </p>
   <textarea name="guaven_woos_ignorelist" id="guaven_woos_ignorelist" class="large-text code" rows="2"><?php
echo $this->kses(get_option("guaven_woos_ignorelist"));
?></textarea>
  <br /><code>Type them in Comma-separated format: f.e. product,and,machine,wearing,from,madeby. Or to ignore characters in search you can use _,/+/,!,<,></code>
  </td> </tr>


  <tr valign="top">
  <th scope="row" class="titledesc">Cache Layout/Structure</th>
  <td scope="row">


  <p>Search suggestions layout (Don't use line-breaks) </p>

   <textarea name="guaven_woos_layout" id="guaven_woos_layout" class="large-text code" rows="3"><?php
echo $this->kses(get_option("guaven_woos_layout"));
?></textarea>

  <code>To restore default layout just empty the area and save settings.
  <br> Avaliable tags: {url},{title},{height},{length},{width},{weight},{currency_sale},{saleprice},{currency_regular},{imgurl},{total_sales}, {stock_quantity},
  &#x3C;a {add_to_cart}&#x3E;Add to cart&#x3C;/a&#x3E;, {product_cat}, {pa_someAttributeName}
  </code>

  <p><br>More Advanced Layout (for developers only)</p>
  <p>
  Each search result is displayed with <i>li</i> container. If you want to edit this container, just create wp_option called "guaven_woos_results_layout" with this default value:
  <br>
  <code>&#x3C;li class=\&#x22;guaven_woos_suggestion_list{guaven_woos_lay_tip}\&#x22;
    tabindex=\&#x22;{guaven_woos_lay_gwsi}\&#x22; id=\&#x22;prli_{guaven_woos_lay_id}\&#x22;&#x3E;  {guaven_woos_lay_parsed} &#x3C;/li&#x3E;</code>
  <br>
  Then, edit this wp_option and REBUILD the cache data. You will get search results with your custom layout.
  </p>


  <br>
  <p>
  <label>
  Image quality in search results: <input name="guaven_woos_thumb_quality" type="text"
  value="<?php
echo get_option("guaven_woos_thumb_quality");
?>"><br>
  <small> (recommended: thumbnail, other values: medium, large or custom size name)</small>
  </label>
  </p>


  <br>
  <p>
  <label>
  <input name="guaven_woos_permalink" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_permalink"), 'checked');
?>>Disable short links in cache data.
  </label>
  </p>
  <small>By default plugin uses ?p=N format in search results to make the cache size smaller. You can easily disable it and let the plugin use the full permalink for products.
  </small>


<br>
<br>
<p>
<label>
<input name="guaven_woos_highlight" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_highlight"), 'checked');
?>>Highlight found word's first occurence in live search results.
</label>
</p>

  </td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc">Narrowed Search </th>
  <td scope="row">

    <p>
    <label>
    <input name="guaven_woos_disable_meta_correction" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_disable_meta_correction"), 'checked');
?>>
    Exclude product metadata from autocorrection.
    </label>
    </p>
    <small>If you enable this feature, then the autocorrection will work just for the product name and would not work for SKU, custom fields, attributes and so on.
    </small>

<br>
  <p><br>
  <label>
  <input name="guaven_woos_exactmatch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_exactmatch"), '1');
?>>
  Exact match search (just for special cases - not recommended)
  </label>
  </p>
  <small>If you enable this feature, then the algortyhm will search exact match among title,tags,attrbutes etc.. F.e. If the visitor types
    phone, it will only display the products which have indepentent "phone" string in their content.
  </small>

  <p><br>
  <label>
  <input name="guaven_woos_large_data" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_large_data"), '1');
?>>
  Enable "first letter rule" (just for special cases - not recommended)
  </label>
  </p>
  <small>If you enable this feature, then it will work so: when user types f.e. Galaxy, it will  search in products which names' start with "G",
    so it will find only the products which starts with Galaxy,
    the products which start with "Samsung Galaxy"
  will not be displayed.
  </small>



  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Who will rebuild/manage</th>
  <td scope="row">


    <p>
    <label>
    <input name="guaven_woos_autorebuild_editor" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_autorebuild_editor"), 'checked');
?>>
    Enable this CACHE REBUILDER for "shop manager" users </label>
    </p>    <small>By default, the feature is available only for administrators.
      <br />If you check this, then administrators and shop managers will be able to use rebuild button and auto-rebuild feature. </small>

    </td>
  </tr>


  <tr valign="top">
  <th scope="row" class="titledesc">Processing Engine</th>
  <td scope="row">

      <p>
      <label><input name="guaven_woos_live_server" class="guaven_woos_live_server" type="radio" value="" class="tog"
        <?php  echo checked(get_option("guaven_woos_live_server"), '');  ?>>
      Guaven Live Search Engine (default)
      <br> <small>Stable and recommended version for all kind of stores</small>
      </label>
      </p>

      <p>
      <label><input name="guaven_woos_live_server" class="guaven_woos_live_server" type="radio" value="1" class="tog"
        <?php  echo checked(get_option("guaven_woos_live_server"), '1');  ?>>
      Pure Backend Search Engine
      <br> <small> <b>Beta version.</b> For stores with >20-30K products.(currently "autocorrection" feature isn't supported 
       in this engine. It will be added later. All other features should work OK. ) </small> 
      </label>
      </p>

  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Google Analytics</th>
  <td scope="row">
  <p>
  <label>
  <input name="guaven_woos_ga" type="checkbox" value="1" class="tog" <?php
  echo checked(get_option("guaven_woos_ga"), 'checked');
  ?>>
  Enable Google Analytics events in live search</label>
  </p>
  <br>
  <p>
  <label>
  <input name="guaven_woos_utm" type="checkbox" value="1" class="tog" <?php
  echo checked(get_option("guaven_woos_utm"), 'checked');
  ?>>
  Enable UTM parameters on live search product URL-s</label>
  </p>
  <br>
  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Miscellaneous</th>
  <td scope="row">

    <p>
    <label>
            Suggestion bar width should be equal to
    <input name="guaven_woos_sugbarwidth" type="number" step="1" min="1" id="guaven_woos_sugbarwidth"
    value="<?php
    echo (int) get_option("guaven_woos_sugbarwidth");
    ?>" class="small-text">% width of search input field.
    </label>
    </p>

<br>
    <p>
    <label>
    Delay duration between a visitor finishes typing and search results appear (in milliseconds).
    <input name="guaven_woos_delay_time" type="number" step="1" min="1" id="guaven_woos_delay_time"
    value="<?php
  echo get_option("guaven_woos_delay_time")!=''?(int) get_option("guaven_woos_delay_time"):500;
  ?>" class="small-text">
    </label>
    <br>
    <small>It is recommended to keep this value around 500 - but if you want to show the results more instantly, then instead of 500 set the value to 5, 10 or 15. </small>
    </p>

<br>
    <p>
    <label>
    <input name="guaven_woos_cache_version_checker" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_cache_version_checker"), 'checked');
?>>
    Bypass Static Page Cache when there is new search indexed data. </label>
    </p>
    <small>Not needed for most cases. If you are using static page caching (wp-rocket, super cache, total cache etc.) 
      and regularly rebuilding search cache (via cron job f.e.), 
      then by checking this checkbox your website visitors will see newest version of search cache, 
      although your static page cache is older. But if you usually don't have outdated page cache in your website, then no need to check this checkbox.</small>
    </p>


    </td>
  </tr>

  </tbody> </table>
</div>

<div id="guaven_woos_tab_updates" class="tabcontent">
  <p>
  <label>
          Enter your purchase code and get the plugin's updates through Plugins page:<br>
  <input name="guaven_woos_purchasecode" type="text"  id="guaven_woos_purchasecode"
  value="<?php echo get_option("guaven_woos_purchasecode") != '' ? (get_option("guaven_woos_purchasecode")) : '';?>">
  </label>
  </p>
</div>

<div id="guaven_woos_tab_faq" class="tabcontent">
<style>#guaven_woos_tab_faq ol li {    display: list-item !important;}</style>
<ol>

  <li>
  Q: What to do first after installation?<br>
  <small><b>Quick start 1:</b>
    <ol><li>Just click to blue button "Rebuild the Cache" and wait the process is done (it can take 3-60 seconds to be finished)
  </li><li>Then check your website's search boxes. That's all.</li></ol> </small>
  <small><b>Quick start 2:</b>
    <ol>
        <li>Go do "Data Building" tab.</li>
        <li>Choose some attributes from the given "Search by more data" list.</li>
        <li>Go to "Backend Search" tab, and choose 2nd or 3rd option.</li>
        <li>Save the settings</li>
        <li>Click to "Rebuild the cache"</li>
    </ol>

   </small>
  </li>

<li>
Q: How to add attributes to product search data?<br>
<small>A: Go to Advanced Settings -> Search by more data, and you will see "Search by attributes and tags" field there, enter desired attribute names, save the settings, rebuild the cache. </small>
</li>

<li>
Q: How to add custom fields to product search data?<br>
<small>A: Go to Advanced Settings -> Search by more data, and you will see "Search by custom post fields" field there, enter desired post field names, save the settings, rebuild the cache. </small>
</li>

<li>
Q: How to show same smart search results at the results page which comes after pressing "Enter"?<br>
<small>A: Go to "Backend Search" tab and choose second "Try to show same..." option. </small>
</li>

<li>
Q: My theme's search bar takes the visitor to WordPress default search page, not to WooCommerce search results page, what to do?<br>
<small>A: You need to add post_type input field to your theme's search forms. To do it simply add this javascript code to "Advanced Settings->Custom JS field: <br>

  <code>jQuery(".searchform").append('&lt;input type="hidden" name="post_type" value="product"&gt;');</code>
<br>You may need to change ".searchform" to actual css class name of your theme's search form.
 </small>
</li>

<li>Q: How can i hide live search box while scrolling?<br>
  <small>A: You can use this JS code for that</small>

<code>jQuery(window ).scroll(function() {jQuery(".guaven_woos_suggestion").hide();});</code>
</li>

<li>
Q: How to use "Search Analytics" ?<br>
<small>A: Check top-right side of this page, you will see gray "Analytics" button, click to it and activate "Search Analytics". Come there after some time and you will see some reports there. </small>
</li>

<li>
Q: How to use WPML within this plugin?<br>
<small>A: In the settings page you can set wpml supported text to input fields. f.e. <pre>
&#x3C;wpml>&#x3C;en>No product found by your keyword&#x3C;/en>&#x3C;de>Kein Produkt von Ihrem Stichwort gefunden&#x3C;/de>&#x3C;/wpml>
</pre>But if you want to use it at frontend side, in UI layout f.e., you can set such CSS rules:
<pre>html:lang(en-US) .guaven_woos_final_results nl {display:none}html:lang(nl-NL) .guaven_woos_final_results en {display:none}</pre> </small>
</li>

<li>
Q: When we enable backend search option, submission process itself takes 1-2 seconds. How to remove that latency?<br>
<small>A: Just put this JS code to custom JS section (advanced settings tab)
<pre>guaven_woos.setpostform=1;</pre>
That's all. Alternatively, you can add the same parameter via PHP.
<pre>&#x3C;?php add_filter('gws_local_values_args',function($args){$args['setpostform']=1; return $args;}); ?> </pre>
</li>

<li>
Q: My search form opens via animated popup, so live search box doesn't appear in right place. What to do to solve that? 
<br><small>A: 
It happens when animation takes some time and after our live search box find it, it continues transition and changes its place.
To solve this issue you just need to use the code below. (note that ".icon-search" here should be replaced with your magnifying glass icon's class name.)
</small>
<pre>jQuery(".icon-search").on('click',function(){setTimeout(function(){guaven_woos_positioner(jQuery(guaven_woos.selector));},300);});
</pre>
</li>

</ol>

</div>


<p>
<input type="submit" class="button button-primary" value="Save settings">
</p>
</form>


<form action="" method="post" name="reset_form">
  <?php
wp_nonce_field('guaven_woos_reset_nonce', 'guaven_woos_reset_nonce_f');
?>


<p>
<br>
<input type="submit" onclick="return confirm('Are you sure to reset all settings to default?')" class="button button-default" value="Reset all settings to default">
</p>
</form>

</div>


<script>
function openSettingTab(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
document.getElementById("guaven_woos_tablink_live").click();
</script>
