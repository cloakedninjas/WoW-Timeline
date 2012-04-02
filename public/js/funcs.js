var Funcs = {
	cache: {
		method: 'js'
	},
	realm_lookup: {
		working: false,
		prefix_ignore: [],
		prefix_last: ''
	},

	init: function() {
		if (typeof(localStorage) != 'undefined') {
			this.cache.method = 'local';
		}
		else {
			this.cache.index = [];
			this.cache.data = [];
		}
	},

	setCookie: function (c_name,value,exdays) {
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	},

	getCookie: function (c_name) {
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++) {
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name) {
				return unescape(y);
			}
		}
	},

	initRealmLookup: function() {
		$("#realm_name").keyup(function() {
			if ($("#realm_name").val().length >= 3 && $("#realm_name").val() != Funcs.realm_lookup.prefix_last) {
				Funcs.lookupRealm($("#realm_name").val());
				Funcs.realm_lookup.prefix_last = $("#realm_name").val();
			}
			else {
				$("#realm_suggest").hide().empty();
				$("#realm_name_loading").hide();
			}
		});
		
		$("#char_name").keyup(function() {
			if ($(this).val().length > 0) {
				var name = $(this).val()[0].toUpperCase() + $(this).val().substr(1);
				$(this).val(name);
			}
		});
	},

	lookupRealm: function(prefix) {
		var cache_index = 'realm_lookup_' + prefix;
		
		for (var i = 0; i < this.realm_lookup.prefix_ignore.length; i++) {
			if (prefix.indexOf(this.realm_lookup.prefix_ignore[i]) != -1) {
				return;
			}
		}
		
		$("#realm_name_loading").show();
		this.realm_lookup.prefix_last = prefix;
		
		var cache_result = this.getCache(cache_index);
		
		if (cache_result == null) {
			$.ajax({
				url: "/ajax/load-realms",
				data: {
					region: $("#regionlist").val(),
					prefix: prefix
				},
				success: function(data) {
					if (data == null) {
						Funcs.realm_lookup.prefix_ignore.push(prefix);
						$("#realm_name_loading").hide();
						$("#realm_suggest").hide().empty();
						return;
					}
					Funcs.setCache(cache_index, data);
					Funcs.updateRealmList(data);
				}
			});
		}
		else {
			this.updateRealmList(cache_result);
		}
	},

	updateRealmList: function(rlist) {
		rlist = rlist.split(",");
		$("#realm_suggest").show().empty();

		for(var i = 0; i < rlist.length; i++) {
			$("#realm_suggest").append('<li onclick="Funcs.pickRealm(\'' + rlist[i].replace("'", "\\'") + '\')">' + rlist[i] + '</li>');
			
		}
		$("#realm_name_loading").hide();
	},
	
	pickRealm: function(name) {
		$("#realm_name").val(name);
		$("#realm_suggest").hide();
		this.realm_lookup.prefix_last = name;
		
	},

	getCache: function(name) {
		if (this.cache.method == 'local') {
			return localStorage.getItem(name);
		}
		else {
			var i = this.cache.index.indexOf(name);

			if (i != -1) {
				return this.cache.data[i];
			}
		}
		return null;
	},

	setCache: function(name, data, method) {
		console.log("setting cache: " + name + " to " + data);
		if (typeof method == 'undefined') {
			method = this.cache.method;
		}

		if (method == 'local') {
			try {
				localStorage.setItem(name, data);
			}
			catch (e) {
				console.log(e);
			}
		}
		else {
			this.cache.index.push(name);
			this.cache.index.data(data);
		}
	}

};