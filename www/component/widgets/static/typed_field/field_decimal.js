/* #depends[typed_field.js] */
/** Decimal field: if editable, it will be a text input, else only a simple text node
 * @constructor
 * @param config must contain: <code>integer_digits</code>: digits before the decimal part, <code>decimal_digits</code> ; can contain: <code>min</code>, <code>max</code>, <code>can_be_null</code>
 */
function field_decimal(data,editable,config) {
	if (typeof data == 'string') data = parseFloat(data);
	if (isNaN(data)) data = null;
	if (config && !config.can_be_null && data == null) data = 0;
	typed_field.call(this, data, editable, config);
}
field_decimal.prototype = new typed_field();
field_decimal.prototype.constructor = field_decimal;		
field_decimal.prototype.canBeNull = function() { return this.config && this.config.can_be_null; };
field_decimal.prototype.compare = function(v1,v2) {
	if (v1 == null) return v2 == null ? 0 : -1;
	if (v2 == null) return 1;
	v1 = parseFloat(v1);
	if (isNaN(v1)) return 1;
	v2 = parseFloat(v2);
	if (isNaN(v2)) return -1;
	if (v1 < v2) return -1;
	if (v1 > v2) return 1;
	return 0;
};
field_decimal.prototype.exportCell = function(cell) {
	var val = this.getCurrentData();
	if (val == null)
		cell.value = "";
	else {
		cell.value = val;
		cell.format = "number:"+this.config.decimal_digits;
	}
};

field_decimal.prototype._create = function(data) {
	if (this.editable) {
		var t=this;
		t.input = document.createElement("INPUT");
		t.input.type = "text";
		t.input.ondomremoved(function() {
			t.input = null;
		});
		t.input.onclick = function(ev) { this.focus(); stopEventPropagation(ev); return false; };
		t.input.maxLength = this.config.integer_digits + 1 + this.config.decimal_digits;
		if (data !== null) t.input.value = parseFloat(data).toFixed(t.config.decimal_digits);
		t.input.style.margin = "0px";
		t.input.style.padding = "0px";
		var onkeyup = new Custom_Event();
		t.input.onkeyup = function(e) { onkeyup.fire(e); };
		t.input.onkeydown = function(e) {
			var ev = getCompatibleKeyEvent(e);
			if (ev.isPrintable) {
				if (!isNaN(parseInt(ev.printableChar)) || ev.ctrlKey) {
					// digit: ok
					return true;
				}
				// not a digit
				if (ev.printableChar == "-" && t.input.value.length == 0) {
					// - at the beginning: ok
					return true;
				}
				if (ev.printableChar == ",") ev.printableChar = e.keyCode = ".";
				if (ev.printableChar == ".") {
					if (t.input.value.length > 0 && t.input.value.indexOf('.') < 0) {
						return true;
					}
				}
				stopEventPropagation(e);
				return false;
			}
			return true;
		};
		var getValueFromInput = function() {
			var value;
			if (t.input.value.length == 0 && t.config.can_be_null) value = null;
			else {
				var i;
				if (t.input.value.length == 0 && !t.config.can_be_null) i = t.config.min ? t.config.min : 0;
				else i = parseFloat(t.input.value);
				if (isNaN(i)) i = t.config.min ? t.config.min : 0;
				if (typeof t.config.min != 'undefined' && i < t.config.min) i = t.config.min;
				if (typeof t.config.max != 'undefined' && i > t.config.max) i = t.config.max;
				value = i.toFixed(t.config.decimal_digits);
			}
			return value;
		};
		t.input.onblur = function(ev) {
			t.setData(t.input.value);
		};
		listenEvent(t.input, 'focus', function() { t.onfocus.fire(); });
		var _fw=false;
		require("input_utils.js",function(){inputAutoresize(t.input);if (_fw) t.input.setMinimumSize(-1); });
		this.element.appendChild(t.input);
		this._getEditedData = function() {
			var value = getValueFromInput();
			if (value == null) return null;
			return parseFloat(value).toFixed(this.config.decimal_digits);
		};
		this._setData = function(data) {
			if (typeof data == 'string') data = parseFloat(data);
			if (isNaN(data)) data = null;
			if (data === null && t.config && !t.config.can_be_null) data = 0;
			if (data === null) t.input.value = "";
			else t.input.value = data.toFixed(t.config.decimal_digits);
			if (typeof t.input.autoresize != 'undefined') t.input.autoresize();
			return data;
		};
		this.signal_error = function(error) {
			this.error = error;
			t.input.style.border = error ? "1px solid red" : "";
			t.input.title = error ? error : "";
		};
		this.onenter = function(listener) {
			onkeyup.add_listener(function(e) {
				var ev = getCompatibleKeyEvent(e);
				if (ev.isEnter) listener(t);
			});
		};
		this.focus = function() { t.input.focus(); };
		this.fillWidth = function() {
			_fw = true;
			this.element.style.width = "100%";
			if (typeof t.input.setMinimumSize != 'undefined') t.input.setMinimumSize(-1);
		};
		this.validate = function() {
			if (!this.config) this.error = null;
			else {
				var val = parseFloat(t.input.value);
				if (isNaN(val)) val = null;
				if (val == null && !this.config.can_be_null) this.error = "Please specify a value";
				else if (typeof this.config.min != 'undefined' && val < this.config.min) this.error = "Must be minimum "+this.config.min.toFixed(t.config.decimal_digits);
				else if (typeof this.config.max != 'undefined' && val > this.config.max) this.error = "Must be maximum "+this.config.max.toFixed(t.config.decimal_digits);
				else this.error = null;
			}
			this.signal_error(this.error);
		};
		this.setMinimum = function(min) {
			t.config.min = min === null ? undefined : min;
			if (typeof t.config.min != 'undefined' && typeof t.config.max != 'undefined' && t.config.max < min) t.config.max = min;
			t.setData(getValueFromInput());
		};
		this.setMaximum = function(max) {
			t.config.max = max === null ? undefined : max;
			if (typeof t.config.min != 'undefined' && typeof t.config.max != 'undefined' && t.config.min < max) t.config.min = max;
			t.setData(getValueFromInput());
		};
		this.setDecimalDigits = function(nb) {
			t.config.decimal_digits = nb;
			t.input.maxLength = t.config.integer_digits + 1 + t.config.decimal_digits;
			t.setData(getValueFromInput());
		};
	} else {
		this.element.appendChild(this.text = document.createTextNode(data == null ? "" : data));
		this._setData = function(data) {
			if (typeof data == 'string') data = parseFloat(data);
			if (isNaN(data)) data = null;
			if (data === null && this.config && !this.config.can_be_null) data = 0;
			if (data === null) this.text.nodeValue = "";
			else this.text.nodeValue = data.toFixed(this.config.decimal_digits);
			return data;
		};
		this.signal_error = function(error) {
			this.error = error;
			this.element.style.color = error ? "red" : "";
		};
	}
};
field_decimal.prototype.setLimits = function(min,max) {
	if (!this.config) this.config = {};
	this.config.min = min;
	this.config.max = max;
	this.setData(this._getEditedData());
};
field_decimal.prototype.setMinimum = function(min) {
	if (!this.config) this.config = {};
	this.config.min = min;
	this.setData(this._getEditedData());
};
field_decimal.prototype.setMaximum = function(max) {
	if (!this.config) this.config = {};
	this.config.max = max;
	this.setData(this._getEditedData());
};
