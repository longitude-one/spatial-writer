# Spatial Encoder

The encoder module provides an interface for encoding any `SpatialInterface` into other formats.

This library provides five strategies for encoding spatial interfaces into other formats:

* A strategy for encoding any spatial interface into Extended Well-Known Binary (EWKB).
* A strategy for encoding any spatial interface into ISO Well-Known Binary (WKB).
* A strategy for encoding spatial interfaces into the internal MySQL storage format.
* A strategy for encoding spatial interfaces into Well-Known Text (WKT).
* A strategy for encoding spatial interfaces into Extended Well-Known Text (EWKT), including a non-zero SRID.

Feel free to provide additional strategies for encoding spatial interfaces into other formats.

See the [strategy guide](docs/strategies.md) for a comparison of the five formats, usage examples, implementation limitations, and links to reference documentation.

## Current status
![longitude-one/spatial--encoder](https://img.shields.io/badge/longitude--one-spatial--encoder-blue)
![Stable release](https://img.shields.io/github/v/release/longitude-one/spatial-encoder)
![Minimum PHP Version](https://img.shields.io/packagist/php-v/longitude-one/spatial-encoder.svg?maxAge=3600)
[![Packagist License](https://img.shields.io/packagist/l/longitude-one/spatial-encoder)](https://github.com/longitude-one/spatial-encoder/blob/main/LICENSE)

[![Last integration test](https://github.com/longitude-one/spatial-encoder/actions/workflows/php-oldest.yaml/badge.svg)](https://github.com/longitude-one/spatial-encoder/actions/workflows/php-oldest.yaml)
[![Downloads](https://img.shields.io/packagist/dm/longitude-one/spatial-encoder.svg)](https://packagist.org/packages/longitude-one/spatial-encoder)
[![codecov](https://codecov.io/gh/longitude-one/spatial-encoder/branch/main/graph/badge.svg?token=NIFES3ETWH)](https://codecov.io/gh/longitude-one/spatial-encoder)


## Installation

```bash
composer require longitude-one/spatial-encoder:1.0.0-RC.0
```
