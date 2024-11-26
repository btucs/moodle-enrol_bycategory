# Change log

## [2024112600] - RELEASED 2024-11-26

### Fixed

- prevent increment notify count if skipped
- seniority info missing exception
- column index exception for Enlisting type waitlist table

### Added

- moodle 4.5 compatility and upgrade notes
- updated messages to MESSAGE_DEFAULT_ENABLED
- enrolment reminder notification days interval. This is at the plugin level as none of 8 customint course specific enrolment setting fields are left unused
- notification cron task to be set for 3 notifications, 4th removal, sent every 3 days
- seniority date from a from an http api
- priority plugin setting per course by seniority or enlisting (default)
- seniority column on waitlist page
- custom message in plugin settings per course displayed when student is placed on the waitlist
- auto enrol based on priority type enlisting (default) vs seniority
- notification counter text in student notifications
- dependency checking if a db field is available
- waitlisting and enrolment based on either seniority or enlisting in the waitlist
- seniority prioritization in waitlisting enrolment
- hook to send welcome message
- custom messages for waitlist info, waitlist notification, and removed from waitlist notification
- api url in plugin settings for external user info to retrieve priority through an api

### Changed

- send waitlist notifications task default period to run daily to 0300z
