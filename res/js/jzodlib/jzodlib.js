/*global PRELOAD_CLASS, PATH_CLASS, Paginator */
(function () {
    "use strict";

    var pages_load = [], // Pages already passed in include
        links_load = []; // Pages already passed in include_link

    /*
        Function include is for include scripts, in Ajax
     */
    window.include = function (src, callback) {
        if (pages_load[src] === undefined) {
            pages_load[src] = true;
            var head = document.getElementsByTagName('head').item(0),
                script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', src);

            if (callback !== undefined) {
                script.onload = callback;
            }
            head.appendChild(script);
        } else {
            if (callback !== undefined && callback !== null) {
                callback();
            }
        }
    };

    /*
        Function include is for include pages (html), in Ajax used for polymer for example
     */
    window.include_link = function (src) {
        if (links_load[src] === undefined) {
            links_load[src] = true;
            var head = document.getElementsByTagName('head').item(0),
                script = document.createElement('link');
            script.setAttribute('rel', 'import');
            script.setAttribute('href', src);

            head.appendChild(script);
        }
    };
	
	if (window.minify_version === undefined || window.minify_version === false) {
	    /* Include all framzod libraries */
	    window.include("framzod/modules/Ajax.js");
	    window.include("framzod/modules/Storage.js");
	    window.include("framzod/modules/Utils.js");
	}
}(1));
