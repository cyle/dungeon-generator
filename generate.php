<?php

/*

	random adventure generator script
		by cyle gage
	
	made with D&D Next playtesting in mind
		- the monster list is based on the Bestiary provided with D&D Next


*/

?>
<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}
body {
	font-family: monospace;
	font-size: 12px;
}
h1 {
	margin-bottom: 10px;
}
pre {
	margin: 10px;
}
div#game-info {
	margin: 10px;
	width: 500px;
}
div#game-info p {
	margin-bottom: 7px;
}
div#the-grid {
	position: absolute;
	left: 550px;
	top: 50px;
}
div#the-rooms {
	width: 500px;
	margin: 10px;
}
div.room-info {
	padding: 10px;
	border: 1px solid #ccc;
	margin-bottom: 10px;
}
div.room {
	background-color: #666;
	color: white;
}
div.first {
	background-color: green;
}
div.last {
	background-color: red;
}
div.hallway {
	background-color: #999;
	color: white;
	-moz-box-sizing: border-box; 
	-webkit-box-sizing: border-box; 
	box-sizing: border-box;
}
div.hallway.v {
	border-top: 2px solid black;
	border-bottom: 2px solid black;
}
div.hallway.h {
	border-left: 2px solid black;
	border-right: 2px solid black;
}
.error {
	color: red;
}
</style>
</head>
<body>
<?php

/*

	set game type and theme options based on input

*/

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

$game_intended_level = 1;
if (isset($_GET['l']) && is_numeric($_GET['l']) && $_GET['l'] * 1 > 0) {
	$game_intended_level = (int) $_GET['l'] * 1;
}
if ($game_intended_level > 5) {
	die('Sorry, cannot make an adventure for above level 5 yet.');
}

/*

	set up possible monsters

*/

$monsters = array();
$bosses = array();

// below is a template with all the monsters
// this will be reset based on the type of game area, set below
// all monsters tiered out by level (so $monsters[1] means level 1 monsters) (a level 1 elite is counted as a level 2)
$monsters[1] = array('fire beetle', 'giant centipede', 'goblin', 'human commoner', 'kobold', 'winged kobold', 'cave rat', 'dire rat', 'stirge');
$monsters[2] = array('gelatinous cube', 'goblin leader', 'kobold dragonshield', 'kobold trap lord', 'skeleton', 'zombie');
$monsters[3] = array('dark acolyte', 'hobgoblin', 'human berserker', 'orc');
$monsters[4] = array('gnoll', 'dark priest', 'drow', 'gray ooze', 'hobgoblin leader', 'ogre', 'orc leader', 'wight');
$monsters[5] = array('dark adept', 'gnoll leader', 'medusa', 'orog', 'owlbear');
$monsters[6] = array('bugbear', 'minotaur');
$monsters[7] = array('troll');

$room_types = array();
// monsters are based on names in the D&D Next Bestiary
if ($game_area == 'cave') {
	// choose between mainly "natural" enemies
	$room_types = array('spring', 'stagnant water pool', 'sinkhole');
	$monsters = array();
	$monsters[1] = array('fire beetle', 'giant centipede', 'goblin', 'kobold', 'winged kobold', 'cave rat', 'dire rat', 'stirge');
	$monsters[2] = array('gelatinous cube', 'goblin leader', 'kobold dragonshield', 'kobold trap lord');
	$monsters[3] = array('hobgoblin', 'human berserker', 'orc');
	$monsters[4] = array('gnoll', 'drow', 'gray ooze', 'hobgoblin leader', 'ogre', 'orc leader');
	$monsters[5] = array('gnoll leader', 'orog', 'owlbear');
	$monsters[6] = array('bugbear', 'minotaur');
	$monsters[7] = array('troll');
} else if ($game_area == 'dungeon') {
	// choose between mainly undead enemies, or randomized, or what...?
	$monsters = array();
	$monsters[1] = array('fire beetle', 'goblin', 'kobold', 'dire rat', 'stirge');
	$monsters[2] = array('gelatinous cube', 'goblin leader', 'kobold dragonshield', 'kobold trap lord', 'skeleton', 'zombie');
	$monsters[3] = array('dark acolyte', 'human berserker', 'orc');
	$monsters[4] = array('dark priest', 'drow', 'gray ooze', 'orc leader', 'wight');
	$monsters[5] = array('dark adept', 'medusa');
	$monsters[6] = array('minotaur');
	$monsters[7] = array('troll');
	$room_types = array('shrine', 'altar', 'barracks', 'storage');
}

// set $bosses to monsters $game_intended_level+1 and $game_intended_level+2 to potentially increase difficulty
$bosses = array_merge($monsters[$game_intended_level+1], $monsters[$game_intended_level+2]);

// array of possible monsters per room (weighted)
$possible_monster_num = array(0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 3);

//echo 'Game type: '.$game_type."\n";
//echo 'Game area: '.$game_area."\n";

/*

	set up adventure text based on game type and theme

*/

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


/*

	generate adventure title!

*/

$possible_name_prefixes = array();
$possible_adverbs = array('dastardly', 'sorrowful', 'unrelenting', 'demonic', 'delightful', 'horrific', 'abundant', 'perpetual', 'moronic', 'obtuse', 'gleeful', 'upsetting');
$possible_name_suffixes = array('happenstance', 'sorrow', 'pain', 'entropy', 'inconvenience', 'pussyfooting', 'bewilderment', 'nightmares', 'negligence', 'silence', 'mystery');

if ($game_area == 'cave') {
	$possible_name_prefixes[] = 'cave';
	$possible_name_prefixes[] = 'sinkhole';
	$possible_name_prefixes[] = 'grotto';
	$possible_name_prefixes[] = 'dark caverns';
	$possible_name_prefixes[] = 'dank cistern';
} else if ($game_area == 'dungeon') {
	$possible_name_prefixes[] = 'dungeon';
	$possible_name_prefixes[] = 'shrine';
	$possible_name_prefixes[] = 'unholy temple';
	$possible_name_prefixes[] = 'undead shrine';
	$possible_name_prefixes[] = 'forsaken halls';
}

$game_name = 'The '.ucwords($possible_name_prefixes[array_rand($possible_name_prefixes)]).' of '.ucwords($possible_adverbs[array_rand($possible_adverbs)]).' '.ucwords($possible_name_suffixes[array_rand($possible_name_suffixes)]);

/*

	okay cool, now show the game info

*/

?>

<div id="game-info">
<h1><?php echo $game_name; ?></h1>
<p><?php echo $possible_adventures[array_rand($possible_adventures)]; ?></p>
<?php
if ($game_type == 'goal') {
	echo '<p>This is a goal-based adventure: you must reach your goal to finish the adventure.</p>'."\n";
} else if ($game_type == 'capture') {
	echo '<p>This is a capture adventure: you must capture whatever it is (entering the room captures the item) and escape via the first room.</p>'."\n";
}
?>
<p>Rooms are connected to each other if adjacent; hallways are only connected from one room to another. A wall is implied if a hallway runs adjacent to a room.</p>
<p>Click on a hallway to go down it to the next room. Monsters appear immediately upon entering!</p>
<p>Adventure intended for level <?php echo $game_intended_level; ?> characters.</p>
</div>

<pre>
<?php

/*

	set up dungeon dimensions based on input

*/

$tile_scale = 5; // 1 tile = 5 pixels
if (isset($_GET['w']) && is_numeric($_GET['w']) && $_GET['w'] > 0) {
	$max_width = (int) $_GET['w'] * 1;
} else {
	$max_width = 100;
}
if (isset($_GET['h']) && is_numeric($_GET['h']) && $_GET['h'] > 0) {
	$max_height = (int) $_GET['h'] * 1;
} else {
	$max_height = 50;
}
$total_surface_area = $max_width * $max_height;
if (isset($_GET['r']) && is_numeric($_GET['r']) && $_GET['r'] > 0) {
	$max_rooms = (int) $_GET['r'] * 1;
} else {
	if ($total_surface_area <= 1000) {
		$max_rooms = floor($total_surface_area/25);
	} else {
		$max_rooms = floor($total_surface_area/100);
	}
	
}

echo 'Grid dimentions: '.$max_width.'x'.$max_height.', surface area: '.$total_surface_area."\n";
echo 'Max rooms: '.$max_rooms."\n";

/*

	set up rooms based on dimensions

*/

$rooms = array();
$last_corners = array();
$last_id = 0;

for ($room_id = 0; $room_id < $max_rooms; $room_id++) {
	
	// make a new room
	$new_room = array();
	
	// room defaults
	$new_room['id'] = $room_id;
	$new_room['cons'] = array();
	$new_room['adjacent'] = array();
	if ($total_surface_area <= 1000) {
		$new_room['w'] = mt_rand(2, 5);
		$new_room['h'] = mt_rand(2, 5);
	} else {
		$new_room['w'] = mt_rand(2, 9);
		$new_room['h'] = mt_rand(2, 9);
	}
	$new_room['sa'] = $new_room['w'] * $new_room['h']; // surface area
	$new_room['corners'] = array();
	$new_room['type'] = 'normal';
	$new_room['monsters'] = array();
	
	// if first room, set it in the top left corner
	if ($room_id == 0) {
		$new_room['x'] = 0;
		$new_room['y'] = 0;
		$new_room['corners']['tl'] = array('x' => $new_room['x'], 'y' => $new_room['y']);
		$new_room['corners']['tr'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y']);
		$new_room['corners']['bl'] = array('x' => $new_room['x'], 'y' => $new_room['y'] + $new_room['h']);
		$new_room['corners']['br'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y'] + $new_room['h']);
	} else {
		// put the room somewhere based on the last one
		$placed = false;
		$placed_tries = 0;
		$placed_max_tries = 5;
		while (!$placed) {
			$placed_tries++;
			if ($placed_tries > $placed_max_tries) { // only try a limited number of times (so as to not loop forever)
				continue 2;
			}
			$which_side_from_last = mt_rand(1, 4);
			switch ($which_side_from_last) {
				case 1: // above last room, x = old x, y = old y--
				$new_room['x'] = $last_corners['tl']['x'] + mt_rand(-5, 5);
				$new_room['y'] = $last_corners['tl']['y'] - $new_room['h'] - mt_rand(0, 10);
				break;
				case 2: // right of last room, x = old x++, y = old y
				$new_room['x'] = $last_corners['tr']['x'] + mt_rand(0, 10);
				$new_room['y'] = $last_corners['tr']['y'] + mt_rand(-5, 5);
				break;
				case 3: // below last room, x = old x, y = old y++
				$new_room['x'] = $last_corners['bl']['x'] + mt_rand(-5, 5);
				$new_room['y'] = $last_corners['bl']['y'] + mt_rand(0, 10);
				break;
				case 4: // left of last room, x = old x--, y = old y
				$new_room['x'] = $last_corners['tl']['x'] - $new_room['w'] - mt_rand(0, 10);
				$new_room['y'] = $last_corners['tl']['y'] + mt_rand(-5, 5);
				break;
			}
			// check to make sure it's not x < 0 or y < 0
			if ($new_room['x'] < 0 || $new_room['y'] < 0) {
				continue;
			}
			// set up room's corner coordinates
			$new_room['corners']['tl'] = array('x' => $new_room['x'], 'y' => $new_room['y']);
			$new_room['corners']['tr'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y']);
			$new_room['corners']['bl'] = array('x' => $new_room['x'], 'y' => $new_room['y'] + $new_room['h']);
			$new_room['corners']['br'] = array('x' => $new_room['x'] + $new_room['w'], 'y' => $new_room['y'] + $new_room['h']);
			// make sure it's not beyond the max bounds (if any)
			if ($new_room['corners']['tr']['x'] > $max_width || $new_room['corners']['br']['y'] > $max_height) {
				continue;
			}
			// check to make sure it's not colliding with anything else
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
			$placed = true; // cool!
		}
	}
	
	$last_corners = $new_room['corners'];
	$rooms[] = $new_room;
	
}

// reset room ID keys
$rooms = array_values($rooms);
$max_rooms = 0;
foreach ($rooms as $room_key => &$room_val) {
	$rooms[$room_key]['id'] = $room_key;
	$max_rooms++;
}
unset($room_val, $room_key);

/*

	set up rooms types and monsters

*/

foreach ($rooms as &$room) {
	if ($room['id'] == 0) { // room id 0 is always first
		$room['type'] = 'first';
	} else {
		// by chance, make this room different
		if (mt_rand(0, 100) > 60) {
			$room['type'] = $room_types[array_rand($room_types)];
		}
		// add monsters
		$num_monsters = $possible_monster_num[array_rand($possible_monster_num)];
		for ($n = 0; $n < $num_monsters; $n++) {
			if (mt_rand(0, 100) > 85) { // small chance of making a hard enemy
				$room['monsters'][] = $monsters[$game_intended_level+1][array_rand($monsters[$game_intended_level+1])];
			} else {
				$room['monsters'][] = $monsters[$game_intended_level][array_rand($monsters[$game_intended_level])];
			}
		}
		if ($room['id'] == $max_rooms - 1) { // last room created is the last room of the adventure
			$room['type'] = 'last';
			$room['monsters'][] = $bosses[array_rand($bosses)];
		}
	}
}
unset($room);

echo 'Actual rooms: '.$max_rooms."\n";

/*

	figure out what rooms are adjacent to each other already

*/

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
			} else if ($room['corners']['tl']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['tr']['y'] < $room['corners']['bl']['y']) {
				$connected = true;
			} else if ($other_room['corners']['br']['y'] <= $room['corners']['tl']['y'] && $room['corners']['bl']['y'] <= $other_room['corners']['bl']['y']) {
				$connected = true;
			} else if ($room['corners']['tl']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['br']['y'] <= $room['corners']['bl']['y']) {
				$connected = true;
			}
		}
		// see if $room right edge is up against anyone's left edge
		if ($room['corners']['tr']['x'] == $other_room['corners']['tl']['x']) {
			if ($room['corners']['tr']['y'] <= $other_room['corners']['tr']['y'] && $other_room['corners']['tr']['y'] < $room['corners']['br']['y']) {
				$connected = true;
			} else if ($other_room['corners']['tl']['y'] <= $room['corners']['tr']['y'] && $room['corners']['tr']['y'] < $other_room['corners']['bl']['y']) {
				$connected = true;
			} else if ($room['corners']['br']['y'] <= $other_room['corners']['tl']['y'] && $other_room['corners']['bl']['y'] <= $room['corners']['bl']['y']) {
				$connected = true;
			} else if ($other_room['corners']['tl']['y'] <= $room['corners']['tr']['y'] && $room['corners']['br']['y'] <= $other_room['corners']['bl']['y']) {
				$connected = true;
			}
		}
		// see if $room top edge is up against anyone's bottom edge
		if ($room['corners']['tl']['y'] == $other_room['corners']['bl']['y']) {
			if ($room['corners']['tl']['x'] <= $other_room['corners']['bl']['x'] && $other_room['corners']['bl']['x'] < $room['corners']['tr']['x']) {
				$connected = true;
			} else if ($room['corners']['tl']['x'] < $other_room['corners']['br']['x'] && $other_room['corners']['br']['x'] <= $room['corners']['tr']['x']) {
				$connected = true;
			} else if ($room['corners']['tl']['x'] <= $other_room['corners']['bl']['x'] && $other_room['corners']['br']['x'] <= $room['corners']['tr']['x']) {
				$connected = true;
			} else if ($other_room['corners']['bl']['x'] <= $room['corners']['tl']['x'] && $room['corners']['tr']['x'] <= $other_room['corners']['bl']['x']) {
				$connected = true;
			}
		}
		// see if $room bottom edge is up against anyone's top edge
		if ($room['corners']['bl']['y'] == $other_room['corners']['tr']['y']) {
			if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $room['corners']['bl']['x'] < $other_room['corners']['tr']['x']) {
				$connected = true;
			} else if ($other_room['corners']['tl']['x'] < $room['corners']['br']['x'] && $room['corners']['br']['x'] <= $other_room['corners']['tr']['x']) {
				$connected = true;
			} else if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $room['corners']['br']['x'] <= $other_room['corners']['tr']['x']) {
				$connected = true;
			} else if ($room['corners']['bl']['x'] <= $other_room['corners']['tl']['x'] && $other_room['corners']['tr']['x'] <= $room['corners']['bl']['x']) {
				$connected = true;
			}
		}
		if ($connected) {
			//echo $room['id'].' is connected to '.$other_room['id']."\n";
			$room['cons'][] = $other_room['id'];
			$room['adjacent'][] = $other_room['id'];
		}
	}
}
unset($room, $connected);

/*

	set up hallways

*/

$hallways = array();
foreach ($rooms as &$room) {
	foreach ($rooms as &$other_room) {
		if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) { // skip if it's the same room!
			continue;
		}
		if (in_array($other_room['id'], $room['cons'])) { // skin if these rooms are already connected
			continue;
		}
		$connected = false;
		$overlap = 0;
		if ($other_room['corners']['tl']['x'] > $room['corners']['tr']['x']) { // check to see if the other room is to the right of the first room
			//echo 'other room #'.$other_room['id'].' is to the right of room #'.$room['id'].''."\n";
			if ($other_room['corners']['tl']['y'] <= $room['corners']['tr']['y'] && $other_room['corners']['bl']['y'] >= $room['corners']['br']['y']) {
				// the other room's top and bottom edges are beyond this room's top and bottom edges
				$overlap = $room['h'];
				$overlap_start = $room['corners']['tr']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tl']['y'] >= $room['corners']['tr']['y'] && $other_room['corners']['bl']['y'] <= $room['corners']['br']['y']) {
				// the other room's top and bottom edges are within this room's top and bottom edges
				$overlap = $other_room['h'];
				$overlap_start = $other_room['corners']['tr']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tl']['y'] >= $room['corners']['tr']['y'] && $other_room['corners']['tl']['y'] < $room['corners']['br']['y']) {
				// the other room's top edge is lower than or is perfectly aligned with this room's top edge, but is still above this room's bottom edge
				// figure out the overlap
				$overlap = abs($other_room['corners']['tl']['y'] - $room['corners']['bl']['y']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['tl']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['bl']['y'] <= $room['corners']['br']['y'] && $other_room['corners']['tr']['y'] > $room['corners']['br']['y']) {
				// the other room's bottom edge is above or perfectly aligned with this room's bottom edge, but is still below this room's top edge
				// figure out the overlap
				$overlap = abs($other_room['corners']['bl']['y'] - $room['corners']['tl']['y']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['bl']['y'] - 2 - mt_rand(0, ($overlap - 2));
			}
			if ($overlap > 0) {
				// figure out how far away it is...
				$distance = $other_room['corners']['tl']['x'] - $room['corners']['tr']['x'];
				if ($distance > 10 && count($room['cons']) > 0) {
					continue;
				}
				// choose a random place in the overlap to start the hallway from the first room
				$new_hallway = array( 'type' => 'h', 'from' => $room['id'], 'to' => $other_room['id'], 'x' => $room['corners']['tr']['x'], 'y' => $overlap_start, 'w' => $distance, 'h' => 2 );
				$connected = true;
			}
		} else if ($other_room['corners']['tr']['x'] < $room['corners']['tl']['x']) { // check to see if the other room is to the left of the first room
			//echo 'other room #'.$other_room['id'].' is to the left of room #'.$room['id'].''."\n";
			if ($other_room['corners']['tr']['y'] <= $room['corners']['tl']['y'] && $other_room['corners']['br']['y'] >= $room['corners']['bl']['y']) {
				// the other room's top and bottom edges are beyond this room's top and bottom edges
				$overlap = $room['h'];
				$overlap_start = $room['corners']['tl']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tr']['y'] >= $room['corners']['tl']['y'] && $other_room['corners']['br']['y'] <= $room['corners']['bl']['y']) {
				// the other room's top and bottom edges are within this room's top and bottom edges
				$overlap = $other_room['h'];
				$overlap_start = $other_room['corners']['tl']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tl']['y'] >= $room['corners']['tr']['y'] && $other_room['corners']['tl']['y'] < $room['corners']['br']['y']) {
				// the other room's top edge is lower than or is perfectly aligned with this room's top edge, but is still above this room's bottom edge
				// figure out the overlap
				$overlap = abs($other_room['corners']['tl']['y'] - $room['corners']['bl']['y']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['tl']['y'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['br']['y'] <= $room['corners']['bl']['y'] && $other_room['corners']['tr']['y'] > $room['corners']['br']['y']) {
				// the other room's bottom edge is above or perfectly aligned with this room's bottom edge, but is still below this room's top edge
				// figure out the overlap
				$overlap = abs($other_room['corners']['bl']['y'] - $room['corners']['tl']['y']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['bl']['y'] - 2 - mt_rand(0, ($overlap - 2));
			}
			if ($overlap > 0) {
				// figure out how far away it is...
				$distance = $room['corners']['tl']['x'] - $other_room['corners']['tr']['x'];
				if ($distance > 10 && count($room['cons']) > 0) {
					continue;
				}
				// choose a random place in the overlap to start the hallway from the first room
				$new_hallway = array( 'type' => 'h', 'from' => $room['id'], 'to' => $other_room['id'], 'x' => $other_room['corners']['tr']['x'], 'y' => $overlap_start, 'w' => $distance, 'h' => 2 );
				$connected = true;
			}
		} else if ($other_room['corners']['bl']['y'] < $room['corners']['tl']['y']) { // check to see if the other room is above the first room
			//echo 'other room #'.$other_room['id'].' is above room #'.$room['id'].''."\n";
			if ($other_room['corners']['bl']['x'] <= $room['corners']['tl']['x'] && $other_room['corners']['br']['x'] >= $room['corners']['tr']['x']) {
				// the other room's left and right edges are beyond this room's left and right edges
				$overlap = $room['w'];
				$overlap_start = $room['corners']['bl']['x'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['bl']['x'] >= $room['corners']['tl']['x'] && $other_room['corners']['br']['x'] <= $room['corners']['tr']['x']) {
				// the other room's left and right edges are within this room's left and right edges
				$overlap = $other_room['w'];
				$overlap_start = $other_room['corners']['tl']['x'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['bl']['x'] <= $room['corners']['tl']['x'] && $other_room['corners']['br']['x'] > $room['corners']['tl']['x']) {
				// the other room's left edge is left of or equal to this room's left edge, but its right edge is right of this room's left edge
				$overlap = abs($other_room['corners']['br']['x'] - $room['corners']['tl']['x']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['br']['x'] - 2 - mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['br']['x'] >= $room['corners']['tr']['x'] && $other_room['corners']['bl']['x'] < $room['corners']['tr']['x']) {
				// the other room's right edge is right of or equal to this room's right edge, but its left edge is left of this room's right edge
				$overlap = abs($other_room['corners']['bl']['x'] - $room['corners']['tr']['x']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['bl']['x'] + mt_rand(0, ($overlap - 2));
			}
			if ($overlap > 0) {
				// figure out how far away it is...
				$distance = $room['corners']['tl']['y'] - $other_room['corners']['br']['y'];
				if ($distance > 10 && count($room['cons']) > 0) {
					continue;
				}
				// choose a random place in the overlap to start the hallway from the first room
				$new_hallway = array( 'type' => 'v', 'from' => $room['id'], 'to' => $other_room['id'], 'x' => $overlap_start, 'y' => $other_room['corners']['br']['y'], 'w' => 2, 'h' => $distance );
				$connected = true;
			}
		} else if ($other_room['corners']['tl']['y'] > $room['corners']['bl']['y']) { // check to see if the other room is below the first room
			//echo 'other room #'.$other_room['id'].' is below room #'.$room['id'].''."\n";
			if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $other_room['corners']['tr']['x'] >= $room['corners']['br']['x']) {
				// the other room's left and right edges are beyond this room's left and right edges
				$overlap = $room['w'];
				$overlap_start = $room['corners']['tl']['x'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tl']['x'] >= $room['corners']['bl']['x'] && $other_room['corners']['tr']['x'] <= $room['corners']['br']['x']) {
				// the other room's left and right edges are within this room's left and right edges
				$overlap = $other_room['w'];
				$overlap_start = $other_room['corners']['bl']['x'] + mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tl']['x'] <= $room['corners']['bl']['x'] && $other_room['corners']['tr']['x'] > $room['corners']['bl']['x']) {
				// the other room's left edge is left of or equal to this room's left edge, but its right edge is right of this room's left edge
				$overlap = abs($other_room['corners']['tr']['x'] - $room['corners']['bl']['x']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['tr']['x'] - 2 - mt_rand(0, ($overlap - 2));
			} else if ($other_room['corners']['tr']['x'] >= $room['corners']['br']['x'] && $other_room['corners']['tl']['x'] < $room['corners']['br']['x']) {
				// the other room's right edge is right of or equal to this room's right edge, but its left edge is left of this room's right edge
				$overlap = abs($other_room['corners']['tl']['x'] - $room['corners']['br']['x']);
				if ($overlap == 1) { continue; }
				$overlap_start = $other_room['corners']['tl']['x'] + mt_rand(0, ($overlap - 2));
			}
			if ($overlap > 0) {
				// figure out how far away it is...
				$distance = $other_room['corners']['tl']['y'] - $room['corners']['br']['y'];
				if ($distance > 10 && count($room['cons']) > 0) {
					continue;
				}
				// choose a random place in the overlap to start the hallway from the first room
				$new_hallway = array( 'type' => 'v', 'from' => $room['id'], 'to' => $other_room['id'], 'x' => $overlap_start, 'y' => $room['corners']['br']['y'], 'w' => 2, 'h' => $distance );
				$connected = true;
			}
		}
		if ($connected) {
			// check for collisions with rooms
			foreach ($rooms as $check_room) {
				if ($new_hallway['y'] + $new_hallway['h'] <= $check_room['corners']['tl']['y']) {
					$collide = false;
				} else if ($new_hallway['y'] >= $check_room['corners']['br']['y']) {
					$collide = false;
				} else if ($new_hallway['x'] + $new_hallway['w'] <= $check_room['corners']['tl']['x']) {
					$collide = false;
				} else if ($new_hallway['x'] >= $check_room['corners']['tr']['x']) {
					$collide = false;
				} else {
					$collide = true;
				}
				if ($collide) {
					continue 2;
				}
			}
			unset($check_room);
			// check for collisions with hallways
			foreach ($hallways as $check_hallway) {
				if ($new_hallway['y'] + $new_hallway['h'] <= $check_hallway['y']) {
					$collide = false;
				} else if ($new_hallway['y'] >= $check_hallway['y'] + $check_hallway['h']) {
					$collide = false;
				} else if ($new_hallway['x'] + $new_hallway['w'] <= $check_hallway['x']) {
					$collide = false;
				} else if ($new_hallway['x'] >= $check_hallway['x'] + $check_hallway['w']) {
					$collide = false;
				} else {
					$collide = true;
				}
				if ($collide) {
					continue 2;
				}
			}
			unset($check_hallway);
			// cool -- new hallway
			//echo 'new hallway from '.$new_hallway['from'].' to '.$new_hallway['to']."\n";
			// add possible difficult? door locked?
			if (mt_rand(0, 100) > 50) {
				$door_difficulty = mt_rand(10, 18);
				$new_hallway['dc'] = $door_difficulty;
				if ($door_difficulty >= 10 && $door_difficulty < 13) {
					// easy
					$new_hallway['dc_message'] = 'The door appears to be stuck.';
				} else if ($door_difficulty >= 13 && $door_difficulty < 16) {
					// medium
					$new_hallway['dc_message'] = 'The door appears to be locked.';
				} else if ($door_difficulty >= 16) {
					// hard!
					$new_hallway['dc_message'] = 'The door appears to be barred, chained, or otherwise very stuck.';
				}
				$new_hallway['dc_message'] .= ' It has a DC of '.$door_difficulty.'.';
			} else {
				$new_hallway['dc'] = 7;
				$new_hallway['dc_message'] = 'It just opens.';
			}
			$hallways[] = $new_hallway;
			$room['cons'][] = $other_room['id'];
			$other_room['cons'][] = $room['id'];
		}
	}
	unset($other_room);
}
unset($room);

//print_r($rooms);

/*

	check for adventure-breaking errors

*/

if (count($rooms[0]['cons']) == 0) {
	echo '<span class="error">ERROR: The first room has no connections, refresh for a new adventure.</span>'."\n";
}
if (count($rooms[$max_rooms-1]['cons']) == 0) {
	echo '<span class="error">ERROR: The last room has no connections, refresh for a new adventure.</span>'."\n";
}

// recurse through dungeon rooms to make sure there's a path to the last room
$rooms_so_far = array();
function recurseDungeon($the_room) {
	global $rooms, $rooms_so_far;
	if (count($the_room['cons']) > 0) {
		$rooms_so_far[] = $the_room['id'];
		foreach ($the_room['cons'] as $room_connection) {
			if (in_array($room_connection, $rooms_so_far)) {
				continue;
			}
			//echo 'room #'.$the_room['id'].' is connected to #'.$room_connection."\n";
			recurseDungeon($rooms[$room_connection]);
		}
	} else {
		$rooms_so_far[] = $the_room['id'];
	}
}

recurseDungeon($rooms[0]);

if (!in_array(($max_rooms-1), $rooms_so_far)) {
	echo '<span class="error">ERROR: The last room not accessible, refresh for a new adventure.</span>'."\n";
}

?>
</pre>
<?php

/*

	ok, a div for a debug log (if necessary)

*/

?>
<pre id="the-log"></pre>
<?php

/*

	list all room info

*/

?>
<div id="the-rooms">
<?php
foreach ($rooms as $room) {
	echo '<div class="room-info" id="room-info-'.$room['id'].'">';
	echo '<p>Room ID: <b>#'.$room['id'].'</b></p>';
	//echo '<p>Room type: '.$room['type'].'</p>';
	echo '<p>Monsters: '.((count($room['monsters']) == 0) ? 'none' : implode(', ', $room['monsters'])).'</p>';
	// show additional room info based on room type
	$possible_room_descriptions = array();
	switch ($room['type']) {
		case 'spring':
		$possible_room_descriptions[] = "There is a small clear water spring in the middle of the room; shadows engulf the room's edges. Resting here grants a +1 to everyone's HP recovery.";
		$possible_room_descriptions[] = "There is a large clear water spring on the left side of the room; shadows engulf the opposite side of the room. Resting here grants a +1 to everyone's HP recovery.";
		$possible_room_descriptions[] = "There is a small clear water spring on the right side of the room; shadows engulf the opposite side of the room. Resting here grants a +1 to everyone's HP recovery.";
		break;
		case 'stagnant water pool':
		$possible_room_descriptions[] = "The room is very dark, with a stagnant water pool in the middle. Gross. Resting here gives a -1 to everyone's HP recovery.";
		$possible_room_descriptions[] = "The room is is brightly lit, with a column of light coming form an overhead hole, beaming down into a pool of stagnant water. It smells vile. Resting here gives a -1 to everyone's HP recovery.";
		break;
		case 'sinkhole':
		$possible_room_descriptions[] = "There is a sinkhole in the middle of the room, be careful not to fall into it!";
		$possible_room_descriptions[] = "There is a sinkhole on the left side of the room, be careful not to fall into it! To use a left-side doorway, whoever wants to lead the party must do a DC 13 dexterity check.";
		$possible_room_descriptions[] = "There is a sinkhole on the right side of the room, be careful not to fall into it! To use a right-side doorway, whoever wants to lead the party must do a DC 13 dexterity check.";
		break;
		case 'storage':
		$possible_room_descriptions[] = "The room seems like it was used for storage. Maybe there's some loot around...";
		$possible_room_descriptions[] = "The room seems like it was used for storage, but everything appears to have been ransacked already.";
		break;
		case 'barracks':
		$possible_room_descriptions[] = "The room appears to have once been a barracks, there are beds around. It'd be a nice place to rest.";
		$possible_room_descriptions[] = "The room appears to have once been a barracks, there are beds around. Probably means there are enemies around.";
		break;
		case 'altar':
		$possible_room_descriptions[] = "There is a strange altar in the center of the room. If you have a wizard in your party, they can use this room as a place to do rituals.";
		break;
		case 'shrine':
		$possible_room_descriptions[] = "There is a strange shrine in the center of the room. If you have a cleric in your party, they can use this room as a place to do rituals.";
		break;
		case 'normal':
		default:
		$possible_room_descriptions[] = "Nothing too special about this room. Shadows cling to the walls; there is light in the center.";
		$possible_room_descriptions[] = "Nothing too special about this room. Shadows engulf the center of the room; there are dim torches along the edges.";
		$possible_room_descriptions[] = "Nothing too special about this room. It's pretty well-lit.";
		$possible_room_descriptions[] = "Nothing too special about this room. There's some crap strewn about, but nothing to get excited about.";
	}
	echo '<p>Description: '.$possible_room_descriptions[array_rand($possible_room_descriptions)].'</p>';
	echo '</div>';
	echo "\n";
}
?>
</div>
<?php

/*

	render the dungeon with a tile grid under it

*/

?>
<div id="the-grid">
<canvas id="a" width="<?php echo $max_width * $tile_scale; ?>px" height="<?php echo $max_height * $tile_scale; ?>px"></canvas>
<?php

// render rooms
foreach ($rooms as $room) {
	$adj_classes = array();
	if (count($room['adjacent']) > 0) {
		foreach ($room['adjacent'] as $adj_room_id) {
			$adj_classes[] = 'adj-'.$adj_room_id;
		}
	}
	echo '<div id="'.$room['id'].'" class="room '.$room['type'].' '.implode(' ', $adj_classes).'" style="position:absolute;top:'.($room['y'] * $tile_scale).'px;left:'.($room['x'] * $tile_scale).'px;width:'.($room['w'] * $tile_scale).'px;height:'.($room['h'] * $tile_scale).'px;" title="room has '.count($room['cons']).' monsters, room type: '.$room['type'].'">'.$room['id'].'</div>'."\n";
}
// render hallways
foreach ($hallways as $hallway) {
	echo '<div data-dc="'.$hallway['dc'].'" data-dc-message="'.$hallway['dc_message'].'" data-room-from="'.$hallway['from'].'" data-room-to="'.$hallway['to'].'" class="hallway '.$hallway['type'].'" style="position:absolute;top:'.($hallway['y'] * $tile_scale).'px;left:'.($hallway['x'] * $tile_scale).'px;width:'.($hallway['w'] * $tile_scale).'px;height:'.($hallway['h'] * $tile_scale).'px;" title="hallway connecting room #'.$hallway['from'].' and room #'.$hallway['to'].'"></div>'."\n";
}

?>
</div>
<script type="text/javascript">
var a_canvas = document.getElementById('a');
var a = a_canvas.getContext('2d');
a.fillStyle = "#000000";
a.strokeStyle = "#ccc";
a.lineWidth = 0.5;
a.font = "12px monospace";
for (var x = 0.5; x < <?php echo $max_width * $tile_scale; ?>; x += <?php echo $tile_scale; ?>) {
	a.beginPath();
	a.moveTo(x, 0);
	a.lineTo(x, <?php echo $max_height * $tile_scale; ?>);
	a.stroke();
	a.closePath();
}
for (var y = 0.5; y < <?php echo $max_height * $tile_scale; ?>; y += <?php echo $tile_scale; ?>) {
	a.beginPath();
	a.moveTo(0, y);
	a.lineTo(<?php echo $max_width * $tile_scale; ?>, y);
	a.stroke();
	a.closePath();
}
</script>
<?php

/*

	game playthrough logic

*/

?>
<script type="text/javascript">

var exposed_rooms = new Array();

$(document).ready(function() {
	
	// first of all, hide all rooms and hallways...
	$('div.room').hide();
	$('div.hallway').hide();
	
	// hide room info
	$('div.room-info').hide();
	
	// show first room and hallways its connected to
	revealRoom(0);
	
	// when you click on a hallway, show the room its connected to, and any rooms adjacent
	$('div.hallway').click(function(event) {
		event.preventDefault();
		var hall_difficulty = $(this).attr('data-dc') * 1;
		if (hall_difficulty * 1 > 7) {
			var input_dc = prompt($(this).attr('data-dc-message') + ' Enter your check roll result.');
			if (input_dc < hall_difficulty) {
				alert('The door does not open!');
				return;
			}
		}
		var room_from = $(this).attr('data-room-from') * 1;
		var room_to = $(this).attr('data-room-to') * 1;
		var show_room = 0;
		var origin_room = 0;
		if ($.inArray(room_from, exposed_rooms) > -1) {
			show_room = room_to;
			origin_room = room_from;
		} else {
			show_room = room_from;
			origin_room = room_to;
		}
		//alert('going down hallway to room '+show_room+'!');
		//$('pre#the-log').append('going down hallway from room '+origin_room+' to room '+show_room+''+"\n");
		revealRoom(show_room);
	});
	
});

function revealRoom(room_id) {
	// show anything (and hallways) of anything with adj-room_id
	// and with the new revealed stuff, show anything adjacent to them recursively
	room_id = room_id * 1;
	$('div#'+room_id).show();
	$('div#room-info-'+room_id).show();
	var rooms_shown = $('div.adj-'+room_id).show();
	//console.log(rooms_shown.length + ' number of rooms adjacent to room #'+room_id);
	$('div.hallway[data-room-from="'+room_id+'"]').show();
	$('div.hallway[data-room-to="'+room_id+'"]').show();
	exposed_rooms.push(room_id);
	if (rooms_shown.length > 0) {
		for (var i = 0; i < rooms_shown.length; i++) {
			var reveal_id = $(rooms_shown[i]).attr('id') * 1;
			if ($.inArray(reveal_id, exposed_rooms) == -1) {
				//console.log('reveal... '+reveal_id);
				revealRoom(reveal_id);
			}
		}
	}
}
</script>
</body>
</html>