/**
 * Abstract class for a typed field 
 * @constructor
 * @param data
 * @param {Boolean} editable true to create an editable field, false for a read-only field
 * @param {Object} config additional information depending on the implementation
 */
function typed_field(data,editable,config){
	this.element = document.createElement("DIV");
	this.element.style.display = "inline-block";
	if (!window.to_cleanup) window.to_cleanup = [];
	window.to_cleanup.push(this);
	this.cleanup = function() {
		this.element = null;
		this.originalData = null;
		this._data = null;
		this.cleanup = null;
		this.config = null;
	};
	this.originalData = data;
	this.editable = editable;
	this.onchange = new Custom_Event();
	this.ondatachanged = new Custom_Event();
	this.ondataunchanged = new Custom_Event();
	this.onfocus = new Custom_Event();
	this._data = data;
	this._in_change_event = false;
	this._datachange = function(force) {
		var cur = this._getEditedData();
		if (!force && objectEquals(cur, this._data)) return; // no change
		this._in_change_event = true;
		this._data = cur;
		this.validate();
		this.onchange.fire(this);
		if (!force && !this.hasChanged())
			this.ondataunchanged.fire(this);
		else
			this.ondatachanged.fire(this);
		this._in_change_event = false;
	};
	this.hasChanged = function() {
		var cur = this.getCurrentData();
		if (cur != this.originalData) return true;
		return !objectEquals(cur, this.originalData);
	};
	this.error = null;
	this.config = config;
	if (getObjectClassName(this) != 'typed_field' && getObjectClassName(this) != 'typed_field_multiple') {
		this._create(data);
		this.validate();
	}
	data = null;
	config = null;
}
typed_field.prototype = {
	/** Internal function resetting and creating the field */
	_create: function(data) { alert("Function _create not implemented in typed_field: "+getObjectClassName(this)); },
	/**
	 * @returns the HTML element representing the field
	 */
	getHTMLElement: function() { return this.element; },
	/** The field must use the full width of its container */
	fillWidth: function() {
		this.element.style.width = "100%";
	},
	/**
	 * @returns true if this field is editable
	 */
	isEditable: function() { return this.editable; },
	/** Set the field as editable or read-only */
	setEditable: function(editable) {
		if (this.editable == editable) return;
		var data = this.getCurrentData();
		this.editable = editable;
		this.element.removeAllChildren();
		// reset some functions which may not be overriden
		this._getEditedData = function() { return this._data; };
		// create
		this._create(data);
	},
	/**
	 * @returns {Boolean} true if the data has been changed by the user since the creation of the field
	 */
	hasChanged: function() { return this.getCurrentData() != this.getOriginalData(); },
	/**
	 * @returns the current data (the edited one)
	 */
	getCurrentData: function() { return this._data; },
	exportCell: function(cell) {
		cell.value = "Export not implemented in typed_field: "+getObjectClassName(this);
	},
	/**
	 * @returns the data from the edited field
	 */
	_getEditedData: function() { return this._data; },
	/**
	 * @returns the original data (at creation time, or set by setOriginalData)
	 */
	getOriginalData: function() { return this.originalData; },
	/**
	 * Change the original data (for example when the value has been saved, and current data should become the original one)
	 */
	setOriginalData: function(data) { return this.originalData = data; },
	/**
	 *  change data
	 *  @param data new data value
	 */
	setData: function(data, same_change, from_input) {
		if (!same_change && objectEquals(data, this._data)) return; // no change
		if (this._in_change_event) {
			var t=this;
			setTimeout(function () { t.setData(data, same_change); },1);
			return;
		}
		data = this._setData(data, from_input);
		this._datachange(same_change);
		this._data = data;
		this.validate();
	},
	getPossibleValues: undefined,
	createValue: undefined, // function(value, name, oncreated)
	compare: function(v1,v2) {
		return (""+v1).localeCompare(""+v2);
	},
	/**
	 *  change data
	 *  @param data new data value
	 */
	_setData: function(data,from_input) { alert("Function _setData not implemented in typed_field "+getObjectClassName(this)); return data; },
	/**
	 * highlight the field to signal an error
	 * @param {Boolean} error if true, the field is highlighted, else it is not
	 */
	signal_error: function(error) {
		this.error = error;
	},
	validate: function() { this.signal_error(null); },
	getError: function() {
		return this.error;
	},
	hasError: function() { return this.error != null; },
	
	canBeNull: function() {
		alert("Function canBeNull not implemented in typed_field: "+getObjectClassName(this));
	},
	
	focus: function () { this.element.focus(); },
	
	isMultiple: function() { return false; },
	
	register_datamodel_cell: function(table, column, row_key) {
		var t=this;
		window.top.datamodel.registerCellWidget(window, table, column, row_key, this.element, function(){
			return t._getEditedData();
		},function(data){
			t._data = t._setData(data);
		},function(listener) {
			t.onchange.add_listener(listener);
		},function(listener) {
			t.onchange.remove_listener(listener);
		});
	},
	unregister_datamodel_cell: function() {
		window.top.datamodel.unregisterCellWidget(this.element);
	},
	register_datamodel_datadisplay: function(data_display, data_key) {
		var t=this;
		window.top.datamodel.registerDataWidget(window, data_display, data_key, this.element, function(){
			return t._getEditedData();
		},function(data){
			t._data = t._setData(data);
		},function(listener) {
			t.onchange.add_listener(listener);
		}, function(listener) {
			t.onchange.remove_listener(listener);
		});
	},
	unregister_datamodel_datadisplay: function() {
		window.top.datamodel.unregisterDataWidget(this.element);
	},
	setDataDisplay: function(data_display, data_key) {
		this.register_datamodel_datadisplay(data_display, data_key);
	}
};
typed_field.prototype.constructor = typed_field;

function typed_field_multiple(data, editable, config) {
	typed_field.call(this, data, editable, config);
}
typed_field_multiple.prototype = new typed_field();
typed_field_multiple.prototype.constructor = typed_field_multiple;		
typed_field_multiple.prototype.isMultiple = function() { return true; };
typed_field_multiple.prototype.addData = function(new_data, from_input) { alert("Function addData not implemented in typed_field_multiple: "+getObjectClassName(this)); };
typed_field_multiple.prototype.getNbData = function() { alert("Function getNbData not implemented in typed_field_multiple: "+getObjectClassName(this)); };
typed_field_multiple.prototype.resetData = function() { alert("Function resetData not implemented in typed_field_multiple: "+getObjectClassName(this)); };
typed_field_multiple.prototype.getDataIndex = function(index) { alert("Function getDataIndex not implemented in typed_field_multiple: "+getObjectClassName(this)); };
typed_field_multiple.prototype.setDataIndex = function(index, value, from_input) { alert("Function setDataIndex not implemented in typed_field_multiple: "+getObjectClassName(this)); };

if (!window.top.sub_field_registry) {
	// allow to register field_addresses having sub data, so that we can synchronize the sub datas
	window.top.sub_field_registry = {
		_fields: [],
		register: function(win, field) {
			this._fields.push({field:field,win:win});
		},
		_in_change: false,
		changed: function(win, field) {
			if (this._in_change) return;
			this._in_change = true;
			for (var i = 0; i < this._fields.length; ++i) {
				var f = this._fields[i];
				if (f.win == win && f.field._data == field._data) {
					// same window, same data
					if (f.field.config.sub_data_index == field.config.sub_data_index) continue; // same
					f.field.setData(field._data, true);
				}
			}
			this._in_change = false;
		},
		_clean: function(win) {
			for (var i = 0; i < this._fields.length; ++i)
				if (this._fields[i].win == win) {
					this._fields.splice(i,1);
					i--;
				}
		},
		getFields: function(win, data) {
			var list = [];
			for (var i = 0; i < this._fields.length; ++i) {
				var f = this._fields[i];
				if (f.win == win && f.field._data == data) {
					// same window, same data
					list.push(f.field);
				}
			}
			return list;
		}
	};
	window.top.pnapplication.onwindowclosed.add_listener(function(c) {c.top.sub_field_registry._clean(c.win); });
}
