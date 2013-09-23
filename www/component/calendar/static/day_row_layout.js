function day_row_layout() {
	this.events = [];
	
	this.layout = function(events, day_boxes, first_day) {
		this.remove_events();
		
		var by_day = [];
		for (var i = 0; i < day_boxes.length; ++i) by_day.push([]);
		
		for (var i = 0; i < events.length; ++i) {
			var ev = events[i];
			var day1 = Math.floor((ev.start.getTime()-first_day.getTime())/(24*60*60*1000));
			if (day1 >= day_boxes.length) continue; // after
			var day_end = Math.floor((ev.end.getTime()-first_day.getTime())/(24*60*60*1000));
			if (day_end < 0) continue; // before
			if (day1 < 0) day1 = 0;
			if (day_end >= day_boxes.length) day_end = day_boxes.length-1;
			
			var y;
			var ok = false;
			for (y = 0; !ok; ++y) {
				ok = true;
				for (var day = day1; day <= day_end; ++day)
					if (y < by_day[day].length && by_day[day][y] != null) { ok = false; break; }
			}
			--y;
			
			for (var day = day1; day <= day_end; ++day) {
				while (by_day[day].length <= y) by_day[day].push(null);
				by_day[day][y] = ev;
			}
			
			var div = document.createElement("DIV");
			div.style.position = "absolute";
			div.style.zIndex = 2;
			div.style.backgroundColor = "#"+ev.calendar.color;
			require("color.js", function() {
				div.style.border = "1px solid "+color_string(color_darker(parse_hex_color(ev.calendar.color), 0x60));
			});
			div.style.left = (day_boxes[day1].offsetLeft+2)+"px";
			div.style.top = (1+y*22)+"px";
			div.style.height = "15px";
			var w = 0;
			for (var day = day1; day <= day_end; ++day) w += day_boxes[day].offsetWidth;
			w -= 2+4+(day_end-day1)+4;
			div.style.width = w+"px";
			div.style.padding = "2px";
			div.innerHTML = ev.title;
			day_boxes[0].parentNode.appendChild(div);
			this.events.push(div);
		}
		
		var h = 0;
		for (var i = 0; i < by_day.length; ++i)
			if (by_day[i].length*22 > h) h = by_day[i].length*22;
		h += 1;
		if (h < 10) h = 10;
		for (var i = 0; i < day_boxes.length; ++i)
			day_boxes[i].style.height = h+"px";
		return h;
	};
	
	this.remove_events = function() {
		for (var i = 0; i < this.events.length; ++i)
			this.events[i].parentNode.removeChild(this.events[i]);
		this.events = [];
	};
	
}