# Changelog

All notable changes to `Laravel Wallet` will be documented in this file

## 1.1.2 - 2025-06-12

### Changed

- Update github action.

## 1.1.1 - 2025-06-12

### Changed

- Update github action.

## 1.1.0 - 2025-06-12

### Added

- Add support for Laravel 11 and 12.
- Drop support for PHP 7.4.

## 1.0.3 - 2023-09-20

### Fixed

- Fix placement return type on Money DTO.

## 1.0.2 - 2023-09-20

### Changed

- Update `display` method on Money DTO.

## 1.0.1 - 2023-09-19

### Fixed

- Declaration of Money cast not compatible.

## 1.0.0 - 2023-09-19

Rework and introduce new features.

### Added

- Add support for Laravel 9, 10 and remove support for previous versions.
- Add more setting for wallet types.
- Add type-safety.
- Add Money casting.
- Add Money DTO.

### Changed

- Update to use PHP enum.
- Change migration to use anonymous class.
- Remove default wallet.
- Update readme.

## 0.0.2 - 2022-01-22

### Added

- Add `config_path` checking for lumen support.

## 0.0.1 - 2022-01-21

### Added

- Add description and metadata field to wallet.

## 0.0.0 - 2021-08-25

- initial release

### Added

- Add `deposit`, `depositNow`, `withdraw`, `withdrawNow`, `transfer`, `transferNow` methods.
