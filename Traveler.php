<?php

/**
 * Static class containing methods for handling points and distances on 
 * a sphere
 */
class Traveler {
    private static $initialized = false;
    private static $precision = 6;

    const R = 6371;

    private function __construct() { }

    private static function init() {
        if(self::$initialized) return;

        self::$initialized = true;
    }

    private static function wrap($i, $l, $h) {
        $r = $h - $l;
        $i = $i - $l;
        while($i > $r) $i -= $r;
        while($i < 0) $i += $r;
        return $i + $l;
    }

    private static function limit($i, $l, $h) {
        if($i > $h) return $h;
        if($i < $l) return $l;
        return $i;
    }

    private static function processCoord($c) {
        return [
            self::limit($c[0], -90, 90),
            self::wrap($c[1], -180, 180)
        ];
    }

    /**
     * Get distance between two coordinates
     *
     * @param array $c1 Coordinate 1
     * @param array $c2 Coordinate 2
     * @return number Distance (km)
     */
    public static function distance($c1, $c2) {
        self::init();

        $c1 = self::processCoord($c1);
        $c2 = self::processCoord($c2);

        $lat1 = deg2rad($c1[0]);
        $lat2 = deg2rad($c2[0]);
        $diflat = deg2rad($c2[0] - $c1[0]);
        $diflng = deg2rad($c2[1] - $c1[1]);
        $sinlat = sin($diflat / 2);
        $sinlng = sin($diflng / 2);

        $a = $sinlat * $sinlat + cos($lat1) * cos($lat2) * $sinlng * $sinlng;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::R * $c, self::$precision);
    }

    /**
     * Get point at distance and bearing from given point
     *
     * @param array $c Starting coordinate
     * @param number $b Bearing (degrees)
     * @param number $d Distance (km)
     * @return array Destination coordinate
     */
    public static function destination($c, $b, $d) {
        self::init();

        $c = self::processCoord($c);

        $b = deg2rad(self::wrap($b, 0, 360));
        $inlat = deg2rad($c[0]);
        $inlng = deg2rad($c[1]);
        $a = $d / self::R;

        $lat = asin(sin($inlat) * cos($a) + cos($inlat) * sin($a) * cos($b));
        $lng = $inlng + atan2(   sin($b) * sin($a) * cos($inlat),
                                cos($a) - sin($inlat) * sin($lat) );

        return [
            round(rad2deg($lat), self::$precision),
            round(rad2deg($lng), self::$precision)
        ];
    }
}

?>
