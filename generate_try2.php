<html>
<head>
<style type="text/css">
* {
	padding: 0;
	margin: 0;
}
table {
	margin: 10px;
}
table td {
	padding: 3px;
}
table td.empty {
	background-color: #ddd;
}
table td.room {
	background-color: #999;
}
table td.first {
	background-color: green;
}
table td.hall {
	background-color: #ccc;
}
table td.other {
	background-color: red;
}
pre {
	margin: 10px;
}
div#grid {
	background-color: #ddd;
	margin: 10px;
	float: left;
}
div#the-grid {
	font-size: 10px;
	font-family: monospace;
	color: white;
}
div#game-info {
	margin: 10px;
	width: 550px;
}
div#game-info p {
	margin-bottom: 7px;
}
</style>
</head>
<body>
<?php

$game_types = array('capture', 'goal');
$game_type = 'random';
if (isset($_GET['g']) && trim($_GET['g']) != '') {
	switch (trim($_GET['g'])) {
		case 'r':
		$game_type = 'random';
		break;
		case 'c':
		$game_type = 'capture';
		break;
		case 'g':
		$game_type = 'goal';
		break;
	}
}

if ($game_type == 'random') {
	$game_type = $game_types[array_rand($game_types)];
}

$game_areas = array('cave', 'dungeon');
$game_area = 'random';
if (isset($_GET['t']) && trim($_GET['t']) != '') {
	switch (trim($_GET['t'])) {
		case 'r':
		$game_area = 'random';
		break;
		case 'cave':
		$game_area = 'cave';
		break;
		case 'dungeon':
		$game_area = 'dungeon';
		break;
	}
}

if ($game_area == 'random') {
	$game_area = $game_areas[array_rand($game_areas)];
}

// set up possible monsters
$monsters = array();
$bosses = array();
$room_types = array();
// monsters are based on names in the D&D Next Bestiary
if ($game_area == 'cave') {
	// choose between mainly "natural" enemies
	$monsters = array('fire beetle', 'bugbear', 'giant centipede', 'gnoll', 'goblin', 'gray ooze', 'kobold', 'orc', 'cave rat', 'dire rat', 'stirge');
	$bosses = array('minotaur', 'ogre', 'kobold trap lord', 'goblin leader', 'orc leader');
	$room_types = array('spring', 'stagnant water pool', 'sinkhole');
} else if ($game_area == 'dungeon') {
	// choose between mainly undead enemies, or randomized, or what...?
	$monsters = array('dark acolyte', 'dark adept', 'gelatinous cube', 'skeleton', 'wight', 'zombie', 'gray ooze', 'kobold');
	$bosses = array('dark priest', 'minotaur');
	$room_types = array('shrine', 'altar', 'barracks', 'storage');
}

$possible_monster_num = array(0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 3);

//echo 'Game type: '.$game_type."\n";
//echo 'Game area: '.$game_area."\n";


// come up with adventure text based on game type and game area
// if "dungeon" game type, specify what kind of dungeon... undead lair, sacred unholy temple, monster hideout
$possible_adventures = array();
$possible_capturables = array('secret tome of ancient knowledge', 'flaming sword of undead wrath', 'spellbook of limitless energy');
$possible_goal_rooms = array("the shrine of Al'gra'zhan, an ancient diety of unspeakable power", "an exit", "a portal to the Netherworld");

if ($game_type == 'goal' && $game_area == 'cave') {
	$possible_adventures[] = "You and your companions are blocked by a giant cliff face. Nearby is a wide cave entrance. Without the means to climb the cliff, and without the time to go around, you must pass through it and find a way beyond. Caves can be home to any number of creatures and lost treasures. Who knows what you may find in the deep...";
} else if ($game_type == 'goal' && $game_area == 'dungeon') {
	$possible_adventures[] = "You and your companions must reach ".$possible_goal_rooms[array_rand($possible_goal_rooms)].", which awaits inside this dungeon, protected by unknown forces of evil.";
}

if (($game_area == 'cave' || $game_area == 'dungeon') && $game_type == 'capture') {
	$possible_adventures[] = "A local townsperson has cited this $game_area as the location of a legendary ".$possible_capturables[array_rand($possible_capturables)]."! Who wouldn't want that? Lurking within is a slew of monsters guarding its existence. It rests in a room deep inside. Capture this item, and escape with it!";
	$possible_adventures[] = "You and your companions overheard in a nearby inn of a long-lost ".$possible_capturables[array_rand($possible_capturables)]." hidden within this $game_area system. Many have tried to recapture it, but all have been lost to the depths of this abyss. Maybe you will be the first to recover it and escape.";
}


// come up with a title!
$possible_name_prefixes = array();
$possible_adverbs = array('dastardly', 'sorrowful', 'unrelenting', 'demonic', 'delightful', 'horrific', 'abundant', 'perpetual', 'moronic', 'obtuse');
$possible_name_suffixes = array('happenstance', 'sorrow', 'pain', 'entropy', 'inconvenience', 'pussyfooting');

if ($game_area == 'cave') {
	$possible_name_prefixes[] = 'Cave of ';
	$possible_name_prefixes[] = 'Sinkhole of ';
} else if ($game_area == 'dungeon') {
	$possible_name_prefixes[] = 'Dungeon of ';
	$possible_name_prefixes[] = 'Unholy Temple of ';
	$possible_name_prefixes[] = 'Undead Shrine of ';
}

$game_name = $possible_name_prefixes[array_rand($possible_name_prefixes)].ucwords($possible_adverbs[array_rand($possible_adverbs)]).' '.ucwords($possible_name_suffixes[array_rand($possible_name_suffixes)]);

echo '<div id="game-info">';
echo '<h1>'.$game_name.'</h1>';
echo '<p>'.$possible_adventures[array_rand($possible_adventures)].'</p>'."\n";
if ($game_type == 'goal') {
	echo '<p>This is a goal-based adventure: you must reach your goal to finish the adventure.</p>';
} else if ($game_type == 'capture') {
	echo '<p>This is a capture adventure: you must capture whatever it is and escape from whence you came.</p>';
}
echo '</div>';

echo '<pre>';

// generate dungeon

$tile_scale = 5; // 1 tile = 5 feet
$unit_radius = 7; // for how far another room should be to offer a connection

$grid_max_width = 100;
$grid_max_height = 50;

$grid_surface_area = $grid_max_height * $grid_max_width;
$max_rooms = ($grid_surface_area/100);
$max_rooms = $max_rooms - mt_rand(0, $max_rooms*0.2);
$rooms_max_surface_area = $grid_surface_area * 0.5;

echo 'Grid dimentions: '.$grid_max_width.'x'.$grid_max_height.', surface area: '.$grid_surface_area."\n";

// init grid
$grid = array();
for ($y = 0; $y < $grid_max_height; $y++) {
	$grid[$y] = array();
	for ($x = 0; $x < $grid_max_width; $x++) {
		$grid[$y][$x] = array();
	}
}

// build rooms
$rooms = array();
$room_surface_area = 0;
$room_num = 0;

while ($room_surface_area < $rooms_max_surface_area) {

	if (count($rooms) >= $max_rooms) {
		$room_surface_area = $rooms_max_surface_area;
	}
	
	$new_room = array();
	
	$new_room['id'] = $room_num;
	$new_room['con_num'] = 0;
	$new_room['cons'] = array();
	$new_room['monsters'] = array();
	$new_room['w'] = mt_rand(3, 10);
	$new_room['h'] = mt_rand(3, 10);
	$new_room['sa'] = $new_room['w'] * $new_room['h'];
	if ($room_num == 0) {
		// first room!
		$new_room['t'] = 'first';
		$new_room['x'] = 0;
		$new_room['y'] = 0;
		$room_color = 'rgb(255, 0, 0)';
	} else {
		$new_room['t'] = 'room';
		if (mt_rand(0, 100) > 80) {
			$new_room['rt'] = $room_types[array_rand($room_types)];
		}
		$new_room['x'] = mt_rand(0, $grid_max_width);
		$new_room['y'] = mt_rand(0, $grid_max_height);
		//$room_color = 'rgb('.rand(10,245).', '.rand(10,245).', '.rand(10,245).')';
		$room_color = 'rgb(180, 180, 180)';
		$num_monsters = $possible_monster_num[array_rand($possible_monster_num)];
		for ($n = 0; $n < $num_monsters; $n++) {
			$new_room['monsters'][] = $monsters[array_rand($monsters)];
		}
	}
	$new_room['c'] = $room_color;
	
	if ($new_room['x'] + $new_room['w'] > $grid_max_width) {
		$x_diff = abs($grid_max_width - ($new_room['x'] + $new_room['w']));
		$new_room['x'] = $new_room['x'] - $x_diff;
	}
	if ($new_room['y'] + $new_room['h'] > $grid_max_height) {
		$y_diff = abs($grid_max_height - ($new_room['y'] + $new_room['h']));
		$new_room['y'] = $new_room['y'] - $y_diff;
	}
	
	// corners
	$new_room['corners'] = array();
	$new_room['corners']['tl'] = array('x' => $new_room['x'], 'y' => $new_room['y']);
	$new_room['corners']['tr'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y']);
	$new_room['corners']['bl'] = array('x' => $new_room['x'], 'y' => $new_room['y'] + $new_room['h']);
	$new_room['corners']['br'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y'] + $new_room['h']);
	
	// collision check
	foreach ($rooms as $room) {
		// go through all this room's corners against that room's corners...
		if ($new_room['corners']['br']['y'] <= $room['corners']['tl']['y']) {
			$collide = false;
		} else if ($new_room['corners']['tl']['y'] >= $room['corners']['br']['y']) {
			$collide = false;
		} else if ($new_room['corners']['tr']['x'] <= $room['corners']['tl']['x']) {
			$collide = false;
		} else if ($new_room['corners']['tl']['x'] >= $room['corners']['tr']['x']) {
			$collide = false;
		} else {
			$collide = true;
		}
		if ($collide) {
			continue 2;
		}
	}
	unset($room);
	
	$room_surface_area += ( $new_room['w'] * $new_room['h'] );
	
	$rooms[] = $new_room;
	$room_num++;
}

// pick a room in the bottom-right to be the "end" room
$below_middle = $grid_max_height/2;
$right_of_middle = $grid_max_width/2;
$rooms_in_lower_right_corner = array();
foreach ($rooms as $room) {
	if ($room['x'] > $right_of_middle && $room['y'] > $below_middle) {
		$rooms_in_lower_right_corner[] = $room['id'];
	}
}

//echo 'Potential end rooms: '.implode(', ', $rooms_in_lower_right_corner)."\n";

$end_room_id = $rooms_in_lower_right_corner[array_rand($rooms_in_lower_right_corner)];

//echo 'End room ID is '.$end_room_id."\n";

$rooms[$end_room_id]['c'] = 'rgb(0, 0, 255)';
$rooms[$end_room_id]['t'] = 'end';
$rooms[$end_room_id]['monsters'][] = $bosses[array_rand($bosses)];

// see what is already connected
foreach ($rooms as &$room) {
	foreach ($rooms as $other_room) {
		
		if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) {
			continue;
		}
		
		$connected = false;
		
		// see if $room left edge is up against anyone's right edge
		if ($room['corners']['tl']['x'] == $other_room['corners']['tr']['x']) {
			if ($other_room['corners']['tr']['y'] <= $room['corners']['tr']['y'] && $room['corners']['tr']['y'] < $other_room['corners']['br']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (1)'."\n";
			} else if ($room['corners']['tl']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['tr']['y'] < $room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (2)'."\n";
			} else if ($other_room['corners']['br']['y'] <= $room['corners']['tl']['y'] && $room['corners']['bl']['y'] <= $other_room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (3)'."\n";
			} else if ($room['corners']['tl']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['br']['y'] <= $room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (4)'."\n";
			}
		}
		
		// see if $room right edge is up against anyone's left edge
		if ($room['corners']['tr']['x'] == $other_room['corners']['tl']['x']) {
			if ($room['corners']['tr']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['tr']['y'] < $room['corners']['br']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (1)'."\n";
			} else if ($other_room['corners']['tl']['y'] <= $room['corners']['tr']['y'] && $room['corners']['tr']['y'] < $other_room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (2)'."\n";
			} else if ($room['corners']['br']['y'] <= $other_room['corners']['tl']['y'] && $other_room['corners']['bl']['y'] <= $room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (3)'."\n";
			} else if ($other_room['corners']['tl']['y'] <= $room['corners']['tr']['y'] && $room['corners']['br']['y'] <= $other_room['corners']['bl']['y']) {
				$connected = true;
				//echo 'first room is being touched on the left side by second room (4)'."\n";
			}
		}
		
		// see if $room top edge is up against anyone's bottom edge
		if ($room['corners']['tl']['y'] == $other_room['corners']['bl']['y']) {
			if ($room['corners']['tl']['x'] <= $other_room['corners']['bl']['x'] && $other_room['corners']['bl']['x'] < $room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the top edge by second room (1)'."\n";
			} else if ($room['corners']['tl']['x'] < $other_room['corners']['br']['x'] && $other_room['corners']['br']['x'] <= $room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the top edge by second room (2)'."\n";
			} else if ($room['corners']['tl']['x'] <= $other_room['corners']['bl']['x'] && $other_room['corners']['br']['x'] <= $room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the top edge by second room (3)'."\n";
			} else if ($other_room['corners']['bl']['x'] <= $room['corners']['tl']['x'] && $room['corners']['tr']['x'] <= $other_room['corners']['bl']['x']) {
				$connected = true;
				//echo 'first room is being touched on the top edge by second room (4)'."\n";
			}
		}
		
		// see if $room bottom edge is up against anyone's top edge
		if ($room['corners']['bl']['y'] == $other_room['corners']['tr']['y']) {
			if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $room['corners']['bl']['x'] < $other_room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the bottom edge by second room (1)'."\n";
			} else if ($other_room['corners']['tl']['x'] < $room['corners']['br']['x'] && $room['corners']['br']['x'] <= $other_room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the bottom edge by second room (2)'."\n";
			} else if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $room['corners']['br']['x'] <= $other_room['corners']['tr']['x']) {
				$connected = true;
				//echo 'first room is being touched on the bottom edge by second room (3)'."\n";
			} else if ($room['corners']['bl']['x'] <= $other_room['corners']['tl']['x'] && $other_room['corners']['tr']['x'] <= $room['corners']['bl']['x']) {
				$connected = true;
				//echo 'first room is being touched on the bottom edge by second room (4)'."\n";
			}
		}
		if ($connected) {
			//echo $room['id'].' is connected to '.$other_room['id']."\n";
			$room['cons'][] = $other_room['id'];
			$room['con_num']++;
		}
	}
}
unset($room);

// do hallways
$lines = array();
foreach ($rooms as &$room) {
	if ($room['con_num'] == 0) {
		// if there is another room within a certain number of units (determined above)
		foreach ($rooms as $other_room) {
			if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) {
				continue;
			}
			foreach ($room['corners'] as $room_corner_key => $room_corner) {
				foreach ($other_room['corners'] as $other_room_corner_key => $other_room_corner) {
					$dx = $other_room_corner['x'] - $room_corner['x'];
					$dy = $other_room_corner['y'] - $room_corner['y'];
					if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
						//echo $room['id'].'\'s corner '.$room_corner_key.' 5 unit radius collides with '.$nearest_id.'\'s '.$other_room_corner_key.'!'."\n";
						$new_line = array();
						$new_line['x1'] = $room_corner['x'];
						$new_line['y1'] = $room_corner['y'];
						$new_line['x2'] = $other_room_corner['x'];
						$new_line['y2'] = $other_room_corner['y'];
						$lines[] = $new_line;
						$room['cons'][] = $other_room['id'];
						$room['con_num']++;
					}
				}
			}
		}
	}
}
unset($room);

/*
// measure connections from origin
$room_graph = array();

function room_recursion($the_room) {
	global $rooms;
	$room_connections = array();
	if (isset($the_room['cons']) && count($the_room['cons']) > 0) {
		foreach ($the_room['cons'] as $connection) {
			//echo $the_room['id'].' connected to '.$connection."\n";
			if (count($rooms[$connection]['cons']) > 0) {
				$room_connections[] = room_recursion($rooms[$connection]);
			} else {
				//echo $the_room['id'].' connected to '.$connection."\n";
				$room_connections[] = $connection;
			}
		}
	} else {
		$room_connections = $the_room['id'];
	}
	return $room_connections;
}

foreach ($rooms as &$room) {
	$room['cons'] = array_values(array_unique($room['cons']));
	if ($room['id'] == 0 && count($room['cons']) == 0) {
		echo 'First room has no connections... not usable!'."\n";
	} else if (count($room['cons']) > 0) {
		//echo 'Checking '.$room['id']."\n";
		$room_graph[] = room_recursion($room);
	} else {
		echo $room['id'].' has no connections... NOT GOOD!'."\n";
	}
}
unset($room);

print_r($room_graph);
*/

foreach ($rooms as $room) {
	if ($room['id'] == 0 && count($room['cons']) == 0) {
		echo 'First room has no connections... trying to make one!'."\n";
		foreach ($rooms as $other_room) {
			if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) {
				continue;
			}
			$unit_radius = 40;
			foreach ($room['corners'] as $room_corner_key => $room_corner) {
				foreach ($other_room['corners'] as $other_room_corner_key => $other_room_corner) {
					$dx = $other_room_corner['x'] - $room_corner['x'];
					$dy = $other_room_corner['y'] - $room_corner['y'];
					if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
						//echo $room['id'].'\'s corner '.$room_corner_key.' 5 unit radius collides with '.$nearest_id.'\'s '.$other_room_corner_key.'!'."\n";
						$new_line = array();
						$new_line['x1'] = $room_corner['x'];
						$new_line['y1'] = $room_corner['y'];
						$new_line['x2'] = $other_room_corner['x'];
						$new_line['y2'] = $other_room_corner['y'];
						$lines[] = $new_line;
						$room['cons'][] = $other_room['id'];
						$room['con_num']++;
						break 3;
					}
				}
			}
		}
	} else if ($room['id'] == $end_room_id && count($room['cons']) == 0) {
		echo 'End room has no connections... trying to make one!'."\n";
		foreach ($rooms as $other_room) {
			if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) {
				continue;
			}
			$unit_radius = 40;
			foreach ($room['corners'] as $room_corner_key => $room_corner) {
				foreach ($other_room['corners'] as $other_room_corner_key => $other_room_corner) {
					$dx = $other_room_corner['x'] - $room_corner['x'];
					$dy = $other_room_corner['y'] - $room_corner['y'];
					if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
						//echo $room['id'].'\'s corner '.$room_corner_key.' 5 unit radius collides with '.$nearest_id.'\'s '.$other_room_corner_key.'!'."\n";
						$new_line = array();
						$new_line['x1'] = $room_corner['x'];
						$new_line['y1'] = $room_corner['y'];
						$new_line['x2'] = $other_room_corner['x'];
						$new_line['y2'] = $other_room_corner['y'];
						$lines[] = $new_line;
						$room['cons'][] = $other_room['id'];
						$room['con_num']++;
						break 3;
					}
				}
			}
		}
	} else if ($room['id'] != $end_room_id && count($room['cons']) == 0) {
		echo $room['id'].' has no connections... not good, but whatevs.'."\n";
	}
}

echo count($rooms).' rooms.'."\n";

foreach ($rooms as $room) {
	if (count($room['monsters']) > 0) {
		echo 'Room #'.$room['id'].' monsters: '.implode(', ', $room['monsters'])."\n";
	}
	if (isset($room['rt'])) {
		echo 'Room #'.$room['id'].' is type: '.$room['rt']."\n";
	}
}

//print_r($rooms);

echo '</pre>';

// display grid
echo '<div id="the-grid" style="position:absolute;top:100px;left:600px;width:'.$grid_max_width*$tile_scale.'px;height:'.$grid_max_height*$tile_scale.'px;">';
echo '<canvas id="a" width="'.$grid_max_width*$tile_scale.'" height="'.$grid_max_height*$tile_scale.'"></canvas>';
echo '</div>';

?>
<script type="text/javascript">
var a_canvas = document.getElementById('a');
var a = a_canvas.getContext('2d');
a.fillStyle = "#000000";
a.strokeStyle = "#ccc";
a.lineWidth = 0.5;
a.font = "12px monospace";
for (var x = 0.5; x < <?php echo $grid_max_width*$tile_scale; ?>; x += <?php echo $tile_scale; ?>) {
	a.beginPath();
	a.moveTo(x, 0);
	a.lineTo(x, <?php echo $grid_max_height*$tile_scale; ?>);
	a.stroke();
	a.closePath();
}
for (var y = 0.5; y < <?php echo $grid_max_height*$tile_scale; ?>; y += <?php echo $tile_scale; ?>) {
	a.beginPath();
	a.moveTo(0, y);
	a.lineTo(<?php echo $grid_max_width*$tile_scale; ?>, y);
	a.stroke();
	a.closePath();
}


a.strokeStyle = "#000";
a.lineCap = "round";
a.lineWidth = <?php echo $tile_scale; ?>;

<?php
foreach ($lines as $line) {
	echo 'a.beginPath();'."\n";
	echo 'a.moveTo('.$line['x1']*$tile_scale.', '.$line['y1']*$tile_scale.');'."\n";
	echo 'a.lineTo('.$line['x2']*$tile_scale.', '.$line['y2']*$tile_scale.');'."\n";
	echo 'a.stroke();'."\n";
	echo 'a.closePath();'."\n";
}
?>

<?php

foreach ($rooms as $room) {
	//echo '<div style="background-color:#999;position:absolute;top:'.$room['y']*$tile_scale.';left:'.$room['x']*$tile_scale.';width:'.$room['w']*$tile_scale.';height:'.$room['h']*$tile_scale.';">';
	echo 'a.fillStyle = "'.$room['c'].'";'."\n";
	//echo 'a.fillStyle = "#999";'."\n";
	echo 'a.fillRect('.$room['x']*$tile_scale.', '.$room['y']*$tile_scale.', '.$room['w']*$tile_scale.', '.$room['h']*$tile_scale.');'."\n";
	//echo 'a.fillStyle = "white";'."\n";
	//echo 'a.fillText("'.$room['id'].'", '.$room['x']*$tile_scale.', '.(10+$room['y']*$tile_scale).');'."\n";
}

?>

a.fillStyle = "#999";

//var counter_limit = 2;

for (var x = 0; x < <?php echo $grid_max_width*$tile_scale; ?>; x += <?php echo $tile_scale; ?>) {
	//var ycounter = 0;
	for (var y = 0; y < <?php echo $grid_max_height*$tile_scale; ?>; y += <?php echo $tile_scale; ?>) {
		//if (ycounter == counter_limit) {
			//a.fillRect(x, y, 5, 5);
			//break;
		//}
		//console.log('x:'+x+', y:'+y);
		var any_black = false;
		var pixelData = a.getImageData(x, y, 5, 5);
		//console.log(pixelData.data);
		for (var i = 0; i < pixelData.data.length; i += 4) {
			//console.log(x + ' ' + y + ' ' + pixelData.data[i] + ' ' + pixelData.data[i+1] + ' ' + pixelData.data[i+2] + ' ' + pixelData.data[i+3]);
			if (pixelData.data[i] * 1 == 0 && pixelData.data[i+1] * 1 == 0 && pixelData.data[i+2] * 1 == 0 && pixelData.data[i+3] == 255) {
				//console.log('HELLO!');
				any_black = true;
			}
		}
		if (any_black) {
			a.fillRect(x, y, <?php echo $tile_scale; ?>, <?php echo $tile_scale; ?>);
		}
		//ycounter++;
	}
	//var pixelData = a.getImageData(1,1,1,1);
	//console.log(pixelData.data);
}

<?php

foreach ($rooms as $room) {
	echo 'a.fillStyle = "white";'."\n";
	echo 'a.fillText("'.$room['id'].'", '.($room['x']*$tile_scale).', '.(10+$room['y']*$tile_scale).');'."\n";
}

?>

</script>
</body>
</html>