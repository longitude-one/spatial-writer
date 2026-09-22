# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## 1.0.0-RC.0 (2026-09-22)

### ✨ New Features

* Add WKT strategy tests for various geometry types and dimensions ([17f200c](https://github.com/longitude-one/spatial-encoder/commit/17f200cebbae56920007ab960553d1f9f6e93698))
* EWKB is now compliant with all known types and all dimensions ([f8279a7](https://github.com/longitude-one/spatial-encoder/commit/f8279a7b13a447a2e9415cc4da119830c8b6cd9d))
* **geojson:** encode LineString geometries ([9995f1a](https://github.com/longitude-one/spatial-encoder/commit/9995f1a2f85c510038ded8f0e5344e96780bc71e))
* **geojson:** encode MultiLineString geometries ([64acd24](https://github.com/longitude-one/spatial-encoder/commit/64acd247bc3055b84b0bdd00cf71738da5c4f93f))
* **geojson:** encode MultiPoint geometries ([821239f](https://github.com/longitude-one/spatial-encoder/commit/821239f28a7242a85483a81979efb70adca97b97))
* **geojson:** encode MultiPolygon geometries ([d8cebd1](https://github.com/longitude-one/spatial-encoder/commit/d8cebd157884bdaf7049763a9f1304a318e4903f))
* **geojson:** encode points and add shared exception contracts ([975f73e](https://github.com/longitude-one/spatial-encoder/commit/975f73e2c588bf0ed4ba1ec8575b838f52eb6ea4))
* **geojson:** encode polygons with approved ring orientation checks ([b15225f](https://github.com/longitude-one/spatial-encoder/commit/b15225f47da5fe2252c680682d4d7da89d315381))
* **geojson:** preserve geometry collection structure ([2f6c9e9](https://github.com/longitude-one/spatial-encoder/commit/2f6c9e91203020b02465d6fd157317e2847d3c26))
* Implement EWKT strategy with SRID prefix and add corresponding tests ([09bb9cb](https://github.com/longitude-one/spatial-encoder/commit/09bb9cb1a507028d20993f8c271a60325efd1504))
* WKB is now compliant with all known types and dimensions ([92f00b3](https://github.com/longitude-one/spatial-encoder/commit/92f00b39a591686068dbbcd768d0edb6bc6ecaa4))

### 🐛 Bug Fixes

* enforce MySQL spatial type restrictions and add validation tests for unsupported dimensions and empty geometries ([1873b86](https://github.com/longitude-one/spatial-encoder/commit/1873b866b07999ed571e37e9487867e1b1ba3f25))
* Ensure SRID is always packed correctly in writeSrid method ([b558a01](https://github.com/longitude-one/spatial-encoder/commit/b558a01ebd2e2bc151150e5052fb79a8a553f005))
* Fix import of two-dimensional points ([7d357aa](https://github.com/longitude-one/spatial-encoder/commit/7d357aa6ee16cc4bf59c5b4066ded782c89d3563))
* MySQLGeometryEncoder and MySQLPointEncoder to remove SRID and long/lat inversion. ([664aebf](https://github.com/longitude-one/spatial-encoder/commit/664aebfab60ebf83e8390740152a86ed927c40cc))
* Update MySQL encoders to use explicit little-endian packing for consistency ([8290d28](https://github.com/longitude-one/spatial-encoder/commit/8290d28d7973041191ed26de6abd69e4cb0b2120))

### ♻️ Refactoring

* Replace TypeEnum with GeometryTypeEnum in binary strategy classes ([579b1c3](https://github.com/longitude-one/spatial-encoder/commit/579b1c3ebd66ce5b05859eed2927d72fe86fd61d))
* Update geometry object instantiation to use constructor for SRID ([fd8e9b6](https://github.com/longitude-one/spatial-encoder/commit/fd8e9b60da6b5da96acc64925293a7068b82ffef))
* Update GeometryCollection instantiation to use constructor for points and lines ([c2557a8](https://github.com/longitude-one/spatial-encoder/commit/c2557a890f5eec054390c99722ef12074b34e7e3))

### 📚 Documentation

* expand repository guidelines for AI agent responsibilities and spatial standards ([1123998](https://github.com/longitude-one/spatial-encoder/commit/1123998b1ce106183d2a0607e82f5a914202590e))
* format GeoJsonStrategy entry for consistency in strategies table ([c437cc9](https://github.com/longitude-one/spatial-encoder/commit/c437cc9fd664de53538644269cda7ddf79ce23f6))

### 👷 CI/CD

* Update cache keys for Composer dependencies to PHP 8.4 ([4223075](https://github.com/longitude-one/spatial-encoder/commit/4223075008097511fb6c7a4aeecfcfda319ac917))
* Update workflow to trigger on push and pull request events for PHP 8.5 tests ([26fe2d7](https://github.com/longitude-one/spatial-encoder/commit/26fe2d7278ee839c1baa08f694e932799d025859))

### 🔧 Maintenance

* Add pull request template for consistent submission guidelines ([f7b76c4](https://github.com/longitude-one/spatial-encoder/commit/f7b76c4509c2f25bd6824bf741e4a686b2eb8769))
* Add repository guidelines for project structure, documentation, testing, and commit practices ([02484a9](https://github.com/longitude-one/spatial-encoder/commit/02484a908a12b742b2e606058ca90dcd9c86e5b9))
* Remove Docker-related content and add serialization strategies documentation ([1eeeb1d](https://github.com/longitude-one/spatial-encoder/commit/1eeeb1d0400087fe98396757f9a2498bdea86f92))
* remove no longer useful docker tools ([6e2ba85](https://github.com/longitude-one/spatial-encoder/commit/6e2ba8552a498881e8a61358d797fc845acebd3d))
* Rename SpatialWriter to SpatialEncoder and update the API contract to use encode instead of convert ([b35b5f2](https://github.com/longitude-one/spatial-encoder/commit/b35b5f2400b84406b65ee25377b5cfed1c30197d))
* Update from psr-0 to psr-4. ([a19da36](https://github.com/longitude-one/spatial-encoder/commit/a19da36f014999c35e0b0c97c3edc10c9e1e75b2))
* Update README installation command to specify version and add version updater script ([564ba9b](https://github.com/longitude-one/spatial-encoder/commit/564ba9b80e6fafd0da259fc95a2d246c6ed3a9c7))
* Update repository references from spatial-writer to spatial-encoder in README and workflow ([351efde](https://github.com/longitude-one/spatial-encoder/commit/351efde167a3c1a42f15df3a4e51cf537eb21a75))

### 📗​ PHPUnit tests

* Add nested collection tests for MultiLineString, MultiPoint, and MultiPolygon with SRID 4326 ([17b0c78](https://github.com/longitude-one/spatial-encoder/commit/17b0c7864e5f5febee784edaaa61116ab894a6f2))
* **geojson:** cover empty MultiPoint members ([57259c9](https://github.com/longitude-one/spatial-encoder/commit/57259c9d82410770e38b42f654ea5d5f8233eaed))
* **geojson:** cover missing Point interface through writer ([d06a018](https://github.com/longitude-one/spatial-encoder/commit/d06a018e2e527c9f378697568cfb417111f24f7a))
