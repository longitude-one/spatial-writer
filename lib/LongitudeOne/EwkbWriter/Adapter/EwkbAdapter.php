<?php
/**
 * This file is part of the ewkb-writer project.
 *
 * PHP 8.1 | 8.2 | 8.3
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024
 * Copyright Longitude One 2024
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\EwkbWriter\Adapter;

use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\Spatial\PHP\Types\LineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiLineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPointInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPolygonInterface;
use LongitudeOne\Spatial\PHP\Types\PointInterface;
use LongitudeOne\Spatial\PHP\Types\PolygonInterface;
use LongitudeOne\Spatial\PHP\Types\SpatialInterface;

/**
 * Ewkb adapter.
 *
 * This adapter converts a spatial interface into its extended well-known binary representation.
 * It is a binary representation of the spatial interface.
 * This representation is based on the PostGIS implementation.
 *
 * @see https://postgis.net/docs/using_postgis_dbmanagement.html#EWKB_EWKT
 *
 * PostGIS extended formats are currently a superset of the OGC ones,
 * so that every valid OGC WKB/WKT is also valid EWKB/EWKT.
 */
class EwkbAdapter implements AdapterInterface
{
    /**
     * Convert a spatial interface to its extended well-known binary representation.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a binary string representing the spatial interface in EWKB format
     *
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     */
    public function convert(SpatialInterface $spatial): string
    {
        $ewkb = $this->writeFirstByte();
        if (empty($spatial->getSrid())) {
            // WKB mode: every valid OGC WKB/WKT is also valid EWKB/EWKT
            $ewkb .= $this->writeType($spatial);
            $ewkb .= $this->writeCoordinates($spatial);

            return $ewkb;
        }

        // EWKB mode
        $ewkb .= $this->writeTypeAndDimension($spatial);
        $ewkb .= $this->writeSrid($spatial);
        $ewkb .= $this->writeCoordinates($spatial);

        return $ewkb;
    }

    /**
     * Let's call the right method to write coordinates.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
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

            default => throw new UnsupportedSpatialInterfaceException($spatial::class),
        };
    }

    /**
     * Write the first byte.
     *
     * @return string the first byte
     */
    private function writeFirstByte(): string
    {
        return pack('C', 1); // We always write into little endian
    }

    /**
     * Write a line string.
     *
     * @param LineStringInterface $lineString the line string to write
     */
    private function writeLineString(LineStringInterface $lineString): string
    {
        $ewkb = pack('L', count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $ewkb .= $this->writePoint($point);
        }

        return $ewkb;
    }

    /**
     * Write a multi line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi line string to write
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString): string
    {
        $ewkb = pack('L', count($multiLineString->getLineStrings()));
        foreach ($multiLineString->getLineStrings() as $lineString) {
            $ewkb .= $this->convert($lineString);
        }

        return $ewkb;
    }

    /**
     * Write a multipoint.
     *
     * @param MultiPointInterface $multiPoint the multi point to write
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeMultiPoint(MultiPointInterface $multiPoint): string
    {
        $ewkb = pack('L', count($multiPoint->getPoints()));
        foreach ($multiPoint->getPoints() as $point) {
            $ewkb .= $this->convert($point);
        }

        return $ewkb;
    }

    /**
     * Write a multi polygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multi polygon to write
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon): string
    {
        $ewkb = pack('L', count($multiPolygon->getPolygons()));
        foreach ($multiPolygon->getPolygons() as $polygon) {
            $ewkb .= $this->convert($polygon);
        }

        return $ewkb;
    }

    /**
     * Write coordinates of a point.
     *
     * @param PointInterface $point the point to write
     */
    private function writePoint(PointInterface $point): string
    {
        return pack('dd', $point->getX(), $point->getY());
    }

    /**
     * Write number of rings then coordinates of a polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     */
    private function writePolygon(PolygonInterface $polygon): string
    {
        $ewkb = pack('L', count($polygon->getRings()));
        $rings = $polygon->getRings();

        foreach ($rings as $ring) {
            $ewkb .= $this->writeLineString($ring);
        }

        return $ewkb;
    }

    /**
     * Write the SRID.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     */
    private function writeSrid(SpatialInterface $spatial): string
    {
        return pack('L', $spatial->getSrid() ?? 0);
    }

    /**
     * Write the type.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeType(SpatialInterface $spatial): string
    {
        return match ($spatial->getType()) {
            SpatialInterface::POINT => pack('L', 1),
            SpatialInterface::LINESTRING => pack('L', 2),
            SpatialInterface::POLYGON => pack('L', 3),
            SpatialInterface::MULTIPOINT => pack('L', 4),
            SpatialInterface::MULTILINESTRING => pack('L', 5),
            SpatialInterface::MULTIPOLYGON => pack('L', 6),
            // TODO GEOMETRYCOLLECTION pack 7
            default => throw new UnsupportedSpatialTypeException($spatial->getType()),
        };
    }

    /**
     * Write the type and dimension.
     * Important: longitude/doctrine-spatial does not support Z and M dimensions, yet.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     */
    private function writeTypeAndDimension(SpatialInterface $spatial): string
    {
        // Version 5.0.2 doctrine/spatial does not supports Z and M, yet.
        return $this->writeType($spatial) | pack('L', pow(2, 29));
    }
}
