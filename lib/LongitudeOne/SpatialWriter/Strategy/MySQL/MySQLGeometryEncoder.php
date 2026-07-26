<?php
/**
 * This file is part of the binary-writer project.
 *
 * PHP 8.4 | 8.5
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024-2026
 * Copyright Longitude One 2024-2026
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialWriter\Strategy\MySQL;

use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;

/**
 * Encodes MySQL geometry coordinate payloads and nested geometry headers.
 */
class MySQLGeometryEncoder
{
    /**
     * MySQLGeometryEncoder constructor.
     *
     * @param MySQLHeaderEncoder $headerEncoder encodes the header of the spatial interface
     * @param MySQLTypeEncoder   $typeEncoder   encodes the type of the spatial interface
     * @param MySQLPointEncoder  $pointEncoder  encodes the ordered coordinates of each point the spatial interface
     */
    public function __construct(
        private readonly MySQLHeaderEncoder $headerEncoder,
        private readonly MySQLTypeEncoder $typeEncoder,
        private readonly MySQLPointEncoder $pointEncoder,
    ) {
    }

    /**
     * Write a geometry's coordinate payload.
     *
     * @param SpatialInterface $spatial the spatial interface to encode
     * @param null|int         $srid    an optional SRID, when omitted, encoder uses the internal spatial interface SRID
     *
     * @return string a binary string representing the coordinates in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    public function writeCoordinates(SpatialInterface $spatial, ?int $srid = null): string
    {
        $srid ??= $spatial->getSrid();

        return match (true) {
            $spatial instanceof PointInterface => $this->pointEncoder->writePoint($spatial, $srid),
            $spatial instanceof LineStringInterface => $this->writeLineString($spatial, $srid),
            $spatial instanceof PolygonInterface => $this->writePolygon($spatial, $srid),
            $spatial instanceof MultiPointInterface => $this->writeMultiPoint($spatial, $srid),
            $spatial instanceof MultiLineStringInterface => $this->writeMultiLineString($spatial, $srid),
            $spatial instanceof MultiPolygonInterface => $this->writeMultiPolygon($spatial, $srid),
            $spatial instanceof CollectionInterface => $this->writeCollection($spatial, $srid),
            default => throw new UnsupportedSpatialInterfaceException(sprintf('MySQL adapter does not support the spatial class %s', $spatial::class)),
        };
    }

    /**
     * Encode the collection into the internal MySQL format.
     *
     * @param CollectionInterface $collection the collection to encode
     * @param null|int            $srid       an optional SRID
     *
     * @return string a binary string representing the collection in the internal MySQL storage format
     */
    private function writeCollection(CollectionInterface $collection, ?int $srid): string
    {
        $binary = pack('L', count($collection->getElements()));

        foreach ($collection->getElements() as $element) {
            $binary .= $this->writeNestedGeometry($element, $srid);
        }

        return $binary;
    }

    /**
     * Encode a line string.
     *
     * @param LineStringInterface $lineString the line string to write
     * @param null|int            $srid       an optional SRID
     *
     * @return string a binary string representing the line string in the internal MySQL storage format
     */
    private function writeLineString(LineStringInterface $lineString, ?int $srid): string
    {
        $binary = pack('L', count($lineString->getPoints()));

        foreach ($lineString->getPoints() as $point) {
            $binary .= $this->pointEncoder->writePoint($point, $srid);
        }

        return $binary;
    }

    /**
     * Write a multi-line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi-line string to write
     * @param null|int                 $srid            an optional SRID
     *
     * @return string a binary string representing the multi-line string in the internal MySQL storage format
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString, ?int $srid): string
    {
        $lineStrings = $multiLineString->getLineStrings();
        $binary = pack('L', count($lineStrings));

        foreach ($lineStrings as $lineString) {
            $binary .= $this->writeNestedGeometry($lineString, $srid);
        }

        return $binary;
    }

    /**
     * Write a multi-point.
     *
     * @param MultiPointInterface $multiPoint the multi-point to write
     * @param null|int            $srid       an optional SRID
     *
     * @return string a binary string representing the multi-point in the internal MySQL storage format
     */
    private function writeMultiPoint(MultiPointInterface $multiPoint, ?int $srid): string
    {
        $binary = pack('L', count($multiPoint->getPoints()));

        foreach ($multiPoint->getPoints() as $point) {
            $binary .= $this->writeNestedGeometry($point, $srid);
        }

        return $binary;
    }

    /**
     * Write a multi-polygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multi-polygon to write
     * @param null|int              $srid         an optional SRID
     *
     * @return string a binary string representing the multi-polygon in the internal MySQL storage format
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon, ?int $srid): string
    {
        $binary = pack('L', count($multiPolygon->getPolygons()));

        foreach ($multiPolygon->getPolygons() as $polygon) {
            $binary .= $this->writeNestedGeometry($polygon, $srid);
        }

        return $binary;
    }

    /**
     * Write the header and coordinates of a nested geometry.
     *
     * @param SpatialInterface $spatial the nested spatial value to write
     * @param null|int         $srid    an optional SRID
     *
     * @return string a binary string representing the nested geometry in the internal MySQL storage format
     */
    private function writeNestedGeometry(SpatialInterface $spatial, ?int $srid): string
    {
        return $this->headerEncoder->writeByteOrder()
            .$this->typeEncoder->writeType($spatial)
            .$this->writeCoordinates($spatial, $srid);
    }

    /**
     * Write a polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     * @param null|int         $srid    an optional SRID
     *
     * @return string a binary string representing the polygon in the internal MySQL storage format
     */
    private function writePolygon(PolygonInterface $polygon, ?int $srid): string
    {
        $binary = pack('L', count($polygon->getRings()));

        foreach ($polygon->getRings() as $ring) {
            $binary .= $this->writeLineString($ring, $srid);
        }

        return $binary;
    }
}
