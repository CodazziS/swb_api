function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
    }
    return undefined;
}

function acceptCookies() {
	opt = {
		'url': '/En/Index/allowcookies',
		'data': {
		},
		'callback': function() {
			document.getElementById('cookiebar').style.display = 'none';
		},
		'checkErrors': false,
		'decode': false
	};
	Ajax.get(opt);
}

function timeToDate(unix_timestamp) {
	var d = new Date(unix_timestamp * 1);
	var m = d.getMonth() + 1;
	var r = (d.getDate() < 10 ? '0' : '') + d.getDate() + '/' + (m < 10 ? '0' : '') + m + '/' + d.getFullYear() + ' ';
	r += (d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes() + ':' + (d.getSeconds() < 10 ? '0' : '') + d.getSeconds();
	return r;
}

function notifyClient (text) {
    var opt = {
        'icon': '/res/img/logo_transp.png'  
    };
    console.log("Notif called at " + Date().toString());
    if (!("Notification" in window)) {
        return;
    } else if (Notification.permission === "granted") {
        
        var notification = new Notification(text, opt);
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission(function (permission) {
            if(!('permission' in Notification)) {
                Notification.permission = permission;
            }
            if (permission === "granted") {
                var notification = new Notification(text, opt);
            }
        });
    }
}

function compareByName(a,b) {
    if (a.name < b.name)
        return -1;
    if (a.name > b.name)
        return 1;
    return 0;
}

window.nb_script_to_load--;