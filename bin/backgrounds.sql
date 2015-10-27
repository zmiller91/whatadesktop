USE whatadesktop;

create table images(
    id int not null auto_increment,
    filehash varchar(64),
    filename varchar(256),
	root varchar(64),
    path varchar(256),
    width int,
    height int,
    imgur_url varchar(256),
    deleted tinyint default 0,
    saved tinyint default 0,
    
    primary key (id),
    unique(filehash)
    
);

create table users(
    id int not null auto_increment,
    username varchar(256),
    password varchar(256),
    created_date datetime,
    primary key (id),
    unique(username)
);

create table user_sessions(
    id int not null auto_increment,
    user int,
    token varchar(256),
    expiration datetime,
    persist tinyint,
    primary key (id),
    foreign key (user) references users(id),
    index (id, user),
    created_date datetime,
    updated_date datetime
);

create table img_status(
    user int not null,
    img_root varchar(256) not null,
	img_id int not null,
    status int not null,
    updated_date datetime,
    primary key(user, img_id),
    index (user, img_root),
    foreign key(user) references users(id),
    foreign key(img_id) references images(id)
);