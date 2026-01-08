# WP REST Post Analytics

A lightweight WordPress plugin that exposes post analytics via a REST API and a minimal admin UI.

## Features
- Custom WordPress REST API endpoint
- Aggregated post statistics using WPDB
- Capability-based access control (relaxed for demo)
- Admin dashboard page that consumes the REST API

## REST Endpoint
GET /wp-json/analytics/v1/post-stats

Returns:
- Total published posts
- Total draft posts
- Author-wise published post count

## Admin UI
Accessible via:
WP Admin → Tools → Post Analytics

The admin page fetches data from the REST API using a WordPress REST nonce.

## Tech Stack
- PHP
- WordPress REST API
- MySQL (WPDB)
- Vanilla JavaScript

## Notes
For demo purposes, the REST endpoint is public.
In production, access can be restricted using WordPress capabilities.
