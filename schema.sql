drop table if exists board;
create table board (
	square tinyint(1) not null,
	x tinyint(1) not null,
	y tinyint(1) not null,
	piece_color enum('b','w'),
	primary key (square, x, y)
) engine=InnoDB default charset=utf8;

lock tables board write;
insert into board values
(1,0,0,null), (1,1,0,null), (1,2,0,null),
(1,0,1,null),				(1,2,1,null),
(1,0,2,null), (1,1,2,null), (1,2,2,null),

(2,0,0,null), (2,1,0,null), (2,2,0,null),
(2,0,1,null),				(2,2,1,null),
(2,0,2,null), (2,1,2,null), (2,2,2,null),

(3,0,0,null), (3,1,0,null), (3,2,0,null),
(3,0,1,null),				(3,2,1,null),
(3,0,2,null), (3,1,2,null), (3,2,2,null);
unlock tables;

drop table if exists board_empty;
create table board_empty (
	square tinyint(1) not null,
	x tinyint(1) not null,
	y tinyint(1) not null,
	piece_color enum('b','w'),
	primary key (square, x, y)
) engine=InnoDB default charset=utf8;

lock tables board_empty write;
insert into board_empty values
(1,0,0,null), (1,1,0,null), (1,2,0,null),
(1,0,1,null),				(1,2,1,null),
(1,0,2,null), (1,1,2,null), (1,2,2,null),

(2,0,0,null), (2,1,0,null), (2,2,0,null),
(2,0,1,null),				(2,2,1,null),
(2,0,2,null), (2,1,2,null), (2,2,2,null),

(3,0,0,null), (3,1,0,null), (3,2,0,null),
(3,0,1,null),				(3,2,1,null),
(3,0,2,null), (3,1,2,null), (3,2,2,null);
unlock tables;

drop table if exists game_status;
create table game_status (
	status enum('not_active','initialized','started','ended','aborted') not null default 'not_active',
	player_turn enum('b','w') default null,
	result enum('b','w','d') default null,
	last_change timestamp null default null
) engine=InnoDB default charset=utf8;

drop table if exists players;
create table players (
	username varchar(20) default null,
	piece_color enum('b','w') not null,
	token varchar(100) default null,
	last_action timestamp null default null,
	primary key (piece_color)
) engine=InnoDB default charset=utf8;

lock tables players write;
insert into players values (null,'b',null,null), (null,'w',null,null);
unlock tables;

delimiter $$
create procedure clean_board()
begin
	replace into board select * from board_empty;
end;
$$ delimiter; 

delimiter $$
create procedure move_piece(square1 tinyint, x1 tinyint, square2 tinyint, y1 tinyint, x2 tinyint, y2 tinyint)
begin
	declare p_color char;

	select piece_color into p_color
	from board where x=x1 and y=y1;

	update board
	set piece_color=p_color
	where square=square2 and x=x2 and y=y2;

	update board
	set piece_color=null
	where square=square1 and x=x1 and y=y1;
end
$$ delimiter;

delimiter $$
create procedure place_piece(square1 tinyint, x1 tinyint, y1 tinyint, p_color char)
begin
	update board
	set piece_color=p_color
	where square=square1 and x=x1 and y=y1;
end
$$ delimiter;