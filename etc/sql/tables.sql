-- SQL data for twitter


CREATE TABLE license (
	id varchar(26) PRIMARY KEY DEFAULT ulid_create(),
	flag int default 0 not null,
	ts_created timestamp with time zone default now() not null,
	ts_updated timestamp with time zone default now() not null,
	ts_deleted timestamp with time zone,
	type varchar(64) not null,
	guid character varying(128) not null,
	hash character varying(64) not null,
	code varchar(256) not null,
	name varchar(256) not null,
	city varchar(256),
	meta jsonb,
	ftsv tsvector
);

CREATE TABLE twitter_follower (
	id varchar(26) PRIMARY KEY DEFAULT ulid_create(),
	flag int default 0 not null,
	stat int default 100 not null,
	ts_created timestamp with time zone default now() not null,
	ts_updated timestamp with time zone default now() not null,
	license_id_origin varchar(64) not null,
	license_id_follow varchar(64) not null
);
ALTER TABLE ONLY twitter_follower
	ADD CONSTRAINT twitter_follower_license_id_origin_fkey FOREIGN KEY (license_id_origin) REFERENCES license(id);
ALTER TABLE ONLY twitter_follower
	ADD CONSTRAINT twitter_follower_license_id_follow_fkey FOREIGN KEY (license_id_follow) REFERENCES license(id);
