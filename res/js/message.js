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
	last_sync_mess_unread: 0,
	refresh_started: false,
	last_contact_page: '',
	images: null,
	
	init: function() {
		Messages.getLastSync();
		window.addEventListener("resize", Messages.resizeSoResetSwitch);
		
	},
	getLastSync: function() {
		var opt;
		
		opt = {
			'url': '/api/messages/getLastSync',
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
		
		if (json_data.last_message_unread > 0 && json_data.last_message_unread > Messages.last_sync_mess_unread) {
	        if (Messages.last_sync_mess_unread > 0 ) {
	            notifyClient(window.lang.message_notification);
	        }
	        Messages.last_sync_mess_unread = json_data.last_message_unread;
	    }
		    
		if (json_data.last_message > Messages.last_sync) {

			Messages.last_sync = json_data.last_message;
			Messages.getActiveContacts();
		}
		Utils.setTimeout(function(){ Messages.getLastSync(); }, 10000);
	},
	
	getActiveContacts: function() {
		var opt;
		
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
		
		Messages.images = {};
		json_data = JSON.parse(data);
		for (i in json_data.address) {
			addr = json_data.address[i];
			html += '<div class="messages_contact_item" onclick="Messages.switchView(true); Messages.launchContactRefresh(\'' + addr.address + '\', \'' + addr.format_address + '\', \'' + addr.android_id + '\', \'' + addr.name + '\')">';
			if (addr.image === undefined || addr.image === null || addr.image === '') {
				html += '	<div class="messages_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			} else {
				Messages.images[addr.android_id + '_' + addr.format_address] = addr.image;
				html += '	<div class="messages_contact_item_img"><img src="data:image/png;base64,'+addr.image+'" /></div>';
			}
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
		
		document.getElementById('messages_list').innerHTML = '<div class="mdl-progress mdl-js-progress mdl-progress__indeterminate center100"></div>';
		
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
		
		opt = {
			'url': '/api/messages/getLastSyncMessage',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'device': Messages.last_android_id,
				'format_address': encodeURI(Messages.last_format_address)
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
		if (json_data.last_message_unread > 0 && json_data.last_message_unread > Messages.last_sync_mess_unread) {
	        if (Messages.last_sync_mess_unread > 0 ) {
	            notifyClient(window.lang.message_notification);
	        }
	        Messages.last_sync_mess_unread = json_data.last_message_unread;
	    }
		    
		if (json_data.last_message > Messages.last_sync_mess) {
			Messages.last_sync_mess = json_data.last_message;
			Messages.getMessages();
			
		}
		Utils.setTimeout(function(){ Messages.getLastSyncMess(); }, 3000);
	},
	
	getMessages: function() {
		var opt;
		
		opt = {
			'url': '/Api/Messages/GetMessages',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'format_address': encodeURI(Messages.last_format_address),
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
		html += '			<input class="mdl-textfield__input" autocomplete="off" type="text" id="messages_input">';
		html += '			<label class="mdl-textfield__label" for="messages_input">' + window.lang.messages_new_message_input + '</label>';
		html += '		</div>';
		
		html += '		<button id="messages_send_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored">';
		html += '			<i class="material-icons">send</i>';
		html += '		</button>';
		
		html += '		<button onclick="Messages.showEmojis(); return false;" id="messages_emoji_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab">';
		html += '			<i class="material-icons">mood</i>';
		html += '		</button>';
		
		html += '	</form>';
		html += '</div>';
		return html;
	},
	
	formatMessageList: function(json_data) {
		var html = '',
			mess,
			img = null,
			i;
			
		if (Messages.images[Messages.last_android_id + '_' + Messages.last_format_address] !== undefined) {
			img = Messages.images[Messages.last_android_id + '_' + Messages.last_format_address]
		}	
		for (i in json_data.messages) {
			mess = json_data.messages[i];
			html += '<div class="messages_list_item emoji_like type_' + mess.type + '">';
			// Type 1 : received
			if (img === null || mess.type != '1') {
				html += '	<div class="messages_list_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			} else {
				html += '	<div class="messages_contact_item_img"><img src="data:image/png;base64,' + img + '" /></div>';
			}
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
	
	sendNewMessage: function() {
		var opt,
			message,
			input_mess,
			input_dev,
			input_pho;
		
		input_mess = document.getElementById('messages_new_input_message');
		input_dev = document.getElementById('messages_new_input_device');
		input_pho = document.getElementById('messages_new_input_address');
		if (input_mess.value === '' || input_mess.value === null ||
			input_dev.value === '' || input_dev.value === null ||
			input_pho.value === '' || input_pho.value === null) {
			return;
		}
		message = {
			'id': (new Date()).getTime(),
			'date': (new Date()).getTime(),
			'read': 1,
			'body': encodeURI(input_mess.value),
			'address': encodeURI(input_pho.value),
			'type': -2,
			'date_sent': (new Date()).getTime()
		};
		opt = {
			'url': '/Api/Messages/Sync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'address': input_dev.value,
				'android_id': input_dev.value,
				'messages': JSON.stringify([message])
			},
			'callback': Messages.sendMessageRes,
			'checkErrors': false,
			'decode': false
		};
		Ajax.post(opt);
		input_mess.value = '';
		input_dev.value = '';
		input_pho.value = '';
		document.getElementById('messages_new_box').style.display = 'none';
	},
	
	sendMessage: function(address, android_id) {
		var opt,
			message,
			input;
		
		input = document.getElementById('messages_input');
		if (input.value === '' || input.value === null) {
			return;
		}
		message = {
			'id': (new Date()).getTime(),
			'date': (new Date()).getTime(),
			'read': 1,
			'body': encodeURI(input.value),
			'address': encodeURI(address),
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
			snackbarContainer.MaterialSnackbar.showSnackbar({message: window.lang.messages_send});
		} else {
			snackbarContainer.MaterialSnackbar.showSnackbar({message: window.lang.messages_send_error});
		}
	},
	
	showEmojis: function() {
		var dialog = document.getElementById('emoji_list');
		dialog.style.display = 'block';
	    dialog.querySelector('.close').addEventListener('click', function() {
	    	dialog.style.display = 'none';
	    });
	},
	
	showNewMessages: function() {
		var dialog = document.getElementById('messages_new_box');
		dialog.style.display = 'block';
	    dialog.querySelector('.close').addEventListener('click', function() {
	    	dialog.style.display = 'none';
	    });
	},
	
	addEmoji:function(span) {
		document.getElementById('messages_input').value += span.innerHTML;
		componentHandler.upgradeDom();
	},
	
	/* Switch view, for small screen */
	switchView: function(toMessage) {
		var contacts = document.getElementById('messages_contacts_bloc'),
			messages = document.getElementById('messages_messages_bloc'),
			width = window.innerWidth
					|| document.documentElement.clientWidth
					|| document.body.clientWidth;
		if (width >= 960) {
			return;
		}
		if (toMessage) {
			contacts.style.display = 'none';
			messages.style.display = 'flex';
			if (document.getElementById("messages_list_items") != null) {
				document.getElementById("messages_list_items").scrollTop = document.getElementById("messages_list_items").scrollHeight;
			}
		} else {
			contacts.style.display = 'flex';
			messages.style.display = 'none';
		}
	},
	
	resizeSoResetSwitch: function() {
		var contacts = document.getElementById('messages_contacts_bloc'),
			messages = document.getElementById('messages_messages_bloc'),
			width = window.innerWidth
					|| document.documentElement.clientWidth
					|| document.body.clientWidth;

		if (width >= 960) {
			contacts.style.display = 'flex';
			messages.style.display = 'flex';
		} else {
			contacts.style.display = 'flex';
			messages.style.display = 'none';
		}
	}
	
};
var Messages = new MessagesClass();

window.nb_script_to_load--;