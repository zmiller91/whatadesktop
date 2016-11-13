<?php

/**
 * Formats an array of images into a structure that is easily consumed by
 * the UI.
 * 
 * @param array $aImages
 * @return array
 */
function format_queue($aImages)
{
    // Group every image by their root
    $aOut = array();
    foreach($aImages as $img)
    {
        if(!isset($aOut[$img['root']]))
        {
            $aOut[$img['root']] = array();
        }

        array_push($aOut[$img['root']], $img);
    }

    // Sort every image according to their width
    foreach($aOut as &$value)
    {
        usort($value, function($a, $b)
        {
            $a = $a["width"];
            $b = $b["width"];
            return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
        });
    }
    
    return $aOut;
}