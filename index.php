<html>
<head>
	<link href="css/main.css" type='text/css' rel='stylesheet' />
	<link href="css/smoothness/jquery-ui-1.8.18.custom.css" type='text/css' rel='stylesheet' />
	<link rel="stylesheet" href="farbtastic.css" type="text/css" />
	<link rel="stylesheet" href="css/tipTip.css" type="text/css" />
	<script type='text/javascript' src='js/jquery-1.7.1.min.js'></script>
	<script type='text/javascript' src='js/jquery-ui-1.8.18.custom.min.js'></script>
	<script type="text/javascript" src="farbtastic.js"></script>
	<script type='text/javascript' src='js/jquery.tipTip.minified.js'></script>
	<script type='text/javascript' src='js/iconset.js'></script>
	<script type='text/javascript'>
		var rate = 1000;
		
		var duid = 0;
		var dname = "";
		var dtype = "";
		var dstatus = "";
		var loggedIn = false;
		var firstLoad = false;
		var missCounter = 0;
		
		var currentMap;
		
		var updater;
		
	//	init();
		//$.ajaxSetup({async:false});
		$(document).ready(function(){
			$('#login input').keydown(function(e){
				if(e.which == 13){
					//console.log($(this).parent().parent().parent()[0]);
					$($(this).parent().parent().parent().find('button')[0]).click();
				}
				
			});
			$('.unitForm input').keydown(function(e){
				if(e.which == 13){
					//console.log($(this).parent().parent().parent()[0]);
					$($(this).parent().parent().find('button')[0]).click();
				}
				
			});
			$('textarea,#tools input').keydown(function(e){
				if((e.which == 13) && !e.ctrlKey){
					$($(this).parent().find('button')[0]).click();
				}
			});
			$('#login').dialog({
				modal:true,
				buttons:{
					"Login":function(){
						promptLogin($('#login input').val());
						$(this).dialog('close');
					}
				}
			});
		});
		
		
		function promptLogin(name){
			//var name = prompt("Please login");
			//name = 'riku';
			//$.post("ajaxy.php",{a:'login',uname:name},function(d){init(d);alert(3)},'json');
			$.ajax({
			  type: 'POST',
			  url: 'ajaxy.php',
			  data: {a:'login',uname:name},
			  success: login,
			  dataType: 'json'
			});
		}
		
		function login(data){
			if(loggedIn) return false;
			loggedIn = true;
			if((!data) || (!data.type)){
				alert("Error: User does not exist");
				return false;
			}
			
			duid = Number(data.uid);
			dname = data.name;
			dtype = data.type;
			dstatus = data.status;
			
			$('#self').html("You are logged in as " + dtype + " " + dname);
			$.post('ajaxy.php',{a:'postchat',uid:duid,uname:dname,type:'dice',content:'has logged in.'});
			init();
		}
		
		function init(){
			if(dtype == 'DM'){
				$('#mapInfo').html('<select></select><div id="rooms"></div>');
				$('#addUnitForm,#editUnitForm').append('<textarea class="notes"></textarea><input class="admin" type="checkbox" value="1" checked="yes" /><label>Hide Stats?</label>')
				$('#tools').prepend('<div style="position:absolute;top:-5px;"><input class="admin" type="checkbox" value="1" /><label style="font-size:12px">Hide?</label>');
				
				$('body').append('<div id="smite">WHO DESERVERS THY WRATH!?<br /><input type="text" />');
				$('#smite').dialog({
					autoOpen:false,
					modal:true,
					width:700,
					height:700,
					buttons:{
						"Smite!":function(){
							var player = $('#smite input').val();
							$.post('ajaxy.php',{a:'setsmite',name:player,smite:'smite'});
							$(this).dialog('close');
						},
						"Unsmite!":function(){
							var player = $('#smite input').val();
							$.post('ajaxy.php',{a:'setsmite',name:player,smite:'none'});
							$(this).dialog('close');
						},
						"Close":function(){
							$(this).dialog('close');
							
						}
					}
				});
				$(window).keydown(function(e){
					if(e.which == 101){
						$('#smite').dialog('open');
						//alert(currentMap);
					}
				});
			}
			$('#addUnitForm .sizeSelector,#editUnitForm .sizeSelector').val('medium');
			$('#map').draggable();
			//$('.unit').on('click',function(){$(this).draggable()});
			
			$('.unit').draggable();
			
			$('#poster button').button().click(function(){
				var text = $.trim($('#poster textarea').val());
				
				if(text != ''){
					$.post('ajaxy.php',{a:'postchat',uname:dname,uid:duid,type:'post',content:text});
					//clearTimeout(updater)
					//update();
				}
				$('#poster textarea').val('');
				
			});
			$('#tools button').button().click(function(){
				var results = parseDie($('#die').val());
				
				if(results === false) return false;
				
				var hide = 0;
				
				if($('#tools .admin:checked').length > 0){
					hide = 1;
				}
				
				var print = "has rolled [b]";
				
				for(var i=0;i<results.length;i++){
					print += results[i] + ', ';
				}
				
				print = print.slice(0,print.length-2);
				print += "[/b] on [b]" + $('#die').val() +'[/b]';
			
				$('#die').val('');
				
				$.post('ajaxy.php',{a:'postchat',uname:dname,uid:duid,type:'dice',content:print,hidden:hide});
				
				//clearTimeout(updater)
			//	update();
			});
			
			$('#addUnitForm .colorSelector').farbtastic('#addUnitForm .color');
			$('#editUnitForm .colorSelector').farbtastic('#editUnitForm .color');
			$('.iconSelector').iconset({iconset: 'http://dwarfs.hekatonstudios.com/online/images/iconset.png'});
			$('#addUnitForm').dialog({
				autoOpen:false,
				modal:true,
				width:700,
				buttons: {
					"Create" : function(){
						var n = $('#addUnitForm .name').val();
						var co = $('#addUnitForm .color').val();
						var ch = $('#addUnitForm .hp').val();
						var ad = 0;
						var notes = '';
						var si = $('#addUnitForm .sizeSelector').val();
						var ic = $('#addUnitForm .iconSelector').attr('class').substr(20)
						//alert(ic)
						if($('#addUnitForm .admin:checked').length > 0){
								ad=1;
						}
						if($('#addUnitForm .notes').length > 0){
							no = $('#addUnitForm .notes').val();
						}
						$.post('ajaxy.php', {a:'newunit',size:si,uid:duid,name:n,color:co,health:ch,admin:ad,note:notes,icon:ic});
						$.post('ajaxy.php',{a:'postchat',type:'dice', uid:duid,uname:dname,content:"has created a new token: " + n});
						$(this).dialog('close');
					},
					"Cancel" : function(){
						$(this).dialog('close');
					}
				}
				
			});
			$('#editUnitForm').dialog({
				autoOpen:false,
				modal:true,
				width:700,
				buttons:{
					"Edit": function(){
						ed = '#editUnitForm';
						var na = $(ed + ' .name').val();
						var mid = $(ed + ' .id').val();
						var co = $(ed + ' .color').val();
						var ch = $(ed + ' .chp').val();
						var mh = $(ed + ' .mhp').val();
						var no = '' /*$(ed + ' .notes').val();*/
						var ad = 0 //$(ed + ' .admin').val()
						var ic = $(ed + ' .iconSelector').attr('class').substr(20)
						var si = $(ed + ' .sizeSelector').val();
						if($('#editUnitForm .admin:checked').length > 0){
								ad=1;
						}
						if($('#editUnitForm .notes').length > 0){
							no = $('#editUnitForm .notes').val();
						}
						
						$.post('ajaxy.php',{a:'setunit',size:si,name:na,chp:ch,mhp:mh,id:mid,notes:no,color:co,admin:ad,icon:ic});
						$.post('ajaxy.php',{a:'clearall'});
						$.post('ajaxy.php',{a:'postchat',type:'dice', uid:duid,uname:dname,content:"has edited token: " + na});
						$(this).dialog('close');
					},
					"Delete":function(){
						var r=confirm("Do you really want to delete this?");
						if(r){
							var jid = $('#editUnitForm .id').val();
							var na = $(ed + ' .name').val();
							$.post('ajaxy.php',{a:'deleteunit',id:jid});
							$.post('ajaxy.php',{a:'postchat',type:'dice', uid:duid,uname:dname,content:"has deleted token: " + na});
							$.post('ajaxy.php',{a:'clearall'});
							$(this).dialog('close');
						}
					},
					"Cancel": function(){
						$(this).dialog('close');
					}
				}
			})
			$('#addUnit').click(function(){
				$('#addUnitForm').dialog('open');
			});
			$('.unit').on('dblclick',editor);
			//alert($('#poster button').length);
			if(dtype == 'DM') $.post('ajaxy.php',{a:'listmaps'},updateMapList,'json');
			update();
			
			
		}
		
		function clearAll(){
			//$('#health').empty();
			//$('.unit,audio').remove();
		}
		
		function editor(){
			
			var owner = $(this).attr('data-owner');
			
			if((duid == owner) || (dtype == 'DM')){
				$('#editUnitForm').dialog('open');
				ed = '#editUnitForm';
			 
				health = $('#hp' + $(this).attr('id'))
				
				$(ed + ' .name').val($(this).attr('title'));
				$(ed + ' .id').val($(this).attr('id'));
				$(ed + ' .color').val($(this).css('background-color'));
				$(ed + ' .notes').val($(this).attr('notes'));
				$(ed + ' .chp').val($(health).attr('data-hp'));
				$(ed + ' .mhp').val(Number($(health).attr('max'))-10);
				$(ed + ' .iconSelector').attr('class','iconSelector ' + $(this).find('.ddicon').attr('class'));
				
				var si = $(ed + ' .sizeSelector');
				
				if($(this).hasClass('tiny')){
					si.val('tiny');
				}
				if($(this).hasClass('small')){
					si.val('small');
				}
				if($(this).hasClass('medium')){
					si.val('medium');
				}
				if($(this).hasClass('large')){
					si.val('large');
				}
				if($(this).hasClass('colossal')){
					si.val('colossal');
				}
				
				
				$(ed).dialog('open');
			}
		}
		
		function update(){
			$.post('ajaxy.php',{a:'getstatus',id:duid},updateStatus,'json');
			$.post('ajaxy.php',{a:'getchat',type:dtype},updateChat,'json');
			$.post('ajaxy.php',{a:'getunits',ut:dtype},updateUnits,'json');
			$.post('ajaxy.php',{a:'getmap',current:currentMap},updateMap,'json');
			missCounter++;
			if(missCounter >6 ){
				alert('The Server is currently experiencing problems. Please reload and try again.')
				return false;
			}
			//if(dtype == 'DM') $.post('ajaxy.php',{a:'listmaps'},updateMapList,'json');
			updater = setTimeout(update,rate)
		}
		
		function updateStatus(data){
			dstatus = data.status;
			missCounter = 0;
			//alert(data.status);
			if(dstatus == 'none'){
				$.post('ajaxy.php',{a:'setstatus',id:duid,status:"clear"});
				//$('audio')[0].pause();
				$('audio').remove();
				//clearAll();
			}
			if(dstatus == 'smite'){
				$.post('ajaxy.php',{a:'setstatus',id:duid,status:"clear"});
				$('audio').remove();
				$('body').append("<audio autoplay='autoplay'><source src='greenhillzone.mp3' /><source src='greenhillzone.ogg' /></audio>"); //sanic
			}
		}
		
		function pauseUpdate(){
			clearTimeout(updater);
		}
		
		function updateMapList(data){
			for(var i=0;i< data.maps.length ; i++){
				var name = data.maps[i].name;
				var id = data.maps[i].mid;
				console.log(data)
				$('#mapInfo select').append('<option value="' + id + '">' + name + '</option>');
			}
			$('#mapInfo select').change(function(){
				var newmap = $(this).val();
				$.post('ajaxy.php',{a:'selectmap',mid:newmap});
			});
		}
		
		function updateRoomList(data){
			$('#mapInfo #rooms').empty();
			for(var i=0;i<data.rooms.length;i++){
				var checked = 'checked="checked"'
				if(data.rooms[i].show == '0'){
					checked = '';
				}
				var roomInfo = '<label>' + data.rooms[i].name + '</label><input type="checkbox" name="' + data.rooms[i].rid + '" ' + checked + ' /><br />';
				$('#mapInfo #rooms').append(roomInfo);
			}
			
			$('#rooms input').click(function(){
				name = $(this).attr('name');
				//alert(name);
				if($('#rooms input[name="'+name+'"]:checked').length > 0){
					$.post('ajaxy.php',{a:'toggleroom',rid:name,show:1});
				}else{
					$.post('ajaxy.php',{a:'toggleroom',rid:name,show:0});
				}
				$.post('ajaxy.php',{a:'mapstatus',status:'change',map:currentMap});
			});
			
		}
		
		function updateChat(data){
			if(data.status == 'new'){
				var chatList = data.chat;
				$('#chatbox').empty();
				//alert(typeof data.chat);
				//alert(data.chat.length);
				for(var i=0;i<data.chat.length;i++){
					//alert(currentChat);
					//if(currentChat == 'ENDOFFILELOL') break;
					$('#chatbox').prepend(data.chat[i] + '<br />');
				}
			}
		}
		
		function updateUnits(data){
		//return false;
			if(!data) return false;
			if(data.cancel) return false;
			var unitArray = data.units;
			var unitId = '';
			var healthId = '';
			for(var i=0;i<unitArray.length;i++){
				var unit = unitArray[i];
				if(unit == 'end') break;
				var id = unit.jid;
				var ow = unit.uid;
				var title = unit.title;
				var color = unit.color;
				var positionX = unit.positionX;
				var positionY = unit.positionY;
				var currentHealth = Number(unit.currentHealth);
				var maxHealth = Number(unit.maxHealth);
				var notes = unit.notes;
				var icon = unit.icon;
				var size = unit.size
				unitId += '#' + id + ',';
				healthId += '#health' + id + ',';
				if($('#' + id).length == 0){
					var marker = '<div title="'+ title +'" class="unit" id="' + id + '"><div class="ddicon '+icon+'"></div></div>'
					$(marker).appendTo($('#map'));
					$('#' + id).on('dblclick',editor).on('dragstart',pauseUpdate).on('dragstop',updatePosition).draggable()
				}
				$('#' + id).attr('title',title).attr('data-notes',notes).attr('data-owner',ow).attr('data-notes',notes).css({background:color,left:positionX,top:positionY})
				
				if(!$('#' + id).hasClass(size)){
					$('#' + id).removeClass('tiny small medium large colossal').addClass(size);
				}
				if($('#hp' + id).length == 0){
					var chp = ' data-hp="'+Number(currentHealth)+'" value="' + (Number(currentHealth)+10) + '"';
					var mhp = ' max="' + (Number(maxHealth)+10) + '"';
					if(maxHealth == -1){
						chp = '';
						mhp = '';
					}
					$('#health').append('<div class="holder" id="health' + id + '"><div class="ddicon '+icon+'"></div><progress id="hp' + id + '"' + chp + mhp + '></progress><label id="label'+id+'">' + title + '</label><br /></div>');
				}
				if(maxHealth != -1){
					$('#hp' + id).attr('data-hp',currentHealth).attr('value',currentHealth+10).attr('max',maxHealth+10).attr('title',currentHealth + '/' + maxHealth);
				}else{
					$('#hp' + id).removeAttr('data-hp').removeAttr('value').removeAttr('max').removeAttr('title')
				}
				
				$('#label'+id).css('color',color);
			}
			$('.unit').not(unitId).remove()
			$('.holder').not(healthId).remove();
			//console.log(unitId);
		}
		
		function updatePosition(e){
			var x = $(e.target).css('left');
			var y = $(e.target).css('top');
			var jid = $(e.target).attr('id');
			//alert(x);
			$.post('ajaxy.php',{a:'setpos',positionX:x,positionY:y,id:jid},update);
			//alert(1);
			//console.log(updater)
			//update();
		}
		
		function updateMap(data){
			console.log(data.change);
			
			if(!data.change && firstLoad) return false;
			
			firstLoad = true;
			$('#map .map').empty();
			for(var i=0;i<data.rooms.length;i++){
				var room = data.rooms[i];
				var dom = '<span class="room" style="left:' + room.posX + ';top:' + room.posY + ';z-index:' + room.layer + ';"><img src="' + room.link + '" alt="' + room.rid + '" title="' + room.name + '" data-mid="' + data.mid + '" /></span>';
				$('#map .map').append(dom);
			}
			if(dtype != 'DM') {$('#mapInfo').html(data.name);}else{$.post('ajaxy.php',{a:'adminmap'},updateRoomList,'json')};
			//$('#map').prepend('<img src="'+data.link+'" />');
			//alert(currentMap);
			currentMap = data.mid;
			console.log(data.mid);
			//alert(currentMap);
			$.post('ajaxy.php',{a:'mapstatus',status:'none',map:currentMap});
		}
		
		function parseDie(roll){
			roll = roll.toLowerCase();
			if(!roll.match(/^\d{1,2}d\d{1,3}$/)) return false;
			var dieArray = roll.split('d',2);
			var results = Array();
			for(var i = 0; i < dieArray[0]; i++){
				results[i] = Math.floor((Math.random() * dieArray[1]))+1;
			}
			return results;
		}
	</script>
	<style type='text/css'>
	<?php
		$con = db_connect('hekaton','Iliek99cabbages!','hekaton_dwarves');
		
		$q = mysql_query('SELECT name,color FROM users');
		
		while($qq = mysql_fetch_assoc($q)){
			?>
		*[data-user="<?php echo $qq['name']; ?>"]{
			color:<?php echo $qq['color']; ?>
		}
		
			<?php
		}
		
		function db_connect($uname, $pword, $db){	//Returns mysql_connect
			$con = mysql_connect("localhost", $uname, $pword) or die("Une erreure s'est produite lors de la connexion au serveur SQL");
			mysql_select_db($db, $con);
			mysql_set_charset("utf8");
			return $con;
		}
		
		mysql_close($con);
	?>
	</style>
</head>
<body>
	
	<div id="map">
		<div class='map'></div>
	</div>
	<div id="sidebar">
		<div id="self">I am the Self</div>
		<div id="chatbox">
		</div>
		<div id="poster">
			<textarea></textarea>
			<button>Send</button>
		</div>
	</div>
	<div id="health">
	</div>
	<div id="mapInfo">Map Name</div>
	<div id="tools"><a id="addUnit">+</a><br /><input id="die" type='text' /><button>Roll</button></div>
	
	<div id="editUnitForm" class='unitForm' style="display:none">
		<input type="hidden" class="id" value="" />
				<div class='floatRight'>Icon<br /><div class='iconSelector'></div></div>

		<label>Name:  </label><input type='text' class='name' /><br /><br />
		<label>Current HP: </label><input type="number" class='chp' /><br /><br />
		<label>Max HP: </label><input type='text' class='mhp' /><br /><br />
		<label>Size: </label><select class='sizeSelector'><option>tiny</option><option>small</option><option>medium</option><option>large</option><option>colossal</option></select><br />
		<label>Color: </label><input type='text' class='color' value='#007700' /><br />
		<div class="colorSelector"></div>
	</div>
	
	<div id="addUnitForm" class='unitForm' style="display:none">
		<div class='floatRight'>Icon<br /><div class='iconSelector'></div></div>
		<label>Name:  </label><input type='text' class='name' /><br /><br />
		<label>Max HP: </label><input type='text' class='hp' /><br /><br />
		<label>Size: </label><select class='sizeSelector'><option>tiny</option><option>small</option><option>medium</option><option>large</option><option>colossal</option></select><br />
		<label>Color: </label><input type='text' class='color' value='#007700' /><br />
		
		<div class="colorSelector"></div>
	</div>
	
	<div id="login"><center>
		Please login<br />
		<input type='text' />
		</center>
	</div>
</body>
</html>