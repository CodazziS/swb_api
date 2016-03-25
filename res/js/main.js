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

var easter_egg = new Konami(function() { 
    konamize();
});
function konamize() {
	var color = "#" + Math.round(Math.random() * 1000000);
	document.getElementById("header").style.backgroundColor = color;
	document.getElementById("footer").style.backgroundColor = color;

	setTimeout(function(){ konamize(); }, 50);
}

function timeToDate(unix_timestamp) {
	var d = new Date(unix_timestamp * 1);
	var m = d.getMonth() + 1;
	var r = (d.getDate() < 10 ? '0' : '') + d.getDate() + '/' + (m < 10 ? '0' : '') + m + '/' + d.getFullYear() + ' ';
	r += (d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes() + ':' + (d.getSeconds() < 10 ? '0' : '') + d.getSeconds();
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
	last_name: '',
	
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
			html += '<div class="messages_contact_item" onclick="Messages.getMessages(\'' + addr.address + '\', \'' + addr.android_id + '\', \'' + addr.name + '\')">';
			html += '	<div class="messages_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			html += '	<div class="messages_contact_item_infos">';
			html += '		<div class="messages_contact_item_name">' + addr.name + '</div>';
			if (addr.unread > 0) {
				html += '		<div class="messages_contact_item_subname mdl-badge" data-badge="'+addr.unread+'">' + addr.model + ' - ' + timeToDate(addr.time) + '</div>';
			} else {
				html += '		<div class="messages_contact_item_subname">' + addr.model + ' - ' + timeToDate(addr.time) + '</div>';
			}
			html += '	</div>';
			html += '</div>';
		}	
		document.getElementById('messages_contacts').innerHTML = html;
	},
	getMessages: function(address, android_id, name) {
		var opt;
		
		Messages.last_name = name;
		
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
			mess,
			i;
		
		json_data = JSON.parse(data);
		console.log(json_data);
		
		html += '<div class="message_list_title">'+Messages.last_name+'</div>';
		html += '<div id="messages_list_items">';
		for (i in json_data.messages) {
			mess = json_data.messages[i];
			console.log(mess);
			html += '<div class="messages_list_item type_' + mess.type + '">';
			html += '	<div class="messages_list_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			html += '	<div class="messages_list_item_infos">';
			html += '		<div class="messages_list_item_body">' + mess.body + '</div>';
			html += '		<div class="messages_list_item_date">' + timeToDate(mess.time) + '</div>';
			html += '	</div>';
			html += '</div>';
		}	
		html += '</div>';
		html += '<div id="messages_inputs">';
		html += '	<form action="#">';
		html += '		<div id="messages_input_block" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">';
		html += '			<input class="mdl-textfield__input" type="text" id="messages_input">';
		html += '			<label class="mdl-textfield__label" for="messages_input">Envoyer un message</label>';
		html += '		</div>';
		html += '		<button id="messages_emoji_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab">';
		html += '			<i class="material-icons">mood</i>';
		html += '		</button>';
		html += '		<button id="messages_send_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored">';
		html += '			<i class="material-icons">send</i>';
		html += '		</button>';
		html += '	</form>';
		html += '</div>';
		document.getElementById('messages_list').innerHTML = html;
		document.getElementById("messages_list_items").scrollTop = document.getElementById("messages_list_items").scrollHeight;
	}
};


var Messages = new MessagesClass();

