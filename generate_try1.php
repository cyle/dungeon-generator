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
</style>
</head>
<body>
<?php

echo '<pre>';

// generate dungeon

$tile_scale = 5; // 1 tile = 5 feet

$max_rooms = 40;

$grid_max_width = 100;
$grid_max_height = 50;

$grid_surface_area = $grid_max_height * $grid_max_width;
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

//print_r($grid);

// build rooms

$rooms = array();
$room_surface_area = 0;

// first room is the entrance -- on the edge somewhere
$first_room = array();
$first_room['con'] = false;
$first_room['t'] = 'first';
$first_room['w'] = mt_rand(3, 10);
$first_room['h'] = mt_rand(3, 10);
$first_room['x'] = 0;
$first_room['y'] = 0;
$first_room['x2'] = $first_room['x'] + $first_room['w'];
$first_room['y2'] = $first_room['y'] + $first_room['h'];
$room_surface_area += ( $first_room['w'] * $first_room['h'] );	
$rooms[] = $first_room;

$room_num = 1;

while ($room_surface_area < $rooms_max_surface_area) {

	if (count($rooms) >= $max_rooms) {
		$room_surface_area = $rooms_max_surface_area;
	}
	
	$new_room = array();
	
	$new_room['con'] = false;
	$new_room['t'] = 'room';
	$new_room['w'] = mt_rand(3, 10);
	$new_room['h'] = mt_rand(3, 10);
	$new_room['x'] = mt_rand(0, $grid_max_width);
	$new_room['y'] = mt_rand(0, $grid_max_height);
	$new_room['x2'] = $new_room['x'] + $new_room['w'];
	$new_room['y2'] = $new_room['y'] + $new_room['h'];
	
	if ($new_room['x2'] > $grid_max_width) {
		$x_diff = abs($grid_max_width - $new_room['x2']);
		$new_room['x'] = $new_room['x'] - $x_diff;
		$new_room['x2'] = $new_room['x'] + $new_room['w'];
	}
	if ($new_room['y2'] > $grid_max_height) {
		$y_diff = abs($grid_max_height - $new_room['y2']);
		$new_room['y'] = $new_room['y'] - $y_diff;
		$new_room['y2'] = $new_room['y'] + $new_room['h'];
	}
	
	// collision check
	foreach ($rooms as $room) {
		if ($new_room['x'] >= $room['x'] && $new_room['x'] <= $room['x2']) {
			if ($new_room['y'] >= $room['y'] && $new_room['y'] <= $room['y2']) {
				continue 2;
			}
		}
		if ($new_room['x'] >= $room['x'] && $new_room['x'] <= $room['x2']) {
			if ($new_room['y2'] >= $room['y'] && $new_room['y2'] <= $room['y2']) {
				continue 2;
			}
		}
		if ($new_room['x2'] >= $room['x'] && $new_room['x2'] <= $room['x2']) {
			if ($new_room['y'] >= $room['y'] && $new_room['y'] <= $room['y2']) {
				continue 2;
			}
		}
		if ($new_room['x2'] >= $room['x'] && $new_room['x2'] <= $room['x2']) {
			if ($new_room['y2'] >= $room['y'] && $new_room['y2'] <= $room['y2']) {
				continue 2;
			}
		}
		
		// ((LineB2.Y Ð LineB1.Y) * (LineA2.X Ð LineA1.X)) - ((LineB2.X Ð lineB1.X) * (LineA2.Y - LineA1.Y))
		/*
		$wut = (($new_room['y2'] - $new_room['y']) * ($room['x2'] - $room['x'])) - (($new_room['x2'] - $new_room['x']) * ($room['y2'] - $room['y']));
		if ($wut != 0) {
			continue 2;
		}
		*/
	}
	
	$room_color = 'rgb('.rand(0,255).', '.rand(0,255).', '.rand(0,255).')';
	//$room_color = 'rgb(127, 127, 127)';
	$new_room['c'] = $room_color;
	
	$room_surface_area += ( $new_room['w'] * $new_room['h'] );
	
	$rooms[] = $new_room;
	$room_num++;
}

// check for rooms that are already connected to another

foreach ($rooms as &$room) {
	if ($room['con'] == true) {
		continue;	
	}
	foreach ($rooms as &$other_room) {
		
		if ($room['x'] == $other_room['x'] && $room['y'] == $other_room['y']) {
			// the same room! move on
			continue;
		}
		
		// whether the other room is touching the right edge
		if ($other_room['x2']+1 == $room['x'] && $other_room['y'] >= $room['y'] && $other_room['y'] < $room['y2']) {
			$room['con'] = true;
			$other_room['con'] = true;
		}
		
		// whether the other room is touching the left edge
		if ($other_room['x']-1 == $room['x'] && $other_room['y'] >= $room['y'] && $other_room['y'] < $room['y2']) {
			$room['con'] = true;
			$other_room['con'] = true;
		}
		
		// whether the other room is touching the top edge
		if ($other_room['y']+1 == $room['y'] && $other_room['x'] >= $room['x'] && $other_room['x'] < $room['x2']) {
			$room['con'] = true;
			$other_room['con'] = true;
		}
		
		// whether the other room is touching the bottom edge
		if ($other_room['y2']-1 == $room['y'] && $other_room['x'] >= $room['x'] && $other_room['x'] < $room['x2']) {
			$room['con'] = true;
			$other_room['con'] = true;
		}
		
	}
	unset($other_room);
}

unset($room);

//print_r($rooms);

$rooms = array();
$rooms[] = array('w' => 10, 'h' => 10, 't' => 'room', 'con' => false, 'con_num' => 0, 'x' => 10, 'y' => 10, 'x2' => 20, 'y2' => 20, 'c' => 'green' );
$rooms[] = array('w' => 10, 'h' => 10, 't' => 'room', 'con' => false, 'con_num' => 0, 'x' => 30, 'y' => 15, 'x2' => 40, 'y2' => 25, 'c' => 'blue' );
$rooms[] = array('w' => 10, 'h' => 10, 't' => 'room', 'con' => false, 'con_num' => 0, 'x' => 50, 'y' => 10, 'x2' => 60, 'y2' => 20, 'c' => 'red' );


// do hallways
foreach ($rooms as $room) {
	if ($room['con_num'] == 0) {
		
		// connect it to something!
		
		// find another room nearby to connect to...?
		
		$top_left = array('x' => $room['x'], 'y' => $room['y']);
		$top_right = array('x' => $room['x2'], 'y' => $room['y']);
		$bottom_left = array('x' => $room['x'], 'y' => $room['y2']);
		$bottom_right = array('x' => $room['x2'], 'y' => $room['y2']);
		
		// if there is another room within... 20 units? connect to it
		$unit_radius = 15;
		foreach ($rooms as $other_room) {
			if ($other_room['x'] == $room['x'] && $other_room['y'] == $room['y']) {
				continue;
			}
			
			echo $room['c'].' checking against '.$other_room['c']."\n";
			
			$other_top_left = array('x' => $other_room['x'], 'y' => $other_room['y']);
			$other_top_right = array('x' => $other_room['x2'], 'y' => $other_room['y']);
			$other_bottom_left = array('x' => $other_room['x'], 'y' => $other_room['y2']);
			$other_bottom_right = array('x' => $other_room['x2'], 'y' => $other_room['y2']);
			
			// check from room top left to other top left
			$dx = $other_room['x'] - $top_left['x'];
			$dy = $other_room['y'] - $top_left['y'];
			if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
				echo $room['c'].' collides with '.$other_room['c'].' from top left!'."\n";
			}
			// check from room top right to other top left
			$dx = $other_room['x'] - $top_right['x'];
			$dy = $other_room['y'] - $top_right['y'];
			if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
				echo $room['c'].' collides with '.$other_room['c'].' from top right!'."\n";
			}
			// check from room bottom left to other top left
			$dx = $other_room['x'] - $bottom_left['x'];
			$dy = $other_room['y'] - $bottom_left['y'];
			if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
				echo $room['c'].' collides with '.$other_room['c'].' from bottom left!'."\n";
			}
			// check from room bottom right to other top left
			$dx = $other_room['x'] - $bottom_right['x'];
			$dy = $other_room['y'] - $bottom_right['y'];
			if (($dx * $dx) + ($dy * $dy) < ($unit_radius*$unit_radius)) {
				echo $room['c'].' collides with '.$other_room['c'].' from bottom right!'."\n";
			}
		}
		unset($other_room);
		
	}
}

unset($room);

echo count($rooms).' rooms.'."\n";

echo '</pre>';

// display grid
foreach ($rooms as $room) {
	for ($x = $room['x']; $x < $room['x2']; $x++) {
		for ($y = $room['y']; $y < $room['y2']; $y++) {
			$grid[$x][$y] = $room;
		}
	}
}

echo '<div id="grid">';
echo '<table cellpadding="0" cellspacing="0">';
for ($y = 0; $y < $grid_max_height; $y++) {
	echo '<tr>';
	for ($x = 0; $x < $grid_max_width; $x++) {
		$class = 'empty';
		if (isset($grid[$x][$y]['t'])) {
			$this_room = $rooms[$grid[$x][$y]['id']];
			switch ($grid[$x][$y]['t']) {
				case 'room':
				case 'first':
				echo '<td class="'.$grid[$x][$y]['t'].'" style="background-color:'.$grid[$x][$y]['c'].'" title="room '.$grid[$x][$y]['id'].', '.(($this_room['con']) ? 'connected': 'not connected').'"> </td>';
				break;
				case 'hall':
				echo '<td class="'.$class.'"> </td>';
				break;
				default:
				$class = 'other';
				echo '<td class="'.$class.'"> </td>';
			}
		} else {
			echo '<td class="empty"> </td>';
		}
		
	}
	echo '</tr>';
}
echo '</table>';
echo '</div>';

?>
</body>
</html>