<?php

//loader config goes in here
return array(
    'repoBasePath' => '../../media/',
    'moveImagesRelativeToLoader' => true,
    'imagePath' => '/media/image/',
    'rewriteImageUrl' => true,
    'globalImageUrl' => 'images/',
    'imageLocation' => '../../media/images/',
    'repos' => array(
        'core' => array(
            'imageUrl' => 'images/',
            'paths' => array(
                'js' => 'core/Source'
            )
        ),
        'more' => array(
            'imageUrl' => 'images/',
            'paths' => array(
                'js'=>'more/Source'
            )
        ),
        'jxlib' => array(
            'imageUrl' => 'images/',
            'paths' => array(
                'js' => 'jxlib/src/Source',
                'css' => 'jxlib/src/themes/{theme}/css',
                'cssalt' => 'jxlib/src/themes/{theme}',
                'images' => 'jxlib/src/themes/{theme}/images'
            )
        ),
        'jxlib-extensions' => array(
            'imageUrl' => 'images/',
            'paths' => array(
                'js' => 'jxlib-extensions/trunk/Source',
                'css' => 'jxlib-extensions/trunk/css',
                'images' => 'jxlib-extensions/trunk/css/images'
            )
        )
    )
);

