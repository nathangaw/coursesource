<?php

namespace Coursesource\Woocommerce;

class Common
{
    /**
     * @param $assets
     * @return void
     */
    public static function register_scripts_and_styles( $assets ) {
        foreach ( $assets as $key => $asset ) {
            foreach ( $asset as $name => $dependencies ) {
                //Optionally add dependencies
                $assetInfo = include COURSESOURCE_PLUGIN_ASSETS . "{$key}/{$name}.asset.php";
                if( count( $dependencies ) > 0 ){
                    $assetInfo['dependencies'] = array_merge( $assetInfo['dependencies'], $dependencies );
                }

                if( $key === 'css' ){
                    \wp_enqueue_style( "{$name}-{$key}", COURSESOURCE_PLUGIN_ASSETS_URL . "{$key}/$name.{$key}",  $assetInfo['dependencies'], $assetInfo['version'] );
                }

                if( $key === 'js' ){
                    \wp_register_script( "{$name}-{$key}", COURSESOURCE_PLUGIN_ASSETS_URL . "{$key}/$name.{$key}", $assetInfo['dependencies'], $assetInfo['version'], ['strategy' => 'defer'] );
                    \wp_enqueue_script("{$name}-{$key}");
                }
            }
        }
    }

}