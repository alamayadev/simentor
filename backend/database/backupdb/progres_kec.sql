SELECT sls_kec.kdkec AS kdkec,sls_kec.nmkec AS nmkec, sls_kec.jml_sls AS target_sls, COUNT(*) AS jml
FROM sls_kec
LEFT JOIN 004_cek_landmark ON 004_cek_landmark.kdkec=sls_kec.kdkec
WHERE 004_cek_landmark.jml_batas_sls > 3 AND 004_cek_landmark.jml_titik_sls > 0
GROUP BY 004_cek_landmark.kdkec,sls_kec.nmkec, sls_kec.jml_sls