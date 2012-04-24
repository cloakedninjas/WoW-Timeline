var Timeline = {
	char_id: 0,
	data: [],
	left_height: 5,
	right_height: 20,
	max_height: 20,
	shift_pad: 5,
	scroll_percent: 0,
	scroll_trigger: 75,
	has_more_achvs: true,
	load_count: 0,
	load_in_progress: false,
	expansions: {
		last_rendered: null,
		px: 0
	},
	time_indexes: {
		prev_mon: null,
		prev_year: null,
		prev_px: 0
	},

	plot: function() {
		for (var day_idx = this.load_count; day_idx < this.data.length; day_idx++) {
			
			var h = ((this.left_height < this.right_height) ? this.left_height : this.right_height) + 5;
			
			if (h - this.time_indexes.prev_px <= 10) {
				console.log(12333);
				
			}
			
			this.time_indexes.prev_px = h;
			
			// time indexes
			if (this.time_indexes.prev_year != this.data[day_idx].y) {
				$("#time_index").append("<p style=\"top: " + h + "px;\">" + this.data[day_idx].y + "</p>");
			}
			else if (this.time_indexes.prev_mon != this.data[day_idx].mm) {
				$("#time_index").append("<p style=\"top: " + h + "px;\">" + this.data[day_idx].m + "</p>");
			}

			this.time_indexes.prev_mon = this.data[day_idx].mm;
			this.time_indexes.prev_year = this.data[day_idx].y;

			// exp gradients
			if (typeof this.data[day_idx].exp != 'undefined') {
				if (this.expansions.last_rendered != this.data[day_idx].exp) {
					if (this.expansions.last_rendered == null) {

					}
					else {
						this.expansions.px = this.max_height;
					}
					$("#exp_" + this.expansions.last_rendered).css("height", this.expansions.px);
				}

				$("#exp_" + this.data[day_idx].exp).css("top", this.expansions.px);
				this.expansions.last_rendered = this.data[day_idx].exp;
			}

			// entries
			var entry = '';

			for (var ach_idx = 0; ach_idx < this.data[day_idx].a.length; ach_idx++) {

				if (this.data[day_idx].a[ach_idx].no) {
					// notable achievements done elsewhere
					this.plotNotable(day_idx, ach_idx);
					continue;
				}
				else {
					entry += "<p title=\"" + this.data[day_idx].a[ach_idx].d + "\"";
					entry += ">" + this.data[day_idx].a[ach_idx].n + "</p>";
				}
			}

			if (entry != '') {
				var html = $("#entry_template").clone();

				html.removeAttr("id");

				html.find(".date").text(this.data[day_idx].da + " " + this.data[day_idx].m);
				html.attr("data-day", day_idx);
				html.append(entry);

				var side = (this.left_height > this.right_height) ? 'right' : 'left';
				html.addClass(side);

				this.addToTimeline(html);
			}
		}
		this.load_count = this.data.length;
	},

	plotNotable: function(day_idx, ach_idx) {
		var html = $("#notable_template").clone();
		html.removeAttr("id");
		html.append("<h4>" + this.data[day_idx].a[ach_idx].n + "<span class=\"date\">" + this.data[day_idx].da + " " + this.data[day_idx].m + "</span></h4><p>" + this.data[day_idx].a[ach_idx].d + "</p>");

		this.addToTimeline(html);
	},

	addToTimeline: function(html) {

		if (html.hasClass("notable")) {
			html.css("top", this.max_height);
		}
		else if (html.hasClass("left")) {
			html.css("top", this.left_height);
		}
		else {
			html.css("top", this.right_height);
		}

		$("#timeline .entries").append(html);

		var new_height = html.outerHeight(true);

		if (html.hasClass("notable")) {
			this.right_height =  this.left_height = this.max_height + new_height;
			this.right_height += this.shift_pad;
		}
		else if (html.hasClass("left")) {
			this.left_height += new_height;
			this.right_height += this.shift_pad;
		}
		else {
			this.right_height += new_height;
			this.left_height += this.shift_pad;
		}

		this.max_height = Math.max(this.left_height, this.right_height);

		$("#timeline").css("height", this.max_height);
		$("#time_index").css("height", this.max_height);
	},

	expandDay: function(day) {
		$("#timeline .entry[data-day='" + day + "']").addClass("hover");
		$("#timeline .entry[data-day='" + day + "'] .hidden").show(200);
	},

	collapseDay: function(day) {
		$("#timeline .entry[data-day='" + day + "']").removeClass("hover");
		$("#timeline .entry[data-day='" + day + "'] .hidden").hide(200);
	},

	scrollHandler: function(e) {
		if (this.scroll_trigger == false) {
			$(window).unbind('scroll', this.scrollHandler);
			return;
		}

        this.scroll_percent = ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100;

        if (this.scroll_percent >= this.scroll_trigger) {
        	if (this.has_more_achvs) {
        		this.loadEntries();
        	}
        	else {
        		this.scroll_trigger = false;
        	}
        }
	},

	loadEntries: function() {
		if (this.load_in_progress) {
			return;
		}

		this.load_in_progress = true;
		$("#timeline .loading").animate({bottom: 0}, 100);

		$.ajax({
			url: "/ajax/load-entries",
			data: {
				char_id: this.char_id,
				start: this.load_count
			},
			dataType: "json",
			success: function(data) {
				Timeline.load_in_progress = false;
				$("#timeline .loading").animate({bottom: "-51px"}, 100);
				Timeline.data = Timeline.data.concat(data);
				Timeline.plot();

				if (Timeline.load_count >= Timeline.total_entries) {
					Timeline.has_more_achvs = false;
				}
			},
			error: function() {
				$("#timeline .loading").html("Error :(");
			}
		});
	},

	init: function() {
		this.plot();

		if (this.load_count >= this.total_entries) {
			this.has_more_achvs = false;
		}
		else {
			$(window).scroll(function (e) {
				Timeline.scrollHandler(e);
			});
		}
	}

};