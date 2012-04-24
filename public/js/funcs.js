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

	initCharLookup: function() {
		$("#regionlist").focus(function() {
			$(this).removeClass("prefill");
		});

		$("#regionlist").change(function() {
			if ($(this)[0].selectedIndex == 0) {
				$(this).addClass("prefill");
			}
		});

		$("#regionlist").blur(function() {
			if ($(this)[0].selectedIndex == 0) {
				$(this).addClass("prefill");
			}
		});

		$("#realm_name").focus(function() {
			if ($(this).val() == "realm") {
				$(this).val("");
			}
		});

		$("#realm_name").blur(function() {
			if ($(this).val() == "") {
				$(this).val("realm");
				$(this).addClass("prefill");
			}
		});

		$("#realm_name").keyup(function() {
			if ($(this).val().length > 0) {
				$(this).removeClass("prefill");
			}

			if ($("#regionlist").val() == "") {
				$("#realm_name_status").addClass("fail");
				Funcs.lookupError("Choose a region first");

				$("#regionlist").change(function() {
					if ($("#regionlist").val() != "") {
						Funcs.hideError();
						$("#realm_name_status").removeClass("fail");
						$("#regionlist").unbind("change", arguments.callee);
					}
				});
				return;
			}

			if ($(this).val().length >= 3 && $(this).val() != Funcs.realm_lookup.prefix_last) {

				var prefix = $(this).val().toLowerCase();
				var ok = true;

				Funcs.realm_lookup.prefix_last = prefix;

				for (var i = 0; i < Funcs.realm_lookup.prefix_ignore.length; i++) {
					if (prefix.indexOf(Funcs.realm_lookup.prefix_ignore[i]) != -1) {
						ok = false;
						break;
					}
				}

				if (ok) {
					Funcs.lookupRealm($(this).val());
					Funcs.realm_lookup.prefix_last = $(this).val();
				}
				else {
					$("#realm_name_status").addClass("fail");
				}
			}
			else {
				$("#realm_suggest").hide().empty();
				$("#realm_name_status").removeClass("loading fail");
			}
		});

		$("#char_name").focus(function() {
			if ($(this).val() == "character") {
				$(this).val("");
			}
		});

		$("#char_name").blur(function() {
			if ($(this).val() == "") {
				$(this).val("character");
				$(this).addClass("prefill");
			}
		});

		$("#char_name").keyup(function() {
			if ($(this).val().length > 0) {
				$(this).removeClass("prefill");
			}
		});
	},

	lookupRealm: function(prefix) {
		var cache_index = 'r_lookup_' + $("#regionlist").val()+ "_" + prefix;

		$("#realm_name_status").addClass("loading");

		var cache_result = this.getCache(cache_index);

		if (cache_result == null) {
			$.ajax({
				url: "/ajax/load-realms",
				data: {
					region: $("#regionlist").val(),
					prefix: prefix
				},
				success: function(data) {
					$("#realm_name_status").removeClass("loading");

					if (data == null || data == "") {
						Funcs.realm_lookup.prefix_ignore.push(prefix);
						$("#realm_name_status").addClass("fail");
						$("#realm_suggest").hide().empty();
						return;
					}
					else {
						Funcs.setCache(cache_index, data);
						Funcs.updateRealmList(data);
					}
				},
				error: function() {
					$("#realm_name_status").removeClass("loading").addClass("fail");
					$("#realm_suggest").hide().empty();
				}
			});
		}
		else {
			$("#realm_name_status").removeClass("loading");
			this.updateRealmList(cache_result);
		}
	},

	updateRealmList: function(rlist) {
		rlist = rlist.split(",");
		$("#realm_suggest").show().empty();

		for(var i = 0; i < rlist.length; i++) {
			$("#realm_suggest").append('<li onclick="Funcs.pickRealm(\'' + rlist[i].replace("'", "\\'") + '\')">' + rlist[i] + '</li>');

		}
	},

	pickRealm: function(name) {
		$("#realm_name").val(name);
		$("#realm_suggest").hide().attr("class", "");
		this.realm_lookup.prefix_last = name;
	},

	lookupError: function(msg, hideCallback) {
		$("#lookup_error").text(msg).addClass("visible");
		//animate({"top": 0}, 200);
	},

	hideError: function() {
		$("#lookup_error").text("").removeClass("visible");
		//.animate({"top": -25}, 200);
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