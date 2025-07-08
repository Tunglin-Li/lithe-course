# Changelog

All notable changes to the Lithe Course plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-19

### Added

- **Course Management System**: Complete course post type with metadata
- **Module & Lesson Structure**: Hierarchical course organization with drag-and-drop reordering
- **Student Enrollment**: Free course enrollment system with access control
- **Progress Tracking**: Lesson completion tracking for enrolled students
- **Gutenberg Blocks**: Modern blocks for course content display
  - Course Metadata block
  - Course Outline block
  - Course Video block (YouTube, Vimeo, BunnyCDN support)
  - Enrollment Button block
  - Lesson Sidebar block
  - My Course block
  - Setting Panel Course block
- **REST API**: Comprehensive API for course structure management
- **Admin Interface**: Course organizer and structure management tools
- **Internationalization**: Translation support with Chinese (Traditional) translations
- **Security**: Proper nonces, sanitization, and permission checks
- **Course Templates**: Single course and lesson templates with block patterns
- **Responsive Design**: Mobile-friendly course layouts

### Security

- Input sanitization and validation throughout
- Nonce verification for all AJAX requests
- Proper capability checks for admin functions
- Secure REST API endpoints with permission callbacks

### Developer Features

- Modern PHP with namespacing and autoloading
- WordPress Coding Standards compliance
- Comprehensive hooks and filters for extensibility
- Well-documented code with PHPDoc comments

### Technical Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Modern browser support for Gutenberg blocks

---

## [Unreleased]

### Planned Features

- WooCommerce integration for paid courses
- Advanced progress analytics
- Course certificates
- Discussion forums
- Assignment system
- Multi-instructor support
