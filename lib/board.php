<?php

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

function handle_piece_impl($input) {
	$token = $input['token'];
	if ($token == null || $token == '') bad_request("Token is not set");

	$color = current_color($token);
	if ($color == null) bad_request("You are not a player of this game");

	$status = read_status();
	if ($status['status'] != 'started') bad_request("Game is not in action");
	if ($status['player_turn'] != $color) bad_request("It is not your turn");
	if ($status['elimination'] == 1 && $input['piece'] != 'eliminate') bad_request("This round is an elimination");
	if (pieces_placed($token) != 9 && $input['piece'] == 'move') bad_request("Place all your pieces first");

	if ($input['piece'] == 'place') place_piece($input);
	else if ($input['piece'] == 'move') move_piece($input);
	else if ($input['piece'] == 'eliminate') eliminate_piece($input);
}

function move_piece($input) {
	$board = get_board();
	$x1 = $input('x1');
	$y1 = $input('y1');
	$x2 = $input('x2');
	$y2 = $input('y2');
	// TODO check for validity of x1 through y2

	if ($board[$x2*7+$y2]['piece_color'] != null) bad_request("Invalid position to move to");

	global $mysqli;
	$sql = 'call move_piece(?,?,?,?)';
	$st = $mysqli->prepare($sql);
	$st->bind_param('iiii', $x1, $y1, $x2, $y2);
	$st->execute();

	elimination_check($board, current_color($input['token']));
}

function place_piece($input) {
	$token = $input['token'];
	$pieces_placed = pieces_placed($token);
	if ($pieces_placed == 9) bad_request("You placed all your pieces");

	$board = get_board();
	$x = $input['x'];
	$y = $input['y'];
	if ($x == null || $y == null) bad_request("No positions x & y provided");
	if ($board[$x*7+$y]['piece_color'] != null) bad_request("Invalid position or already taken");

	global $mysqli;
	$sql = 'call place_piece(?,?,?,?)';
	$st = $mysqli->prepare($sql);
	$pieces_placed++;
	$st->bind_param('iiis', $x, $y, $pieces_placed, $token);
	$st->execute();

	elimination_check($board, current_color($token));
}

function eliminate_piece($input) {
	$other_color = (current_color($input['token']) == 'w') ? 'b' : 'w';

	$x = $input['x'];
	$y = $input['y'];
	if ($x == null || $y == null) bad_request("No positions x & y provided");
	if (get_board()[$x*7+$y]['piece_color'] != $other_color) bad_request("Invalid position to eliminate");

	global $mysqli;
	$sql = 'call eliminate_piece(?, ?)';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii', $x, $y);
	$st->execute();
}

function elimination_check($board, $color) {
	$lines = array(
		[[0,0],[0,3],[0,6]],
		[[0,0],[3,0],[6,0]],
		[[0,6],[3,6],[6,6]],
		[[6,0],[6,3],[6,6]],

		[[1,1],[1,3],[1,5]],
		[[1,1],[3,1],[5,1]],
		[[1,5],[3,5],[5,5]],
		[[5,1],[5,3],[5,5]],

		[[2,2],[2,3],[2,4]],
		[[2,2],[3,2],[4,2]],
		[[2,4],[3,4],[4,4]],
		[[4,2],[4,3],[4,4]],

		[[0,3],[1,3],[2,3]],
		[[3,0],[3,1],[3,2]],
		[[3,4],[3,5],[3,6]],
		[[4,3],[5,3],[6,3]]
	);

	for ($i = 0; $i < count($lines); $i++)
	{
		$x1 = $lines[$i][0][0];
		$y1 = $lines[$i][0][1];
		$x2 = $lines[$i][1][0];
		$y2 = $lines[$i][1][1];
		$x3 = $lines[$i][2][0];
		$y3 = $lines[$i][2][1];

		if ($board[$x1*7+$y1]['piece_color'] == $color &&
			$board[$x2*7+$y2]['piece_color'] == $color &&
			$board[$x3*7+$y3]['piece_color'] == $color)
		{
			global $mysqli;
			$st = $mysqli->prepare('update game_status set elimination=1');
			$st->execute();
			return;
		}
	}

	//update_turn();
}

?>