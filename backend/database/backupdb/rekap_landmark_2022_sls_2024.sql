create view rekap_landmark_2022_sls_2024 as select `rekap_landmark`.`id` AS `id`,
`rekap_landmark`.`kdkec` AS `kdkec`,
`rekap_landmark`.`kddesa` AS `kddesa`,
`rekap_landmark`.`kdsls` AS `kdsls`,
`rekap_landmark`.`idsls` AS `idsls`,
UPPER(`rekap_landmark`.`pemeta`) AS `pemeta`,
`rekap_landmark`.`nm_project` AS `nm_project`,
`rekap_landmark`.`deskripsi_project` AS `deskripsi_project`,
`rekap_landmark`.`kode_landmark_tipe` AS `kode_landmark_tipe`,
`rekap_landmark`.`tipe_landmark` AS `tipe_landmark`,
`rekap_landmark`.`Jml` AS `Jml`,
`sls_2024_2`.`idsls` AS `idsls_2024`,
`sls_2024_2`.`nmsls` AS `nmsls` 
from (`rekap_landmark` 
left join `sls_2024_2` on((`rekap_landmark`.`idsls` = `sls_2024_2`.`idsls`)))