drop table if exists board;
create table board (
	x tinyint(1) not null,
	y tinyint(1) not null,
	piece_color enum('b','w','n'),
	primary key (x, y)
) engine=InnoDB default charset=utf8;

lock tables board write;
insert into board values
(0,0,null), (1,0,'n'),  (2,0,'n'),  (3,0,null), (4,0,'n'),  (5,0,'n'),  (6,0,null),
(0,1,'n'),  (1,1,null), (2,1,'n'),  (3,1,null), (4,1,'n'),  (5,1,null), (6,1,'n'),
(0,2,'n'),  (1,2,'n'),  (2,2,null), (3,2,null), (4,2,null), (5,2,'n'),  (6,2,'n'),
(0,3,'n'),  (1,3,null), (2,3,null), (3,3,'n'),  (4,3,null), (5,3,null), (6,3,'n'),
(0,4,'n'),  (1,4,'n'),  (2,4,null), (3,4,null), (4,4,null), (5,4,'n'),  (6,4,'n'),
(0,5,'n'),  (1,5,null), (2,5,'n'),  (3,5,null), (4,5,'n'),  (5,5,null), (6,5,'n'),
(0,6,null), (1,6,'n'),  (2,6,'n'),  (3,6,null), (4,6,'n'),  (5,6,'n'),  (6,6,null);
unlock tables;

drop table if exists board_empty;
create table board_empty (
	x tinyint(1) not null,
	y tinyint(1) not null,
	piece_color enum('b','w','n'),
	primary key (x, y)
) engine=InnoDB default charset=utf8;

lock tables board_empty write;
insert into board_empty values
(0,0,null), (1,0,'n'),  (2,0,'n'),  (3,0,null), (4,0,'n'),  (5,0,'n'),  (6,0,null),
(0,1,'n'),  (1,1,null), (2,1,'n'),  (3,1,null), (4,1,'n'),  (5,1,null), (6,1,'n'),
(0,2,'n'),  (1,2,'n'),  (2,2,null), (3,2,null), (4,2,null), (5,2,'n'),  (6,2,'n'),
(0,3,'n'),  (1,3,null), (2,3,null), (3,3,'n'),  (4,3,null), (5,3,null), (6,3,'n'),
(0,4,'n'),  (1,4,'n'),  (2,4,null), (3,4,null), (4,4,null), (5,4,'n'),  (6,4,'n'),
(0,5,'n'),  (1,5,null), (2,5,'n'),  (3,5,null), (4,5,'n'),  (5,5,null), (6,5,'n'),
(0,6,null), (1,6,'n'),  (2,6,'n'),  (3,6,null), (4,6,'n'),  (5,6,'n'),  (6,6,null);
unlock tables;

drop table if exists game_status;
create table game_status (
	status enum('not_active','initialized','started','ended','aborted') not null default 'not_active',
	player_turn enum('b','w') default null,
	result enum('b','w','d') default null,
	last_change timestamp null default null
) engine=InnoDB default charset=utf8;

lock tables game_status write;
insert into game_status values ('not_active',null,null,'2022-12-20 20:00:00');
unlock tables;

drop table if exists players;
create table players (
	username varchar(20) default null,
	piece_color enum('b','w') not null,
	pieces_placed tinyint(1) not null,
	pieces_remaining tinyint(1) not null,
	token varchar(100) default null,
	last_action timestamp null default null,
	primary key (piece_color)
) engine=InnoDB default charset=utf8;

lock tables players write;
insert into players values (null,'b',0,9,null,null), (null,'w',0,9,null,null);
unlock tables;

delimiter $$
create procedure clean_board()
begin
	replace into board select * from board_empty;
end;
$$ delimiter; 

delimiter $$
create procedure move_piece(x1 tinyint, y1 tinyint, x2 tinyint, y2 tinyint)
begin
	declare p_color char;

	select piece_color into p_color
	from board where x=x1 and y=y1;

	update board
	set piece_color=p_color
	where x=x2 and y=y2;

	update board
	set piece_color=null
	where x=x1 and y=y1;
end
$$ delimiter;

delimiter $$
create procedure place_piece(x1 tinyint, y1 tinyint)
begin
	declare p_color char;

	select player_turn into p_color
	from game_status;

	update board
	set piece_color=p_color
	where x=x1 and y=y1;
end
$$ delimiter;