# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-09-08

Initial release.

- `WistiaClient::account()` and `accountStats()`
- Projects: list, get, create, update, delete, stats
- Medias: list, get, update, delete, copy, stats
- Stats: by date, engagement, visitors, visitor, events
- Captions: list, create
- Config file with `WISTIA_API_KEY` / `WISTIA_TIMEOUT`, falling back to `services.wistia.token`
- Null-safe: every call returns `null`/`false` on network failure or non-2xx instead of throwing

## [Unreleased]
