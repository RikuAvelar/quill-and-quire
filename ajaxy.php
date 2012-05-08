<?php
	
	switch($_POST['a']){
		case 'mapstatus':
			mysql_query("UPDATE maps SET status='{$_POST['status']}' WHERE mid=" . $_POST['map']);
			echo mysql_error();
			exit;
			break;
		case 'listmaps':
			$sql = '(SELECT name,mid FROM maps WHERE current = 1) UNION (SELECT name,mid FROM maps WHERE current = 0)';
			$q = mysql_query($sql) or die(mysql_error());
			$o = '{"maps":[';
			$json = Array(
				maps => Array()
			);
			while($qq = mysql_fetch_assoc($q)){
				array_push($json['maps'],$qq);
				//$o .= "['{$qq['name']}','{$qq['link']}'],";
			}
			
			//$o .= '["none",""]]}';
			//echo $o;
			echo json_encode($json);
			exit;
			break;
		
		case 'selectmap':
			$mid = $_POST['mid'];
			
			mysql_query('UPDATE maps SET current=0 WHERE current=1');
			mysql_query("UPDATE maps SET current=1 WHERE mid=$mid");
			exit;
			break;
			
		case 'getchat':
			$type = $_POST['type'];
			$ts = time() - 86400;
			$sql = "SELECT name,content,hidden FROM chat INNER JOIN users ON chat.uid = users.uid ORDER BY chat.timestamp DESC LIMIT 20";
			$q = mysql_query($sql);
			$o = '{"chat":[';
			$json = Array(
				chat => Array()
			);
			$json['status'] = 'none';
			while($qq = mysql_fetch_assoc($q)){
				if(($type != 'DM')  &&($qq['hidden'] == '1')) continue;
				array_push($json['chat'],$qq['content']);
				$json['status'] = 'new';
			}
			
			if($json['status'] == 'new'){
				mysql_query("DELETE FROM chat WHERE timestamp <= $ts");
			}
			echo json_encode($json);
			exit;
			break;
			
		case 'login':
			$uname = $_POST['uname'];
			$q = mysql_query("SELECT * FROM users WHERE name LIKE '$uname'");
				
			$i = 0;
			while($qq = mysql_fetch_assoc($q)){
				echo json_encode($qq);
				//echo "{'uid':{$qq['uid']},'name':'{$qq['name']}','type':'{$qq['type']}'}";
				$i++;
			}
			
			if($i == 0){
				echo '{"uid":"0","name":"NONE","type":false}';
			}
			exit;
			break;
			
		case 'postchat':
			$user = $_POST['uname'];
			$uid = $_POST['uid'];
			$type = $_POST['type'];
			$content = htmlspecialchars($_POST['content'],ENT_QUOTES,"UTF-8");
			$ts = time();
			$hide = 0;
			
			if(isset($_POST['hidden'])){
				$hide = $_POST['hidden'];
			}
			
			if($type == 'dice'){
				$content = str_replace('[b]','<strong>',$content);
				$content = str_replace('[/b]','</strong>',$content);
				$content = '<span data-user="' . $user . '">' . $user . ' ' . $content . '</span>';
			}else{
				$content = '<span data-user="' . $user . '">' . $user . ' says:</span><br />' . $content;
			}
			
			mysql_query("INSERT INTO chat (uid,content,timestamp,hidden) VALUES ('$uid','$content','$ts',$hide)");
			exit;
			break;
		case 'toggleroom':
			mysql_query("UPDATE rooms SET `show`={$_POST['show']} WHERE rid=" . $_POST['rid']);
			echo mysql_error();
			exit;
			break;
		case 'adminmap':
			$map = Array(
				rooms => Array(),
				change => true
			);
			$new = mysql_query('SELECT maps.mid,maps.name AS map,rooms.rid,rooms.name,rooms.posX,rooms.posY,rooms.layer,rooms.show,rooms.link FROM maps INNER JOIN rooms ON maps.mid=rooms.mid WHERE current=1');
			while($qq = mysql_fetch_assoc($new)){
				$map['name'] = $qq['map'];
				$map['mid'] = $qq['mid'];
				$room = Array(
					rid		=> $qq['rid'],
					name	=> $qq['name'],
					show	=> $qq['show']
				);
				array_push($map['rooms'],$room);
				//echo json_encode($qq);
				//echo "{'mid':{$qq['mid']},'name':'{$qq['name']}','link':'{$qq['link']}','change':true}";
				//exit;
			}
			echo json_encode($map);
			exit;
			break;
		case 'getmap':
			$current = $_POST['current'];
			$mc = mysql_query("SELECT current,status FROM maps WHERE mid=$current");
			$mapcheck = '0';
			$changecheck = 'change';
			if($mc){
				while($mm = mysql_fetch_assoc($mc)){
					$mapcheck = $mm['current'];
					$changecheck = $mm['status'];
					//var_dump($mm);
				}
			}
			$map = Array(
				rooms => Array(),
				change => true,
				mid => $current
			);
			if(($mapcheck == '0') || ($changecheck == 'change')){
				$new = mysql_query('SELECT maps.mid,maps.name AS map,rooms.rid,rooms.name,rooms.posX,rooms.posY,rooms.layer,rooms.show,rooms.link FROM maps INNER JOIN rooms ON maps.mid=rooms.mid WHERE current=1');
				while($qq = mysql_fetch_assoc($new)){
					
					$map['name'] = $qq['map'];
					$map['mid'] = $qq['mid'];
					if($qq['show'] == '0') continue;
					$room = Array(
						rid		=> $qq['rid'],
						name	=> $qq['name'],
						posX	=> $qq['posX'],
						posY	=> $qq['posY'],
						layer	=> $qq['layer'],
						link	=> $qq['link']
					);
					array_push($map['rooms'],$room);
					//echo json_encode($qq);
					//echo "{'mid':{$qq['mid']},'name':'{$qq['name']}','link':'{$qq['link']}','change':true}";
					//exit;
				}
				echo json_encode($map);
				exit;
			}
			
				echo '{"mid":0,"name":"","link":"","change":false}';
				exit;
			
			break;
			
		case 'newunit':
			$name = htmlspecialchars($_POST['name'],ENT_QUOTES,"UTF-8");
			$color = $_POST['color'];
			$health = $_POST['health'];
			$uid = $_POST['uid'];
			$admin = $_POST['admin'];
			$notes = $_POST['note'];
			$icon = $_POST['icon'];
			$size = $_POST['size'];
			
			mysql_query("INSERT INTO markers (uid,title,color,currentHealth,maxHealth,admin,notes,icon,size) VALUES ($uid,'$name','$color',$health,$health,$admin,'$notes','$icon','$size')");
			echo mysql_error();
			exit;
			break;
		
		case 'getunits':
			$ut = $_POST['ut'];
			$check = mysql_query('SELECT * FROM markers');
			if(!$check) die('{units:[],"cancel":true}');
			$o = '{"units":[';
			$json = Array(
				units => Array()
			);
			if($ut != 'DM'){
				$q1 = mysql_query('SELECT * FROM markers WHERE admin=0');
				$q2 = mysql_query('SELECT * FROM markers WHERE admin=1');
				while($qq = mysql_fetch_assoc($q1)){
					$qq['notes'] = '';
					array_push($json['units'],$qq);
					
					//$o .= "[{$qq['jid']},{$qq['uid']},'{$qq['title']}','{$qq['color']}',{$qq['position']},[{$qq['currentHealth']},{$qq['maxHealth']}],'','{$qq['icon']}'],";
				}
				while($qq = mysql_fetch_assoc($q2)){
					$qq['notes'] = '';
					$qq['currentHealth'] = 0;
					$qq['maxHealth'] = -1;
					array_push($json['units'],$qq);
					//$o .= "[{$qq['jid']},{$qq['uid']},'{$qq['title']}','{$qq['color']}',{$qq['position']},[0,-1],'','{$qq['icon']}'],";
				}
				//$o .= '"end"]}';
				//echo $o;
				//exit;
			}else{
				$q = mysql_query('SELECT * FROM markers');
				while($qq = mysql_fetch_assoc($q)){
					array_push($json['units'],$qq);
					//$o .= "[{$qq['jid']},{$qq['uid']},'{$qq['title']}','{$qq['color']}',{$qq['position']},[{$qq['currentHealth']},{$qq['maxHealth']}],'{$qq['notes']}','{$qq['icon']}'],";
				}
			}
			echo json_encode($json);
			exit;
			break;
		
		case 'setunit':
			$name = mysql_real_escape_string($_POST['name']);
			$chp = $_POST['chp'];
			$mhp = $_POST['mhp'];
			$id = $_POST['id'];
			$notes = mysql_real_escape_string($_POST['notes']);
			$color = $_POST['color'];
			$admin = $_POST['admin'];
			$icon = $_POST['icon'];
			$size = $_POST['size'];
			
			mysql_query("UPDATE markers SET size='$size',title='$name',icon='$icon',currentHealth=$chp,maxHealth=$mhp,notes='$notes',color='$color',admin=$admin WHERE jid=$id");
			//echo mysql_error();
			exit;
			break;
		
		case 'deleteunit':
			$id = $_POST['id'];
			
			mysql_query("DELETE FROM markers WHERE jid=$id");
			//echo mysql_error();
			exit;
			break;
		
		case 'setpos':
			$id = $_POST['id'];
			$posX = $_POST['positionX'];
			$posY = $_POST['positionY'];
			
			mysql_query("UPDATE markers SET positionX='$posX',positionY='$posY' WHERE jid=$id");
			//echo "UPDATE markers SET positionX='$posX' positionY='$posY' WHERE jid=$id";
			//echo mysql_error();
			exit;
			break;
			
		case 'getstatus':
			$id = $_POST['id'];
			$q = mysql_query("SELECT status FROM users WHERE uid=$id");
			while($qq = mysql_fetch_assoc($q)){
				echo json_encode($qq);
			}
			
			//echo mysql_error();
			exit;
			break;
		case 'setstatus':
			$id = $_POST['id'];
			$status = $_POST['status'];
			$q = mysql_query("UPDATE users SET status='$status' WHERE uid=$id");
			//$qq = mysql_fetch_assoc($q);
			//echo json_encode($qq);
			exit;
			break;
			
		case 'setsmite':
			$name = $_POST['user'];
			$smite = $_POST['smite'];
			mysql_query("UPDATE users SET status='$smite' WHERE name LIKE '$name'");
			echo mysql_error();
			exit;
			break;
		case 'clearall':
			mysql_query('UPDATE users SET status="none"');
			exit;
			break;
		
		case 'setroom':
			mysql_query("UPDATE rooms SET posX='{$_POST['posx']}',posY='{$_POST['posy']}' WHERE rid={$_POST['room']}");
			exit;
			break;
	}
	
	function db_connect($uname, $pword, $db){	//Returns mysql_connect
		$con = mysql_connect("localhost", $uname, $pword) or die("Une erreure s'est produite lors de la connexion au serveur SQL");
		mysql_select_db($db, $con);
		mysql_set_charset("utf8");
		return $con;
	}
?>