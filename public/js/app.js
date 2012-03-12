var Timeline = {
	left_height: 5,
	right_height: 20,
	max_height: 20,

	plot: function() {
		var prev_mon = null;

		for (var day_idx = 0; day_idx < this.data.length; day_idx++) {

			// time indexes
			if (prev_mon != this.data[day_idx].mm) {
				var h = (this.left_height < this.right_height) ? this.left_height : this.right_height;
				var h = this.max_height;
				$("#time_index").append("<p style=\"top: " + h + "px;\">" + this.data[day_idx].m + "</p>");
			}
			prev_mon = this.data[day_idx].mm;

			// entries
			var html = $("#entry_template").clone();

			html.removeAttr("id");

			html.find(".date").text(this.data[day_idx].da + " " + this.data[day_idx].m);
			html.attr("data-day", day_idx);

			var entry = '';
			var expand = this.data[day_idx].a.length > 2;

			for (var ach_idx = 0; ach_idx < this.data[day_idx].a.length; ach_idx++) {

				if (this.data[day_idx].a[ach_idx].no) {
					// notable achievements done elsewhere
					this.plotNotable(this.data[day_idx].a[ach_idx]);
					continue;
				}

				entry = "<p title=\"" + this.data[day_idx].a[ach_idx].d + "\"";
				if (ach_idx >= 2) {
					entry += " class=\"hidden\"";
				}
				entry += ">" + this.data[day_idx].a[ach_idx].n + "</p>";

				html.append(entry);
			}

			if (expand) {
				html.append("<a class=\"exp\" onmouseenter=\"Timeline.expandDay(" + day_idx + ")\">+" + (this.data[day_idx].a.length - 2) + "</a>");
				html.attr("onmouseleave", "Timeline.collapseDay(" + day_idx + ")");
			}

			var side = (this.left_height > this.right_height) ? 'right' : 'left';
			html.addClass(side);

			this.addToTimeline(html);
		}
	},

	plotNotable: function(data) {
		var html = $("#notable_template").clone();
		html.removeAttr("id");
		html.append("<h4>" + data.n + "</h4><p>" + data.d + "</p>");

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

			this.max_height = this.right_height =  this.left_height = this.max_height + new_height;
		}
		else if (html.hasClass("left")) {
			this.left_height += new_height;
			this.max_height = this.left_height;
		}
		else {
			this.right_height += new_height;
			this.max_height = this.right_height;
		}

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

	init: function() {
		this.plot();
	}

};