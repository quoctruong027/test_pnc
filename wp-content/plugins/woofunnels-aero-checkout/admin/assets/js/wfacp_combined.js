!function(t){t.fn.xlAjaxChosen=function(e,n,a){var r,s,l,o;return null==e&&(e={}),null==a&&(a={}),s={minTermLength:3,afterTypeDelay:500,jsonTermKey:"term",keepTypingMsg:"Keep typing...",lookingForMsg:"Looking for"},o=this,r=null,l=t.extend({},s,t(o).data(),e),this.xlChosen(a||{}),this.each(function(){return t(this).next(".chosen-container").find(".search-field > input, .chosen-search > input").bind("keyup",function(){var a,s,u,c;if(cmb2_select_last_interact=t(this).parents(".chosen-container").prev("select"),u=t(this).val(),s=(c=t.trim(t(this).val())).length<l.minTermLength?l.keepTypingMsg:l.lookingForMsg+" '"+c+"'",o.next(".chosen-container").find(".no-results").text(s),0!==c.length)return c!==t(this).data("prevVal")&&(t(this).data("prevVal",c),this.timer&&clearTimeout(this.timer),!(c.length<l.minTermLength)&&(a=t(this),null==l.data&&(l.data={}),l.data[l.jsonTermKey]=c,null!=l.dataCallback&&(l.data=l.dataCallback(l.data)),l.success,l.success=function(r){var i,s,l;if(o=cmb2_select_last_interact,null!=r)return l=[],o.find("option").each(function(){return t(this).is(":selected")?l.push(t(this).val()+"-"+t(this).text()):t(this).remove()}),o.find("optgroup:empty").each(function(){return t(this).remove()}),i=null!=n?n(r,a):r,s=0,t.each(i,function(e,n){var a,r,i;return s++,n.group?((a=o.find("optgroup[label='"+n.text+"']")).size()||(a=t("<optgroup />")),a.attr("label",n.text).appendTo(o),t.each(n.items,function(e,n){var r,i;if(r="string"==typeof n?(i=e,n):(i=n.value,n.text),-1===t.inArray(i+"-"+r,l))return t("<option />").attr("value",i).html(r).appendTo(a)})):(r="string"==typeof n?(i=e,n):(i=n.value,n.text),-1===t.inArray(i+"-"+r,l)?t("<option />").attr("value",i).html(r).appendTo(o):void 0)}),s?o.trigger("chosen:updated"):(o.data().chosen.no_results_clear(),o.data().chosen.no_results(a.val())),null!=e.success&&e.success(r),a.val(u)},this.timer=setTimeout(function(){return r&&r.abort(),r=t.ajax(l)},l.afterTypeDelay)));selected_values_pre=[],o.find("option").each(function(){return t(this).is(":selected")?selected_values_pre.push(t(this).val()+"-"+t(this).text()):t(this).remove()});var h=[];return void 0!==o.attr("data-pre-data")&&(h=JSON.parse(o.attr("data-pre-data"))),t.each(h,function(e,n){if(text="string"==typeof n?(value=i,n):(value=n.value,n.text),-1===t.inArray(value+"-"+text,selected_values_pre))return t("<option />").attr("value",value).html(text).appendTo(o)}),o.trigger("chosen:updated"),!1})})}}(jQuery);

window.wfacp = (function ($) {
    return {
        "ajax": function (cls, is_form, cb) {
            const self = this;
            let element = null;
            let handler = {};
            let prefix = "wfacp_";
            this.action = null;

            this.data = function (form_data, formEl = null) {

                return form_data;
            };
            this.before_send = function (formEl) {
            };
            this.async = function (bool) {
                return bool;
            };
            this.method = function (method) {
                return method;
            };
            this.success = function (rsp, fieldset, loader, jqxhr, status) {
            };
            this.complete = function (rsp, fieldset, loader, jqxhr, status) {
            };
            this.error = function (rsp, fieldset, loader, jqxhr, status) {
                if (wfacp_localization.error.hasOwnProperty(rsp.status)) {
                    wfacp.swal(wfacp_localization.error[rsp.status]);
                }

                wfacp.hide_spinner();
            };
            this.action = function (action) {
                return action;
            };

            function reset_form(action, fieldset, loader, rsp, jqxhr, status) {
                if (fieldset.length > 0) {
                    fieldset.prop('disabled', false);
                }
                loader.remove();
                let loader2;
                loader2 = $(".bwf_ajax_btn_bottom_container");
                loader2.removeClass('ajax_loader_show');

                if (self.hasOwnProperty(action) === true && typeof self[action] === 'function') {
                    self[action](rsp, fieldset, loader, jqxhr, status);
                }
            }

            function form_post(action) {
                let formEl = element;
                let ajax_loader = null;

                let form_data = new FormData(formEl);

                form_data.append('action', action);

                form_data.append('wfacp_nonce', wfacp_secure.nonce);

                let form_method = $(formEl).attr('method');

                let method = (form_method !== "undefined" && form_method !== "") ? form_method : 'POST';
                if ($(formEl).find("." + action + "_ajax_loader").length === 0) {
                    $(formEl).find(".bwf_form_submit").prepend("<span class='" + action + "_ajax_loader spinner" + "'></span>");
                    ajax_loader = $(formEl).find("." + action + "_ajax_loader");
                } else {
                    ajax_loader = $(formEl).find("." + action + "_ajax_loader");
                }

                let ajax_loader2 = $(".bwf_ajax_btn_bottom_container");
                ajax_loader.addClass('ajax_loader_show');
                ajax_loader2.addClass('ajax_loader_show');

                let fieldset = $(formEl).find("fieldset");
                if (fieldset.length > 0) {
                    fieldset.prop('disabled', true);
                }

                self.before_send(formEl, action);

                let data = self.data(form_data, formEl);

                let request = {
                    url: ajaxurl,
                    async: self.async(true),
                    method: self.method('POST'),
                    data: data,
                    processData: false,
                    contentType: false,
                    //       contentType: self.content_type(false),
                    success: function (rsp, jqxhr, status) {
                        if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                            wfacp_secure.nonce = rsp.nonce;
                            delete rsp.nonce;
                        }
                        reset_form(action + "_ajax_success", fieldset, ajax_loader, rsp, jqxhr, status);
                        self.success(rsp, jqxhr, status, fieldset, ajax_loader);
                    },
                    complete: function (rsp, jqxhr, status) {
                        reset_form(action + "_ajax_complete", fieldset, ajax_loader, rsp, jqxhr, status);
                        self.complete(rsp, jqxhr, status, fieldset, ajax_loader);
                    },
                    error: function (rsp, jqxhr, status) {
                        reset_form(action + "_ajax_error", fieldset, ajax_loader, rsp, jqxhr, status);
                        self.error(rsp, jqxhr, status, fieldset, ajax_loader);
                    }
                };
                if (handler.hasOwnProperty(action)) {
                    clearTimeout(handler[action]);
                } else {
                    handler[action] = null;
                }
                handler[action] = setTimeout(
                    function (request) {
                        $.ajax(request);
                    }, 200, request
                );
            }

            function send_json(action) {
                let formEl = element;
                let data = self.data({}, formEl);
                if (typeof data === 'object') {
                    data.action = action;
                } else {
                    data = {
                        'action': action
                    };
                }

                self.before_send(formEl, action);
                data.wfacp_nonce = wfacp_secure.nonce;
                let request = {
                    url: ajaxurl,
                    async: self.async(true),
                    method: self.method('POST'),
                    data: data,
                    success: function (rsp, jqxhr, status) {

                        if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                            wfacp_secure.nonce = rsp.nonce;
                            delete rsp.nonce;
                        }
                        self.success(rsp, jqxhr, status, element);
                    },
                    complete: function (rsp, jqxhr, status) {

                        self.complete(rsp, jqxhr, status, element);
                    },
                    error: function (rsp, jqxhr, status) {
                        self.error(rsp, jqxhr, status, element);
                    }
                };
                if (handler.hasOwnProperty(action)) {
                    clearTimeout(handler[action]);
                } else {
                    handler[action] = null;
                }
                handler[action] = setTimeout(
                    function (request) {
                        $.ajax(request);
                    }, 200, request
                );
            }

            this.ajax = function (action, data) {
                if (typeof data === 'object') {
                    data.action = action;
                } else {
                    data = {
                        'action': action
                    };
                }

                data.action = prefix + action;
                self.before_send(document.body, action);
                data.wfacp_nonce = wfacp_secure.nonce;
                let request = {
                    url: ajaxurl,
                    async: self.async(true),
                    method: self.method('POST'),
                    data: data,
                    success: function (rsp, jqxhr, status) {
                        if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                            wfacp_secure.nonce = rsp.nonce;
                            delete rsp.nonce;
                        }
                        self.success(rsp, jqxhr, status, action);
                    },
                    complete: function (rsp, jqxhr, status) {
                        self.complete(rsp, jqxhr, status, action);
                    },
                    error: function (rsp, jqxhr, status) {

                        self.error(rsp, jqxhr, status, action);
                    }
                };
                if (handler.hasOwnProperty(action)) {
                    clearTimeout(handler[action]);
                } else {
                    handler[action] = null;
                }
                handler[action] = setTimeout(
                    function (request) {
                        $.ajax(request);
                    }, 200, request
                );
            };

            function form_init(cls) {
                if ($(cls).length > 0) {

                    $(cls).on("submit", function (e) {
                        e.preventDefault();
                        let action = $(this).data('bwf-action');
                        if (action !== 'undefined') {
                            action = prefix + action;
                            action = action.trim();
                            element = this;
                            self.action = action;
                            form_post(action);
                        }
                    });

                    if (typeof cb === 'function') {

                        cb(self);
                    }
                }
            }

            function click_init(cls) {
                if ($(cls).length > 0) {
                    $(cls).on("click", function (e) {
                            e.preventDefault();
                            let action = $(this).data('bwf-action');
                            if (action !== 'undefined') {
                                action = prefix + action;
                                action = action.trim();
                                element = this;
                                self.action = action;
                                send_json(action);
                            }
                        }
                    );

                    if (typeof cb === 'function') {
                        cb(self);
                    }
                }
            }

            if (is_form === true) {
                form_init(cls, cb);
                return this;
            }

            if (is_form === false) {
                click_init(cls, cb);
                return this;
            }

            return this;
        },
        "hooks": {
            hooks: {
                action: {},
                filter: {}
            },
            addAction: function (action, callable, priority = 10, tag = '') {
                this.addHook('action', action, callable, priority, tag);
            },
            addFilter: function (action, callable, priority = 10, tag = '') {
                this.addHook('filter', action, callable, priority, tag);
            },
            doAction: function (action) {
                this.doHook('action', action, arguments);
            },
            applyFilters: function (action) {
                return this.doHook('filter', action, arguments);
            },
            removeAction: function (action, tag) {
                this.removeHook('action', action, tag);
            },
            removeFilter: function (action, priority, tag) {
                this.removeHook('filter', action, priority, tag);
            },
            addHook: function (hookType, action, callable, priority, tag) {
                if (undefined == this.hooks[hookType][action]) {
                    this.hooks[hookType][action] = [];
                }
                let hooks = this.hooks[hookType][action];
                if (undefined == tag) {
                    tag = action + '_' + hooks.length;
                }
                if (priority == undefined) {
                    priority = 10;
                }

                this.hooks[hookType][action].push({
                    tag: tag,
                    callable: callable,
                    priority: priority
                });
            },
            doHook: function (hookType, action, args) {

                // splice args from object into array and remove first index which is the hook name
                args = Array.prototype.slice.call(args, 1);
                if (undefined != this.hooks[hookType][action]) {
                    let hooks = this.hooks[hookType][action],
                        hook;
                    //sort by priority
                    hooks.sort(
                        function (a, b) {
                            return a.priority - b.priority;
                        }
                    );

                    for (let i = 0; i < hooks.length; i++) {
                        hook = hooks[i].callable;
                        if (typeof hook != 'function') {
                            hook = window[hook];
                        }
                        if ('action' == hookType) {
                            hook.apply(null, args);
                        } else {
                            args[0] = hook.apply(null, args);
                        }
                    }
                }
                if ('filter' == hookType) {
                    return args[0];
                }
            },
            removeHook: function (hookType, action, priority, tag) {
                if (undefined != this.hooks[hookType][action]) {
                    let hooks = this.hooks[hookType][action];
                    for (let i = hooks.length - 1; i >= 0; i--) {
                        if ((undefined == tag || tag == hooks[i].tag) && (undefined == priority || priority == hooks[i].priority)) {
                            hooks.splice(i, 1);
                        }
                    }
                }
            }
        },
        "tools": {
            /**
             * get keys length of object and array
             * @param obj
             * @returns {number}
             */
            ol: function (obj) {
                let c = 0;
                if (obj != null && typeof obj === "object") {
                    c = Object.keys(obj).length;
                }
                return c;
            },
            isEmpty: function (obj) {
                for (let key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        return false;
                    }
                }
                return true;
            },
            /**
             * Check property exist in object or Array
             * @param obj
             * @param key
             * @returns {boolean}
             */
            hp: function (obj, key) {
                let c = false;
                if (typeof obj === "object" && key !== undefined) {
                    c = obj.hasOwnProperty(key);
                }
                return c;
            },
            /**
             * Convert destroy refresh and reconvert into in json without refrence
             * @param obj
             * @returns {*}
             */
            jsp: function (obj) {
                if (typeof obj === 'object') {
                    let doc = JSON.stringify(obj);
                    doc = JSON.parse(doc);
                    return doc;
                } else {
                    return obj;
                }
            },
            /**
             * get object keys array
             * @param obj
             * @returns Array
             */
            kys: function (obj) {
                if (typeof obj === 'object' && obj != null && this.ol(obj) > 0) {
                    return Object.keys(obj);
                }
                return [];
            },
            ucfirst: (string) => {
                return string.replace(/^\w/, c => c.toUpperCase());
            },
            stripHTML: function (dirtyString) {
                let dirty = $("<div>" + dirtyString + "</div>");
                return dirty.text();
            },
            string_to_bool: (content = '') => {
                if ('' === content || 'false' == content) {
                    return false;
                }

                return (typeof content === "boolean") ? content : ('yes' === content || 1 === content || 'true' === content || '1' === content);
            },
            is_object: function (options) {
                if (options == null) {
                    return false;
                }

                if (typeof options === 'object') {
                    return true;
                }
                return false;
            },
            is_bool: function (mixed_var) {
                mixed_var = wfacp.tools.string_to_bool(mixed_var);
                return (typeof mixed_var === 'boolean');
            },
            timestamp: function () {
                let date = new Date();
                return date.getTime();
            }
        },
        "swal": (property) => {
            return wfacp_swal($.extend({
                title: '',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0073aa',
                cancelButtonColor: '#e33b3b',
                confirmButtonText: ''
            }, property));
        },

        "model": {
            'add_product': () => {
                let modal_add_add_product = $("#modal-add-product");
                if (modal_add_add_product.length > 0) {
                    modal_add_add_product.iziModal(
                        {
                            title: 'Add Product',
                            headerColor: '#6dbe45',
                            background: '#efefef',
                            borderBottom: false,
                            width: 600,
                            overlayColor: 'rgba(0, 0, 0, 0.6)',
                            transitionIn: 'fadeInDown',
                            transitionOut: 'fadeOutDown',
                            navigateArrows: "false",
                            onOpening: function (modal) {
                                modal.startLoading();
                            },
                            onOpened: function (modal) {
                                modal.stopLoading();
                                // product_search_setting(modal);
                            },
                            onClosed: function (modal) {
                            }
                        }
                    );
                }
            }
        },
        'sortable': (ui_class, start, stop, hover, item_class = '.wfacp_item_drag', non_drag_class = '.ui-state-disabled', placeholder = '', extra_options = {}) => {


            let sortable = $(ui_class);
            if (sortable.length === 0)
                return;


            sortable.off('sortable');

            let options = {
                connectWith: ui_class,
                start: function (event, ui) {
                    start(event, ui);
                },

                stop: function (event, ui) {
                    stop(event, ui);
                },
                over: function (event, ui) {
                    hover('in', event, ui);
                },
                out: function (event, ui) {
                    hover('out', event, ui);
                },
                //axis: 'y',
                cursor: 'move'
            };
            if (wfacp.tools.ol(extra_options) > 0) {
                for (let i in extra_options) {
                    options[i] = extra_options[i];
                }
            }
            let drag_item_class = '.wfacp_item_drag';
            if (item_class !== '') {
                drag_item_class = item_class;
            }
            if (non_drag_class !== '') {
                drag_item_class += ':not(' + non_drag_class + ')';
            }

            options.items = drag_item_class;

            if ('' !== placeholder) {
                options.placeholder = placeholder;
            }

            sortable.sortable(options);
            sortable.disableSelection();


        },

        'addClass': (el, cl) => {
            $(el).addClass(cl);
        },
        'removeClass': (el, cl) => {
            $(el).removeClass(cl);
        },
        show_spinner: () => {
            let spinner = $('.wfacp_spinner.spinner');

            if (spinner.length > 0) {
                spinner.css("visibility", "visible");
            }
        },
        hide_spinner: function hide_spinner() {
            var spinner = $('.wfacp_spinner.spinner');

            if (spinner.length > 0) {
                spinner.css("visibility", "hidden");
            }
        },
        show_data_save_model: (title = '') => {
            if ('' !== title) {
                wfacp.show_saved_model.iziModal('setTitle', title);
                wfacp.show_saved_model.iziModal('open');
            }

        },
        is_global_checkout() {
            return (wfacp.tools.hp(wfacp_data, 'global_settings') && wfacp.tools.hp(wfacp_data.global_settings, 'override_checkout_page_id') && wfacp_data.global_settings.override_checkout_page_id == wfacp_data.id);
        },
        editorConfig: {
            //'mediaButtons': true,
            "tinymce": {
                "theme": "modern",
                "skin": "lightgray",
                "language": "en",
                "formats": {
                    "alignleft": [{"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", "styles": {"textAlign": "left"}}, {"selector": "img,table,dl.wp-caption", "classes": "alignleft"}],
                    "aligncenter": [{"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", "styles": {"textAlign": "center"}}, {"selector": "img,table,dl.wp-caption", "classes": "aligncenter"}],
                    "alignright": [{"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", "styles": {"textAlign": "right"}}, {"selector": "img,table,dl.wp-caption", "classes": "alignright"}],
                    "strikethrough": {"inline": "del"}
                },
                "relative_urls": false,
                "remove_script_host": false,
                "convert_urls": false,
                "browser_spellcheck": true,
                "fix_list_elements": true,
                "entities": "38,amp,60,lt,62,gt",
                "entity_encoding": "raw",
                "keep_styles": false,
                "cache_suffix": "wp-mce-4800-20180716",
                "resize": "vertical",
                "menubar": false,
                "branding": false,
                "preview_styles": "font-family font-size font-weight font-style text-decoration text-transform",
                "end_container_on_empty_block": true,
                "wpeditimage_html5_captions": true,
                "wp_lang_attr": "en-US",
                "wp_keep_scroll_position": false,
                "wp_shortcut_labels": {
                    "Heading 1": "access1",
                    "Heading 2": "access2",
                    "Heading 3": "access3",
                    "Heading 4": "access4",
                    "Heading 5": "access5",
                    "Heading 6": "access6",
                    "Paragraph": "access7",
                    "Blockquote": "accessQ",
                    "Underline": "metaU",
                    "Strikethrough": "accessD",
                    "Bold": "metaB",
                    "Italic": "metaI",
                    "Code": "accessX",
                    "Align center": "accessC",
                    "Align right": "accessR",
                    "Align left": "accessL",
                    "Justify": "accessJ",
                    "Cut": "metaX",
                    "Copy": "metaC",
                    "Paste": "metaV",
                    "Select all": "metaA",
                    "Undo": "metaZ",
                    "Redo": "metaY",
                    "Bullet list": "accessU",
                    "Numbered list": "accessO",
                    "Insert/edit image": "accessM",
                    "Remove link": "accessS",
                    "Toolbar Toggle": "accessZ",
                    "Insert Read More tag": "accessT",
                    "Insert Page Break tag": "accessP",
                    "Distraction-free writing mode": "accessW",
                    "Add Media": "accessM",
                    "Keyboard Shortcuts": "accessH"
                },
                "toolbar1": "bold,italic,bullist,numlist,link",
                "wpautop": false,
                "indent": true,
                "elementpath": false,
                "plugins": "charmap,colorpicker,hr,lists,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern",

            }, "quicktags": {"buttons": "strong,em,link,ul,ol,li,code"},

        }

    };
})(jQuery);



(function ($) {
    'use strict';

    class wfacp_products {
        /**
         *
         * @var builder wfacp_builder
         */
        constructor(builder) {
            let el = document.getElementById("wfacp_product_container");
            if (null === el) {
                return;
            }
            this.init(builder);

        }

        init(builder) {
            this.search_timeout = null;
            this.search_model_open = null;
            this.show_saved_model = null;
            this.id = builder.id;
            this.name = builder.name;
            this.wfacp_id = builder.id;
            this.currency = builder.currency;
            this.products = builder.products;
            this.add_to_cart_setting = builder.products_settings.add_to_cart_setting;
            this.last_active_product = '';
            this.addProductModel();
            this.main();
        }


        /**
         * this function initialize vue.js in product section
         * @returns {boolean}
         */
        main() {
            let self = this;
            this.product_vue = new Vue({
                'el': "#wfacp_product_container",
                created: function () {
                    wfacp.hooks.doAction('wfacp_product_ui_created', this);
                    this.sortable();
                },

                methods: {
                    sortable() {

                        setTimeout(() => {
                            let $tbody = $('.wfacp_product_table_sortable');
                            $tbody.sortable({
                                items: 'tr',
                                cursor: 'move',
                                axis: 'y',
                                handle: 'td.wfacp_product_drag_handle .wfacp_drag_handle',
                                scrollSensitivity: 40,
                                stop: (event, ui) => {
                                    this.StopSortable(event, ui);
                                },
                            });
                        }, 800);
                    },
                    StopSortable(event, element) {
                    },
                    /**
                     * checking product is object
                     * @returns {boolean}
                     */
                    isEmpty() {
                        return (wfacp.tools.ol(this.products) === 0);
                    },
                    /**
                     * Add product to table and display new element in bottom of table
                     * @param unique_id
                     * @param product
                     */
                    addProduct(unique_id, product) {
                        $('.wfacp-preview').show();
                        $('.wfacp_s_menu_rules').removeClass('wfacp_stop_navigation');
                        Vue.set(this.products, unique_id, product);

                        this.save_products();
                    },
                    /**
                     * Delete product from list
                     * @param unique_id
                     */
                    removeProduct(unique_id) {
                        let swl = wfacp.swal(wfacp_localization.global.remove_product);
                        swl.then(result => {
                            if (result.value) {
                                self.formHasChanges = true;
                                self.removeProduct(unique_id);
                            }
                        });
                        swl.catch(err => {
                        });

                    },

                    /**
                     * Set input value in product child level property field like discount_amount
                     * @param index product index
                     * @param property property like discount_amount
                     * @param event   event object of current input
                     */
                    set_product_data(index, property, event) {
                        if (event.target != null) {
                            self.formHasChanges = true;
                            let real_value = event.target.value ? event.target.value : '';
                            this.last_active_product = index;
                            if ('discount_amount' === property) {
                                let discount_type = this.products[index].discount_type;

                                if ('percent_discount_sale' === discount_type || 'percent_discount_reg' === discount_type) {
                                    if (real_value > 100) {
                                        real_value = 100;
                                    }
                                }
                            } else if ('discount_type' === property) {
                                let discount_amount = this.products[index].discount_amount;
                                if ('percent_discount_sale' === real_value || 'percent_discount_reg' === real_value) {
                                    discount_amount = 0;
                                    Vue.set(this.products[index], 'discount_amount', discount_amount);
                                }
                            }
                            Vue.set(this.products[index], property, real_value);
                        }
                    },

                    /**
                     * Remove non required keys from product object
                     */
                    remove_non_required_keys(products) {
                        if (wfacp.tools.ol(products) > 0) {

                            products = wfacp.tools.jsp(products);
                            for (let i in products) {
                                delete products[i].price;
                                delete products[i].regular_price;
                                delete products[i].sale_price;
                                delete products[i].image;
                            }
                        }
                        return products;

                    },
                    /**
                     * Save products to database
                     * with additional details
                     */
                    save_products() {
                        let wp_ajax = new wfacp.ajax();
                        let products = this.remove_non_required_keys(this.products);
                        let temp_product = {};
                        let rows = $('.wfacp-product-row');
                        if (rows.length === 0) {
                            temp_product = products;
                        } else {
                            rows.each(function () {
                                let id = $(this).attr('id');
                                temp_product[id] = products[id];
                            });
                        }
                        let save_products = {
                            'products': temp_product,
                            'wfacp_id': self.id,
                            'settings': {'add_to_cart_setting': this.add_to_cart_setting},
                            'last_active_product': this.last_active_product
                        };
                        wp_ajax.ajax('save_products', save_products);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {

                            wfacp.show_data_save_model(rsp.msg);
                            this.sortable();
                        };
                    }
                },
                data: {
                    have_variable: false,
                    name: this.name,
                    currency: this.currency,
                    products: this.products,
                    add_to_cart_setting: this.add_to_cart_setting,
                    last_active_product: this.last_active_product
                }
            });
            return this.product_vue;

        }

        addProducts(data) {
            if (wfacp.tools.hp(data, 'products') && wfacp.tools.ol(data.products) > 0) {
                let products = data.products;
                for (let unique_id in products) {
                    this.product_vue.addProduct(unique_id, products[unique_id]);
                }
            }
        }

        removeProduct(unique_id) {
            let tr = $("#" + unique_id);
            if (tr.length > 0) {
                tr.find(".wfacp_overlay_active").removeClass("wfacp_overlay_none_here");
            }

            let wp_ajax = new wfacp.ajax();
            wp_ajax.ajax('remove_product', {'product_key': unique_id, 'wfacp_id': this.id});
            wp_ajax.complete = (rsp) => {
                if (wfacp.tools.ol(rsp) > 0) {
                    Vue.delete(this.product_vue.products, unique_id);
                    if (wfacp.tools.ol(this.product_vue.products) === 0) {
                        $('.wfacp-preview').hide();
                        $('.wfacp_s_menu_rules').addClass('wfacp_stop_navigation');
                    } else {
                        $(".wfacp_overlay_active").addClass('wfacp_overlay_none_here');
                    }
                }
            };
        }

        addProductVue(modal) {
            if (this.search_model_open === true) {
                return;
            }
            this.search_model_open = true;
            let self = this;

            this.add_product_vue = new Vue({
                'el': '#modal-add-product-form',
                components: {
                    Multiselect: window.VueMultiselect.default
                },
                data: {
                    modal: modal,
                    isLoading: false,
                    products: [],
                    is_single: '',
                    include_variations: false,
                    selectedProducts: []
                },
                methods: {
                    onSubmit: function () {
                        let wp_ajax = new wfacp.ajax();
                        let vthis = this;
                        vthis.modal.startLoading();
                        let selected_products = [];
                        let products = this.selectedProducts;
                        if (wfacp.tools.ol(this.selectedProducts) > 0) {
                            for (let pid in products) {
                                selected_products.push(products[pid].id);
                            }
                        }
                        let add_query = {'products': selected_products, 'wfacp_id': self.id};
                        wp_ajax.ajax('add_product', add_query);
                        wp_ajax.success = (rsp) => {
                            if (wfacp.tools.ol(rsp) > 0) {
                                self.addProducts(rsp);
                            }
                            $("#modal-add-product").iziModal('close');
                        };
                        wp_ajax.complete = () => {
                            vthis.clearAll();
                            vthis.modal.stopLoading();
                        };
                    },
                    asyncFind(query) {
                        this.isLoading = true;
                        if (query !== "") {
                            clearTimeout(self.search_timeout);
                            self.search_timeout = setTimeout((query) => {
                                let wp_ajax = new wfacp.ajax();
                                let product_query = {'term': query, 'variations': this.include_variations};
                                wp_ajax.ajax('product_search', product_query);
                                wp_ajax.success = (rsp) => {
                                    this.products = rsp;
                                    this.isLoading = false;
                                };
                                wp_ajax.complete = () => {
                                    this.isLoading = false;
                                };
                            }, 1000, query);
                        } else {
                            this.isLoading = false;
                        }
                    },
                    clearAll() {
                        this.products = [];
                        this.selectedProducts = [];
                        this.isLoading = false;
                    }
                }
            });
            return this.add_product_vue;
        }


        addProductModel() {
            let self = this;
            let modal_add_add_product = $("#modal-add-product");
            if (modal_add_add_product.length > 0) {
                modal_add_add_product.iziModal({
                    title: wfacp_localization.global.add_product_popup,
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    width: 600,
                    overlayColor: 'rgba(0, 0, 0, 0.6)',
                    transitionIn: 'fadeInDown',
                    transitionOut: 'fadeOutDown',
                    navigateArrows: "false",
                    onOpening: function (modal) {
                        modal.startLoading();
                    },
                    onOpened: function (modal) {
                        modal.stopLoading();
                        self.addProductVue(modal);
                    },
                    onClosed: function (modal) {
                    }
                });
            }
        }
    }

    class wfacp_layouts {
        constructor(builder) {
            this.add_field_model_open = false;
            this.add_section_model_open = false;
            this.edit_field_model_open = false;
            this.step_name = 'single_step';
            this.current_edit_field = {};
            this.field_index = null;
            let el = document.getElementById("wfacp_layout_container");
            if (el != null) {
                this.builder = builder;
                this.id = builder.id;
                this.steps = builder.layout.steps;
                this.input_fields = builder.layout.input_fields;
                this.available_fields = builder.layout.available_fields;
                this.fieldsets = builder.layout.fieldsets;
                this.available_steps = ['single_step', 'two_step', 'third_step'];
                this.model();
                this.main();
                this.editFieldVue();
                wfacp.hooks.addFilter('wfacp_field_data_merge_with_model', (model, data) => {
                    return this.field_data_merge_with_model(model, data);
                });
                wfacp.hooks.addFilter('wfacp_before_field_save', (data, model) => {
                    return this.wfacp_before_field_save(data, model);
                });
            }
        }

        main() {
            let self = this;

            this.layout_vue = new Vue({
                    el: "#wfacp_layout_container",
                    created: function () {
                        this.enableSortable();
                        wfacp.hooks.doAction('wfacp_layout_created', this);
                        this.enableTabs();
                        this.validateDependency();
                    },
                    methods: {

                        enableTabs() {
                            let $this = this;
                            setTimeout(() => {
                                $(document).on('click', ".wfacp_template_tabs", function () {
                                    if ($(this).hasClass('wfacp_active_tabs')) {
                                        return;
                                    }
                                    let slug = $(this).data('slug');
                                    $this.show_template_frame(slug);
                                });
                            }, 1500);

                        },
                        addNewStep() {
                            if (self.available_steps.indexOf(this.current_step) > -1) {

                                if ('single_step' === this.current_step) {
                                    this.set_template('two_step');
                                    Vue.set(this.steps.two_step, 'active', 'yes');
                                } else if ('two_step' === this.current_step) {
                                    this.set_template('third_step');
                                    Vue.set(this.steps.third_step, 'active', 'yes');
                                }
                            }
                        },
                        deleteStep(step) {
                            if (self.available_steps.indexOf(step) > -1) {

                                if ('two_step' === step) {
                                    this.moveStepField('two_step');
                                    this.set_template('single_step');
                                    Vue.set(this.steps.two_step, 'active', 'no');
                                } else if ('third_step' === step) {
                                    this.moveStepField('third_step');
                                    this.set_template('two_step');
                                    Vue.set(this.steps.third_step, 'active', 'no');
                                }
                            }
                        },
                        set_template(template) {

                            Vue.set(this, 'current_step', template);
                            this.enableSortable();
                            this.show_template_frame(template);

                        },
                        show_template_frame(template) {

                            setTimeout(() => {
                                let frame = $(".single_step_template[data-slug='" + template + "']");
                                let tabs = $(".wfacp_template_tabs[data-slug='" + template + "']");
                                if (frame.length > 0) {
                                    $(".wfacp_template_tabs").removeClass('wfacp_active_tabs');
                                    $(".single_step_template").hide();
                                    tabs.addClass('wfacp_active_tabs');
                                    frame.show();
                                }
                            }, 0, template);
                        },
                        enableSortable() {
                            setTimeout(() => {
                                // for item drag
                                let placeholder = 'wfacp_save_btn_style ui-sortable-placeholder ui-state-highlight';
                                wfacp.sortable('.template_field_container', this.StartSortable, this.StopSortable, this.hoverDrag, '', '', placeholder);


                                // for steps dragging
                                wfacp.sortable('.wfacp_sections_holder.single_step', this.StartSectionSortable, this.StopSectionSortable, () => {
                                }, '.wfacp_field_container', '', '', {'axis': 'y'});
                                wfacp.sortable('.wfacp_sections_holder.two_step', this.StartSectionSortable, this.StopSectionSortable, () => {
                                }, '.wfacp_field_container');

                                wfacp.sortable('.wfacp_sections_holder.third_step', this.StartSectionSortable, this.StopSectionSortable, () => {
                                }, '.wfacp_field_container');
                            }, 300);
                        },

                        StartSectionSortable(event, element) {
                            $(event.srcElement).parents(".wfacp_field_container").addClass('highlight_field_container');
                        },

                        StopSectionSortable(event, element) {
                            $(event.srcElement).parents(".wfacp_field_container").removeClass('highlight_field_container');
                        },
                        hoverDrag(type, event, element) {
                            let placeholder = $(event.target).find('.template_field_placeholder_tbl');
                            if ("in" === type) {
                                if ($(event.srcElement).parents(".wfacp_field_container")[0] !== $(event.target).parents(".wfacp_field_container")[0]) {
                                    $(event.target).parents(".wfacp_field_container").addClass('highlight_field_container');

                                    if (placeholder.length > 0) {
                                        placeholder.hide();
                                    }

                                }
                            } else {
                                if (placeholder.length > 0) {
                                    placeholder.show();
                                }
                                $(event.target).parents(".wfacp_field_container").removeClass('highlight_field_container');
                            }
                        },
                        /**
                         * this function when element drag internal
                         * @param event
                         * @param element
                         * @constructor
                         */
                        StartSortable(event, element) {
                            let text_placeholder = $('.wfacp_save_btn_style.ui-sortable-placeholder.ui-state-highlight');
                            if ($(element.item[0]).length > 0 && text_placeholder.length > 0) {
                                text_placeholder.width($(element.item[0]).width());
                                text_placeholder.html("<span class='wfacp_placeholder_text_here'>placeholder</span>");
                            }
                            wfacp.addClass('.wfacp_field_container', 'activate_dragging_field');
                        },
                        /**
                         * Reposition of fields array
                         * @param event HTMLElementEvent
                         * @param element
                         * @constructor
                         */
                        StopSortable(event, element) {

                            $('.wfacp_field_container').removeClass('activate_dragging_field highlight_field_container');
                            let new_parent = $(event.toElement).parents('.wfacp_field_container');
                            let old_parent = $(event.target);
                            let unique_id;
                            let listElement = $(event.toElement);
                            if (event.toElement.localName === 'span') {
                                listElement = $(event.toElement).parent('div');
                            }
                            unique_id = listElement.attr('data-id');
                            let step_name = new_parent.attr('step-name');
                            let old_step_name = old_parent.attr('step-name');
                            let new_index = new_parent.attr('field-index');
                            let old_index = old_parent.attr('field-index');
                            if (step_name !== old_step_name) {
                                listElement.remove();
                                console.log('parentSwitching');
                                this.parentSwitching(old_step_name, step_name, old_index, new_index, unique_id);
                            } else {
                                // Same step Switching
                                if (new_index !== old_index) {
                                    listElement.remove();
                                    console.log('updateInput');
                                    this.updateInput(step_name, old_index, new_index, unique_id);

                                } else {

                                }
                            }
                        },
                        elementSorting(event, element) {
                        },
                        dragStart(event) {

                            self.dragStart(event);
                        },
                        allowDrop(event) {

                            self.allowDrop(event);
                        },
                        dragEnd(event) {


                            self.dragEnd(event);
                        },
                        dragEnter(event) {

                            self.dragEnter(event);
                        },
                        dragLeave(event) {

                            self.dragLeave(event);
                        },


                        /**
                         * step_name means single step of multistep
                         * @param event
                         * @param section fieldset section
                         * @param step_name
                         */
                        drop(event, step_name, section) {

                            let el_data = self.drop(event);
                            if (el_data === false) {
                                return null;
                            }
                            let unique_id = el_data[0];
                            let input_section = el_data[1];

                            if (input_section === '')
                                return null;


                            this.addInput(step_name, section, unique_id, input_section);
                        },
                        /**
                         * Add input field this work when person drag element from right to section container
                         * @param step_name
                         * @param section
                         * @param unique_id
                         * @param input_section
                         * @returns {null}
                         */
                        addInput(step_name, section, unique_id, input_section) {

                            let allowEnter = true;

                            let sub_section = $(".single_step").find(".wfacp_field_container[field-index=" + section + "]");
                            if (sub_section.length > 0) {
                                if (sub_section.find('.wfacp_item_drag[data-id=' + unique_id + ']').length > 0) {
                                    allowEnter = false;
                                }
                            }

                            if (!allowEnter) {
                                return;
                            }

                            let fields;
                            fields = wfacp.tools.jsp(this.input_fields[input_section][unique_id]);
                            fields.id = unique_id;
                            fields.field_type = input_section;
                            this.pushFieldInStep(step_name, section, fields);
                            Vue.delete(this.input_fields[input_section], unique_id);

                            this.validateDependency();
                            $(".wfacp_field_container").removeClass('highlight_field_container');
                            $(".wfacp_field_container").removeClass('highlight_field_container activate_dragging_field');
                        },
                        getFieldFromSection(step_name, section, unique_id) {

                            let index = this.findFieldIndex(step_name, section, unique_id);
                            if (index == null)
                                return null;

                            let fields = wfacp.tools.jsp(this.fieldsets[step_name][section].fields[index]);
                            if (!wfacp.tools.hp(this.fieldsets[step_name][section], 'fields')) {
                                Vue.set(this.fieldsets[step_name][section], 'fields', []);
                            }
                            return fields;
                        },
                        pushFieldInStep(step_name, section, fields) {
                            if (!wfacp.tools.hp(this.fieldsets[step_name][section], 'fields')) {
                                Vue.set(this.fieldsets[step_name][section], 'fields', []);
                            }
                            let step_section_fields = wfacp.tools.jsp(this.fieldsets[step_name][section].fields);
                            step_section_fields.push(fields);

                            Vue.set(this.fieldsets[step_name][section], 'fields', step_section_fields);
                            this.hideFieldContainerPlaceholder(step_name, section);
                        },
                        parentSwitching(old_step_name, step_name, old_index, new_index, unique_id) {
                            let fields = this.getFieldFromSection(old_step_name, old_index, unique_id);
                            if (null !== fields) {
                                this.pushFieldInStep(step_name, new_index, fields);
                                let old_section_fields = wfacp.tools.jsp(this.fieldsets[old_step_name][old_index].fields);
                                Vue.set(this.fieldsets[old_step_name][old_index], 'fields', old_section_fields);
                            }
                        },

                        /**
                         * Update field this work when person drag element btw section container
                         * @param step_name
                         * @param old_index
                         * @param new_index
                         * @param unique_id
                         * @returns {null}
                         */
                        updateInput(step_name, old_index, new_index, unique_id) {
                            let fields = this.getFieldFromSection(step_name, old_index, unique_id);

                            if (null == fields) {
                                return;
                            }
                            this.pushFieldInStep(step_name, new_index, fields);
                        },
                        /**
                         * Move two step section field back to input fields
                         * @returns {boolean}
                         */
                        moveStepField(current_step) {

                            wfacp.hooks.doAction('manage_step_field', wfacp.tools.jsp(current_step), this);
                            let section_fields = wfacp.tools.jsp(this.fieldsets[current_step]);
                            if (typeof section_fields === "undefined" || wfacp.tools.ol(section_fields) === 0) {
                                return;
                            }
                            for (let step in section_fields) {
                                if (wfacp.tools.ol(section_fields[step].fields) === 0) {
                                    continue;
                                }
                                let fields = section_fields[step].fields;
                                for (let i = 0; i < fields.length; i++) {
                                    let id = fields[i].id;
                                    let field_type = fields[i].field_type;
                                    Vue.set(this.input_fields[field_type], id, fields[i]);
                                }
                                Vue.set(this.fieldsets[current_step][step], 'fields', []);
                            }
                        },
                        /**
                         * Remove field from steps objects
                         * @param step_name
                         * @param unique_id input field ID
                         * @param field_type Means Billing or shipping
                         * @param section  section index number 0,1,2
                         * @returns {null|true}
                         */
                        removeField(step_name, unique_id, field_type, section, event) {
                            event.stopPropagation();

                            let index = this.findFieldIndex(step_name, section, unique_id);
                            if (index == null) {
                                return null;
                            }

                            let step_section_fields = wfacp.tools.jsp(this.fieldsets[step_name][section].fields);
                            let fields = step_section_fields[index];
                            step_section_fields.splice(index, 1);

                            Vue.set(this.fieldsets[step_name][section], 'fields', step_section_fields);
                            if (wfacp.tools.ol(fields) > 0) {
                                Vue.set(this.input_fields[field_type], unique_id, fields);
                            }

                            this.showFieldContainerPlaceholder(step_name, section);
                            this.validateDependency();
                            return true;
                        },

                        /**
                         * Find index of element in steps array
                         * @param step_name
                         * @param unique_id
                         * @param section
                         * @returns {*}
                         */
                        findFieldIndex(step_name, section, unique_id) {
                            if (!wfacp.tools.hp(this.fieldsets[step_name][section], 'fields')) {
                                this.fieldsets[step_name][section].fields = [];
                                return null;
                            }

                            let out = wfacp.tools.jsp(this.fieldsets[step_name][section].fields);
                            if (out.length === 0) {
                                return null;
                            }
                            for (let i = 0; i < out.length; i++) {
                                if (out[i].id === unique_id) {
                                    return i;
                                }
                            }
                            return null;
                        },


                        prepareErrorMsg(required_fields, separator = '') {
                            let msg = [];

                            for (let key in required_fields) {

                                msg.push(required_fields[key]);
                            }
                            return msg.join(separator);
                        },
                        getRequiredField() {
                            return wfacp.tools.jsp(wfacp_localization.fields.input_field_error);
                        },
                        getEmptySteps() {
                            return wfacp.tools.jsp(wfacp_localization.fields.steps_error_msgs);
                        },
                        /**
                         *Return Sorted all input fields from field container
                         * @returns Object steps and remaining required fied
                         *
                         */


                        getSortedElement() {
                            let self = this;
                            let required_fields = this.getRequiredField();
                            let empty_steps_errors = this.getEmptySteps();
                            let empty_steps = {};
                            let have_billing_address = false;
                            let have_shipping_address = false;
                            let have_billing_address_index = 0;
                            let have_shipping_address_index = 1;
                            let enabled_product_switching = 'no';
                            let have_coupon_field = false;
                            let have_shipping_method = false;
                            let templates = $('.single_step_template');
                            let new_step_field = {'single_step': [], 'two_step': [], 'third_step': []};
                            let html_fields = ['order_summary', 'order_coupon', 'product_switching', 'shipping_calculator'];
                            if (templates.length > 0) {
                                let field_index = 1;
                                templates.each(function () {
                                    let new_section = 0;
                                    let step_name = $(this).attr('data-slug');

                                    if ($(this).find(".wfacp_item_drag").length === 0) {
                                        empty_steps[step_name] = empty_steps_errors[step_name];
                                        return;
                                    }

                                    if (!wfacp.tools.hp(self.fieldsets, step_name)) {
                                        empty_steps[step_name] = empty_steps_errors[step_name];
                                        return;
                                    }
                                    let temp_step_data = wfacp.tools.jsp(self.fieldsets[step_name]);


                                    if (0 === temp_step_data.length) {
                                        empty_steps[step_name] = empty_steps_errors[step_name];
                                        return;
                                    }


                                    let field_container = $(this).find('.wfacp_field_container');

                                    if (field_container.length > 0) {

                                        field_container.each(function (i, d) {
                                            let el = $(this);
                                            let section = el.attr('field-index');// field set index
                                            let fields_el = el[0].querySelectorAll('.wfacp_item_drag');

                                            if (false === wfacp.tools.hp(new_step_field, step_name)) {
                                                new_step_field[step_name] = wfacp.tools.jsp(temp_step_data);
                                            }
                                            if (fields_el.length === 0) {
                                                return;
                                            }

                                            new_step_field[step_name][new_section] = wfacp.tools.jsp(temp_step_data[section]);
                                            new_step_field[step_name][new_section].fields = [];
                                            new_step_field[step_name][new_section].html_fields = {};
                                            let steps_fields = wfacp.tools.jsp(temp_step_data[section].fields);
                                            fields_el.forEach((ele, el_index) => {
                                                let id = ele.getAttribute('data-id');
                                                if (id === 'address') {
                                                    have_billing_address = true;
                                                    have_billing_address_index = field_index;
                                                }
                                                if (id === 'shipping-address') {
                                                    have_shipping_address = true;
                                                    have_shipping_address_index = field_index;
                                                }
                                                if (id === 'product_switching') {
                                                    enabled_product_switching = 'yes';
                                                }

                                                if (id === 'order_coupon') {
                                                    have_coupon_field = true;
                                                }
                                                if (id === 'shipping_calculator') {
                                                    have_shipping_method = true;
                                                }
                                                if (html_fields.indexOf(id) > -1) {
                                                    new_step_field[step_name][new_section].html_fields[id] = true;
                                                }


                                                if (wfacp.tools.hp(required_fields, id)) {
                                                    delete required_fields[id];
                                                }
                                                let index = self.findFieldIndex(step_name, section, id);
                                                if (index !== null) {
                                                    let field = steps_fields[index];
                                                    new_step_field[step_name][new_section].fields.push(field);
                                                }
                                                field_index++;
                                            });
                                            new_section++;
                                        });
                                    }
                                });
                            }

                            return {
                                'fieldsets': new_step_field,
                                'required': required_fields,
                                'empty_steps': empty_steps,
                                'have_billing_address': have_billing_address,
                                'have_shipping_address': have_shipping_address,
                                'have_billing_address_index': have_billing_address_index,
                                'have_shipping_address_index': have_shipping_address_index,
                                'enabled_product_switching': enabled_product_switching,
                                'have_shipping_method': have_shipping_method,
                                'have_coupon_field': have_coupon_field
                            };
                        },

                        showStepsError(empty_step) {
                            let empty_step1 = empty_step;

                            let button_text = wfacp_localization.global.confirm_button_text;

                            if (Object.keys(empty_step1).length !== 0 && empty_step1.hasOwnProperty("third_step")) {
                                delete empty_step1.third_step;
                                button_text = wfacp_localization.global.confirm_button_text_ok;
                            }


                            let error_msg = this.prepareErrorMsg(empty_step1, ' and ');

                            error_msg += " " + wfacp_localization.fields.empty_step_error;

                            wfacp.swal({
                                'text': error_msg,
                                'title': wfacp_localization.fields.validation_error,
                                'type': 'error',
                                'confirmButtonText': wfacp_localization.importer.close_prompt_text,
                                'showCancelButton': false
                            });
                            return false;
                        },
                        /**
                         * Save Layout data into db
                         * @returns {boolean}
                         */
                        save_template() {
                            let sorted_steps = this.getSortedElement();
                            let empty_step = sorted_steps.empty_steps;
                            let step_length = wfacp.tools.ol(empty_step);
                            if (step_length > 0) {
                                if (step_length === 1) {
                                    let last_step = $('.wfacp_step_heading .wfacp_template_tabs:last').data('slug');
                                    if (!wfacp.tools.hp(empty_step, last_step)) {
                                        this.showStepsError(empty_step);
                                        return false;
                                    }

                                } else {
                                    this.showStepsError(empty_step);
                                    return false;
                                }
                            }
                            let required_field = sorted_steps.required;
                            if (wfacp.tools.ol(required_field) > 0) {

                                let error_msg = this.prepareErrorMsg(required_field);
                                wfacp.swal({
                                    'text': error_msg,
                                    'title': 'Validation Error',
                                    'type': 'error',
                                    'confirmButtonText': wfacp_localization.global.confirm_button_text,
                                    'showCancelButton': false
                                });
                                return false;
                            }

                            /**
                             * Check billing email present in single step. If no then show alert message
                             */

                            let editfield = self.editFieldVue();
                            let wp_ajax = new wfacp.ajax();
                            let save_layout = {
                                'steps': wfacp.tools.jsp(this.steps),
                                'fieldsets': sorted_steps.fieldsets,
                                'products': editfield.products,
                                'address_order': editfield.addressOrder,
                                'default_products': editfield.default_products,
                                'product_settings': editfield.product_settings,
                                'wfacp_id': self.id,
                                'have_coupon_field': sorted_steps.have_coupon_field,
                                'have_billing_address': sorted_steps.have_billing_address,
                                'have_shipping_address': sorted_steps.have_shipping_address,
                                'have_billing_address_index': sorted_steps.have_billing_address_index,
                                'have_shipping_address_index': sorted_steps.have_shipping_address_index,
                                'enabled_product_switching': sorted_steps.enabled_product_switching,
                                'have_shipping_method': sorted_steps.have_shipping_method,
                                'current_step': this.current_step
                            };
                            wp_ajax.ajax('save_layout', save_layout);
                            wfacp.show_spinner();
                            wp_ajax.success = (rsp) => {
                                wfacp.show_data_save_model(rsp.msg);
                            };
                        },


                        addField() {
                            self.modal_add_field.iziModal('open');
                        },

                        /**
                         * Open edit field model whit default data
                         * @param step_name
                         * @param section
                         * @param index
                         * @returns {*}
                         */
                        editField(step_name, section, index, event) {


                            let editFieldVue = self.editFieldVue();
                            let field = wfacp.tools.jsp(this.fieldsets[step_name][section].fields[index]);
                            editFieldVue.current_field_id = field.id;
                            self.current_edit_field = {'step_name': step_name, 'index': index, 'section': section, 'field': field};
                            //   let exclude_id = ['address', 'shipping_calculator', 'shipping-address', 'product_switching', 'shipping-address', 'wysiwyg_editor'];
                            let exclude_id = ['address', 'shipping-address', 'product_switching', 'shipping-address', 'wysiwyg_editor'];
                            let exclude_type = [];
                            if ($.inArray(field.id, exclude_id) > -1 || $.inArray(field.type, exclude_type) > -1) {
                                editFieldVue.model_sub_title = '';
                                editFieldVue.edit_model_field_label = '';
                                editFieldVue.model_field_type = '';
                            } else {
                                let fieldtype_text = '';

                                if (field.field_type === "advanced") {
                                    fieldtype_text = " | " + wfacp_localization.fields.field_types_label + " : " + field.type;
                                }

                                editFieldVue.edit_model_field_label = field.data_label;

                                if (field.type != "wfacp_wysiwyg") {
                                    editFieldVue.model_sub_title = wfacp_localization.fields.field_id_slug + " : " + field.id;
                                } else {
                                    editFieldVue.model_sub_title = wfacp_localization.fields.field_types_label + " : " + field.type;
                                }
                            }
                            editFieldVue.setData();
                        },
                        /**
                         * Delete custom created fields
                         * @param section billing or shipping or other
                         * @param index i
                         * @param label String
                         */
                        deleteCustomField(section, index, label) {

                            let action = wfacp.swal({
                                'text': wfacp_localization.fields.delete_c_field_sub_heading,
                                'title': wfacp_localization.fields.delete_c_field + ' `' + label + '`?',
                                'type': 'error',
                                'confirmButtonText': wfacp_localization.fields.yes_delete_the_field,
                                'cancelButtonText': wfacp_localization.global.cancel_button_text,

                                'showCancelButton': true
                            });
                            action.then((status) => {
                                if (!wfacp.tools.hp(status, 'value')) {
                                    return false;
                                }
                                Vue.delete(this.input_fields[section], index);
                                Vue.delete(this.available_fields[section], index);

                                let wp_ajax = new wfacp.ajax();

                                let add_query = {'section': section, 'index': index, 'wfacp_id': self.id};
                                wp_ajax.ajax('delete_custom_field', add_query);
                                wfacp.show_spinner();
                                wp_ajax.success = (rsp) => {
                                    if (rsp.status === true) {
                                        self.addField(rsp.data);
                                        wfacp.show_data_save_model(rsp.msg);
                                    }
                                };

                            });


                        },
                        addSection(step_name) {

                            self.step_name = step_name;
                            self.field_index = null;
                            self.modal_add_section.iziModal('setTitle', 'Add Section');
                            self.modal_add_section.iziModal('open');
                        },
                        editSection(step_name, field_index) {

                            self.step_name = step_name;
                            self.field_index = field_index;
                            self.modal_add_section.iziModal('setTitle', 'Edit Section');
                            self.modal_add_section.iziModal('open');
                        },
                        deleteSection(step_name, field_index) {

                            let section_data = wfacp.tools.jsp(this.fieldsets[step_name][field_index]);
                            let section_name = section_data.name;
                            let msg = wfacp_localization.fields.section.delete.replace("{{section_name}}", section_name);
                            let action = wfacp.swal({'text': msg, 'type': 'error', 'confirmButtonText': wfacp_localization.global.confirm_button_text, 'reverseButtons': true});
                            action.then((status) => {
                                if (!wfacp.tools.hp(status, 'value')) {
                                    return false;
                                }
                                if (wfacp.tools.hp(section_data, 'fields') && wfacp.tools.ol(section_data.fields) > 0) {
                                    let fields = section_data.fields;
                                    for (let i = 0; i < fields.length; i++) {
                                        let field = fields[i];
                                        let id = field.id;
                                        let field_type = field.field_type;
                                        Vue.set(this.input_fields[field_type], id, field);
                                    }
                                }
                                Vue.delete(this.fieldsets[step_name], field_index);
                                // this.save_template();
                                this.validateDependency();
                            });
                            action.catch((e) => {

                            });
                        },
                        showFieldContainerPlaceholder(step_name, section) {
                            let el = $('.template_field_container.' + step_name + '[field-index=' + section + ']');
                            if (el.length > 0) {
                                let fields = el.find('.wfacp_item_drag');
                                if (fields.length === 0) {
                                    el.find('.template_field_placeholder_tbl').show();
                                }
                            }

                        },
                        hideFieldContainerPlaceholder(step_name, section) {
                            let el = $('.template_field_container.' + step_name + '[field-index=' + section + ']');
                            if (el.length > 0) {
                                el.find('.template_field_placeholder_tbl').hide();
                            }
                        },
                        showHideGlobalDependency(checkout_fields) {

                            let messages = this.global_dependency_messages;

                            if (wfacp.tools.ol(messages) > 0) {
                                for (let index in messages) {
                                    let message = messages[index];
                                    if (wfacp.tools.hp(message, 'id')) {
                                        if (wfacp.tools.hp(checkout_fields, message.id)) {
                                            messages[index].show = 'no';
                                        } else {
                                            messages[index].show = 'yes';
                                        }
                                    } else if (wfacp.tools.hp(message, 'ids')) {
                                        let ids = message.ids;
                                        let t_ids = [];
                                        for (let t = 0; t < ids.length; t++) {
                                            if (wfacp.tools.hp(checkout_fields, ids[t])) {
                                                t_ids.push(ids[t]);
                                            }
                                        }
                                        if (t_ids.length === 0) {
                                            messages[index].show = 'yes';
                                        } else {
                                            messages[index].show = 'no';
                                        }

                                    }
                                }
                            }
                            wfacp.hooks.doAction('show_hide_global_dependency', messages);
                        },
                        remove_dependency_messages(index) {
                            let wp_ajax = new wfacp.ajax();
                            let message = this.global_dependency_messages[index];
                            let message_type = 'self';
                            if (wfacp.tools.hp(message, 'is_global') && wfacp.tools.string_to_bool(message.is_global)) {
                                message_type = 'global';
                            }

                            let add_query = {'index': index, 'wfacp_id': self.id, 'message_type': message_type};
                            wp_ajax.ajax('hide_notification', add_query);
                            wp_ajax.success = (rsp) => {
                                Vue.delete(this.global_dependency_messages, index);
                            };
                        },
                        validateDependency() {

                            setTimeout(() => {
                                let fieldsets = wfacp.tools.jsp(this.fieldsets);
                                let checkout_fields = {};
                                for (let step in fieldsets) {
                                    let sections = fieldsets[step];
                                    if (sections.length === 0) {
                                        continue;
                                    }
                                    for (let section in sections) {
                                        let fields = sections[section].fields;
                                        if (fields.length === 0) {
                                            continue;
                                        }
                                        for (let f = 0; f < fields.length; f++) {
                                            let id = fields[f].id;
                                            checkout_fields[id] = 1;
                                        }
                                    }
                                }
                                this.showHideGlobalDependency(checkout_fields);
                            });
                        }
                    },
                    data: {
                        current_step: this.builder.layout.current_step,
                        steps: this.steps,
                        fieldsets: this.fieldsets,
                        input_fields: this.input_fields,
                        available_fields: this.available_fields,
                        required_fields: wfacp_localization.fields.input_field_error,
                        error: [],
                        noofstep: 1,
                        error_msg: {},
                        global_dependency_messages: this.builder.global_dependency_messages,
                    }
                }
            );
            return this.layout_vue;
        }

        defaultStepObj(number) {
            return {'name': 'Step', "slug": 'step_' + number, 'friendly_name': 'Step ' + number, 'active': 'yes'};
        }

        /**
         * Work when user drag field from available fields
         * @param ev
         */
        dragStart(ev) {
            console.log('DragStart');
            wfacp.addClass('.wfacp_field_container', 'activate_dragging_field');
            ev.dataTransfer.setData("unique_id", ev.target.id);
            ev.dataTransfer.setData("section_name", ev.target.getAttribute('data-input-section'));
        }

        /**
         * Work when user drag field from available fields
         * @param ev
         */
        dragEnd(ev) {
            console.log('DragE');
            wfacp.removeClass('.wfacp_field_container', 'activate_dragging_field');
            wfacp.removeClass('.wfacp_field_container', 'highlight_field_container');
        }

        drop(ev) {
            ev.preventDefault();
            let unique_id = ev.dataTransfer.getData("unique_id");
            if (unique_id === ev.target.id) {
                return false;
            }
            let section_name = ev.dataTransfer.getData("section_name");
            this.layout_vue.enableSortable();
            return [unique_id, section_name];
        }

        allowDrop(ev) {
            ev.preventDefault();
        }

        /**
         * Work when user drag field from available fields
         * @param ev
         */
        dragEnter(ev) {
            $(ev.target).parents('.wfacp_field_container').removeClass(ev.target, 'activate_dragging_field');
            $(ev.target).parents('.wfacp_field_container').addClass('highlight_field_container');
        }

        /**
         * Work when user drag field from available fields
         * @param ev
         */
        dragLeave(ev) {
            $(ev.target).parents('.wfacp_field_container').addClass(ev.target, 'activate_dragging_field');
            $(ev.target).parents('.wfacp_field_container').removeClass('highlight_field_container');
        }


        getSection(step_name, field_index) {
            if (wfacp.tools.hp(this.layout_vue.fieldsets, step_name) && wfacp.tools.hp(this.layout_vue.fieldsets[step_name], field_index)) {
                return wfacp.tools.jsp(this.layout_vue.fieldsets[step_name][field_index]);
            }
            return null;
        }

        /**
         * Create new Section or field set
         * @param step_name
         * @param data Object
         */
        addSection(step_name, data = {}) {
            if (wfacp.tools.ol(data) === 0)
                return false;
            if (false === wfacp.tools.hp(this.layout_vue.fieldsets, this.step_name)) {
                Vue.set(this.layout_vue.fieldsets, this.step_name, []);
            }
            let fieldset = wfacp.tools.jsp(this.layout_vue.fieldsets[this.step_name]);
            fieldset.push({'name': data.name, 'class': data.class, 'sub_heading': data.sub_heading, 'fields': []});
            Vue.set(this.layout_vue.fieldsets, this.step_name, fieldset);
            this.layout_vue.enableSortable();
        }

        /**
         * Update section data like name and css classes
         * @param step_name
         * @param field_index
         * @param data
         * @returns {boolean}
         */
        updateSection(step_name, field_index, data = {}) {
            if (wfacp.tools.ol(data) === 0)
                return false;

            if (wfacp.tools.hp(this.layout_vue.fieldsets, step_name) && wfacp.tools.hp(this.layout_vue.fieldsets[step_name], field_index)) {
                let sections_data = wfacp.tools.jsp(this.layout_vue.fieldsets[step_name][field_index]);
                sections_data.name = data.name;
                sections_data.class = data.class;
                sections_data.sub_heading = data.sub_heading;
                Vue.set(this.layout_vue.fieldsets[step_name], field_index, sections_data);
                return true;
            }
            return false;

        }

        /**
         * Adding custom field to billing or shipping section
         * @param data
         */
        addField(data) {
            if (wfacp.tools.ol(data) > 0) {
                let unique_id = data.unique_id;
                let field_type = data.field_type;
                Vue.set(this.layout_vue.input_fields[field_type], unique_id, data);
                Vue.set(this.layout_vue.available_fields[field_type], unique_id, data);
            }
        }

        getAddressFields() {
            let fields = [
                {
                    type: "label",
                    label: wfacp_localization.fields.show_field_label1,
                    styleClasses: 'wfacp_show_field_lable',
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                {
                    type: "label",
                    label: wfacp_localization.fields.show_field_label2,
                    styleClasses: 'wfacp_show_field_lable',
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                {
                    type: "label",
                    label: wfacp_localization.fields.show_field_label3,
                    styleClasses: 'wfacp_show_field_lable',
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                //first shipping name
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.first_name,
                    model: "first_name",
                    hint: '',
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    styleClasses: ['wfacp_first_name'],
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },

                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "first_name_label",
                    hint: wfacp_localization.fields.address.billing_address_last_name_hint,
                    placeholder: wfacp_localization.fields.first_name_label,
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'address');
                    },
                    validator: VueFormGenerator.validators.string
                },

                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "first_name_label",
                    placeholder: wfacp_localization.fields.first_name_label,
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'shipping-address');
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.placeholder_field_label,
                    model: "first_name_placeholder",
                    placeholder: "First Name",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },

                //shipping last name
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.last_name,
                    model: "last_name",
                    hint: '',
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    styleClasses: ['wfacp_last_name'],
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "last_name_label",
                    hint: wfacp_localization.fields.address.billing_address_last_name_hint,
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'address');
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "last_name_label",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'shipping-address');
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "last_name_placeholder",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },


                // Company
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.company,
                    model: "company",
                    hint: '',
                    styleClasses: ['wfacp_company'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "company_label",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "company_placeholder",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },

                // Street address 1
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.street_address1,
                    model: "street_address1",
                    hint: '',
                    styleClasses: ['wfacp_address_1'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "street_address_1_label",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "street_address_1_placeholder",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },

                // Street Address 2

                {
                    type: "switch",
                    label: wfacp_localization.fields.address.street_address2,
                    model: "street_address2",
                    hint: '',
                    styleClasses: ['wfacp_address_2'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "street_address_2_label",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "street_address_2_placeholder",
                    placeholder: "",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },


                {
                    type: "switch",
                    label: wfacp_localization.fields.address.city,
                    hint: '',
                    model: "address_city",
                    styleClasses: ['wfacp_city'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "address_city_label",
                    placeholder: "City",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "address_city_placeholder",
                    placeholder: "City",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.state,
                    model: "address_state",
                    styleClasses: ['wfacp_state'],
                    hint: '',
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "address_state_label",
                    placeholder: "State",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "address_state_placeholder",
                    placeholder: "State",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.zip,
                    model: "address_postcode",
                    hint: '',
                    styleClasses: ['wfacp_postcode'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.label,
                    model: "address_postcode_label",

                    placeholder: "Postcode",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "address_postcode_placeholder",
                    placeholder: "Postcode",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "switch",
                    label: wfacp_localization.fields.address.country,
                    model: "address_country",
                    hint: "",
                    styleClasses: ['wfacp_country'],
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    visible: (model) => {
                        return (model.is_address === true);
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.lable,
                    model: "address_country_label",
                    placeholder: "USA",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.placeholder,
                    model: "address_country_placeholder",
                    placeholder: "USA",
                    visible: (model) => {
                        return (model.is_address === true);
                    },
                    validator: VueFormGenerator.validators.string
                },
                {
                    type: "switch",
                    label: wfacp_localization.fields.same_as_billing,
                    model: "same_as_billing",
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    styleClasses: 'wfacp_full_width_fields',
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'shipping-address');
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.lable,
                    model: "same_as_billing_label",
                    hint: wfacp_localization.fields.same_as_billing_label_hint,
                    styleClasses: 'wfacp_full_width_input',
                    placeholder: "USA",
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'shipping-address');
                    },
                    validator: VueFormGenerator.validators.string
                },

                {
                    type: "switch",
                    label: wfacp_localization.fields.same_as_shipping,
                    model: "same_as_shipping",
                    textOn: wfacp_localization.global.active,
                    textOff: wfacp_localization.global.inactive,
                    styleClasses: 'wfacp_full_width_fields',
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'address');
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.address.lable,
                    model: "same_as_shipping_label",
                    hint: wfacp_localization.fields.same_as_shipping_label_hint,
                    styleClasses: 'wfacp_full_width_input',
                    placeholder: "USA",
                    visible: (model) => {
                        return (model.is_address === true && model.id === 'address');
                    },
                    validator: VueFormGenerator.validators.string
                }

            ];

            return wfacp.hooks.applyFilters('wfacp_address_fields', fields);
        }

        getEditableFields() {

            let fields = [
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.label_field_label,
                    model: "label",
                    required: false,
                    validator: VueFormGenerator.validators.string

                },
                {
                    type: "checkbox",
                    label: wfacp_localization.fields.order_total_breakup_label,
                    model: "default",
                    hint: wfacp_localization.fields.order_total_breakup_hint,
                    styleClasses: 'wfacp_required_cls',
                    visible: (model) => {
                        return (model.id === 'order_total');
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.options_field_label,
                    model: "options",
                    required: true,
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return ($.inArray(model.field_type, ['select', 'wfacp_radio', 'multiselect', 'select2']) > -1);
                    }

                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.shipping_field_placeholder,
                    hint: wfacp_localization.fields.shipping_field_placeholder_hint,
                    model: "default",
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return (model.id === 'shipping_calculator');
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.multiselect_maximum_selection,
                    // hint: wfacp_localization.fields.shipping_field_placeholder_hint,
                    model: "multiselect_maximum",
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return (model.field_type === 'multiselect');
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.multiselect_maximum_error_field_label,
                    // hint: wfacp_localization.fields.shipping_field_placeholder_hint,
                    model: "multiselect_maximum_error",
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return (model.field_type === 'multiselect');
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.placeholder_field_label,
                    model: "placeholder",
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return !(($.inArray(model.field_type, ['select', 'wfacp_radio', 'checkbox', 'hidden', 'date', 'address', 'product', 'wfacp_html', 'multiselect', 'wfacp_dob']) > -1) || model.is_address === true);
                    },
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.fields.default_field_label,
                    model: "default",
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return model.is_wfacp_field;
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.settings.product_switching.you_save_text,
                    model: "default",
                    hint: wfacp_localization.fields.product_you_save_merge_tags,
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return (model.field_type === 'product');
                    }
                },
                {
                    type: "select",
                    inputType: "select",
                    label: wfacp_localization.fields.default_field_label,
                    model: "default",
                    selectOptions: {hideNoneSelectedText: true},
                    values: () => {
                        return wfacp.tools.jsp(wfacp_localization.fields.default_field_checkbox_options);
                    },
                    visible: (model) => {
                        return (model.field_type === 'checkbox');
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    label: wfacp_localization.settings.coupon.success_message_heading,
                    model: "coupon_success_message_heading",
                    hint: wfacp_localization.settings.coupon.success_message_heading_hint,
                    validator: VueFormGenerator.validators.string,
                    visible: (model) => {
                        return (model.id === 'order_coupon');
                    }
                },
                {
                    type: "checkbox",
                    label: wfacp_localization.settings.coupon.style_heading,
                    model: "coupon_style",
                    styleClasses: 'wfacp_required_cls',
                    visible: (model) => {
                        return (model.id === 'order_coupon');
                    }
                },
                {
                    type: "checkbox",
                    label: wfacp_localization.fields.show_on_thankyou,
                    model: "show_custom_field_at_thankyou",
                    styleClasses: 'wfacp_required_cls',
                    visible: (model) => {
                        return (model.is_wfacp_field);
                    }
                }, {
                    type: "checkbox",
                    label: wfacp_localization.fields.show_in_email,
                    model: "show_custom_field_at_email",
                    styleClasses: 'wfacp_required_cls',
                    visible: (model) => {
                        return (model.is_wfacp_field);
                    }
                }
            ];

            fields = wfacp.hooks.applyFilters('wfacp_editable_fields', fields);

            let required = {
                type: "checkbox",
                label: wfacp_localization.fields.required_field_label,
                model: "required",
                styleClasses: 'wfacp_required_cls',
                default: true,
                visible: (model) => {
                    return (model.field_type !== 'hidden' && model.is_address !== true && ($.inArray(model.field_type, ['product', 'wfacp_html']) < 0));
                }
            };


            fields.push(required);

            let temp_required = {
                type: "checkbox",
                label: 'wfacp_initiator_field',
                model: "temp_required",
                styleClasses: 'wfacp_initiator_field',
                default: true,
            };

            fields.push(temp_required);
            return fields;
        }

        updateField(data) {
            if (wfacp.tools.ol(data) === 0) {
                return false;
            }

            if (wfacp.tools.hp(this.layout_vue.fieldsets, data.step_name) && wfacp.tools.hp(this.layout_vue.fieldsets[data.step_name][data.section], 'fields')) {
                let myoptions = {};
                let options = wfacp.tools.jsp(data.field.options);

                if (wfacp.tools.ol(options) > 0) {
                    for (let i in options) {
                        let v = options[i];
                        v = v.trim();
                        myoptions[v] = v;
                    }
                    data.field.options = myoptions;
                }
                Vue.set(this.layout_vue.fieldsets[data.step_name][data.section].fields, data.index, data.field);
            }
        }

        addFieldVue(modal) {
            if (this.add_field_model_open === true) {
                return;
            }
            this.add_field_model_open = true;
            let self = this;

            this.add_field_vue = new Vue({
                    'el': '#add-field-form',
                    components: {
                        "vue-form-generator": VueFormGenerator.component
                    },
                    methods: {
                        defaultModel() {
                            return {
                                label: '',
                                placeholder: "",
                                name: "",
                                cssready: '',
                                field_type: 'text',
                                section_type: 'advanced',
                                options: '',
                                default: '',
                                enable_time: false,
                                preferred_time_format: '12',
                                multiselect_maximum: wfacp_localization.fields.multiselect_maximum_selection_default_count,
                                multiselect_maximum_error: wfacp_localization.fields.multiselect_maximum_error,
                                show_custom_field_at_thankyou: false,
                                show_custom_field_at_email: false,
                                required: true
                            };
                        },
                        onSubmit() {
                            this.modal.startLoading();
                            let wp_ajax = new wfacp.ajax();
                            if (this.model.field_type === 'wfacp_wysiwyg') {
                                let dt = new Date();
                                this.model.name = dt.getTime();
                            }
                            if (this.model.field_type !== 'multiselect') {
                                delete this.model.multiselect_maximum;
                                delete this.model.multiselect_maximum_error;
                            }

                            let add_query = {'fields': wfacp.tools.jsp(this.model), 'section': 'billing', 'wfacp_id': self.id};
                            wp_ajax.ajax('add_field', add_query);
                            wp_ajax.success = (rsp) => {
                                if (rsp.status === true) {
                                    self.addField(rsp.data);
                                }
                            };
                            wp_ajax.complete = () => {
                                this.modal.stopLoading();
                                self.modal_add_field.iziModal('close');
                                this.model = this.defaultModel();
                            };
                        }
                    },
                    data: {
                        modal: modal,
                        isLoading: false,
                        wfacp_id: self.builder.id,

                        model: {
                            label: '',
                            placeholder: "",
                            name: "",
                            cssready: '',
                            field_type: 'text',
                            section_type: 'advanced',
                            options: '',
                            default: '',
                            enable_time: false,
                            preferred_time_format: '12',
                            show_custom_field_at_thankyou: false,
                            show_custom_field_at_email: false,
                            multiselect_maximum: wfacp_localization.fields.multiselect_maximum_selection_default_count,
                            multiselect_maximum_error: wfacp_localization.fields.multiselect_maximum_error,
                            required: true
                        },
                        schema: {
                            fields: [
                                {
                                    type: "select",
                                    label: wfacp_localization.fields.field_types_label,
                                    model: "field_type",
                                    values: () => {
                                        return wfacp.tools.jsp(wfacp_localization.fields.field_types);
                                    },
                                    selectOptions: {hideNoneSelectedText: true},

                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.label_field_label,
                                    model: "label",
                                    required: true,
                                    validator: VueFormGenerator.validators.string

                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.name_field_label,
                                    hint: wfacp_localization.fields.name_field_label_hint,
                                    model: "name",
                                    required: true,
                                    visible: (model) => {
                                        return (model.field_type !== 'wfacp_wysiwyg');
                                    },
                                    validator: VueFormGenerator.validators.string
                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.options_field_label,
                                    model: "options",
                                    required: true,
                                    visible: (model) => {
                                        return ($.inArray(model.field_type, ['select', 'wfacp_radio', 'multiselect', 'select2']) > -1);
                                    },
                                    validator: VueFormGenerator.validators.string
                                },


                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.multiselect_maximum_selection,
                                    model: "multiselect_maximum",
                                    visible: (model) => {
                                        return (model.field_type === 'multiselect');
                                    }
                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.multiselect_maximum_error_field_label,
                                    model: "multiselect_maximum_error",
                                    visible: (model) => {
                                        return (model.field_type === 'multiselect');
                                    }
                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.default_field_label,
                                    model: "default",
                                    validator: VueFormGenerator.validators.string,
                                    visible: (model) => {
                                        return ($.inArray(model.field_type, ['checkbox', 'wfacp_wysiwyg']) > -1) ? false : true;
                                    },
                                },
                                {
                                    type: "select",
                                    label: wfacp_localization.fields.default_field_label,
                                    model: "default",
                                    validator: VueFormGenerator.validators.string,
                                    values: () => {
                                        return wfacp.tools.jsp(wfacp_localization.fields.default_field_checkbox_options);
                                    },
                                    visible: (model) => {
                                        return (model.field_type === 'checkbox');
                                    },
                                    selectOptions: {hideNoneSelectedText: true},
                                },
                                {
                                    type: "input",
                                    inputType: "text",
                                    label: wfacp_localization.fields.placeholder_field_label,
                                    model: "placeholder",
                                    validator: VueFormGenerator.validators.string,
                                    visible: (model) => {
                                        return ($.inArray(model.field_type, ['select', 'wfacp_radio', 'checkbox', 'hidden', 'date', 'wfacp_wysiwyg', 'multiselect', 'wfacp_dob']) < 0);
                                    },
                                },
                                {
                                    type: "checkbox",
                                    label: wfacp_localization.fields.show_on_thankyou,
                                    model: "show_custom_field_at_thankyou",
                                    styleClasses: 'wfacp_required_cls'
                                }, {
                                    type: "checkbox",
                                    label: wfacp_localization.fields.show_in_email,
                                    model: "show_custom_field_at_email",
                                    styleClasses: 'wfacp_required_cls'
                                },
                                {
                                    type: "checkbox",
                                    label: wfacp_localization.fields.required_field_label,
                                    model: "required",
                                    styleClasses: ['wfacp_required_cls', 'wfacp_full_width_fields'],
                                    visible: (model) => {
                                        return (model.field_type !== 'hidden' && model.field_type !== 'wfacp_wysiwyg');
                                    }
                                }]
                        },
                        formOptions: {
                            validateAfterChanged: true
                        }
                    }
                }
            );
            return this.add_field_vue;
        }

        editFieldVue() {

            if (this.edit_field_model_open === true) {
                return this.edit_field_vue;
            }
            this.edit_field_model_open = true;
            let self = this;
            let product_settings = {};

            for (let s in wfacp_data.product_switcher_data.settings) {

                if ($.inArray(wfacp_data.product_switcher_data.settings[s], ['false', 'true', false, true]) > -1) {
                    product_settings[s] = wfacp.tools.string_to_bool(wfacp_data.product_switcher_data.settings[s]);
                } else {
                    product_settings[s] = wfacp_data.product_switcher_data.settings[s];
                }
            }


            for (let p in wfacp_data.product_switcher_data.products) {
                wfacp_data.product_switcher_data.products[p].enable_delete = wfacp.tools.string_to_bool(wfacp_data.product_switcher_data.products[p].enable_delete);
            }

            this.edit_field_vue = new Vue({
                    'el': '#edit-field-form',
                    components: {
                        "vue-form-generator": VueFormGenerator.component
                    },
                    methods: {
                        defaultModel() {
                            return {
                                field_type: 'text',
                                section_type: 'billing',
                                label: '',
                                placeholder: "",
                                cssready: '',
                                options: '',
                                is_address: false,
                                id: "",
                                default: '',
                                is_wfacp_field: false,
                                required: true,
                            };
                        },
                        enableAddressSortable() {

                            setTimeout(() => {
                                let $tbody = $('.wfacp_address_sortable_area');
                                $tbody.sortable({
                                    items: 'div.wfacp_sortable_address_field',
                                    cursor: 'move',
                                    axis: 'y',
                                    handle: '.wfacp_drag_address_icon',
                                    scrollSensitivity: 40,
                                    stop: (event, ui) => {
                                        this.AddressSortableStop(event, ui);
                                    },
                                });
                                $(document.body).off('click', '.wfacp_drag_address_field_enable');

                                $(document.body).on('click', '.wfacp_drag_address_field_enable', function () {
                                    if ($(this).hasClass('dashicons-visibility')) {
                                        $(this).removeClass('dashicons-visibility');
                                        $(this).addClass('dashicons-hidden');
                                    } else {
                                        $(this).removeClass('dashicons-hidden');
                                        $(this).addClass('dashicons-visibility');
                                    }
                                });

                                $(document.body).off('click', '.wfacp_address_open_accordian');
                                $(document.body).on('click', '.wfacp_address_open_accordian', function () {
                                    let parent = $(this).parents('.wfacp_address_field');
                                    let accordian = parent.children('.wfacp_billing_accordion_content');
                                    if ($(this).hasClass('dashicons-arrow-up')) {
                                        accordian.slideUp();
                                        $(this).removeClass('dashicons-arrow-up');
                                    } else {
                                        accordian.slideDown();
                                        $(this).addClass('dashicons-arrow-up');
                                    }

                                });

                                $(document.body).on('change', '.wfacp_address_type', function () {
                                    let parent = $(this).parents('.wfacp_billing_accordion_content');
                                    if ($(this).is(":checked")) {
                                        let v = $(this).val();
                                        if ('radio' === v) {
                                            parent.find('.wfacp_address_type_radio_container').addClass('wfacp_address_radio_container_show');
                                        } else {
                                            parent.find('.wfacp_address_type_radio_container').removeClass('wfacp_address_radio_container_show');
                                        }
                                    } else {
                                        parent.find('.wfacp_address_type_radio_container').removeClass('wfacp_address_radio_container_show');
                                    }
                                });

                            }, 800);


                        },
                        AddressSortableStop(event, ui) {
                        },
                        setData() {
                            let data = self.current_edit_field;
                            if (wfacp.tools.ol(data) === 0)
                                return true;

                            this.model.label = data.field.label;
                            this.model.placeholder = typeof data.field.placeholder !== "undefined" ? data.field.placeholder : '';
                            if (wfacp.tools.hp(data.field, 'cssready')) {
                                this.model.cssready = data.field.cssready.join(',');
                            } else {
                                this.model.cssready = '';
                            }


                            this.model.section_type = data.field.field_type;
                            this.model.field_type = data.field.type;
                            if ('multiselect' == this.model.field_type) {
                                this.model.multiselect_maximum = data.field.multiselect_maximum;
                                this.model.multiselect_maximum_error = data.field.multiselect_maximum_error;
                            }

                            this.model.is_wfacp_field = wfacp.tools.hp(data.field, 'is_wfacp_field') ? true : false;
                            if (wfacp.tools.ol(data.field.options) > 0) {
                                this.model.options = wfacp.tools.kys(data.field.options).join("|");
                            } else {
                                this.model.options = '';
                            }

                            this.model.default = data.field.default;
                            this.model.required = wfacp.tools.string_to_bool(data.field.required);
                            this.model.id = data.field.id;
                            if (this.model.id === 'product_switching') {
                                this.products = wfacp.tools.jsp(this.products_updated);
                                this.default_products = wfacp.tools.jsp(this.default_products_updated);
                                this.product_settings = wfacp.tools.jsp(this.product_settings_updated);
                            }
                            if (this.model.field_type === 'wfacp_wysiwyg') {
                                this.model.required = false;
                            }

                            this.model = wfacp.hooks.applyFilters('wfacp_field_data_merge_with_model', this.model, data);
                            let wfacp_initiator_field = document.getElementById('wfacp-initiator-field');
                            if (null !== wfacp_initiator_field) {
                                wfacp_initiator_field.click();
                            }

                            setTimeout(() => {
                                self.modal_edit_field.WoofunnelModal('open');
                                this.setAddressOrder();
                            }, 100);

                        },
                        setAddressOrder() {

                            if (wfacp.tools.hp(wfacp_data.address_order, this.model.id)) {
                                let data = wfacp_data.address_order[this.model.id];

                                let html = this.address_order_html[this.model.id];
                                if ('' !== html) {
                                    $('#wfacp_address_field_' + this.model.id).html(html);
                                }
                                setTimeout((data) => {
                                    for (let i = 0; i < data.length; i++) {
                                        let key = data[i].key;
                                        let status = data[i].status;
                                        let label = data[i].label;
                                        let row = $('.wfacp_address_field[data-key="' + key + '"]');
                                        if (row.length > 0) {
                                            let status_row = row.find('.wfacp_drag_address_field_enable');
                                            if (true == status || 'true' == status) {
                                                status_row.removeClass('dashicons-hidden');
                                                status_row.addClass('dashicons-visibility"');
                                            } else {
                                                status_row.removeClass('dashicons-visibility');
                                                status_row.addClass('dashicons-hidden');
                                            }
                                            let label_row = row.find('.wfacp_label');
                                            label_row.val(label);
                                            let row_placeholder = row.find('.wfacp_placeholder');
                                            if (row_placeholder.length > 0) {
                                                row_placeholder.val(data[i].placeholder);
                                            }
                                            let row_required = row.find('.wfacp_required');
                                            if (row_required.length > 0) {
                                                if (true == data[i].required || 'true' == data[i].required) {
                                                    row_required.prop('checked', true);
                                                } else {
                                                    row_required.prop('checked', false);
                                                }
                                            }

                                        }
                                    }

                                }, 200, data);

                            }

                        },

                        saveAddressOrder(data) {

                            return new Promise((resolve) => {

                                if (data.field.id === 'address' || data.field.id === 'shipping-address') {
                                    let addressContainer = $('#wfacp_address_field_' + data.field.id);
                                    if (addressContainer.length > 0) {
                                        let fieldOptions = [];
                                        let fields = addressContainer.find('.wfacp_address_field');
                                        let self = this;
                                        fields.each(function () {
                                            let key = $(this).data('key');
                                            let label = $(this).find('.wfacp_label').val();
                                            let options = {'key': key, 'status': false, 'label': label};

                                            let address_type = $(this).find('.wfacp_address_type:checked');
                                            let address_type_val = address_type.val();
                                            self.addressOrder['display_type_' + data.field.id] = address_type_val;

                                            let label_2 = $(this).find('.wfacp_label_2');
                                            if (label_2.length > 0 && 'radio' == address_type_val) {
                                                options.label_2 = label_2.val();
                                            }


                                            if ($(this).find('.wfacp_drag_address_field_enable').hasClass('dashicons-visibility')) {
                                                options.status = true;
                                            }
                                            let placeholderEl = $(this).find('.wfacp_placeholder');
                                            if (placeholderEl.length > 0) {
                                                options.placeholder = placeholderEl.val();
                                            }

                                            let required = $(this).find('.wfacp_required');
                                            if (required.length > 0) {
                                                if (required.is(':checked')) {
                                                    options.required = true;
                                                } else {
                                                    options.required = false;
                                                }
                                            }
                                            fieldOptions.push(options);
                                        });
                                        self.addressOrder[data.field.id] = fieldOptions;
                                        $('.wfacp_address_open_accordian').removeClass('dashicons-arrow-up');
                                        $('.wfacp_billing_accordion_content').hide();
                                        resolve(fieldOptions);
                                    }
                                } else {
                                    resolve({});
                                }
                            });
                        },
                        onSubmit() {
                            let model = wfacp.tools.jsp(this.model);
                            let data = self.current_edit_field;
                            data.field.label = model.label;
                            data.field.placeholder = model.placeholder;
                            data.field.cssready = model.cssready.split(',');
                            data.field.type = model.field_type;
                            data.field.field_type = model.section_type;
                            data.field.options = model.options.split('|');
                            if ('multiselect' == data.field.type) {
                                data.field.multiselect_maximum = model.multiselect_maximum;
                                data.field.multiselect_maximum_error = model.multiselect_maximum_error;
                            }

                            data.field.default = '';
                            if (wfacp.tools.hp(model, 'default')) {
                                data.field.default = model.default;
                            }
                            data.field.required = model.required;

                            data = wfacp.hooks.applyFilters('wfacp_before_field_save', data, model);
                            data = wfacp.tools.jsp(data);
                            if (wfacp.tools.hp(data.field, 'is_wfacp_field') && data.field.type == 'hidden') {
                                data.field.required = false;
                            }
                            self.updateField(data);
                            if (data.field.id === 'product_switching') {
                                this.products_updated = wfacp.tools.jsp(this.products);
                                this.default_products_updated = wfacp.tools.jsp(this.default_products);
                                this.product_settings_updated = wfacp.tools.jsp(this.product_settings);
                            }
                            if (data.field.id === 'address' || data.field.id === 'shipping-address') {
                                this.address_order_html[data.field.id] = $('#wfacp_address_field_' + data.field.id).html();
                            }
                            this.model = this.defaultModel();

                            this.saveAddressOrder(data).finally(() => {
                                document.getElementById('wfacp_save_form_layout').click();
                                self.modal_edit_field.WoofunnelModal('close');
                            });
                        }
                    },
                    data: {
                        product_switcher_updated: false,
                        wfacp_id: self.builder.id,
                        current_field_id: 'default',
                        products: wfacp.tools.jsp(wfacp_data.product_switcher_data.products),
                        products_updated: wfacp.tools.jsp(wfacp_data.product_switcher_data.products),
                        is_hide_additional_information_updated: wfacp.tools.jsp(wfacp_data.product_switcher_data.is_hide_additional_information),
                        is_hide_additional_information: wfacp.tools.jsp(wfacp_data.product_switcher_data.is_hide_additional_information),
                        additional_information_title_updated: wfacp.tools.jsp(wfacp_data.product_switcher_data.additional_information_title),
                        additional_information_title: wfacp.tools.jsp(wfacp_data.product_switcher_data.additional_information_title),
                        default_products: [],
                        default_products_updated: wfacp.tools.jsp(wfacp_data.product_switcher_data.default_products),
                        product_settings: wfacp.tools.jsp(product_settings),
                        product_settings_updated: wfacp.tools.jsp(product_settings),
                        model_title: wfacp_localization.fields.add_field,
                        addressOrder: wfacp_data.address_order,
                        address_order_html: {'address': '', 'shipping-address': ''},
                        model_sub_title: '',
                        edit_model_field_label: '',
                        model_field_type: '',
                        model: {
                            field_type: 'text',
                            section_type: 'billing',
                            options: '',
                            default: '',
                            label: '',
                            placeholder: "",
                            cssready: '',
                            is_address: false,
                            is_wfacp_field: false,
                            required: true
                        },
                        schema: {
                            groups: [{
                                fields: self.getEditableFields()
                            }, {
                                fields: self.getAddressFields()
                            }],

                        },
                        formOptions: {
                            validateAfterChanged: true
                        }
                    }
                }
            );
            return this.edit_field_vue;
        }

        addSectionVue(modal) {
            if (this.add_section_model_open === true) {
                return;
            }
            this.add_section_model_open = true;
            let self = this;

            this.add_section_vue = new Vue({
                    'el': '#add-section-form',
                    components: {
                        "vue-form-generator": VueFormGenerator.component
                    },
                    methods: {
                        defaultModel() {
                            return {
                                name: "",
                                classes: wfacp_localization.fields.section.default_classes,
                                sub_heading: '',
                            };
                        },
                        clearData() {
                            this.model = this.defaultModel();
                        },
                        setData() {
                            let data = self.getSection(self.step_name, self.field_index);
                            if (data !== null) {
                                this.btn_name = wfacp_localization.global.update_btn;
                                this.model.name = data.name;
                                this.model.classes = data.class;
                                this.model.sub_heading = data.sub_heading;
                            } else {
                                this.btn_name = wfacp_localization.fields.add_new_btn;
                            }
                        },
                        onSubmit() {
                            let data = wfacp.tools.jsp(this.model);

                            if (self.field_index == null) {
                                self.addSection(self.step_name, {'name': data.name, 'class': data.classes, 'sub_heading': data.sub_heading});
                            } else {
                                self.updateSection(self.step_name, self.field_index, {'name': data.name, 'class': data.classes, 'sub_heading': data.sub_heading});
                            }

                            this.model = this.defaultModel();
                            self.modal_add_section.iziModal('close');
                        }
                    },
                    data: {
                        modal: modal,
                        isLoading: false,
                        step_name: 'single_step',
                        btn_name: wfacp_localization.fields.section.add_heading,
                        wfacp_id: self.builder.id,
                        model: {
                            name: "",
                            classes: wfacp_localization.fields.section.default_classes,
                            sub_heading: '',
                        },
                        schema: {
                            fields: [{
                                type: "input",
                                inputType: "text",
                                label: wfacp_localization.fields.section.fields.heading,
                                model: "name",
                                required: false,
                                validator: VueFormGenerator.validators.string
                            }, {
                                type: "input",
                                inputType: "text",
                                label: wfacp_localization.fields.section.fields.sub_heading,

                                model: "sub_heading",
                                validator: VueFormGenerator.validators.string
                            }, {
                                type: "input",
                                inputType: "text",
                                label: wfacp_localization.fields.section.fields.classes,
                                model: "classes"
                            }]
                        },
                        formOptions: {
                            validateAfterChanged: true
                        }
                    }
                }
            );
            return this.add_section_vue;
        }


        model() {
            let self = this;
            this.modal_add_field = $("#modal-add-field");
            if (this.modal_add_field.length > 0) {
                this.modal_add_field.iziModal({
                    title: wfacp_localization.fields.add_field,
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    width: 800,
                    overlayColor: 'rgba(0, 0, 0, 0.6)',
                    transitionIn: 'fadeInDown',
                    transitionOut: 'fadeOutDown',
                    navigateArrows: "false",
                    onOpening: function (modal) {
                        modal.startLoading();
                    },
                    onOpened: function (modal) {
                        modal.stopLoading();
                        self.addFieldVue(modal);
                    },
                    onClosed: function (modal) {

                    }
                });
            }

            this.modal_edit_field = $("#modal-edit-field");
            if (this.modal_edit_field.length > 0) {
                this.modal_edit_field.WoofunnelModal();
                this.modal_edit_field.on('onopend', function () {
                    let id = self.current_edit_field.field.id;
                    if (id === 'address' || id === 'shipping-address') {
                        self.editFieldVue().enableAddressSortable();
                    }
                });
                this.modal_edit_field.on('onopend', function () {
                    setTimeout(() => {
                        let product_switching = $("#product_switching");
                        if (product_switching.length > 0) {
                            product_switching.find(".wfacp_tabs .wfacp_tab_link").on('click', function (e) {
                                e.preventDefault();
                                $('.wfacp_tab_link').removeClass('activelink');
                                $(this).addClass('activelink');
                                var tagid = $(this).data('tag');
                                $('.wfacp_tab_container').removeClass('wfacp_tab_active').addClass('wfacp_tab_hide');
                                $(tagid).addClass('wfacp_tab_active').removeClass('wfacp_tab_hide');
                            });
                        }
                        let editFieldVue = self.editFieldVue();
                        if (wfacp.tools.ol(editFieldVue.products) > 0) {
                            let product_keys = wfacp.tools.kys(editFieldVue.products);
                            product_keys.forEach((i, j) => {
                                let editorID = 'whats_included_' + i;
                                wp.editor.remove(editorID);
                                let editorConfig = wfacp.tools.jsp(wfacp.editorConfig);
                                editorConfig.tinymce.init_instance_callback = (editor) => {
                                    editor.on('Change', (e) => {
                                        let product_id = editor.targetElm.getAttribute('product_id');
                                        let editFieldVue = self.editFieldVue();
                                        editFieldVue.products[product_id].whats_included = editor.getContent();
                                    });
                                };
                                wp.editor.initialize(editorID, editorConfig);
                            });
                        }

                    }, 600);

                    setTimeout(() => {
                        let wfacp_wysiwyg = $("#wfacp_wysiwyg_editor");
                        if (wfacp_wysiwyg.length > 0) {
                            let wysiwyg_editor = 'wfacp_wysiwyg_editor';
                            wp.editor.remove(wysiwyg_editor);
                            let editorConfig = wfacp.tools.jsp(wfacp.editorConfig);
                            editorConfig.tinymce.init_instance_callback = (editor) => {
                                editor.on('Change', (e) => {
                                    let editFieldVue = self.editFieldVue();
                                    Vue.set(editFieldVue.model, 'default', editor.getContent());
                                });
                            };
                            wp.editor.initialize(wysiwyg_editor, editorConfig);
                        }
                    });
                });
                this.modal_edit_field.on('onclosed', function () {
                    let editField = self.editFieldVue();
                    editField.products = editField.products_updated;
                    let wfacp_wysiwyg = $("#wfacp_wysiwyg_editor");
                    if (wfacp_wysiwyg.length > 0) {
                        let wysiwyg_editor = 'wfacp_wysiwyg_editor';
                        wp.editor.remove(wysiwyg_editor);
                    }
                });
            }
            this.modal_add_section = $("#modal-add-section");
            if (this.modal_add_section.length > 0) {
                this.modal_add_section.iziModal({
                    // title: 'Add Section',
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    width: 600,
                    overlayColor: 'rgba(0, 0, 0, 0.6)',
                    transitionIn: 'fadeInDown',
                    transitionOut: 'fadeOutDown',
                    navigateArrows: "false",
                    onOpening: function (modal) {
                        modal.startLoading();
                    },
                    onOpened: function (modal) {
                        modal.stopLoading();
                        self.addSectionVue(modal);
                        self.add_section_vue.setData();
                    },
                    onClosed: function (modal) {
                        self.add_section_vue.clearData();
                    }
                });
            }
        }


        field_data_merge_with_model(model, data) {
            if (wfacp.tools.hp(model, 'id') && model.id === 'order_coupon') {
                model.coupon_success_message_heading = data.field.coupon_success_message_heading;
                model.coupon_remove_message_heading = data.field.coupon_remove_message_heading;
                model.coupon_style = wfacp.tools.string_to_bool(data.field.coupon_style);
            }

            if (wfacp.tools.hp(model, 'is_wfacp_field') && true == model.is_wfacp_field) {
                model.enable_time = wfacp.tools.string_to_bool(data.field.enable_time);
                model.preferred_time_format = data.field.preferred_time_format;
                model.show_custom_field_at_thankyou = wfacp.tools.string_to_bool(data.field.show_custom_field_at_thankyou);
                model.show_custom_field_at_email = wfacp.tools.string_to_bool(data.field.show_custom_field_at_email);
            }
            return model;
        }

        wfacp_before_field_save(data, model) {
            if (wfacp.tools.hp(model, 'id') && model.id === 'order_coupon') {
                data.field.coupon_success_message_heading = model.coupon_success_message_heading;
                data.field.coupon_remove_message_heading = model.coupon_remove_message_heading;
                data.field.coupon_style = wfacp.tools.string_to_bool(model.coupon_style);
            }

            if (wfacp.tools.hp(model, 'is_wfacp_field') && true == model.is_wfacp_field) {
                data.field.enable_time = model.enable_time;
                data.field.preferred_time_format = model.preferred_time_format;
                data.field.show_custom_field_at_thankyou = model.show_custom_field_at_thankyou;
                data.field.show_custom_field_at_email = model.show_custom_field_at_email;
            }
            return data;
        }
    }

    class wfacp_design {
        constructor(builder) {
            let el = document.getElementById("wfacp_design_container");
            if (el != null) {
                this.id = builder.id;
                this.selected = builder.design.selected;
                this.selected_type = builder.design.selected_type;
                this.designs = builder.design.designs;
                this.design_types = builder.design.design_types;
                this.description = '';
                this.template_active = builder.design.template_active;
                this.selected_template = this.designs[this.selected_type][this.selected];
                this.main();
                this.model();
            }
            this.jQuery();

        }

        main() {
            let self = this;
            if (!wfacp.tools.hp(this.designs, this.selected_type)) {
                this.selected_type = 'pre_built';
                this.selected = 'shopcheckout';
            } else {
                if (!wfacp.tools.hp(this.designs[this.selected_type], this.selected)) {
                    this.selected_type = 'pre_built';
                    this.selected = 'shopcheckout';
                }
            }
            this.main = new Vue({
                el: "#wfacp_design_container",
                methods: {
                    get_page_title() {
                        return wfacp_data.name;
                    },
                    get_edit_link() {
                        return wfacp_data.template_edit_url[this.selected_type].url;
                    },
                    get_button_text() {
                        return wfacp_data.template_edit_url[this.selected_type].button_text;
                    },

                    setTemplateType(template_type) {

                        Vue.set(this, 'current_template_type', template_type);

                    },
                    setTemplate(selected, type, cb = '') {
                        Vue.set(this, 'selected', selected);
                        Vue.set(this, 'selected_type', type);
                        wfacp.hooks.doAction('wfacp_set_template', selected, type);
                        this.template_active = 'yes';
                        return this.save('yes', cb);
                    },
                    removeDesign(cb) {
                        let wp_ajax = new wfacp.ajax();
                        let save_layout = {
                            'wfacp_id': self.id,
                        };
                        wp_ajax.ajax('remove_design', save_layout);

                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            wfacp_data.layout.current_step = 'single_step';
                            if (typeof cb == "function") {
                                cb(rsp);
                            }
                        };
                        wp_ajax.error = (rsp) => {

                        };
                    },
                    showFailedImport(warning_text) {
                        wfacp.swal({
                            'html': warning_text,
                            'title': wfacp_localization.importer.failed_import,
                            'type': 'warning',
                            'allowEscapeKey': true,
                            'showCancelButton': false,
                            'confirmButtonText': wfacp_localization.importer.close_prompt_text,
                        });
                    },
                    importTemplate(template, type, cb) {
                        let wp_ajax = new wfacp.ajax();
                        let save_layout = {
                            'builder': type,
                            'template': template.slug,
                            'wfacp_id': self.id,
                            'is_multi': ('yes' === template.multistep) ? 'yes' : 'no'
                        };

                        wp_ajax.ajax('import_template', save_layout);
                        wfacp.show_spinner();

                        this.swalLoadingText(builder.wfacp_i18n.importing);

                        wp_ajax.success = (rsp) => {

                            if (true === rsp.status) {
                                if ('' !== template.thumbnail) {
                                    this.setTemplate(template.slug, type);
                                    setTimeout(() => {
                                        let selected_designed = $('.wfacp_template_preview_container');
                                        selected_designed.find('.wfacp_template_importing_loader').show();
                                        let im = new Image();
                                        im.src = template.thumbnail;

                                        im.onload = () => {
                                            setTimeout((rsp) => {
                                                selected_designed.find('.wfacp_template_importing_loader').hide();
                                            }, 1000, rsp);
                                        };
                                        let scroll_Div = $('.wfacp_templates_inner.wfacp_selected_designed');


                                        cb(rsp);
                                    }, 300);
                                } else {
                                    cb(rsp);
                                }
                            } else {
                                setTimeout((msg) => {
                                    this.showFailedImport(msg);
                                }, 200, rsp.msg);
                                cb(rsp);
                            }

                        };
                    },

                    get_remove_template() {
                        wfacp.swal({
                            'title': wfacp_localization.importer.remove_template.heading,
                            'type': 'warning',
                            'allowEscapeKey': false,
                            'confirmButtonText': wfacp_localization.importer.remove_template.button_text,
                            'text': wfacp_localization.importer.remove_template.sub_heading,
                            'showLoaderOnConfirm': true,
                            'preConfirm': () => {
                                $('button.swal2-cancel.swal2-styled').css({'display': 'none'});
                                return new Promise((resolve) => {
                                    this.removeDesign((rsp) => {
                                        this.template_active = 'no';
                                        resolve(rsp);
                                    });
                                });
                            }
                        });

                    },
                    maybeInstallPlugin(template, type, cb) {
                        let currentObj = this;
                        this.cb = cb;
                        this.selected_template = template;
                        let page_builder_plugins = builder.pageBuildersOptions[this.current_template_type].plugins;
                        let pluginToInstall = 0;
                        $.each(page_builder_plugins, function (index, plugin) {
                            if ('install' === plugin.status) {
                                currentObj.swalLoadingText(builder.wfacp_i18n.plugin_install);
                                pluginToInstall++;
                                // Add each plugin activate request in Ajax queue.
                                // @see wp-admin/js/updates.js
                                window.wp.updates.queue.push({
                                    action: 'install-plugin', // Required action.
                                    data: {
                                        slug: plugin.slug
                                    }
                                });
                            }
                        });

                        // Required to set queue.
                        window.wp.updates.queueChecker();

                        if (0 === pluginToInstall) {
                            $.each(page_builder_plugins, function (index, plugin) {
                                if ('activate' === plugin.status) {
                                    currentObj.activatePlugin(plugin.init);
                                }
                            });
                        }
                    },
                    afterInstall(event, response) {
                        let currentObj = this;
                        var page_builder_plugins = builder.pageBuildersOptions[this.current_template_type].plugins;

                        $.each(page_builder_plugins, function (index, plugin) {
                            if ('install' === plugin.status && response.slug === plugin.slug) {
                                currentObj.activatePlugin(plugin.init);
                            }
                        });
                    },
                    swalLoadingText(text) {
                        if ($(".swal2-actions.swal2-loading .loading-text").length === 0) {
                            $(".swal2-actions.swal2-loading").append("<div class='loading-text'></div>");
                        }
                        $(".swal2-actions.swal2-loading .loading-text").text(text);
                    },

                    activatePlugin(plugin_slug) {
                        let wp_ajax = new wfacp.ajax();
                        let currentObj = this;
                        currentObj.swalLoadingText(builder.wfacp_i18n.plugin_activate);
                        let add_plugin = {
                            'plugin_init': plugin_slug,
                        };

                        wp_ajax.ajax('activate_plugin', add_plugin);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            currentObj.importTemplate(currentObj.selected_template, currentObj.current_template_type, currentObj.cb);
                            var page_builder_plugins = builder.pageBuildersOptions[currentObj.current_template_type].plugins;
                            $.each(page_builder_plugins, function (index, plugin) {
                                if (plugin.init === rsp.init) {
                                    if ('install' === plugin.status || 'activate' === plugin.status) {
                                        builder.pageBuildersOptions[currentObj.current_template_type].plugins[index].status = null;
                                    }
                                }
                            });
                        };
                        wp_ajax.error = (rsp) => {
                        };
                    },


                    triggerImport(template, slug, type) {
                        let title = wfacp_localization.importer.activate_template.heading;
                        let sub_heading = wfacp_localization.importer.activate_template.sub_heading;
                        let button_text = wfacp_localization.importer.activate_template.button_text;
                        if ('yes' === template.show_import_popup) {
                            title = wfacp_localization.importer.add_template.heading;
                            sub_heading = wfacp_localization.importer.add_template.sub_heading;
                            button_text = wfacp_localization.importer.add_template.button_text;
                        }

                        /**
                         * Loop over the plugin dependency for the every page builder
                         * If we found any dependency plugin inactive Or not installed we need to hold back the import process and
                         * Alert user about missing dependency and further process to install and activate
                         */
                        var anyPluginInactive = true;
                        if (!_.isUndefined(builder.pageBuildersOptions[this.current_template_type])) {
                            var page_builder_plugins = builder.pageBuildersOptions[this.current_template_type].plugins;
                            $.each(page_builder_plugins, function (index, plugin) {
                                if (anyPluginInactive) {
                                    if ('install' === plugin.status || 'activate' === plugin.status) {
                                        anyPluginInactive = false;
                                    }
                                }
                            });
                        }

                        if (false === anyPluginInactive) {
                            wfacp.swal({
                                'title': builder.pageBuildersTexts[this.current_template_type].title,
                                'type': 'warning',
                                'allowEscapeKey': false,
                                'showCancelButton': false,
                                'confirmButtonText': builder.pageBuildersTexts[this.current_template_type].ButtonText,
                                'html': builder.pageBuildersTexts[this.current_template_type].text,
                                showLoaderOnConfirm: true,
                                'preConfirm': () => {
                                    if ('no' === builder.pageBuildersTexts[this.current_template_type].noInstall) {
                                        return new Promise((resolve) => {
                                            this.maybeInstallPlugin(template, type, resolve);
                                        });
                                    }
                                }
                            });
                            return;
                        }

                        wfacp.swal({
                            'title': title,
                            'type': 'warning',
                            'allowEscapeKey': false,
                            'confirmButtonText': button_text,
                            'showCancelButton': false,
                            'text': sub_heading,
                            showLoaderOnConfirm: true,
                            'preConfirm': () => {
                                $('button.swal2-cancel.swal2-styled').css({'display': 'none'});

                                if ('yes' === template.multistep || 'yes' === template.import) {
                                    return new Promise((resolve) => {
                                        this.importTemplate(template, type, resolve);
                                    });
                                } else {
                                    return new Promise((resolve) => {
                                        this.setTemplate(slug, type, resolve);
                                    });
                                }

                            }
                        });

                    },
                    save(template_active = 'yes', cb = '') {
                        let wp_ajax = new wfacp.ajax();
                        let save_layout = {
                            'selected_type': this.current_template_type,
                            'selected': this.selected,
                            'wfacp_id': self.id,
                            'template_active': template_active,
                        };

                        wp_ajax.ajax('save_design', save_layout);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            wfacp.show_data_save_model(rsp.msg);
                            this.selected_type = this.current_template_type;
                            this.selected_template = this.designs[this.selected_type][this.selected];
                            $('#wfacp_control > .wfacp_p20_noside').show();
                            console.log(this.selected_template);
                            if (this.selected_template.no_steps == '2') {
                                wfacp_data.layout.current_step = 'two_step';
                            } else if (this.selected_template.no_steps == '3') {
                                wfacp_data.layout.current_step = 'third_step';
                            } else {
                                wfacp_data.layout.current_step = 'single_step';
                            }


                            if (typeof cb == "function") {
                                cb(rsp);
                            }
                        };

                    }
                },
                data: {
                    current_template_type: this.selected_type,
                    selected_type: this.selected_type,
                    designs: this.designs,
                    design_types: this.design_types,
                    selected: this.selected,
                    selected_template: this.selected_template,
                    template_active: this.template_active,
                    temp_template_type: '',
                    temp_template_slug: '',
                    description: this.description,
                }
            });
            return this.main;
        }

        model() {
            this.show_design_model = $("#modal-show-design-template");
            if (this.show_design_model.length > 0) {
                this.show_design_model.iziModal({
                        headerColor: '#6dbe45',
                        background: '#efefef',
                        borderBottom: false,
                        width: 600,
                        overlayColor: 'rgba(0, 0, 0, 0.6)',
                        transitionIn: 'fadeInDown',
                        transitionOut: 'fadeOutDown',
                        navigateArrows: "false",
                        onOpening: (modal) => {
                            modal.startLoading();
                        },
                        onOpened: (modal) => {
                            modal.stopLoading();
                        },
                        onClosed: () => {
                        }
                    }
                );
            }

            $(document.body).on('click', '.wfacp_embed_form_tab', function () {

                let aria_control = $(this).attr('aria-controls');
                $('.wfacp_embed_form_tab').removeClass('wfacp-active');
                $(this).addClass('wfacp-active');
                $('.wfacp_embed_fieldset').hide();
                $('.wfacp_embed_fieldset' + '.' + aria_control).show();

            });
        }

        jQuery() {

            let wfacp_obj = this;

            /**
             * Trigger async event on plugin install success as we are executing wp native js API to update/install a plugin
             */
            $(document).on('wp-plugin-install-success', function (event, response) {
                wfacp_obj.main.afterInstall(event, response);
            });
            $(document).on('click', '.wfacp_filter_container_inner', function () {
                $('.wfacp_filter_container_inner').removeClass('wfacp_selected_filter');
                let filter_type = $(this).data('filter-type');
                let card = $('.wfacp_temp_card');
                $(this).addClass('wfacp_selected_filter');
                if (0 == filter_type) {
                    card.show();
                    return;
                } else {
                    card.hide();
                    let display_card = $('.wfacp_temp_card[data-steps=' + filter_type + ']');
                    display_card.show();
                }

            });
        }

    }


    class wfacp_add_new_page {
        constructor(builder) {

            let el = document.getElementById("modal-checkout-page");
            if (el != null) {
                this.add_new_model_open = false;
                this.id = wfacp.tools.hp(builder, 'id') ? builder.id : 0;
                this.name = wfacp.tools.hp(builder, 'name') ? builder.name : '';
                this.post_name = wfacp.tools.hp(builder, 'post_name') ? builder.post_name : '';
                this.post_content = wfacp.tools.hp(builder, 'post_content') ? builder.post_content : '';
                this.base_url = wfacp.tools.hp(builder, 'base_url') ? builder.base_url : '';
                this.model();

            }
        }

        main(modal) {

            if (this.add_new_model_open !== false) {
                return;
            }
            let self = this;
            this.add_new_model_open = true;
            this.add_new = new Vue({
                'el': '#add-new-form',
                components: {
                    "vue-form-generator": VueFormGenerator.component
                },
                methods: {
                    defaultModel() {
                        return {
                            name: "",
                            post_content: "",
                        };
                    },
                    clearData() {
                        this.model = this.defaultModel();
                    },
                    onSubmit() {
                        this.modal.startLoading();
                        let page_title = this.model.name;
                        let wp_ajax = new wfacp.ajax();
                        let add_query = {'wfacp_name': page_title, 'wfacp_id': self.id, 'post_name': this.model.post_name, 'post_content': this.model.post_content};
                        wp_ajax.ajax('add_checkout_page', add_query);
                        wp_ajax.success = (rsp) => {
                            if (rsp.status === true && rsp.redirect_url !== "#") {
                                this.modal.stopLoading();
                                $('#add-new-form').hide();
                                $(".wfacp-funnel-create-success-wrap").show();
                                setTimeout(() => {
                                    window.location.href = rsp.redirect_url;
                                }, 1000);
                            }
                            if (self.id > 0) {
                                this.modal.stopLoading();
                                self.add_new_page_model.iziModal('close');
                                wfacp_data.name = this.model.name;
                                $(".wfacp_page_title").text(this.model.name);
                                $(".wfacp-preview").attr('href', rsp.new_url);
                            }
                        };
                    }
                },
                data: {
                    modal: modal,
                    btn_name: (this.id > 0 ? wfacp_localization.global.update_btn : wfacp_localization.global.add_checkout_btn),
                    model: {
                        id: this.id,
                        name: self.name,
                        post_name: self.post_name,
                        post_url: self.post_url,
                        base_url: self.base_url,
                        post_content: self.post_content,
                    },
                    schema: {
                        fields: [
                            {
                                type: "input",
                                inputType: "text",
                                label: wfacp_localization.global.add_checkout.heading,
                                model: "name",
                                required: true,
                                validator: VueFormGenerator.validators.string
                            },
                            {
                                type: "textArea",
                                label: wfacp_localization.global.add_checkout.post_content,
                                model: "post_content",
                                validator: VueFormGenerator.validators.string
                            },
                            {
                                type: "input",
                                inputType: "text",
                                label: wfacp_localization.global.add_checkout.checkout_url_slug,
                                model: "post_name",
                                required: true,
                                validator: VueFormGenerator.validators.string,
                                visible: function (model) {
                                    return (model.id > 0);
                                },
                            }]
                    },
                    formOptions: {
                        validateAfterChanged: true
                    }
                }

            });
            return this.add_new;
        }

        model() {
            (function ($) {
                var re = /([^&=]+)=?([^&]*)/g;
                var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
                var decode = function (str) {
                    return decodeURIComponent(str.replace(decodeRE, " "));
                };
                $.parseParams = function (query) {
                    var params = {}, e;
                    while ((e = re.exec(query))) {
                        var k = decode(e[1]), v = decode(e[2]);
                        if (k.substring(k.length - 2) === '[]') {
                            k = k.substring(0, k.length - 2);
                            (params[k] || (params[k] = [])).push(v);
                        } else params[k] = v;
                    }
                    return params;
                };
            })(jQuery);
            $('.woofunnels_page_wfacp .icl_translations a').on('click', function (e) {

                let otgs_edit = $(this).find('.otgs-ico-add');
                if (otgs_edit.length > 0) {
                    let href = $(this).attr('href');
                    href = href.replace('post-new.php?', '');
                    e.preventDefault();
                    href = $.parseParams(href);
                    if (typeof href == 'object' && wfacp.tools.ol(href) > 0) {
                        e.preventDefault();
                        let ajax = new wfacp.ajax();
                        ajax.ajax('make_wpml_duplicate', href);
                        ajax.success = (rsp) => {
                            if (rsp.status == true) {
                                window.location.href = rsp.redirect_url;
                            } else {
                                alert(rsp.msg);
                            }
                        };
                    }
                }
            });
            this.add_new_page_model = $("#modal-checkout-page");
            if (this.add_new_page_model.length > 0) {
                this.add_new_page_model.iziModal({
                        title: (this.id > 0 ? wfacp_localization.global.edit_checkout_page : wfacp_localization.global.add_checkout_page),
                        headerColor: '#6dbe45',
                        background: '#efefef',
                        borderBottom: false,
                        width: 600,
                        overlayColor: 'rgba(0, 0, 0, 0.6)',
                        transitionIn: 'fadeInDown',
                        transitionOut: 'fadeOutDown',
                        navigateArrows: "false",
                        onOpening: (modal) => {
                            modal.startLoading();
                        },
                        onOpened: (modal) => {
                            modal.stopLoading();
                            this.main(modal);
                        }

                    }
                );
            }
        }
    }


    class wfacp_settings {
        constructor(builder) {
            this.id = builder.id;
            this.settings = builder.settings;
            let el = document.getElementById("wfacp_setting_container");
            if (el != null) {
                this.main();
            }
        }

        getFields() {
            let fields = [
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.analytics.heading,
                            hint: wfacp_localization.settings.analytics.hint,
                            styleClasses: 'wfacp_setting_heading',
                        },

                        {
                            type: 'radios',
                            inputType: 'text',
                            label: wfacp_localization.settings.analytics.override,
                            default: 'false',
                            model: 'override_global_track_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "label",
                            label: wfacp_localization.settings.analytics.pixel.heading,
                            styleClasses: ['wfacp_setting_heading', 'wfacp_setting_child_heading'],
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'radios',
                            inputType: 'text',
                            label: wfacp_localization.settings.analytics.events.page_view,
                            default: 'false',
                            styleClasses: ['wfacp_checkbox_wrap'],
                            model: 'pixel_is_page_view',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'radios',
                            inputType: 'text',
                            label: wfacp_localization.settings.analytics.events.add_to_cart,
                            default: 'false',
                            styleClasses: ['wfacp_checkbox_wrap'],
                            model: 'pixel_add_to_cart_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },

                        {
                            type: 'select',
                            styleClasses: 'group-one-class wfacp_track_option_dropdown',
                            label: wfacp_localization.settings.analytics.options_label,
                            default: 'load',
                            values: wfacp_localization.settings.analytics.track_event_options,
                            model: 'pixel_add_to_cart_event_position',
                            selectOptions: {
                                hideNoneSelectedText: true,
                            },
                            visible: (model) => {
                                return ('true' == model.pixel_add_to_cart_event && 'true' == model.override_global_track_event);
                            }
                        },
                        {
                            'type': 'radios',
                            'inputType': 'text',
                            'label': wfacp_localization.settings.analytics.events.checkout,
                            'default': 'false',
                            'styleClasses': ['wfacp_checkbox_wrap'],
                            'model': 'pixel_initiate_checkout_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'select',
                            styleClasses: 'group-one-class wfacp_track_option_dropdown',
                            label: wfacp_localization.settings.analytics.options_label,
                            default: 'load',
                            values: wfacp_localization.settings.analytics.track_event_options,
                            model: 'pixel_initiate_checkout_event_position',
                            selectOptions: {
                                hideNoneSelectedText: true,
                            },
                            visible: (model) => {
                                return ('true' == model.pixel_initiate_checkout_event && 'true' == model.override_global_track_event);
                            }
                        },
                        {
                            'type': 'radios',
                            'inputType': 'text',
                            'label': wfacp_localization.settings.analytics.events.payment,
                            'default': 'false',
                            'styleClasses': ['wfacp_checkbox_wrap'],
                            'model': 'pixel_add_payment_info_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        //Google
                        {
                            type: 'label',
                            label: wfacp_localization.settings.analytics.google.heading,
                            styleClasses: ['wfacp_setting_heading', 'wfacp_setting_child_heading'],
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'radios',
                            inputType: 'text',
                            label: wfacp_localization.settings.analytics.events.page_view,
                            default: 'false',
                            styleClasses: ['wfacp_checkbox_wrap'],
                            model: 'google_ua_is_page_view',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            'type': 'radios',
                            'inputType': 'text',
                            'label': wfacp_localization.settings.analytics.events.add_to_cart,
                            'default': 'false',
                            'styleClasses': ['wfacp_checkbox_wrap'],
                            'model': 'google_ua_add_to_cart_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'select',
                            styleClasses: 'group-one-class wfacp_track_option_dropdown',
                            label: wfacp_localization.settings.analytics.options_label,
                            default: 'load',
                            values: wfacp_localization.settings.analytics.track_event_options,
                            model: 'google_ua_add_to_cart_event_position',
                            selectOptions: {
                                hideNoneSelectedText: true,
                            },
                            visible: (model) => {
                                return ('true' == model.google_ua_add_to_cart_event && 'true' == model.override_global_track_event);
                            }
                        },
                        {
                            'type': 'radios',
                            'inputType': 'text',
                            'label': wfacp_localization.settings.analytics.events.checkout,
                            'default': 'false',
                            'styleClasses': ['wfacp_checkbox_wrap'],
                            'model': 'google_ua_initiate_checkout_event',
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        },
                        {
                            type: 'select',
                            styleClasses: 'group-one-class wfacp_track_option_dropdown',
                            label: wfacp_localization.settings.analytics.options_label,
                            default: '0',
                            values: wfacp_localization.settings.analytics.track_event_options,
                            model: 'google_ua_initiate_checkout_event_position',
                            selectOptions: {
                                hideNoneSelectedText: true,
                            },
                            visible: (model) => {
                                return ('true' == model.google_ua_initiate_checkout_event && 'true' == model.override_global_track_event);
                            }
                        },
                        {
                            'type': 'radios',
                            'inputType': 'text',
                            'label': wfacp_localization.settings.analytics.events.payment,
                            'default': 'false',
                            'styleClasses': ['wfacp_checkbox_wrap'],
                            'model': 'google_ua_add_payment_info_event',

                            values: () => {
                                return (wfacp_localization.settings.radio_fields);
                            },
                            visible: (model) => {
                                return ('true' == model.override_global_track_event);
                            }
                        }
                    ]
                },
                //Header Footer field
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.scripts.heading,
                            hint: wfacp_localization.settings.scripts.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "textArea",
                            label: wfacp_localization.settings.scripts.header_heading,
                            placeholder: wfacp_localization.settings.scripts.header_script_placeholder,
                            model: "header_script",
                            rows: 4,
                            validator: VueFormGenerator.validators.string
                        },
                        {
                            type: "textArea",
                            label: wfacp_localization.settings.scripts.footer_heading,
                            placeholder: wfacp_localization.settings.scripts.footer_script_placeholder,
                            model: "footer_script",
                            rows: 4,
                            validator: VueFormGenerator.validators.string
                        },

                    ]
                },
                //Track and analytics
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.style.heading,
                            hint: wfacp_localization.settings.style.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "textArea",
                            label: wfacp_localization.settings.style.header_heading,
                            placeholder: wfacp_localization.settings.style.header_style_placeholder,
                            model: "header_css",
                            rows: 4,
                            validator: VueFormGenerator.validators.string
                        },
                    ]
                },
            ];
            return wfacp.hooks.applyFilters('wfacp_settings_fields', fields, this);
        }

        getModels() {
            let model = wfacp.tools.jsp(this.settings);
            model = wfacp.hooks.applyFilters('wfacp_settings_data_model', model, this);
            return model;
        }

        main() {
            let self = this;
            this.settings_vue = new Vue({
                "el": "#wfacp_setting_container",
                components: {

                    "vue-form-generator": VueFormGenerator.component,
                    Multiselect: window.VueMultiselect.default,
                },

                methods: {
                    changed() {
                        setTimeout(() => {
                            $('.wfacp_date_field .form-control').attr('type', 'date');
                        }, 100);
                    },
                    save() {
                        let wp_ajax = new wfacp.ajax();
                        let settings = wfacp.tools.jsp(this.model);
                        if (settings.close_checkout_on !== '' && settings.close_checkout_on > 0) {
                            settings.close_checkout_on = parseInt(settings.close_checkout_on / 1000);
                        }

                        let add_query = {'wfacp_id': self.id, 'settings': settings};
                        add_query = wfacp.hooks.applyFilters('wfacp_before_settings_data_saved', add_query, this);
                        wp_ajax.ajax('save_settings', add_query);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            wfacp.show_data_save_model(rsp.msg);
                        };
                    }

                },
                data: {
                    search_timeout: false,
                    isLoading: false,
                    model: this.getModels(),
                    formOptions: {},
                    schema: {
                        groups: this.getFields()
                    }
                }
            });
            return this.settings_vue;
        }

    }

    class wfacp_optimizations {
        constructor(builder) {

            let el = document.getElementById('wfacp_optimization_container');
            if (null == el) {
                return;
            }
            this.settings = builder.settings;
            this.id = builder.id;
            this.preview_field();
            this.main();

        }


        preview_field() {
            if (false == wfacp.tools.hp(wfacp_data, 'layout')) {
                return;
            }


            let fieldset = wfacp_data.layout;
            let my_models = {'single_step': [], 'two_step': [], 'third_step': []};
            let notAllowedType = ['product', 'wfacp_html', 'wfacp_end_divider', 'password', 'wfacp_start_divider'];

            let available_steps = [];
            if (wfacp_data.layout.current_step === 'two_step') {
                available_steps = ['single_step'];
            } else if (wfacp_data.layout.current_step === 'third_step') {
                available_steps = ['single_step', 'two_step'];
            }


            function not_allowed_field(single_field) {
                if (notAllowedType.indexOf(single_field.type) > -1 && single_field.id !== 'shipping_calculator') {
                    return true;
                }
                return false;

            }


            wfacp.hooks.addFilter('wfacp_optimization_preview_fields', function (fields) {
                if (available_steps.length === 0) {

                    let tmp_h1 = {
                        type: "label",
                        label: wfacp_localization.settings.preview_field_admin_heading,
                        hint: wfacp_localization.settings.preview_field_admin_heading_hint,
                        styleClasses: 'wfacp_setting_heading wfacp_admin_preview_feilds_wrap',
                    };
                    let tmp_h2 = {
                        type: "label",
                        hint: wfacp_localization.settings.preview_field_admin_note,
                        styleClasses: 'wfacp_setting_heading wfacp_preview_note',
                    };


                    fields.push(tmp_h1);
                    fields.push(tmp_h2);

                    return fields;
                }


                let header1 = {
                    type: "label",
                    label: wfacp_localization.settings.preview_field_admin_heading,
                    hint: wfacp_localization.settings.preview_field_admin_heading_hint,
                    styleClasses: 'wfacp_setting_heading preview_heading_wrap',

                };
                fields.push(header1);

                let preview_checked = {};

                for (let f = 0; f < available_steps.length; f++) {
                    let i = available_steps[f];
                    let sections = fieldset.fieldsets[i];
                    for (let k = 0; k < sections.length > 0; k++) {
                        let step_fields = sections[k].fields;
                        for (let n in step_fields) {
                            let single_field = step_fields[n];
                            if (not_allowed_field(single_field)) {
                                continue;
                            }

                            fields.push({
                                type: "checkbox",
                                label: ('' !== single_field.label) ? single_field.label : single_field.data_label,
                                model: single_field.id,
                                styleClasses: 'wfacp_preview_fields',
                            });
                        }
                    }
                }

                if (fields.length > 1) {
                    let headingsection1 = {
                        type: "input",
                        inputType: "text",
                        label: wfacp_localization.settings.preview_section_heading,
                        model: "preview_section_heading",
                        styleClasses: 'preview_section_heading_wrap wfaco_preview_top_spacing',
                        validator: VueFormGenerator.validators.string
                    };
                    let headingsection2 = {
                        type: "input",
                        inputType: "text",
                        label: wfacp_localization.settings.preview_section_subheading,
                        model: "preview_section_subheading",
                        styleClasses: 'preview_section_subheading_wrap',
                        validator: VueFormGenerator.validators.string
                    };

                    let preview_field_text = {
                        type: "input",
                        inputType: "text",
                        label: wfacp_localization.settings.preview_field_preview_text,
                        model: "preview_field_preview_text",
                        styleClasses: 'preview_section_subheading_wrap',
                        validator: VueFormGenerator.validators.string
                    };
                    fields.push(headingsection1);
                    fields.push(headingsection2);
                    fields.push(preview_field_text);

                }
                return fields;
            });
            wfacp.hooks.addFilter('wfacp_optimizations_data_model', function (model, instance) {

                if (available_steps.length === 0) {
                    return model;
                }

                for (let f = 0; f < available_steps.length; f++) {
                    let i = available_steps[f];
                    let sections = fieldset.fieldsets[i];
                    for (let k = 0; k < sections.length > 0; k++) {
                        let step_fields = sections[k].fields;
                        for (let n in step_fields) {
                            let single_field = step_fields[n];
                            if (not_allowed_field(single_field)) {
                                continue;
                            }
                            if (wfacp.tools.hp(instance.settings.show_on_next_step[i], single_field.id) && 'true' == instance.settings.show_on_next_step[i][single_field.id]) {
                                model[single_field.id] = true;
                            } else {
                                model[single_field.id] = false;
                            }
                            my_models[i].push(single_field.id);
                        }
                    }
                }


                if (wfacp.tools.hp(instance.settings, 'preview_section_heading')) {
                    model.preview_section_heading = instance.settings.preview_section_heading;
                }
                if (wfacp.tools.hp(instance.settings, 'preview_section_subheading')) {
                    model.preview_section_subheading = instance.settings.preview_section_subheading;
                }


                return model;

            });
            wfacp.hooks.addFilter('wfacp_before_optimization_data_saved', function (data, instance) {
                data.settings.show_on_next_step = {};
                for (let i in my_models) {
                    if (my_models[i].length === 0) {
                        continue;
                    }
                    data.settings.show_on_next_step[i] = {};
                    let fields = my_models[i];
                    for (let j = 0; j < fields.length; j++) {
                        let model = fields[j];
                        if (wfacp.tools.hp(data.settings, model)) {
                            data.settings.show_on_next_step[i][model] = data.settings[model];
                            delete data.settings[model];
                        }
                    }
                }
                return data;

            });
        }

        getFields() {

            let preview_fields = wfacp.hooks.applyFilters('wfacp_optimization_preview_fields', [], this);

            let fields = [
                //Google Autocomplete
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.google_autocomplete.heading,
                            hint: wfacp_localization.google_autocomplete.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.global.enable,
                            model: "enable_google_autocomplete",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "vueMultiSelect",
                            model: "disallow_autocomplete_countries",
                            label: wfacp_localization.google_autocomplete.country_label,
                            required: false,
                            selectOptions: {
                                multiple: true,
                                trackBy: "id",
                                label: "name",
                            },
                            values: wfacp_data.available_countries,
                            visible: (model) => {
                                return (model.enable_google_autocomplete === 'true');
                            },
                        },
                    ]
                },
                //coupons
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.coupons.heading,
                            hint: wfacp_localization.settings.coupons.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.settings.coupons.auto_add_coupon_heading,
                            model: "enable_coupon",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "input",
                            inputType: "text",
                            model: "coupons",
                            label: wfacp_localization.settings.coupons.coupon_heading,
                            placeholder: wfacp_localization.settings.coupons.search_placeholder,
                            required: true,
                            visible: (model) => {
                                return (model.enable_coupon === 'true');
                            },
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.settings.coupons.disable_coupon,
                            model: "disable_coupon",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                    ]
                },
                //smart buttons
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.optimizations.smart_buttons.heading,
                            hint: wfacp_localization.optimizations.smart_buttons.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.global.enable,
                            model: "enable_smart_buttons",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "vueMultiSelect",
                            model: "smart_button_position",
                            label: wfacp_localization.optimizations.smart_buttons.position_heading,
                            required: true,
                            selectOptions: {
                                selectedValue: 'wfacp_before_product_switching_field',
                                multiple: false,
                                trackBy: "id",
                                label: "name",

                            },
                            values: wfacp_localization.optimizations.smart_buttons.positions,
                            visible: (model) => {
                                return (model.enable_smart_buttons == "true");
                            },
                        },
                    ]
                },
                //Preview field
                {
                    fields: preview_fields
                },
                //preferred country
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.optimizations.preferred_country.heading,
                            hint: wfacp_localization.optimizations.preferred_country.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.global.enable,
                            model: "preferred_countries_enable",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "vueMultiSelect",
                            model: "preferred_countries",
                            label: wfacp_localization.optimizations.preferred_country.label,
                            placeholder: wfacp_localization.optimizations.preferred_country.placeholder,
                            required: true,
                            selectOptions: {
                                multiple: true,
                                trackBy: "id",
                                label: "name",
                            },
                            values: wfacp_data.available_countries,
                            visible: (model) => {

                                return (model.preferred_countries_enable === 'true');
                            },
                        },
                    ]
                },
                //expire settings
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.advanced.heading,
                            hint: wfacp_localization.settings.advanced.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.settings.advanced.close_after,
                            model: "close_after_x_purchase",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                        {
                            type: "input",
                            inputType: "text",
                            model: "total_purchased_allowed",
                            label: wfacp_localization.settings.advanced.total_purchased_allowed,
                            hint: wfacp_localization.settings.advanced.total_purchased_allowed_hint,
                            placeholder: '100',
                            required: true,
                            visible: (model) => {
                                return (model.close_after_x_purchase === 'true');
                            },
                        },
                        {
                            type: "input",
                            inputType: "URL",
                            model: "total_purchased_redirect_url",
                            label: wfacp_localization.settings.advanced.total_purchased_redirect_url,
                            hint: wfacp_localization.settings.advanced.total_purchased_redirect_url_hint,
                            placeholder: 'http://',
                            required: true,
                            visible: (model) => {
                                return (model.close_after_x_purchase === 'true');
                            },
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.settings.advanced.close_checkout_after_date,
                            model: "close_checkout_after_date",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },

                        },
                        {
                            type: "input",
                            inputType: "text",
                            label: wfacp_localization.settings.advanced.close_checkout_on,
                            model: "close_checkout_on",
                            styleClasses: 'wfacp_date_field',
                            hint: wfacp_localization.settings.advanced.close_checkout_on_hint,
                            validator: VueFormGenerator.validators.date,
                            required: true,
                            visible: (model) => {
                                return (model.close_checkout_after_date === 'true');
                            },
                        },
                        {
                            type: "input",
                            inputType: "URL",
                            model: "close_checkout_redirect_url",

                            label: wfacp_localization.settings.advanced.close_checkout_redirect_url,
                            hint: wfacp_localization.settings.advanced.close_checkout_redirect_url_hint,
                            placeholder: 'http://',
                            required: true,
                            visible: (model) => {
                                return (model.close_checkout_after_date === 'true');
                            },
                        },
                    ]
                },
                //auto populate field
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.autopopulate_fields.heading,
                            hint: wfacp_localization.settings.autopopulate_fields.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.global.enable,
                            model: "enable_autopopulate_fields",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        },
                    ]
                },
                //auto fill state
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.autopopulate_state.heading,
                            hint: wfacp_localization.settings.autopopulate_state.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },
                        {
                            type: "radios",
                            label: wfacp_localization.global.enable,
                            model: "enable_autopopulate_state",
                            values: () => {
                                return wfacp_localization.settings.radio_fields;
                            },
                        }
                    ]
                },
                {
                    fields: [
                        {
                            type: "label",
                            label: wfacp_localization.settings.auto_fill_url.heading,
                            hint: wfacp_localization.settings.auto_fill_url.sub_heading,
                            styleClasses: 'wfacp_setting_heading',
                        },

                        {
                            type: "input",
                            inputType: "text",
                            label: wfacp_localization.settings.auto_fill_url.product_ids,
                            model: "auto_fill_url_product_ids",
                            hint: wfacp_localization.settings.auto_fill_url.product_ids_hint,

                        },
                        {
                            type: "input",
                            inputType: "text",
                            label: wfacp_localization.settings.auto_fill_url.quantity,
                            model: "auto_fill_url_product_qty",
                            hint: wfacp_localization.settings.auto_fill_url.quantity_hint,

                        },
                        {
                            type: "select",
                            model: "auto_fill_url_autoresponder",
                            label: wfacp_localization.settings.auto_fill_url.auto_responder_label,
                            selectOptions: {
                                hideNoneSelectedText: true,
                            },
                            values: function () {
                                return Object.values(wfacp_localization.settings.auto_fill_url.auto_responder_options);
                            },
                        },
                        {
                            type: "checklist",
                            label: wfacp_localization.settings.auto_fill_url.fields_label,
                            model: "auto_fill_url_fields_options",
                            listBox: true,
                            values: function () {
                                return wfacp_localization.settings.auto_fill_url.fields_options;
                            },
                            visible: (model) => {
                                return (model.auto_fill_url_autoresponder !== 'select_email_provider');
                            }
                        },
                        {
                            type: "textArea",
                            label: wfacp_localization.settings.auto_fill_url.perfill_url,
                            model: "auto_fill_url_product_qty_url",
                            styleClasses: 'auto_fill_url_product_qty_url',
                            readonly: true,

                        },
                    ]
                },

            ];

            return wfacp.hooks.applyFilters('wfacp_optimization_fields', fields, this);
        }

        getModels() {
            let model = wfacp.tools.jsp(this.settings);
            model = wfacp.hooks.applyFilters('wfacp_optimizations_data_model', model, this);
            return model;
        }

        main() {
            let self = this;
            this.settings_vue = new Vue({
                "el": "#wfacp_optimization_container",
                components: {

                    "vue-form-generator": VueFormGenerator.component,
                },
                created: () => {
                    setTimeout(() => {
                        $('.wfacp_date_field .form-control').attr('type', 'date');
                        $('.auto_fill_url_product_qty_url  textarea').prop('readonly', true);
                    }, 100);

                },

                methods: {
                    changed() {
                        console.log('Hello');
                        setTimeout(() => {
                            $('.wfacp_date_field .form-control').attr('type', 'date');


                        }, 100);
                        this.prefil_url();
                    },
                    prefil_url: () => {
                        // self.settings_vue.model;
                        let params = [];
                        let model = self.settings_vue.model;
                        let ids = model.auto_fill_url_product_ids;
                        let qty = model.auto_fill_url_product_qty;
                        if (undefined !== ids && '' !== ids) {
                            params.push(wfacp_data.parameters.add_to_checkout + '=' + ids);
                            if (undefined !== qty && '' !== qty) {
                                params.push(wfacp_data.parameters.qty + '=' + qty);
                            }
                        }
                        let options = model.auto_fill_url_fields_options;
                        let url = wfacp.tools.jsp(wfacp_data.post_url);
                        console.log(model.auto_fill_url_autoresponder);
                        if (undefined !== model.auto_fill_url_autoresponder) {
                            let responder = model.auto_fill_url_autoresponder;
                            let merge_tags = wfacp.tools.jsp(wfacp_localization.settings.auto_fill_url.auto_responder_options[responder].merge_tags);
                            if (undefined !== options && options.length > 0 && undefined !== merge_tags && wfacp.tools.ol(merge_tags) > 0) {
                                for (let tag in merge_tags) {
                                    if (options.indexOf(tag) > -1) {
                                        params.push(tag + '=' + merge_tags[tag]);
                                    }
                                }
                            }
                        }
                        console.log(params);
                        if (params.length > 0) {
                            if (url.indexOf('?') > -1) {
                                //means get URL;
                                url += '&' + params.join('&');
                            } else {
                                //pretty url
                                url += '?' + params.join('&');
                            }
                            self.settings_vue.model.auto_fill_url_product_qty_url = url;
                            $('.auto_fill_url_product_qty_url textarea').val(url);
                        }
                    },
                    save() {
                        let settings = wfacp.tools.jsp(this.model);
                        let wp_ajax = new wfacp.ajax();
                        let add_query = {'wfacp_id': self.id, 'settings': settings};
                        add_query = wfacp.hooks.applyFilters('wfacp_before_optimization_data_saved', add_query, this);
                        wp_ajax.ajax('save_settings', add_query);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            wfacp.show_data_save_model(rsp.msg);
                        };
                    }
                },
                data: {
                    model: this.getModels(),
                    formOptions: {},
                    schema: {
                        groups: this.getFields()
                    }

                }
            });

        }
    }


    class wfacp_global_settings {
        constructor(builder) {
            this.id = builder.id;
            this.schema = builder.global_settings.schema;
            this.model = builder.global_settings.model;
            let el = document.getElementById("wfacp_global_settings");
            if (el != null) {
                this.main();
                this.jquery();
            }
        }

        main() {

            function track_events_options_callback_add_to_cart(model) {
                console.log('add_to_cart', model);
            }

            function wfacp_checkout_pixel_initiate_checkout_event(model) {
                console.log('initiate_checkout', model);
            }

            let self = this;
            for (let i in this.model) {
                if (this.model[i] === 'true') {
                    this.model[i] = true;
                } else if (this.model[i] === 'false') {
                    this.model[i] = false;
                }
            }
            this.settings_vue = new Vue({
                "el": "#wfacp_global_settings",
                components: {
                    "vue-form-generator": VueFormGenerator.component,
                    Multiselect: window.VueMultiselect.default
                },
                created: () => {
                    setTimeout(() => {
                        $('.form-group.field-checkbox').each(function () {
                            let label = $(this).find('label');
                            let label_text = label.text();
                            label.remove();
                            $(this).find('.field-wrap').append(label_text);
                        });
                        for (let i = 0; i < this.schema.groups.length; i++) {
                            let fields = this.schema.groups[i].fields;
                            for (let j = 0; j < fields.length; j++) {
                                if (wfacp.tools.hp(fields[j], 'hint') && wfacp.tools.hp(fields[j], 'styleClasses')) {
                                    if (fields[j].styleClasses.indexOf('wfacp_html_hint') > -1) {
                                        $('.' + fields[j].styleClasses.join('.')).find('.hint').html(fields[j].hint);
                                    }
                                }
                            }
                        }

                    }, 1000);
                },
                methods: {
                    onSubmit() {
                        let wp_ajax = new wfacp.ajax();
                        let add_query = {'wfacp_id': self.id, 'settings': this.model};
                        if (this.model.rewrite_slug === wfacp_data.checkout_page_slug) {
                            alert(wfacp_data.checkout_page_slug_error);
                            return;
                        }
                        wp_ajax.ajax('save_global_settings', add_query);
                        wfacp.show_spinner();
                        wp_ajax.success = (rsp) => {
                            wfacp.show_data_save_model(rsp.msg);
                        };
                    }
                },
                data: {
                    model: this.model,
                    search_timeout: false,
                    isLoading: false,
                    schema: this.schema,
                    formOptions: {}
                }
            });
            return this.settings_vue;
        }

        jquery() {
            $(document.body).on('change', '.wfacp_checkout_google_ua_enable_dynamic_ads input', function () {
                if ($(this).is(':checked')) {
                    $('.google_ua_dynamic_ads').show();
                } else {
                    $('.google_ua_dynamic_ads').hide();
                }

            });
        }
    }


    class wfacp_builder {
        constructor(data) {
            this.el = '#wfacp_control';
            this.setupData(data);
            this.initializeVue();
            this.model();
        }

        /**
         *
         * @param data
         */
        setupData(data) {
            if (wfacp.tools.ol(data) > 0) {
                for (let i in data) {
                    this[i] = data[i];
                }
            }
            wfacp.hooks.doAction('after_setup_data', this, data);
        }


        /*
         * Initialize All Vue
         */
        initializeVue() {
            wfacp.products = new wfacp_products(this);
            wfacp.fields = new wfacp_layouts(this);
            wfacp.design = new wfacp_design(this);
            wfacp.add_new_page = new wfacp_add_new_page(this);
            wfacp.settings = new wfacp_settings(this);
            wfacp.optimization = new wfacp_optimizations(this);
            wfacp.global_settings = new wfacp_global_settings(this);
            this.jQuery_events();
            wfacp.hooks.doAction('wfacp_loaded', this);
        }

        getData() {
            return this.data;
        }

        /**
         * Enable ajax handlers to all wfacp_forms_wrap class
         */


        jQuery_events() {
            $('.wfacp_checkout_page_status').on('change', function (e) {
                let is_checked = false;
                if ($(this).is(':checked')) {
                    is_checked = true;
                }
                let state_on = $(".wfacp_head_funnel_state_on");
                let state_off = $(".wfacp_head_funnel_state_off");
                if (is_checked) {
                    state_on.show();
                    state_off.hide();
                } else {
                    state_on.hide();
                    state_off.show();
                }
                let post_id = $(this).data('id');
                let ajax = new wfacp.ajax();
                ajax.ajax('update_page_status', {'post_status': is_checked, 'id': post_id});
            });

            $("#the-list .wfacp_funnels .wfacp-preview").on("click", function () {
                let wfacp_id = $(this).attr('data-id');
                let elem = $(this);
                elem.addClass('disabled');
                if (wfacp_id > 0) {
                    let wp_ajax = new wfacp.ajax();
                    wp_ajax.ajax("preview_details", {'wfacp_id': wfacp_id});
                    wp_ajax.success = (rsp) => {
                        if (rsp.status === true) {
                            $(this).WCBackboneModal({
                                template: 'wfacp-page-popup',
                                variable: rsp
                            });
                        }
                        elem.removeClass('disabled');
                    };
                }
                return false;
            });

            if ($(".wfacp-product-widget-tabs").length > 0) {
                let wfctb = $('.wfacp-product-widget-tabs .wfacp-tab-title');
                wfctb.on('click', function () {
                    let $this = $(this).closest('.wfacp-product-widget-tabs');
                    console.log($this);
                    let tabindex = $(this).attr('data-tab');
                    $this.find('.wfacp-tab-title').removeClass('wfacp-active');
                    $this.find('.wfacp-tab-title[data-tab=' + tabindex + ']').addClass('wfacp-active');
                    $($this).find('.wfacp-tab-content').removeClass('wfacp-activeTab');
                    let fieldset = $($this).find('.wfacp_forms_global_settings .vue-form-generator fieldset');
                    fieldset.hide();
                    fieldset.eq(tabindex - 1).addClass('wfacp-activeTab');
                    fieldset.eq(tabindex - 1).show();
                });
                wfctb.eq(0).trigger('click');
            }
            if ($(".wfacp-product-widget-tabs").length > 0) {
                let wfctb_1 = $('#wfacp_setting_container .wfacp-tab-title');
                wfctb_1.on('click', function () {
                    let $this = $(this).closest('.wfacp-product-widget-tabs');
                    console.log($this);
                    let tabindex = $(this).attr('data-tab');
                    console.log(tabindex);
                    $this.find('.wfacp-tab-title').removeClass('wfacp-active');
                    $this.find('.wfacp-tab-title[data-tab=' + tabindex + ']').addClass('wfacp-active');
                    $($this).find('.wfacp-tab-content').removeClass('wfacp-activeTab');
                    let fieldset = $('.wfacp_global_container .vue-form-generator fieldset');
                    console.log(fieldset);
                    fieldset.hide();
                    fieldset.eq(tabindex - 1).addClass('wfacp-activeTab');
                    fieldset.eq(tabindex - 1).show();
                });
                wfctb_1.eq(0).trigger('click');
            }

            $(".wfacp_delete_checkout_page").on('click', function (e) {

                e.preventDefault();
                let href = $(this).attr('href');
                let action = wfacp.swal({
                    'text': wfacp_localization.global.delete_checkout_page,
                    'title': wfacp_localization.global.delete_checkout_page_head,
                    'type': 'error',
                    'confirmButtonText': wfacp_localization.global.delete_checkout_page_btn,
                    'cancelButtonText': wfacp_localization.global.cancel_button_text
                });

                action.then((status) => {
                    if (wfacp.tools.hp(status, 'value')) {
                        window.location.href = href;
                    }
                });
                action.catch(() => {

                });
            });


            // copy to clipboard code
            $(document).on('click', '.wfacp_copy_text', function () {
                var sibling = $(this).siblings('.wfacp_description');
                if (sibling.length > 0) {
                    sibling.find('input').select();
                    document.execCommand("copy");
                    wfacp.show_data_save_model(wfacp_localization.global.shortcode_copy_message);
                }
            });

        }

        model() {
            wfacp.show_saved_model = $("#modal-saved-data-success");
            if (wfacp.show_saved_model.length === 0) {
                return;
            }
            wfacp.show_saved_model.iziModal({
                    title: wfacp_localization.global.data_saving,
                    icon: 'icon-check',
                    headerColor: '#6dbe45',
                    background: '#6dbe45',
                    borderBottom: false,
                    width: 600,
                    timeout: 4000,
                    timeoutProgressbar: true,
                    transitionIn: 'fadeInUp',
                    transitionOut: 'fadeOutDown',
                    bottom: 0,
                    loop: true,
                    pauseOnHover: true,
                    overlay: false,
                    onOpening: (modal) => {

                        let spinner = $('.wfacp_spinner.spinner');
                        if (spinner.length > 0) {
                            spinner.css("visibility", "hidden");
                        }
                    },
                    onClosed: () => {
                        wfacp.show_saved_model.iziModal('setTitle', wfacp_localization.global.data_saving);
                        let spinner_el = $(".wfacp_spinner.spinner");
                        if (spinner_el.is(":visible")) {
                            spinner_el.css("visibility", "hidden");
                        }

                    }
                }
            );

        }
    }

    $(window).on('load', function () {
        Vue.component('multiselect', window.VueMultiselect.default);
        window.builder = new wfacp_builder(wfacp_data);
        setTimeout(function () {
            $('.wfacp_loader').hide();
        }, 600);

        /** Metabox panel close */
        $(".wfacp_allow_panel_close .hndle").on("click", function () {
            var parentPanel = $(this).parents(".wfacp_allow_panel_close");
            parentPanel.toggleClass("closed");
        });
        let wpml_href = $('a[data-original-link]');
        if (wpml_href.length > 0) {
            wpml_href.each(function () {
                let o_link = $(this).data('original-link');
                $(this).attr('href', o_link);
            });
        }

    });
    var WoofunnelModalMethods = {
        init: function (options) {
            let el = $(this);
            setTimeout(el => {
                el.find('.iziModal-button-close').on('click', function () {
                    $(this).trigger('onclosed');
                    $('html').removeClass('iziModal-isAttached');
                    $('.wfacp_overlay').removeClass('wfacp_overlay_active');
                    el.fadeOut();
                });
                $(this).trigger('oninit');
            }, 300, el);
        },
        open: function () {
            let el = $(this);
            el.css('top', '50px');
            el.css('bottom', 'auto');

            setTimeout(() => {
                el.fadeIn();
                el.addClass('hasScroll');

                let wH = $(window).height();
                let mH = parseInt(wH * 0.9);
                let iziHeadH = parseInt($('.iziModal-header').outerHeight());
                let iciContH = parseInt($(this).find('.iziModal-content').height());
                let eTop = parseInt((wH - (iziHeadH + iciContH)) / 2) + 'px';

                if ((iziHeadH + iciContH) < mH) {
                    el.height((iziHeadH + iciContH));
                    el.find('.iziModal-wrap').height(iciContH);

                    el.css('top', eTop);
                } else {
                    el.height(mH);
                    el.find('.iziModal-wrap').height((mH - iziHeadH));
                }

                $('html').addClass('iziModal-isAttached');
                $('.wfacp_overlay').addClass('wfacp_overlay_active');
            }, 150);

            $(this).trigger('onopend');
        },// IS
        close: function () {
            $(this).trigger('onclosed');

            $('html').removeClass('iziModal-isAttached');
            $('.wfacp_overlay').removeClass('wfacp_overlay_active');
            $(this).fadeOut();

        },// GOOD
        setSubtitle: function (options) {
        }
    };

    $.fn.WoofunnelModal = function (methodOrOptions) {
        if (WoofunnelModalMethods[methodOrOptions]) {
            return WoofunnelModalMethods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return WoofunnelModalMethods.init.apply(this, arguments);
        } else {
            $.error('Method ' + methodOrOptions + ' does not exist on jQuery.tooltip');
        }
    };


})(jQuery);