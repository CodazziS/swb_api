var MessagesClass = function () {
    return;
};
MessagesClass.prototype = {
	last_name: '',
	last_format_address: '',
	last_address: '',
	last_android_id: '',
	last_sync: 0,
	last_sync_mess: 0,
	refresh_started: false,
	last_contact_page: '',
	
	getLastSync: function() {
		var opt;
		
		/* TODO: set correct URL */
		opt = {
			'url': '/api/messages/getlastsync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key')
			},
			'callback': Messages.getLastSyncRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	getLastSyncRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		if (json_data.last_message > Messages.last_sync) {
			Messages.last_sync = json_data.last_message;
			Messages.getActiveContacts();
		}
		Utils.setTimeout(function(){ Messages.getLastSync(); }, 10000);
	},
	
	getActiveContacts: function() {
		var opt;
		
		/* TODO: set correct URL */
		opt = {
			'url': '/Api/Contacts/GetActive',
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
		for (i in json_data.address) {
			addr = json_data.address[i];
			html += '<div class="messages_contact_item" onclick="Messages.launchContactRefresh(\'' + addr.address + '\', \'' + addr.format_address + '\', \'' + addr.android_id + '\', \'' + addr.name + '\')">';
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
	
	launchContactRefresh: function(address, format_address, android_id, name) {
		
		
		document.getElementById('messages_list').innerHTML = '<div class="mdl-progress mdl-js-progress mdl-progress__indeterminate full"></div>';
		
		Messages.last_format_address = format_address;
		Messages.last_address = address;
		Messages.last_android_id = android_id;
		Messages.last_name = name;
		Messages.last_sync_mess = 0;
		Messages.last_contact_page = '';
		
		componentHandler.upgradeDom();
		
		if (!Messages.refresh_started) {
			Messages.getLastSyncMess();
			Messages.refresh_started = true;
		}
	},
	getLastSyncMess: function() {
		var opt;
		
		/* TODO: set correct URL */
		opt = {
			'url': '/api/messages/getLastSyncMessage',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'device': Messages.last_android_id,
				'format_address': Messages.last_format_address
			},
			'callback': Messages.getLastSyncMessRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	
	getLastSyncMessRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		if (json_data.last_message > Messages.last_sync_mess) {
			Messages.last_sync_mess = json_data.last_message;
			Messages.getMessages();
		}
		Utils.setTimeout(function(){ Messages.getLastSyncMess(); }, 2000);
	},
	
	getMessages: function() {
		var opt;
		
		opt = {
			'url': '/Api/Messages/GetMessages',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'format_address': Messages.last_format_address,
				'android_id': Messages.last_android_id
			},
			'callback': Messages.getMessagesRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	
	getMessagesRes: function(data) {
		var json_data = JSON.parse(data);

		if (Messages.last_contact_page !== (Messages.last_address + Messages.last_android_id)) {
			document.getElementById('messages_list').innerHTML = Messages.formatMessagePage();
		}
		
		Messages.last_contact_page = (Messages.last_address + Messages.last_android_id);
		document.getElementById('messages_list_items').innerHTML =  Messages.formatMessageList(json_data);
		document.getElementById("messages_list_items").scrollTop = document.getElementById("messages_list_items").scrollHeight;
		componentHandler.upgradeDom();
	},
	
	formatMessagePage: function() {
		var html = '';
		
		html += '<div class="message_list_title">' + Messages.last_name + '</div>';
		html += '<div id="messages_list_items"></div>';
		html += '<div id="messages_inputs">';
		html += '	<form action="#" onsubmit="Messages.sendMessage(\'' + Messages.last_address + '\', \'' + Messages.last_android_id + '\'); return false;">';
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
		return html;
	},
	
	formatMessageList: function(json_data) {
		var html = '',
			mess,
			i;
			
		for (i in json_data.messages) {
			mess = json_data.messages[i];
			html += '<div class="messages_list_item type_' + mess.type + '">';
			html += '	<div class="messages_list_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			html += '	<div class="messages_list_item_infos">';
			html += '		<div class="messages_list_item_body">' + mess.body + '</div>';
			html += '		<div class="messages_list_item_date">';
			html +=         	timeToDate(mess.time);
			if (mess.type == '-1') {
				html += 		' - ' + window.lang.messages_sent;	
			} else if (mess.type == '-2') {
				html += 		' - ' + window.lang.messages_todo_send;	
			}
			html += '		</div>';
			html += '	</div>';
			html += '</div>';
		}
		return html;
	},
	
	sendMessage: function(address, android_id) {
		var opt,
			message,
			input;
		
		input = document.getElementById('messages_input');	
		message = {
			'id': (new Date()).getTime(),
			'date': (new Date()).getTime(),
			'read': 1,
			'body': input.value,
			'address': address,
			'type': -2,
			'date_sent': (new Date()).getTime()
		};
		opt = {
			'url': '/Api/Messages/Sync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'address': address,
				'android_id': android_id,
				'messages': JSON.stringify([message])
			},
			'callback': Messages.sendMessageRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.post(opt);
		input.value = '';
	},
	
	sendMessageRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		var snackbarContainer = document.querySelector('#messages_confirm_send');
		if (json_data.error === 0) {
			snackbarContainer.MaterialSnackbar.showSnackbar({message: 'Message send'});
			/*
			Search Contact in menu by ID (format_address_android_id) and simulate click
				Or view to new method for refresh the sms list
			*/
		} else {
			snackbarContainer.MaterialSnackbar.showSnackbar({message: 'Error'});
		}
	}
};
var Messages = new MessagesClass();

window.nb_script_to_load--;