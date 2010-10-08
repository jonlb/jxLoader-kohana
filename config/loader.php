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
                'js' => 'jxlib/Source',
                'css' => 'jxlib/themes/{theme}/css',
                'cssalt' => 'jxlib/themes/{theme}',
                'images' => 'jxlib/themes/{theme}/images'
            )
        ),
        'jx-extensions' => array(
            'imageUrl' => 'images/',
            'paths' => array(
                'js' => 'jx-extensions/Source',
                'css' => 'jx-extensions/css',
                'images' => 'jx-extensions/images'
            )
        )
    )
);

