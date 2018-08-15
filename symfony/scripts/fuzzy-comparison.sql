select
   a.id
  ,b.id
  ,CONCAT(TRIM(CONCAT(a.full_name, ' ',  a.birth_date, ' ', a.passport_series, ' ',  a.passport_number)), CHAR(13)
  ,TRIM(CONCAT(b.full_name, ' ',  b.birth_date, ' ', b.passport_series, ' ',  b.passport_number))) data
from client a
join client b
  on
    b.id > a.id
    and
    a.id <> b.id
    and
    ssdeep_fuzzy_compare(
      ssdeep_fuzzy_hash(CONCAT(a.full_name, a.birth_date, a.passport_series, a.passport_number)),
      ssdeep_fuzzy_hash(CONCAT(b.full_name, b.birth_date, b.passport_series, b.passport_number))
    ) > 32

order by
  ssdeep_fuzzy_compare(
    ssdeep_fuzzy_hash(CONCAT(a.full_name, a.birth_date, a.passport_series, a.passport_number)),
    ssdeep_fuzzy_hash(CONCAT(b.full_name, b.birth_date, b.passport_series, b.passport_number))
  ) DESC;