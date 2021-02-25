jQuery(document).ready(function()
{
	wcuf_activate_new_category_select_box(".js-data-product-categories-ajax");
	wcuf_activate_new_product_select_box(".js-data-products-ajax");
	wcuf_activate_new_attibute_select_box(".js-data-attributes-ajax");
});
function wcuf_activate_new_product_select_box(selector)
{
	if(jQuery(selector).length < 1)
		return;
	
	jQuery(selector).selectWoo(
	{
	  ajax: {
		url: ajaxurl,
		dataType: 'json',
		delay: 250,
		width:380,
		multiple: true,
		data: function (params) {
		  return {
			product: params.term, // search term
			page: params.page,
			action: 'wcuf_get_products_list'
		  };
		},
		processResults: function (data, page) 
		{
	   
		   return {
			results: jQuery.map(data, function(obj) {
				return { id: obj.id, text: "<strong>(SKU: "+obj.product_sku+" ID: "+obj.id+")</strong> "+obj.product_name };
			})
			};
		},
		cache: true
	  },
	  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
	  minimumInputLength: 1,
	  templateResult: wcuf_formatRepo, 
	  templateSelection: wcuf_formatRepoSelection  
	});
}
function wcuf_activate_new_category_select_box (selector) 
{
	if(jQuery(selector).length < 1)
		return;
	
	jQuery(selector).selectWoo(
		{
			ajax: {
			url: ajaxurl,
			dataType: 'json',
			delay: 250,
			width:300,
			multiple: true,
			data: function (params) {
			  return {
				product_category: params.term, // search term
				page: params.page,
				action: 'wcuf_get_product_categories_list'
			  };
			},
			processResults: function (data, page) 
			{
		   
			   return {
				results: jQuery.map(data, function(obj) {
					return { id: obj.id, text: obj.category_name };
				})
				};
			},
			cache: true
		  },
		  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		  minimumInputLength: 3,
		  templateResult: wcuf_formatRepo, 
		  templateSelection: wcuf_formatRepoSelection  
		});
}
function wcuf_activate_new_attibute_select_box(selector)
{
	if(jQuery(selector).length < 1)
		return;
	
	jQuery(selector).selectWoo(
		{
			ajax: {
			url: ajaxurl,
			dataType: 'json',
			delay: 250,
			width:300,
			multiple: true,
			data: function (params) {
			  return {
				product_attribute: params.term, // search term
				page: params.page,
				action: 'wcuf_get_attributes_list'
			  };
			},
			processResults: function (data, page) 
			{
		   
			   return {
				results: jQuery.map(data, function(obj) {
					return { id: obj.id, text: obj.attribute_name };
				})
				};
			},
			cache: true
		  },
		  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		  minimumInputLength: 3,
		  templateResult: wcuf_formatRepo, 
		  templateSelection: wcuf_formatRepoSelection  
		});
}
function wcuf_formatRepo (repo) 
{
	if (repo.loading) return repo.text;
	
	var markup = '<div class="clearfix">' +
			'<div class="col-sm-12">' + repo.text + '</div>';
    markup += '</div>'; 
	
    return markup;
  }

  function wcuf_formatRepoSelection (repo) 
  {
	//In the latest version of select2/selectWoo, custom html for selection won't work. It is then stripped.
	var tmp = document.createElement("DIV");
	tmp.innerHTML = repo.full_name || repo.text;
	return tmp.textContent || tmp.innerText || ""; 
  }