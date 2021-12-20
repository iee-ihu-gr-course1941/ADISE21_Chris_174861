<?php

//function add_piece($square, $x, $y) {
//
//}

function get_board() {
	global $mysqli;

	$sql = 'select * from board';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	$board = $res->fetch_all(MYSQLI_ASSOC);
	return $board;
}

function show_board() {
	$board = get_board();

//	header('Content-type: application/json');
//	print json_encode($board, JSON_PRETTY_PRINT);

	print("  0  1  2  3  4  5  6\n");
	$line = 1;
	for ($i = 0; $i < count($board); $i++) {
		if ($line == 1)
			print($board[$i]['x']);
		if ($board[$i]['piece_color'] == 'n')
			print('___');
		elseif ($board[$i]['piece_color'] == 'w') 
			print('|w|');
		elseif ($board[$i]['piece_color'] == 'b') 
			print('|b|');
		else
			print('|.|');

		if ($line == 7) {
			print("\n");
			$line = 0;
		}
		$line++;
	}
}

function reset_board() {
	global $mysqli;
	$mysqli->query('call clean_board()');
}

function show_piece($x,$y) {
	global $mysqli;
	
	$sql = 'select * from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii',$x,$y);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

function move_piece($x, $y, $x2, $y2, $token) {
	
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}

	$color = current_color($token);
	if($color==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}

	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
}

function place_piece($x, $y, $token) {
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}

	$color = current_color($token);
	if($color==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}

	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['player_turn']!=$color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}

	$pieces_placed = pieces_placed($token);
	if ($pieces_placed == 9) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You placed all your pieces"]);
		exit;
	}

	if (! in_array([$x,$y], get_valid_positions())) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Invalid positions"]);
		exit;
	}

	$board = get_board();
	if ($board[$x*7+$y]['piece_color'] != null) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Position already taken"]);
		exit;
	}

	global $mysqli;
	$sql = 'call place_piece(?,?)';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii', $x, $y);
	$st->execute();

	header('Content-type: application/json');
	print json_encode(read_board(), JSON_PRETTY_PRINT);
}

function get_valid_positions() {
	$pos = array([0,0],[0,3],[0,6],
				 [1,1],[1,3],[1,5],
				 [2,2],[2,3],[2,4],
				 [3,0],[3,1],[3,2],[3,4],[3,5],[3,6],
				 [4,2],[4,3],[4,4],
				 [5,1],[5,3],[5,5],
				 [6,0],[6,3],[6,5]);
	return $pos;
}
?>