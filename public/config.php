<?php
    $dbConfig = [
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'Test-13579',
            'database' => 'userdb',
            'port'     => 3306
        ];

    $s3Config = [
            'bucket_or_arn'   => 'creatlantis-com-s3-private',
            'region'          => 'eu-north-1',
            'use_path_style'  => false,
            'use_arn_region'  => false               // not needed unless using ARNs
        ];
?>