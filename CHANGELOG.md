# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.5.3] - 2020-03-02

### Changed

- Extended the plugin in order to support RCIAM Assurance Model. The Model is associated with OrgIdentities.

## [v0.5.2] - 2020-02-08

### Fixed

- Not caching Attributes mapped in Plugin's Configuration

## [v0.5.1] - 2020-02-08

### Changed

- isser_dn_attribute, subject_dn_attribute length to 64 chars

### Fixed

- Wrong column name in Upgrade Console script

## [0.5.0] - 2021-02-08

### Added

- Configuration Attribute mapping for Certificate Subject and Issuer DN
- Extend createOrgIdentity in order to correctly save both Certificate Subject and Issuer DN

## [v0.4.1] - 2020-11-19

### Fixed

- user_id_attribute validation rule

## [v0.4.0] - 2020-11-19

### Added

- Configuration User Id Attribute which denotes the type of Identifier, e.g. eduPersonUniqueId

## [v0.3.2] - 2020-11-18

### Fixed

- Typo error in ConfigureShell.php

## [v0.3.1] - 2020-11-18

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
