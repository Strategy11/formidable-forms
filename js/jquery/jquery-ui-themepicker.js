//global for tracking open and focused toolbar panels on refresh
var openGroups = [];
var focusedEl = null;

//backbutton and hash bookmarks support
var hash = {
	storedHash: '',
	currentTabHash: '', //The hash that's only stored on a tab switch
	cache: '',
	interval: null,
	listen: true, // listen to hash changes?
	
	 // start listening again
	startListening: function() {
		setTimeout(function(){hash.listen = true;}, 600);
	},
	 // stop listening to hash changes
	stopListening:function(){hash.listen = false;},
	//check if hash has changed
	checkHashChange:function(){
		var locStr = hash.currHash();
		if(hash.storedHash != locStr) {
			if(hash.listen == true) hash.refreshToHash(); ////update was made by back button
			hash.storedHash = locStr;
		}
		if(!hash.interval) hash.interval = setInterval(hash.checkHashChange, 500);
	},
	
	//refresh to a certain hash
	refreshToHash: function(locStr) {
		if(locStr) var newHash = true;
		locStr = locStr || hash.currHash();
		frmUpdateCSS(locStr);
		// remember which groups are open
		openGroups = [];
		jQuery('div.theme-group-content').each(function(i){
			if(jQuery(this).is(':visible')){openGroups.push(i);}
		});
		
		// remember any focused element
		focusedEl = null;
		jQuery('form input, form select, form .texturePicker').each(function(i){
			if(jQuery(this).is('.focus')){focusedEl = i;}
		});
		
		// if the hash is passed
		if(newHash){ hash.updateHash(locStr, true); }
	},
	
	updateHash: function(locStr, ignore) {
		if(ignore == true){ hash.stopListening(); }
		window.location.hash = locStr;
		if(ignore == true){ 
			hash.storedHash = locStr; 
			hash.startListening();
		}
		
	},
	
	clean: function(locStr){return locStr.replace(/%23/g, "").replace(/[\?#]+/g, "");},
	
	currHash: function(){return hash.clean(window.location.hash);},
	
	currSearch: function(){return hash.clean(window.location.search);},
	
	init: function(){
		hash.storedHash = '';
		hash.checkHashChange();
	}	
};

jQuery.fn.spinDown = function() {
	return this.click(function() {
		var $this = jQuery(this);
		$this.next().slideToggle(100);
		$this.parent().siblings().children('.state-active').click(); //close open tabs
		$this.prev().toggleClass('not-active');
		$this.find('.icon').toggleClass('icon-triangle-1-s').end().toggleClass('state-active');
		//jQuery('li.ui-state-default .ui-state-active').removeClass('ui-state-active');
		$this.find('.ui-icon').toggleClass('ui-icon-triangle-1-s').end().toggleClass('ui-state-active');
		if($this.is('.corner-all')) { $this.removeClass('corner-all').addClass('corner-top'); }
		else if($this.is('.corner-top')) { $this.removeClass('corner-top').addClass('corner-all'); }
		if($this.is('.ui-corner-all')) { $this.removeClass('ui-corner-all').addClass('ui-corner-top'); }
		else if($this.is('.ui-corner-top')) { $this.removeClass('ui-corner-top').addClass('ui-corner-all'); }
		return false;
	});
};

// validation for hex inputs
jQuery.fn.validHex = function() {
	return this.each(function() {
		var value = jQuery(this).val();
		value = value.replace(/[^#a-fA-F0-9]/g, ''); // non [#a-f0-9]
		if(value.match(/#/g) && value.match(/#/g).length > 1) value = value.replace(/#/g, ''); // ##
		if(value.indexOf('#') == -1) value = '#'+value; // no #
		if(value.length > 7) value = value.substr(0,7); // too many chars
		jQuery(this).val(value);	
	});	
};

//color pickers setup (sets bg color of inputs)
jQuery.fn.applyFarbtastic = function() {
	return this.each(function() {
		jQuery('<div/>').farbtastic(this).remove();
	});
};


//function called after a change event in the form
function formChange(){
	var locStr = jQuery('.frm_settings_page input, .frm_settings_page select, .frm_settings_page textarea').serialize();
	locStr = hash.clean(locStr);
	frmUpdateCSS(locStr);
	hash.updateHash(locStr, true);
};

jQuery(document).ready(function($){
    // hover class toggles in app panel
    jQuery('.state-default').hover(
    	function(){ jQuery(this).addClass('state-hover'); }, 
    	function(){ jQuery(this).removeClass('state-hover'); }
    );
    
    $('div.theme-group .theme-group-header').addClass('corner-all').spinDown();
    
    // focus and blur classes in form
	$('input, select').focus(function(){
		$('input.focus, select.focus').removeClass('focus');
		$(this).addClass('focus');
	}).blur(function(){ $(this).removeClass('focus');});
	
	// change event in form
	$('form[name="frm_settings_form"] .styling_settings').bind('change', function() {
		formChange();
		return false;
	});
	
	// hex inputs
	$('input.hex').validHex().keyup(function() {$(this).validHex();})
		.click(function(){
			$(this).addClass('focus');
			$('#picker').remove();
			$('div.picker-on').removeClass('picker-on');
			$('div.texturePicker ul:visible').hide(0).parent().css('position', 'static');
			$(this).after('<div id="picker"></div>').parent().addClass('picker-on');
			$('#picker').farbtastic(this);
			return false;
		})
		.wrap('<div class="hasPicker"></div>')
		.applyFarbtastic();
	
	$('body').click(function() {
		$('div.picker-on').removeClass('picker-on');
		$('#picker').remove();
		$('input.focus, select.focus').removeClass('focus');
		$('div.texturePicker ul:visible').hide().parent().css('position', 'static');
	});
	
	// texture pickers from select menus
		$('select.texture').each(function() {

			$(this).after('<div class="texturePicker"><a href="#"></a><ul></ul></div>');
			var texturePicker = $(this).next();
			var a = texturePicker.find('a');
			var ul = texturePicker.find('ul');
			var sIndex = texturePicker.prev().get(0).selectedIndex;

			// scrape options
			$(this).find('option').each(function(){
				ul.append('<li class="'+ $(this).attr('value') +'" data-texturewidth="16" data-textureheight="16" style="background: #FFF url('+$(this).attr('value')+') 50% 50% no-repeat"><a href="#" title="'+ $(this).text() +'">'+ $(this).text() +'</a></li>');
				if($(this).get(0).index == sIndex){texturePicker.attr('title',$(this).text()).css('background', '#FFF url('+$(this).attr('value')+') 50% 50% no-repeat');}
			});

			ul.find('li').click(function() {
				texturePicker.prev().get(0).selectedIndex = texturePicker.prev().find('option[value="'+ $(this).attr('class') +'"]').get(0).index;
				texturePicker.attr('title',$(this).text()).css('background', '#FFF url('+$(this).attr('class')+')  50% 50% no-repeat');
				$('.frm_error_style img').attr('src',$(this).attr('class'));
				//ul.fadeOut(100);
				formChange();
				return false;
			});

			// hide the menu and select el
			ul.hide();

			// show/hide of menus
			texturePicker.click(function() {
				$(this).addClass('focus');
				$('#picker').remove();
				var showIt;
				if(ul.is(':hidden')){showIt = true;}
				$('div.texturePicker ul:visible').hide().parent().css('position', 'static');
				if(showIt == true){
					texturePicker.css('position', 'relative');
					ul.show();
				}
				return false;
			});
		});
});



// $Id: farbtastic.js,v 1.2 2007/01/08 22:53:01 unconed Exp $
// Farbtastic 1.2

jQuery.fn.farbtastic = function (callback) {
jQuery.farbtastic(this, callback);
return this;
};

jQuery.farbtastic = function (container, callback) {
var container = jQuery(container).get(0);
return container.farbtastic || (container.farbtastic = new jQuery._farbtastic(container, callback));
};

jQuery._farbtastic = function (container, callback) {
// Store farbtastic object
var fb = this;

// Insert markup
jQuery(container).html('<div class="farbtastic"><div class="color"></div><div class="wheel"></div><div class="overlay"></div><div class="h-marker marker"></div><div class="sl-marker marker"></div></div>');
var e = jQuery('.farbtastic', container);
fb.wheel = jQuery('.wheel', container).get(0);
// Dimensions
fb.radius = 84;
fb.square = 100;
fb.width = 194;

// Fix background PNGs in IE6
if (navigator.appVersion.match(/MSIE [0-6]\./)) {
jQuery('*', e).each(function () {
if (this.currentStyle.backgroundImage != 'none') {
var image = this.currentStyle.backgroundImage;
image = this.currentStyle.backgroundImage.substring(5, image.length - 2);
jQuery(this).css({
'backgroundImage': 'none',
'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='" + image + "')"
});
}
});
}

/**
* Link to the given element(s) or callback.
*/
fb.linkTo = function (callback) {
// Unbind previous nodes
if (typeof fb.callback == 'object'){jQuery(fb.callback).unbind('keyup', fb.updateValue);}

// Reset color
fb.color = null;

// Bind callback or elements
if (typeof callback == 'function'){fb.callback = callback;}
else if (typeof callback == 'object' || typeof callback == 'string') {
fb.callback = jQuery(callback);
fb.callback.bind('keyup', fb.updateValue);
if (fb.callback.get(0).value){fb.setColor(fb.callback.get(0).value);}
}
return this;
};
fb.updateValue = function (event) {
if (this.value && this.value != fb.color){fb.setColor(this.value);}
};

/**
* Change color with HTML syntax #123456
*/
fb.setColor = function (color) {
var unpack = fb.unpack(color);
if (fb.color != color && unpack) {
fb.color = color;
fb.rgb = unpack;
fb.hsl = fb.RGBToHSL(fb.rgb);
fb.updateDisplay();
}
return this;
};

/**
* Change color with HSL triplet [0..1, 0..1, 0..1]
*/
fb.setHSL = function (hsl) {
fb.hsl = hsl;
fb.rgb = fb.HSLToRGB(hsl);
fb.color = fb.pack(fb.rgb);
fb.updateDisplay();
return this;
};

/////////////////////////////////////////////////////

/**
* Retrieve the coordinates of the given event relative to the center
* of the widget.
*/
fb.widgetCoords = function (event) {
var x, y;
var el = event.target || event.srcElement;
var reference = fb.wheel;

if (typeof event.offsetX != 'undefined') {
// Use offset coordinates and find common offsetParent
var pos = { x: event.offsetX, y: event.offsetY };

// Send the coordinates upwards through the offsetParent chain.
var e = el;
while (e) {
e.mouseX = pos.x;
e.mouseY = pos.y;
pos.x += e.offsetLeft;
pos.y += e.offsetTop;
e = e.offsetParent;
};

// Look for the coordinates starting from the wheel widget.
var e = reference;
var offset = { x: 0, y: 0 };
while (e) {
if (typeof e.mouseX != 'undefined') {
x = e.mouseX - offset.x;
y = e.mouseY - offset.y;
break;
}
offset.x += e.offsetLeft;
offset.y += e.offsetTop;
e = e.offsetParent;
}

// Reset stored coordinates
e = el;
while (e) {
e.mouseX = undefined;
e.mouseY = undefined;
e = e.offsetParent;
}
}
else {
// Use absolute coordinates
var pos = fb.absolutePosition(reference);
x = (event.pageX || 0*(event.clientX + jQuery('html').get(0).scrollLeft)) - pos.x;
y = (event.pageY || 0*(event.clientY + jQuery('html').get(0).scrollTop)) - pos.y;
}
// Subtract distance to middle
return { x: x - fb.width / 2, y: y - fb.width / 2 };
};

/**
* Mousedown handler
*/
fb.mousedown = function (event) {
// Capture mouse
if (!document.dragging) {
jQuery(document).bind('mousemove', fb.mousemove).bind('mouseup', fb.mouseup);
document.dragging = true;
};

// Check which area is being dragged
var pos = fb.widgetCoords(event);
fb.circleDrag = Math.max(Math.abs(pos.x), Math.abs(pos.y)) * 2 > fb.square;

// Process
fb.mousemove(event);
return false;
};

/**
* Mousemove handler
*/
fb.mousemove = function (event) {
// Get coordinates relative to color picker center
var pos = fb.widgetCoords(event);

// Set new HSL parameters
if (fb.circleDrag) {
var hue = Math.atan2(pos.x, -pos.y) / 6.28;
if (hue < 0) hue += 1;
fb.setHSL([hue, fb.hsl[1], fb.hsl[2]]);
}else{
var sat = Math.max(0, Math.min(1, -(pos.x / fb.square) + .5));
var lum = Math.max(0, Math.min(1, -(pos.y / fb.square) + .5));
fb.setHSL([fb.hsl[0], sat, lum]);
}
return false;
};

/**
* Mouseup handler
*/
fb.mouseup = function () {
// Uncapture mouse
jQuery(document).unbind('mousemove', fb.mousemove);
jQuery(document).unbind('mouseup', fb.mouseup);
document.dragging = false;
formChange();
};

/**
* Update the markers and styles
*/
fb.updateDisplay = function () {
// Markers
var angle = fb.hsl[0] * 6.28;
jQuery('.h-marker', e).css({
left: Math.round(Math.sin(angle) * fb.radius + fb.width / 2) + 'px',
top: Math.round(-Math.cos(angle) * fb.radius + fb.width / 2) + 'px'
});

jQuery('.sl-marker', e).css({
left: Math.round(fb.square * (.5 - fb.hsl[1]) + fb.width / 2) + 'px',
top: Math.round(fb.square * (.5 - fb.hsl[2]) + fb.width / 2) + 'px'
});

// Saturation/Luminance gradient
jQuery('.color', e).css('backgroundColor', fb.pack(fb.HSLToRGB([fb.hsl[0], 1, 0.5])));

// Linked elements or callback
if (typeof fb.callback == 'object') {
// Set background/foreground color
jQuery(fb.callback).css({
backgroundColor: fb.color,
color: fb.hsl[2] > 0.5 ? '#000' : '#fff'
});

// Change linked value
jQuery(fb.callback).each(function() {
if (this.value && this.value != fb.color){this.value = fb.color;}
});
}else if (typeof fb.callback == 'function'){fb.callback.call(fb, fb.color);}
};

/**
* Get absolute position of element
*/
fb.absolutePosition = function (el) {
var r = { x: el.offsetLeft, y: el.offsetTop };
// Resolve relative to offsetParent
if (el.offsetParent) {
var tmp = fb.absolutePosition(el.offsetParent);
r.x += tmp.x;
r.y += tmp.y;
}
return r;
};

/* Various color utility functions */
fb.pack = function (rgb) {
var r = Math.round(rgb[0] * 255);
var g = Math.round(rgb[1] * 255);
var b = Math.round(rgb[2] * 255);
return '#' + (r < 16 ? '0' : '') + r.toString(16) +
(g < 16 ? '0' : '') + g.toString(16) +
(b < 16 ? '0' : '') + b.toString(16);
};

fb.unpack = function (color) {
if (color.length == 7) {
return [parseInt('0x' + color.substring(1, 3)) / 255,
parseInt('0x' + color.substring(3, 5)) / 255,
parseInt('0x' + color.substring(5, 7)) / 255];
}
else if (color.length == 4) {
return [parseInt('0x' + color.substring(1, 2)) / 15,
parseInt('0x' + color.substring(2, 3)) / 15,
parseInt('0x' + color.substring(3, 4)) / 15];
}
};

fb.HSLToRGB = function (hsl) {
var m1, m2, r, g, b;
var h = hsl[0], s = hsl[1], l = hsl[2];
m2 = (l <= 0.5) ? l * (s + 1) : l + s - l*s;
m1 = l * 2 - m2;
return [this.hueToRGB(m1, m2, h+0.33333),
this.hueToRGB(m1, m2, h),
this.hueToRGB(m1, m2, h-0.33333)];
};

fb.hueToRGB = function (m1, m2, h) {
h = (h < 0) ? h + 1 : ((h > 1) ? h - 1 : h);
if (h * 6 < 1) return m1 + (m2 - m1) * h * 6;
if (h * 2 < 1) return m2;
if (h * 3 < 2) return m1 + (m2 - m1) * (0.66666 - h) * 6;
return m1;
};

fb.RGBToHSL = function (rgb) {
var min, max, delta, h, s, l;
var r = rgb[0], g = rgb[1], b = rgb[2];
min = Math.min(r, Math.min(g, b));
max = Math.max(r, Math.max(g, b));
delta = max - min;
l = (min + max) / 2;
s = 0;
if (l > 0 && l < 1) {
s = delta / (l < 0.5 ? (2 * l) : (2 - 2 * l));
}
h = 0;
if (delta > 0) {
if (max == r && max != g) h += (g - b) / delta;
if (max == g && max != b) h += (2 + (b - r) / delta);
if (max == b && max != r) h += (4 + (r - g) / delta);
h /= 6;
}
return [h, s, l];
};

// Install mousedown handler (the others are set on the document on-demand)
jQuery('*', e).mousedown(fb.mousedown);

// Init color
fb.setColor('#000000');

// Set linked elements/callback
if (callback) {
fb.linkTo(callback);
}
};