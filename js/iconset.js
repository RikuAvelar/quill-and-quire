(function($) {

	$.fn.iconset = function(settings){
		settings = jQuery.extend({
			iconset: 'images/iconset.png',
			tileWidth: 24,
			tileHeight: 24,
			selectorColor: '#115599',
			selectorStyle: 'double',
			selectorSize: 3
		},settings)
		
		var matchedObj = this;
		var preloadImg = new Image();
		preloadImg.src = settings.iconset;
		var iconsetWidth = preloadImg.width;
		var iconsetHeight = preloadImg.height;
		var originalClass = $(this).attr('class');
		//alert(iconsetWidth)
		
		function _init(){
			matchedObj.css({position:'relative',width:settings.tileWidth+'px',height:settings.tileHeight+'px',border:'1px solid black'})
					.addClass('ddicon').addClass('icon-0-0')
					.append('<div class="iconset-selector" style="display:none"></div>');
			var selector = matchedObj.find('.iconset-selector');
			selector.css({
				width:(settings.tileWidth-settings.selectorSize)+'px',
				height:(settings.tileHeight-settings.selectorSize)+'px',
				borderColor:settings.selectorColor,
				borderStyle:settings.selectorStyle,
				borderWidth:settings.selectorSize,
				position:'absolute'
			});
			matchedObj.off('click').click(_toggle);
		}
		function _toggle(e){
			if($(this).css('width') == settings.tileWidth + 'px'){
				$(this).animate({backgroundPosition:0,width:iconsetWidth+'px',height:iconsetHeight+'px'},200);
				//$(this).find('.iconset-selector').show();
				var pattern = $(this).attr('class').substr(12).split('-');
				if(pattern != ''){
					$(this).find('.iconset-selector').css({left:(pattern[0] * settings.tileWidth - (settings.selectorSize/2)) + 'px',top:(pattern[1] * settings.tileHeight - (settings.selectorSize/2)) +'px'})
				}
			}else{
				var x = e.pageX - $(this).offset().left;
				var y = e.pageY - $(this).offset().top;
				var iconX = Math.floor(x/settings.tileWidth);
				var iconY = Math.floor(y/settings.tileHeight);
				//alert(iconX*24 + ' ' + iconY*24);
				$(this).css('background-position','');
				$(this).animate({width:settings.tileWidth+'px',height:settings.tileHeight+'px'},200);
				$(this).attr('class',originalClass + ' ddicon').addClass('icon-'+iconX+'-'+iconY);
				$(this).find('.iconset-selector').hide();
			}
		}
		_init();
	}


})(jQuery)