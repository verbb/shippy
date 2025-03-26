# Changelog

## 1.2.6 - 2025-03-27

### Changed
- Update packages to return a miminum value for weight and dimensions based on the decimal rounding given.

## 1.2.5 - 2025-03-11

### Added
- Add support for multi-box rates for USPS.

## 1.2.4 - 2025-03-07

### Added
- Support Laravel Collections 12. (thanks @tm1000).

## 1.2.3 - 2025-03-06

### Changed
- Update FedEx tracking URL.

## 1.2.2 - 2025-03-05

### Added
- Add `carrier` to Label, Rate and Tracking events.

## 1.2.1 - 2025-03-03

### Changed
- Update UPS to use `transId` and `transactionSrc`.

## 1.2.0 - 2025-02-12

### Changed
- USPS service codes are now provided in the format `{mailClass}__{rateIndicator}__{processingCategory}`.
- USPS service codes are now expanded to allow more fine-grained control.

## 1.1.2 - 2025-02-11

### Added
- Add support for legacy USPS API.

## 1.1.1 - 2025-01-30

### Changed
- Update `symfony/event-dispatcher` and `symfony/serializer` packages to support `^5.0`.

### Fixed
- Add missing FedEx service codes.

## 1.1.0 - 2024-12-06

### Added
- Add Online and Post Office rates for Royal Mail.

### Changed
- Extract all static rate providers into separate files.
- Update Royal Mail International tracked options for some countries.
- Royal Mail rates now default to Online rates.

## 1.0.11 - 2024-12-05

### Added
- Add 2024 pricing for Royal Mail.
- Add constants for Royal Mail.

### Fixed
- Fix an error with box-packing for static rates.
- Fix static rates box-sizing not working correctly.

## 1.0.10 - 2024-10-03

### Fixed
- Fix total-value package functions enforcing integer values.

## 1.0.9 - 2024-10-03

### Changed
- Update `getTotalWidth` and `getTotalLength` function to assume multiple boxes will be stacked for better rate handling.

## 1.0.8 - 2024-10-01

### Changed
- Change USPS behaviour to now only return the cheapest rate for the same service code to prevent unnecessary duplicate rates.

### Fixed
- Fix USPS rates not using a minimum dimension/weight value for packages.

## 1.0.7 - 2024-08-25

### Added
- Add support for tax-inclusive rates for UPS, where applicable.

### Changed
- Improve USPS rates by not returning duplicate services (that have the same price, just different description).
- Update USPS service codes for new API.

## 1.0.6 - 2024-08-03

### Fixed
- Fix rounding of parcel dimension and weights for Australia Post for more accurate rates.

## 1.0.5 - 2024-07-26

### Added
- Add `includeInsurance` for UPS packages.
- Add `DeclaredValue` for UPS packages.

### Fixed
- Fix UPS `pickupType` reference.

## 1.0.4 - 2024-07-25

### Fixed
- Fix New Zealand Post package rounding for rates.
- Fix packages not retaining destination weight/dimension units when being converted.

## 1.0.3 - 2024-07-24

### Changed
- Update deprecated `${var}` in strings to `{$var}`.

### Fixed
- Fix Guzzle truncating error messages in responses.
- Fix an error when catching Guzzle-based errors.

## 1.0.2 - 2024-07-19

### Added
- Add Aramex New Zealand carrier.

### Fixed
- Fix Aramex Australia rates response.

## 1.0.1 - 2024-07-11

### Fixed
- Fix UPS addresses using `Residential` and not `ResidentialAddressIndicator`.
- Fix setting “residential” to be false for all addresses.

## 1.0.0 - 2024-05-26

- Initial release