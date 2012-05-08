<?php
	db_connect('hekaton','Iliek99cabbages!','hekaton_dwarves');
	
	if(isset($_GET['create'])){
		mysql_query("INSERT INTO maps (name) VALUES ('{$_GET['create']}')");
		header('location:maps.php');
	}
	if(isset($_FILES['room'])){
		if($_FILES['room']['error'] > 0){
			die("Error: {$_FILES['room']['error']}");
		}
		$path = "maps/" . $_FILES["room"]["name"];
		$name = $_POST['name'];
		$mid = $_POST['mid'];
		if(file_exists($path)){
			die('Please rename your file. (File already exists)');
		}
		move_uploaded_file($_FILES['room']['tmp_name'], $path);
		mysql_query("INSERT INTO rooms (mid,name,link) VALUES ($mid,'$name','$path')");
		header('location:maps.php?map=' . $mid);
	}
	if(isset($_GET['map'])){
		$m = mysql_query('SELECT name FROM maps WHERE mid=' . $_GET['map']);
		while($mm = mysql_fetch_row($m)){
			$mapName = $mm[0];
			break;
		} 
	}
	
	if(isset($_POST['action'])){
		if($_POST['action'] == 'Delete'){
			mysql_query('DELETE FROM rooms WHERE rid=' . $_POST['rid']);
		}else{
			mysql_query("UPDATE rooms SET name='{$_POST['name']}',layer={$_POST['layer']} WHERE rid=" . $_POST['rid']);
		}
		//echo "UPDATE rooms SET name='{$_POST['name']}',layer={$_POST['layer']} WHERE rid=" . $_POST['rid'];
		//die(mysql_error());
		header('location:maps.php?map=' . $_POST['map']);
	}
?>
<html>
<head>
<script type='text/javascript' src='js/jquery-1.7.1.min.js'></script>
	<script type='text/javascript' src='js/jquery-ui-1.8.18.custom.min.js'></script>
	<link href="css/smoothness/jquery-ui-1.8.18.custom.css" type='text/css' rel='stylesheet' />
<style>
*{
	margin:0;
	padding:0;
	font-family: Arial;
}
html,body{
	width:100%;
	height:100%;
}
#forms{
	height:100%;
	background:#aeaeae;
	position:fixed;
	left:0;
	color:#e6e6e6;
	padding: 15px;
	z-index:5;
	width:300px;
}
#forms form,#roomInfo{
	display:inline-block;
	border:1px solid #e6e6e6;
	border-radius:5px;
	padding:15px;
	margin-bottom:15px;
}

#roomInfo{
	position:absolute;
	bottom:15px;
	left:15px;
}

#mapinfo{
	positon:fixed;
	top:0;
	right:20px;
	padding-right:15px;
	text-align:right;
	color:#aeaeae;
	font-style:italic;
}

#map{
	z-index:0;
	position:absolute;
	left:350px;
}

#map .map img{
	position:absolute;
}

.floatRight{
	float:right;
}

.red{
	background:red;
}

.blue{
	background:blue;
}

.green{
	background:green;
}

.layer{
	width:10px;
	height:10px;
	display:inline-block;
}

.room{
	display:inline-block;
}

.overlay{
	width:100%;
	height:100%;
}

</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#map .map .room').draggable().on('dragstop',function(){
		var mid = $(this).find('img').attr('data-mid');
		var rid = $(this).find('img').attr('alt');
		var x = $(this).css('left');
		var y = $(this).css('top');
		$.post('ajaxy.php',{a:'setroom',map:mid,room:rid,posx:x,posy:y});
	})/*.hover(function(){
		$(this).prepend('<div class="overlay"></div>');
		switch($(this).css('z-index')){
			case 3:
				$(this).find('.overlay').css('background-color','rgba(255,0,0,0.25)');
				break;
			case 2:
				$(this).find('.overlay').css('background-color','rgba(0,255,0,0.25');
				break
			case 1:
				$(this).find('.overlay').css('background-color','rgba(0,0,255,0.25');
				break;
		}
	},function(){
		$(this).find('.overlay').remove();
	})*/.click(function(){
		$('#roomInfo input[name="rid"]').val($(this).find('img').attr('alt'));
		$('#roomInfo input[name="name"]').val($(this).find('img').attr('title'));
		$($('#roomInfo input[type="radio"]')[3-$(this).css('z-index')]).click();
	});
	$('#roomInfo input[type="submit"][value="Delete"]').click(function(e){
		e.preventDefault();
		var d = confirm('Are you sure you want to delete this room?');
		if(d) $(this).off('click').click();
	});
});
</script>
</head>
<body>
<div id="forms">
<form action="">
<p>Select a map</p>
<select name='map'>
<?php
function db_connect($uname, $pword, $db){	//Returns mysql_connect
	$con = mysql_connect("localhost", $uname, $pword) or die("Une erreure s'est produite lors de la connexion au serveur SQL");
	mysql_select_db($db, $con);
	mysql_set_charset("utf8");
	return $con;
}

	$q = mysql_query('SELECT * FROM maps');
	while($qq = mysql_fetch_assoc($q)){
		?>
	<option value="<?php echo $qq['mid']; ?>"><?php echo $qq['name']?></option>
		<?php
	}
?>
</select><br />
<input type="submit" value='Select' />
<input type="hidden" name="" value="" />
</form>
<br />
<form action="">
<p>Create a new map</p>
<label>Name :</label> <input name="create" type="text" /><br />
<input type="submit" value='Create' />
</form>
<br />
<?php
	if(isset($_GET['map'])){?>
<form action="" method="POST" enctype="multipart/form-data">
<p>Upload a new room</p>
<label>Name :</label><input type="text" name='name' /><br />
<input type="file" name='room' /><br />
<input type="submit" value='Upload' />
<input type="hidden" name="mid" value="<?php echo $_GET['map']; ?>" />
</form>

<div id="roomInfo">
<form action='' method='post'>
<label>Room ID: </label><input type='text' disabled='disabled' name='rid' /><br />
<label>Room Name: </label><input type='text' name='name' /><br />
<label>Room Layer :</label>
<div class="floatRight">
<input type="radio" name="layer" value="3" /><div class="layer red"></div> Top<br />
<input type="radio" name="layer" value="2" /><div class="layer green"></div> Mid<br />
<input type="radio" name="layer" value="1" /><div class="layer blue"></div> Bottom<br />
</div><br />
<input type='hidden' name='map' value='<?php echo $_GET['map']; ?>' />
<input type='hidden' name='rid' value='' />
<input type="submit" name='action' value="Edit" />
<input type="submit" name='action' value="Delete" />
</form>
</div>
<?php }?>
</div>
<div id="editArea">
<div id="mapinfo"><?php if(isset($mapName)) echo $mapName; ?></div>
<div id="map">
	<div class="map">
<?php
	if(isset($_GET['map'])){
		$r = mysql_query("SELECT * FROM rooms WHERE mid=" . $_GET['map'] . ' ORDER BY rid DESC');
		while($rr = mysql_fetch_assoc($r)){
			echo "<span class='room' style='left:{$rr['posX']};top:{$rr['posY']};z-index:{$rr['layer']}'><img data-mid='{$rr['mid']}' src='{$rr['link']}' alt='{$rr['rid']}' title='{$rr['name']}' /></span>";
		}
	}
?>
	</div>
</div>
</div>