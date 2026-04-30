create VIEW 004_cek_landmark as select `rekap_landmark_2022_sls_2024`.`kdkec` AS `kdkec`,
`rekap_landmark_2022_sls_2024`.`kddesa` AS `kddesa`,
`rekap_landmark_2022_sls_2024`.`idsls_2024` AS `idsls_2024`,
`rekap_landmark_2022_sls_2024`.`pemeta` AS `pemeta`,
`rekap_landmark_2022_sls_2024`.`nm_project` AS `nm_project`,
`rekap_landmark_2022_sls_2024`.`nmsls` AS `nmsls`,
`rekap_landmark_2022_sls_2024`.`deskripsi_project` AS `deskripsi_project`,
sum(if((`rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` = '15040'),`rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_batas_sls`,
sum(if((`rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` = '15060'),`rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_titik_sls`,
sum(if((`rekap_landmark_2022_sls_2024`.`kode_landmark_tipe` like '16%'),`rekap_landmark_2022_sls_2024`.`Jml`,0)) AS `jml_landmark` 
from `rekap_landmark_2022_sls_2024` 
group by `rekap_landmark_2022_sls_2024`.`kdkec`,`rekap_landmark_2022_sls_2024`.`kddesa`,`rekap_landmark_2022_sls_2024`.`idsls_2024`,`rekap_landmark_2022_sls_2024`.`pemeta`,`rekap_landmark_2022_sls_2024`.`nm_project`,`rekap_landmark_2022_sls_2024`.`nmsls`,`rekap_landmark_2022_sls_2024`.`deskripsi_project`