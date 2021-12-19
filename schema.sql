drop table if exists board;
create table board (
	square tinyint(1) not null,
    x tinyint(1) not null,
  	y tinyint(1) not null,
    piece_color enum('B','W') not null,
    primary key (square, x, y)
) engine=InnoDB default charset=utf8;

drop table if exists game_status;
create table game_status (
  status enum('not_active','initialized','started','ended','aborted') not null default 'not active',
  player_turn enum('B','W') default null,
  result enum('B','W','D') default null,
  last_change timestamp null default null
) engine=InnoDB default charset=utf8;