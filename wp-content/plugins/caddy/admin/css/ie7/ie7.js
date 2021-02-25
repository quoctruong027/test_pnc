/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referencing this file must be placed before the ending body tag. */

/* Use conditional comments in order to target IE 7 and older:
	<!--[if lt IE 8]><!-->
	<script src="ie7/ie7.js"></script>
	<!--<![endif]-->
*/

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'Caddy-Admin-Icons\'">' + entity + '</span>' + html;
	}
	var icons = {
		'cc-admin-icon-pencil': '&#xe905;',
		'cc-admin-icon-droplet': '&#xe90b;',
		'cc-admin-icon-book': '&#xe91f;',
		'cc-admin-icon-ticket': '&#xe939;',
		'cc-admin-icon-lifebuoy': '&#xe941;',
		'cc-admin-icon-equalizer': '&#xe992;',
		'cc-admin-icon-stats-bars': '&#xe99c;',
		'cc-admin-icon-truck': '&#xe9b0;',
		'cc-admin-icon-power-cord': '&#xe9b7;',
		'cc-admin-icon-star-full': '&#xe9d9;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		c = el.className;
		c = c.match(/cc-admin-icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
