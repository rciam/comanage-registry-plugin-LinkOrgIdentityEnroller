# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
## [v.0.3.2] - 2020-11-18
### Fixed
- Typo error in ConfigureShell.php

## [v.0.3.1] - 2020-11-18
### Added
- Configuration shell script for setup
- SQL script containing tables and constraints required for the plugin

## [v0.3.0] - 2020-10-21
### Added
- Added type of Linking(Implicit|Explicit) in LinkOrgIdentityState model. It will be used for keeping history records.

## [v0.2.0] - 2020-10-01
### Added
- Blacklist Community IdPs during implicit linking

### Fixed
- In case of a multi value Aunthenticated Authority attribute, keep the last IdP
- Handle accounts with multiple verified emails
- Allow empty value for the given name in the OrgIdentity entity
- Allow empty value for the email in the OrgIdentity entity

## [v0.1.0] - 2020-09-28
### Added
- Explicit, Implicit OrgIdentity Linking to CO Person profile/canvas
