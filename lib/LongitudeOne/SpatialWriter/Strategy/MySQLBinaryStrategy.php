<?php
/**
 * This file is part of the binary-writer project.
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

namespace LongitudeOne\SpatialWriter\Strategy;

use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Helper\AxisOrderEnum;
use LongitudeOne\SpatialWriter\Helper\SpatialReferenceHelper;
use LongitudeOne\SpatialTypes\Enum\TypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * MySQL adapter.
 *
 * This class is responsible for converting a spatial interface to the internal MySQL storage format.
 */
class MySQLBinaryStrategy implements BinaryStrategyInterface
{
    /**
     * Convert a spatial interface to the internal MySQL storage format.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a binary string representing the spatial interface in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        $ims = $this->writeSrid($spatial);
        $ims .= $this->writeFirstByte();
        $ims .= $this->writeType($spatial);
        $ims .= $this->writeCoordinates($spatial);

        return $ims;
    }

    /**
     * Write the coordinates.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the coordinates in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof PointInterface => $this->writePoint($spatial, $spatial->getSrid()),
            $spatial instanceof LineStringInterface => $this->writeLineString($spatial, $spatial->getSrid()),
            $spatial instanceof PolygonInterface => $this->writePolygon($spatial, $spatial->getSrid()),
            $spatial instanceof MultiPointInterface => $this->writeMultiPoint($spatial, $spatial->getSrid()),
            $spatial instanceof MultiLineStringInterface => $this->writeMultiLineString($spatial, $spatial->getSrid()),
            $spatial instanceof MultiPolygonInterface => $this->writeMultiPolygon($spatial, $spatial->getSrid()),

            default => throw new UnsupportedSpatialInterfaceException(sprintf('MySQL adapter does not spatial class %s', $spatial::class))
        };
    }

    /**
     * Write the first byte to inform the reader that the binary is in little endian order.
     *
     * @return string a binary string representing the first byte in the internal MySQL storage format
     */
    private function writeFirstByte(): string
    {
        // The internal MySQL storage is always written into little endian
        return pack('C', 1);
    }

    /**
     * Write the coordinates of line string.
     *
     * @param LineStringInterface $lineString the line string to write
     * @param null|int            $srid       the SRID of the containing spatial collection
     *
     * @return string a binary string representing the line string in the internal MySQL storage format
     */
    private function writeLineString(LineStringInterface $lineString, ?int $srid): string
    {
        $ims = pack('L', count($lineString->getPoints()));

        foreach ($lineString->getPoints() as $point) {
            $ims .= $this->writePoint($point, $srid ?? $lineString->getSrid());
        }

        return $ims;
    }

    /**
     * Write the coordinates of a multi-line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi-line string to write
     * @param null|int                 $srid            the SRID of the containing spatial collection
     *
     * @return string a binary string representing the multi-line string in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString, ?int $srid): string
    {
        $lineStrings = $multiLineString->getLineStrings();
        $ims = pack('L', count($lineStrings));

        foreach ($lineStrings as $lineString) {
            $ims .= $this->writeFirstByte();
            $ims .= $this->writeType($lineString);
            $ims .= $this->writeLineString($lineString, $srid ?? $multiLineString->getSrid());
        }

        return $ims;
    }

    /**
     * Write the coordinates of a multipoint.
     *
     * @param MultiPointInterface $multiPoint the multipoint to write
     * @param null|int            $srid       the SRID of the containing spatial collection
     *
     * @return string a binary string representing the multipoint in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeMultiPoint(MultiPointInterface $multiPoint, ?int $srid): string
    {
        $ims = pack('L', count($multiPoint->getPoints()));

        foreach ($multiPoint->getPoints() as $point) {
            $ims .= $this->writeFirstByte();
            $ims .= $this->writeType($point);
            $ims .= $this->writePoint($point, $srid ?? $multiPoint->getSrid());
        }

        return $ims;
    }

    /**
     * Write the coordinates of a multi-polygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multi-polygon to write
     * @param null|int              $srid         the SRID of the containing spatial collection
     *
     * @return string a binary string representing the multi-polygon in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon, ?int $srid): string
    {
        $ims = pack('L', count($multiPolygon->getPolygons()));

        foreach ($multiPolygon->getPolygons() as $polygon) {
            $ims .= $this->writeFirstByte();
            $ims .= $this->writeType($polygon);
            $ims .= $this->writePolygon($polygon, $srid ?? $multiPolygon->getSrid());
        }

        return $ims;
    }

    /**
     * Write a point.
     *
     * Be careful, depending on the SRID, the axis order may be different.
     *
     * @param PointInterface $point the point to write
     * @param null|int       $srid  the SRID of the containing spatial collection
     *
     * @return string a binary string representing the point in the internal MySQL storage format
     */
    private function writePoint(PointInterface $point, ?int $srid): string
    {
        return match (SpatialReferenceHelper::getAxisOrder($srid ?? $point->getSrid())) {
            AxisOrderEnum::XY => pack('dd', $point->getX(), $point->getY()),
            AxisOrderEnum::YX => pack('dd', $point->getY(), $point->getX()),
        };
    }

    /**
     * Write a Polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     * @param null|int         $srid    the SRID of the containing spatial collection
     *
     * @return string a binary string representing the polygon in the internal MySQL storage format
     */
    private function writePolygon(PolygonInterface $polygon, ?int $srid): string
    {
        $ims = pack('L', count($polygon->getRings()));

        foreach ($polygon->getRings() as $ring) {
            $ims .= $this->writeLineString($ring, $srid ?? $polygon->getSrid());
        }

        return $ims;
    }

    /**
     * Write the SRID.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the SRID in the internal MySQL storage format
     */
    private function writeSrid(SpatialInterface $spatial): string
    {
        return pack('L', $spatial->getSrid() ?? 0);
    }

    /**
     * Write the type.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the type in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    private function writeType(SpatialInterface $spatial): string
    {
        return match ($spatial->getType()) {
            TypeEnum::POINT->value => pack('L', 1),
            TypeEnum::LINESTRING->value => pack('L', 2),
            TypeEnum::POLYGON->value => pack('L', 3),
            TypeEnum::MULTIPOINT->value => pack('L', 4),
            TypeEnum::MULTILINESTRING->value => pack('L', 5),
            TypeEnum::MULTIPOLYGON->value => pack('L', 6),
            TypeEnum::COLLECTION->value => pack('L', 7),
            default => throw new UnsupportedSpatialTypeException(sprintf('MySQL adapter does not support spatial type %s', $spatial->getType()))
        };
    }
}
