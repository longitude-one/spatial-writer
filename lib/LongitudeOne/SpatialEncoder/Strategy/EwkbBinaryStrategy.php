<?php
/**
 * This file is part of the spatial-encoder project.
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

namespace LongitudeOne\SpatialEncoder\Strategy;

use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Strategy\Ewkb\EwkbTypeEncoder;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolyhedralSurfaceInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Extended Well-Known Binary adapter for XY, XYZ, XYM and XYZM geometries.
 *
 * This class is responsible for encoding a spatial interface to its well-known binary representation.
 */
class EwkbBinaryStrategy implements StrategyInterface
{
    /**
     * Encode a spatial interface to its well-known binary representation.
     *
     * Well-Known binary representation is a standard binary format for representing simple and complex geometries,
     * defined by the Open Geospatial Consortium (OGC).
     *
     * @see https://libgeos.org/specifications/wkb/#extended-wkb
     *
     * @param SpatialInterface $spatial the spatial interface to encode into EWKB format
     *
     * @return string a binary string representing the spatial interface in EWKB format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    public function encode(SpatialInterface $spatial): string
    {
        return $this->writeGeometry($spatial, true);
    }

    /**
     * Write the byte order.
     *
     * The byte order is always little endian.
     *
     * @return string a binary string representing the byte order in little endian
     */
    private function writeByteOrder(): string
    {
        // We always write into little endian
        return pack('C', 1);
    }

    /**
     * Encode each collection member with its own WKB header and dimension.
     *
     * @param CollectionInterface $collection the collection to encode
     *
     * @return string a binary string representing the collection members
     */
    private function writeCollection(CollectionInterface $collection): string
    {
        $wkb = pack('V', count($collection->getElements()));

        foreach ($collection->getElements() as $element) {
            $wkb .= $this->writeGeometry($element);
        }

        return $wkb;
    }

    /**
     * Write the coordinates.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the coordinates
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof PointInterface => $this->writePoint($spatial),
            $spatial instanceof LineStringInterface => $this->writeLineString($spatial),
            $spatial instanceof PolygonInterface => $this->writePolygon($spatial),
            $spatial instanceof MultiPointInterface => $this->writeMultiPoint($spatial),
            $spatial instanceof MultiLineStringInterface => $this->writeMultiLineString($spatial),
            $spatial instanceof MultiPolygonInterface => $this->writeMultiPolygon($spatial),
            $spatial instanceof CollectionInterface => $this->writeCollection($spatial),
            $spatial instanceof PolyhedralSurfaceInterface => $this->writePolyhedralSurface($spatial),

            default => throw new UnsupportedSpatialInterfaceException($spatial::class),
        };
    }

    /**
     * Write a complete geometry, including the SRID only at the root.
     *
     * @param SpatialInterface $spatial     the geometry to encode
     * @param bool             $includeSrid whether to include its spatial reference
     */
    private function writeGeometry(SpatialInterface $spatial, bool $includeSrid = false): string
    {
        $includeSrid = $includeSrid && 0 !== $spatial->getSrid();
        $wkb = $this->writeByteOrder();
        $wkb .= (new EwkbTypeEncoder())->writeType($spatial, $includeSrid);
        if ($includeSrid) {
            $wkb .= pack('V', $spatial->getSrid());
        }
        $wkb .= $this->writeCoordinates($spatial);

        return $wkb;
    }

    /**
     * Write a line string.
     *
     * @param LineStringInterface $lineString the line string to write
     *
     * @return string a binary string representing the line string
     */
    private function writeLineString(LineStringInterface $lineString): string
    {
        $wkb = pack('V', count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $wkb .= $this->writePoint($point);
        }

        return $wkb;
    }

    /**
     * Write a multi-line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi-line string to write
     *
     * @return string a binary string representing the multi-line string
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString): string
    {
        $wkb = pack('V', count($multiLineString->getLineStrings()));
        foreach ($multiLineString->getLineStrings() as $lineString) {
            $wkb .= $this->writeGeometry($lineString);
        }

        return $wkb;
    }

    /**
     * Write a multipoint.
     *
     * @param MultiPointInterface $multiPoint the multipoint to write
     *
     * @return string a binary string representing the multipoint
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeMultiPoint(MultiPointInterface $multiPoint): string
    {
        $wkb = pack('V', count($multiPoint->getPoints()));

        foreach ($multiPoint->getPoints() as $point) {
            $wkb .= $this->writeGeometry($point);
        }

        return $wkb;
    }

    /**
     * Write a multipolygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multipolygon to write
     *
     * @return string a binary string representing the multipolygon
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon): string
    {
        $wkb = pack('V', count($multiPolygon->getPolygons()));
        foreach ($multiPolygon->getPolygons() as $polygon) {
            $wkb .= $this->writeGeometry($polygon);
        }

        return $wkb;
    }

    /**
     * Write a point.
     *
     * @param PointInterface $point the point to write
     *
     * @return string a binary string representing the point
     */
    private function writePoint(PointInterface $point): string
    {
        if ($point->isEmpty()) {
            $dimension = 2 + ($point->hasZ() ? 1 : 0) + ($point->hasM() ? 1 : 0);

            // Canonical little-endian IEEE-754 quiet NaN for every ordinate.
            return str_repeat(pack('H*', '000000000000f87f'), $dimension);
        }

        return pack('e*', ...$point->toArray());
    }

    /**
     * Write a polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     *
     * @return string a binary string representing the polygon
     */
    private function writePolygon(PolygonInterface $polygon): string
    {
        $wkb = pack('V', count($polygon->getRings()));

        foreach ($polygon->getRings() as $ring) {
            $wkb .= $this->writeLineString($ring);
        }

        return $wkb;
    }

    /**
     * Write a polyhedral surface as a count followed by complete polygon WKBs.
     *
     * @param PolyhedralSurfaceInterface $surface the surface to write
     */
    private function writePolyhedralSurface(PolyhedralSurfaceInterface $surface): string
    {
        $wkb = pack('V', count($surface->getPatches()));
        foreach ($surface->getPatches() as $patch) {
            $wkb .= $this->writeGeometry($patch);
        }

        return $wkb;
    }
}
