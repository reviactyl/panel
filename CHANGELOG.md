# Changelog
This file is a running track of new features and fixes to each version of the panel released starting with `v2.0.0`.

This project follows [Semantic Versioning](http://semver.org) guidelines.

## v2.2.4
### Fixed
* Fixes task execution jobs to correctly dispatch the next job in the chain.
* Fixes dropdown menu not appearing correctly inside modal when transferring a server.
* Fixes startup variables logging as changed in the activity log even when no change was actually made.
* Fixes multiple issues with the docker image.
* Fixes server transfers getting stuck due to incorrect permission checks in the API.

## v2.2.3
### Fixed
* Fixed issue with Designify Preview not rendering.

## v2.2.2
### Added
* HTTP responses now include default security headers if not otherwise set.
* Adds modal popup when running a Hytale server that requires additional auth.
* Adds support for administrators to view any application API key that has been created, regardless of the owning account.

### Changed
* SFTP sessions are now revoked on nodes when a user changes their password or their account is deleted.
* Remote node access tokens are now scoped to only allow access to servers that belong to the same node. Previously a node could access information and control the installation status for any server in the system.
* The default rate limit for the client API was bumped from `128` to `256` requests per minute.

### Fixed
* Fixed issue with image rendering in `ScreenBlock`.
* [CVE-2026-26016](https://github.com/pterodactyl/panel/security/advisories/GHSA-g7vw-f8p5-c728)
* [GHSA-hr7j-63v7-vj7g](https://github.com/pterodactyl/panel/security/advisories/GHSA-hr7j-63v7-vj7g)
* Fixes improper display of unicode characters in console output.
* Upscaled gravatar image.

## v2.2.1
### Added
* Allocation Preset Manager by @wylayy
* Better Compatibility with Pterodactyl Addons (See Fixes)

### Fixed
* Fixed issue with network allocation alias by @wylayy
* Fixed missing props with `Field` & `Code` components.
* Fixed activity log user gravatar calculated wrongly by @chetanupare

## v2.2.0
### Added
* Turnstile integration by @wylayy
* Quick Card in Dashboard by @wylayy
* Language switcher in Navbar by @wylayy
* Logout button in sidebar by @wylayy
* Added missing translations by translation team on crowdin

### Fixed
* Fixes CVE-2025-68954, CVE-2025-69197, CVE-2025-69198.
* Fixes a self-XSS issue when entering random data into boxes while creating a new database host.
* Fixes missing `HttpForbiddenException` import in the backup status controller.
* Fixes issue where scheduled tasks would execute every minute regardless of their configured cron syntax.
* Pressing `Ctrl+Z` to undo while editing a file no longer deletes the initial file content.
* Fixed incorrect error message being returned when attempting to delete your own account as an admin.
* Fixes node description not being settable via the API.
* Fixes 0-bytes files returning an error when attempting to upload.
* Fixes nodes displaying the first available location even when that field was not edited and the node has a different value set.
* Fixes allocation notes not being reset when a server is deleted.
* Fixes Issue with `daemonText` and `containerText` not showing old values.

### Changed
* Minimum NodeJS version updated to 22 for building.
* Updated all JS and PHP dependencies to their latest versions (where feasible).
* The endpoint for disabling 2FA on an account using the client API changed from `DELETE /api/client/account/two-factor` to `POST /api/client/account/two-factor/disable`
* `^C` in an egg's stop configuration no longer rewrites itself into the default stop configuration.
* Upload size on nodes is no longer restricted to a max of 1024MB, any positive integer value can be used.
* Administrators are now listed first when viewing a list of all users on the system.
* Websocket no longer endlessly polls when connection issues are encountered, or when Wings disconnects the user for a reason that should not be re-attempted.

## v2.1.2
### Added
* New Translations for Admin panel
* Logo, Favicon, Debug Option in Admin > General Settings
* Indonesian (`id`) translation by @wylayy
* French (`fr`) translation by @MartinLavalais
* Added missing translations by translation team on crowdin

### Fixed
* Schedule timezone issue
* Broken dialog structure

## v2.1.0
### Added
* New Translations for Admin Panel
* Logo, Favicon, Debug Option in Admin > General Settings
* Chinese Simplified (`zh`) translation
* Spanish (`es`) translation
* Mail Template Customization
* New button variants
* Font Family Configuration
* Configurable Layouts (!WIP)
* Progressive Web App (PWA Desktop, Mobile App)

### Changed
* Updated Designify Editor to use proper settings system
* Use proper settings variable for Buttons
* Use `revicons`, `react-icons` package for icons
* Security Improvements
* Update primary colour from `orange` to `blue`
* Update Reviactyl assets
* UI Refinements on Admin & Client Dashboard

### Fixed
* Sidebar elements appear broken
* Admin Styles appear broken

### BREAKING CHANGES
* Updated Designify Structure
* Updated Sidebar Structure

## v2.0.2
### Added
* New strings for search & server layout.
* Swedish (`se`) translation

### Changed
* Change Reviactyl UI Components import from `@/components/` to `@/reviactyl/`
* Store user locale in database
* Update Telemetry Collection *(Reviactyl now collects anonymous telemetry data)*
* Make locale list utilise existing methods

### Fixed
* Some elements appear broken in smaller screens
* Fixed UI for widescreen devices

## v2.0.1
### Added
* Customizable Terminal, Daemon Prelude from per-Node Settings
* Artisan Command to Check for Updates (`p:update:check`)
* Hindi (`hi`) translation

### Changed
* Use Reviactyl Repo for Upgrade Command (`p:upgrade`)
* Use proper color shade for Search Bar
* Re-style few parts of Admin dashboard

### Fixed
* Admin dashboard url not opening in sidebar
* Broken parts of admin dashboard

## v2.0.0
### Added
* ThemeEngine for switching between multiple color palettes
* Panel-wide translation system
* Portuguese (Brazil) translation by @d-belli
* New Designify Editor for modifying panel look & feel
* Customizable Color scheme
* Customizable Site Logo
* Customizable Meta tags
* Custom copyright
* Maintenance mode
* Blur for allocation and ips in activity
* Customizable Panel background image
* Per-egg banner for Server Card layout
* Customizable Alert/Notification system
* Top Server Details for all routes under /server
* Language Switcher
* Smart Color Invert Filter
* Nest and Egg limitation for Navigation

### Changed
* Updated UI Design for Administration & Dashboard
* Use Gravatar instead of Boring Avatars
* Updated ServerRow with more accessible Card
* Icons for navigation links
* Option to show password before login
