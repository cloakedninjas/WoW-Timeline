var Timeline = {
	left_height: 5,
	right_height: 20,
	total_height: 5,
	pad: 17,
	margin: 15,

	plot: function() {
		var prev_mon = null;
		
		for (var i = 0; i < this.data.length; i++) {
			
			if (prev_mon != this.data[i].mm) {
				var h = (this.left_height < this.right_height) ? this.left_height : this.right_height;
				//h -= 10;
				$("#time_index").append("<p style=\"top: " + h + "px;\">" + this.data[i].m + "</p>");
			}
			prev_mon = this.data[i].mm;
			
			var html = $("#entry_template").clone();
			
			html.removeAttr("id");
			
			html.find(".date").text(this.data[i].da + " " + this.data[i].m);
			html.attr("data-day", i);
			
			var entry = '';
			var expand = this.data[i].a.length > 2; 
			
			for (var j = 0; j < this.data[i].a.length; j++) {
				
				entry = "<p title=\"" + this.data[i].d[j] + "\"";
				if (j >= 2) {
					entry += " class=\"hidden\"";
				}
				entry += ">" + this.data[i].a[j] + "</p>";
				

				html.append(entry);
			}
			
			if (expand) {
				html.append("<a class=\"exp\" onmouseenter=\"Timeline.expandDay(" + i + ")\">+" + (this.data[i].a.length - 2) + "</a>");
				html.attr("onmouseleave", "Timeline.collapseDay(" + i + ")");
			}
			
			var side = (this.left_height > this.right_height) ? 'right' : 'left';
			
			html.addClass(side);
			
			$("#timeline .entries").append(html);
			
			var new_height = this.margin + html.height() + this.pad;

			if (side == "left") {
				html.css("top", this.left_height);
				this.left_height += new_height;
				this.total_height = this.left_height; 
				
			}
			else {
				html.css("top", this.right_height);
				this.right_height += new_height;
				this.total_height = this.right_height;
			}
			
			$("#timeline").css("height", this.total_height);
			$("#time_index").css("height", this.total_height);

		}
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