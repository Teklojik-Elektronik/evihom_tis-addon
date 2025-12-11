# Changelog

All notable changes to this project will be documented in this file.

## [1.5.9] - 2025-01-XX

### Added
- Delete functionality for devices and appliances
  - Delete buttons added to device headers and appliance items
  - Confirmation dialogs for deletion
  - Automatic unpublish from Home Assistant before deletion
  - Cascade deletion: deleting device removes all its appliances
- Search functionality
  - Search box in device/appliance tree view
  - Real-time filtering by device and appliance names
  - Shows devices with matching appliances even if device name doesn't match

### Changed
- Relocated "Publish Selected" button below "Devices & Appliances" title
- Improved button layout with search box on the same row

### Technical
- Added DELETE routes for devices and appliances
- Enhanced DeviceController::destroy() to unpublish appliances before deletion
- Implemented ApplianceController::destroy() with Home Assistant integration cleanup
- Added translation keys: confirm_delete_device, confirm_delete_appliance, device_deleted, appliance_deleted, delete_error

## [1.5.8] - 2025-01-XX

### Added
- Dark theme for device/appliance tree view
- Filter to hide devices with 0 appliances

### Changed
- Updated color scheme: backgrounds (#1a1c20, #23262b, #2d3035), text (#e3e6e9), borders (#3a3f47)

## [1.5.7] - 2025-01-XX

### Added
- Grouped device channel management
- Drawer-style UI for channel selection
- Device templates for 7 device types (Lighting, AC, Switch, Scene, Curtain, Security, Audio)

### Changed
- Replaced modal with drawer UI for better UX
- Added "Select All" functionality for channels
