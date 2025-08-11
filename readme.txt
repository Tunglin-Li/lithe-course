=== Lithe Course ===
Contributors: chopperbell
Tags: course, education, learning, academy, lms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful course management plugin for WordPress with modern blocks and intuitive course organization.

== Description ==

Lithe Course is a comprehensive learning management system (LMS) plugin for WordPress that allows you to create, organize, and manage online courses with ease. Built with modern WordPress blocks and featuring a drag-and-drop course structure editor.

**Key Features:**

* **Course Management**: Create and organize courses with modules and lessons
* **Student Enrollment**: Manage student enrollments and track progress
* **Modern Blocks**: Gutenberg blocks for course content, videos, and enrollment
* **Drag & Drop Structure**: Intuitive course structure editor
* **Progress Tracking**: Track student lesson completion
* **Course Metadata**: Rich course information and settings
* **Block Theme Compatible**: Designed specifically for WordPress block themes

**Perfect for:**

* Educational institutions
* Online course creators
* Training organizations
* Corporate learning platforms
* WordPress block theme users

== Installation ==

**Important**: This plugin is designed specifically for WordPress block themes. It may not work properly with classic themes.

1. Upload the plugin files to the `/wp-content/plugins/lithe-course` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Courses' in your WordPress admin to start creating courses
4. Use the course blocks in the Gutenberg editor to build your course pages

== External Services and Libraries ==

This plugin uses several external services and third-party libraries to provide enhanced functionality. 
The following section explains, for each service or library: what it is, its purpose, what data is sent and when, and provides links to their terms of service and privacy policies where applicable.

### External Video Services

**YouTube (Google)**
- **Purpose**: Video hosting and embedding for course content
- **Data sent**: Video URLs and embed requests when users view course videos
- **When data is sent**: Only when course videos are loaded and played
- **Terms of Service**: https://www.youtube.com/t/terms
- **Privacy Policy**: https://policies.google.com/privacy

**Vimeo**
- **Purpose**: Video hosting and embedding for course content
- **Data sent**: Video URLs and embed requests when users view course videos
- **When data is sent**: Only when course videos are loaded and played
- **Terms of Service**: https://vimeo.com/terms
- **Privacy Policy**: https://vimeo.com/privacy

**BunnyCDN (Bunny.net)**
- **Purpose**: Video hosting and streaming for course content
- **Data sent**: Video URLs and embed requests when users view course videos
- **When data is sent**: Only when course videos are loaded and played
- **Terms of Service**: https://bunny.net/terms/
- **Privacy Policy**: https://bunny.net/privacy/

### JavaScript Libraries

**@dnd-kit (Drag and Drop Kit)**
- **Purpose**: Provides drag-and-drop functionality for course structure editing
- **Data sent**: None - this is a client-side library only
- **License**: MIT License
- **Repository**: https://github.com/clauderic/dnd-kit

**Motion (Framer Motion)**
- **Purpose**: Provides smooth animations and transitions in the user interface
- **Data sent**: None - this is a client-side library only
- **License**: MIT License
- **Repository**: https://github.com/motiondivision/motion

**@wordpress/icons**
- **Purpose**: Provides WordPress-style icons for the user interface
- **Data sent**: None - this is a client-side library only
- **License**: GPL v2 or later
- **Repository**: https://github.com/WordPress/gutenberg

**@wordpress/scripts**
- **Purpose**: Build tools for development (not included in production)
- **Data sent**: None - this is a development dependency only
- **License**: GPL v2 or later
- **Repository**: https://github.com/WordPress/gutenberg

### Data Privacy

- **No personal data is sent to third-party services** except for standard web requests when loading embedded videos
- **All course data, student information, and user interactions are stored locally** in your WordPress database
- **Video services only receive the video URL and standard web analytics** (cookies, IP addresses) as per their respective privacy policies
- **No course content, student names, or enrollment data is shared** with any third-party services

== Development ==

This plugin uses modern build tools for development. The source code for all JavaScript and CSS files is available in the `blocks/` directory.

### Building from Source

To build the plugin from source:

1. Install dependencies:
   ```bash
   npm install
   ```

2. Build the plugin:
   ```bash
   npm run build
   ```

3. For development with hot reloading:
   ```bash
   npm run start
   ```

### Source Code Structure

- **Source files**: Located in `blocks/` directory
- **Built files**: Generated in `build/` directory using @wordpress/scripts
- **Dependencies**: Managed via npm (see package.json)

The source code is also available on GitHub: https://github.com/Tunglin-Li/lithe-course

== Frequently Asked Questions ==

= What are the minimum requirements? =

* WordPress 6.0 or higher
* PHP 8.0 or higher

= How do I create a course? =

1. Go to 'Courses' in your WordPress admin
2. Click 'Add New Course'
3. Add your course content using the available blocks
4. Set up your course structure with modules and lessons
5. Configure enrollment settings

= Can students track their progress? =

Yes, the plugin includes lesson completion tracking and progress monitoring for enrolled students.

= Is the plugin compatible with themes? =

Lithe Course is designed to work with only WordPress block theme.

== Changelog ==

= 1.0.0 =
* Initial release
* Course post type with structure management
* Student enrollment system
* Course blocks for Gutenberg
* Lesson completion tracking
* Drag-and-drop course organizer
* Course taxonomy and categorization

== Upgrade Notice ==

= 1.0.0 =
Initial release of Lithe Course plugin. 