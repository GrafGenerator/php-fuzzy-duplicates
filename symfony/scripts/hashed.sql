create table hashes(id int not null primary key, hash varchar(500) not null);

insert into hashes select id, ssdeep_fuzzy_hash(CONCAT(full_name, birth_date, passport_series, passport_number)) from client;

create unique index idx_hashes_temp on hashes(id, hash);

select a.id, b.id from hashes a join hashes b on b.id > a.id and ssdeep_fuzzy_compare(a.hash, b.hash) > 80 where a.id < 1000 and b.id < 1000;

select
   a.id
  ,b.id
  ,CONCAT(TRIM(a.full_name), ' ',  a.birth_date, ' ', a.passport_series, ' ',  a.passport_number, ' | '
  ,TRIM(b.full_name), ' ',  b.birth_date, ' ', b.passport_series, ' ',  b.passport_number)

from (
  select a.id a_id, b.id b_id from hashes a join hashes b on b.id > a.id and ssdeep_fuzzy_compare(a.hash, b.hash) > 80 where a.id < 1000 and b.id < 1000
) t
join client a
  on a.id = t.a_id
join client b
  on b.id = t.b_id;