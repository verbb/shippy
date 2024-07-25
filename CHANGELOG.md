# Changelog

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