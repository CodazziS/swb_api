/* TODO : Reparse file */
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) === 0) return c.substring(name.length,c.length);
    }
    return "";
}

function timeToDate(unix_timestamp) {
	var d = new Date(unix_timestamp * 1);
	var r = d.getDate() + '/' + d.getMonth() + '/' + d.getFullYear() + ' ';
	r += d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes() + ':' + (d.getSeconds() < 10 ? '0' : '') + d.getSeconds();
	return r;
}

function loadPage () {
	if (document.getElementById('messages_contacts') !== null) {
		Messages.getActiveContacts();
	}
	
}

DEFAULT_ENCODE=function(a){return a; };
DEFAULT_DECODE=function(a){return a; };

var MessagesClass = function () {
    return;
};

MessagesClass.prototype = {
	
	getActiveContacts: function() {
		var opt;
		
		/* TODO: set correct URL */
		opt = {
			'url': 'https://devapi.swb.ovh/Api/Contacts/GetActive',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key')
			},
			'callback': Messages.getActiveContactsRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	getActiveContactsRes: function(data) {
		var html = '',
			json_data,
			addr,
			i;
		
		json_data = JSON.parse(data);
		console.log(json_data);
		for (i in json_data.address) {
			addr = json_data.address[i];
			console.log(addr);
			html += '<div class="message_contact_item" onclick="Messages.getMessages(\'' + addr.address + '\', \'' + addr.android_id + '\')">';
			html += '	<div class="message_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			html += '	<div class="message_contact_item_infos">';
			html += '		<div class="message_contact_item_name">' + addr.name + '</div>';
			html += '		<div class="message_contact_item_subname">' + timeToDate(addr.time) + '</div>';
			html += '	</div>';
			html += '</div>';
		}	
		document.getElementById('messages_contacts').innerHTML = html;
	},
	getMessages: function(address, android_id) {
		var opt;
		
		/* TODO: set correct URL */
		opt = {
			'url': 'https://devapi.swb.ovh/Api/Messages/GetMessages',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'address': address,
				'android_id': android_id
			},
			'callback': Messages.getMessagesRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	getMessagesRes: function(data) {
		var html = '',
			json_data,
			addr,
			i;
		
		json_data = JSON.parse(data);
		console.log(json_data);
		/*
		for (i in json_data.address) {
			addr = json_data.address[i];
			console.log(addr);
			html += '<div class="message_contact_item">';
			html += '	<div class="message_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			html += '	<div class="message_contact_item_infos">';
			html += '		<div class="message_contact_item_name">' + addr.name + '</div>';
			html += '		<div class="message_contact_item_subname">' + timeToDate(addr.time) + '</div>';
			html += '	</div>';
			html += '</div>';
		}	
		document.getElementById('messages_contacts').innerHTML = html;
		*/
	}
};


var Messages = new MessagesClass();

