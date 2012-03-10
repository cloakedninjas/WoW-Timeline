var Timeline = {
	left_height: 5,
	right_height: 5,
	total_height: 5,
	pad: 17,

	plot: function() {
		for(var i = 0; i < this.data.length; i++) {
			var html = $("#entry_template").clone();
			
			html.removeAttr("id");
			
			html.find(".date").text(this.data[i].day);
			
			for (var j = 0; j < this.data[i].a.length; j++) {
				html.append("<span title=\"" + this.data[i].d[j] + "\">" + this.data[i].a[j] + "</span><br />");
			}
			
			var side = ((this.left_height + this.pad) > this.right_height) ? 'right' : 'left';
			
			html.addClass(side);
			
			$("#timeline .entries").append(html);

			if (side == "left") {
				html.css("top", this.left_height);
				this.left_height += html.height() + this.pad;
				this.total_height = this.left_height; 
				
			}
			else {
				html.css("top", this.right_height);
				this.right_height += html.height() + this.pad;
				this.total_height = this.right_height;
			}
			
			$("#timeline").css("height", this.total_height);
			$("#time_index").css("height", this.total_height);

		}
	},

	init: function() {
		this.plot();
	}

};