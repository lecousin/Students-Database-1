/** UI control for a DataDisplay, where the user can switch between editable and non-editable, and save a new value
 * @param {Element} container where to put it
 * @param {DataDisplay} data_display the data
 * @param {String} come_from to column from which we come to access to the table containing the data (to use the right DataDisplayHandler)
 * @param {Number} key the key identifying the row in the table
 * @param {Object} data the initial value
 * @param {Function} onchange (optional) called when the user is editing the value
 * @constructor
 */
function editable_datadisplay(container, data_display, come_from, key, data, onchange) {
	
	var t=this;
	if (key == -1) {
		// new data
		// TODO
	} else {
		// existing data
		require("editable_field.js",function() {
			t.editable_field = new editable_field(container, data_display.field_classname, data_display.field_config, data, function(data, handler) {
				service.json("data_model", "lock_datadisplay", {table:data_display.table,name:data_display.name,come_from:come_from,key:key}, function(result) {
					if (!result) handler(null);
					else handler(result.locks, result.data);
				});
			}, function(data, handler) {
				service.json("data_model", "save_datadisplay", {table:data_display.table,name:data_display.name,come_from:come_from,key:key,data:data}, function(result) {
					handler(data);
				});
			});
			if (onchange) t.editable_field.field.onchange.add_listener(onchange);
			t.editable_field.field.register_datamodel_datadisplay(data_display, key);
		});
	}
	
}