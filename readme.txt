=== Lithe Course ===
Contributors: chopperbell
Tags: course, education, learning, academy, lms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, modern course management plugin for WordPress. Create, organize, and deliver courses with intuitive block-based tools.

== Description ==

Lithe Course is a block-first learning management system (LMS) plugin for WordPress. It lets you create and organize courses with modules and lessons, manage student enrollment, and track progress — all with a simple, modern interface.

### Key Features
- **Course Management** – Create and organize courses, modules, and lessons  
- **Student Enrollment** – Manage enrollments and monitor progress  
- **Modern Blocks** – Gutenberg blocks for course content, videos, and enrollment  
- **Drag & Drop Organizer** – Intuitive course structure editor  
- **Progress Tracking** – Lesson completion and course progress monitoring  
- **Block Theme Compatible** – Designed specifically for WordPress block themes  

### Perfect For
- Online educators and trainers  
- Universities and schools  
- Corporate learning platforms  
- WordPress professionals who want a clean, block-based LMS  

### Learn More
For a full step-by-step tutorial with images, please visit:  
👉 [Complete Lithe Course Introduction Guide](https://tunglinli.com/blog/lithe-course-intro/)

== Screenshots ==

1. Manage all your courses from the WordPress admin list view
2. Add and customize course blocks in the block editor
3. Example layout of a course template using Lithe Course
4. Example layout of a lesson template with progress tracking

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/lithecourse`, or install directly from the Plugins screen.  
2. Activate the plugin through the “Plugins” screen.  
3. Go to **Courses** in your WordPress admin to start creating courses.  
4. Use the provided blocks in the Gutenberg editor to build course pages.  

== Frequently Asked Questions ==

= What are the minimum requirements? =  
- WordPress 6.0 or higher  
- PHP 8.0 or higher  

= Can students track their progress? =  
Yes. Lesson completion tracking and overall course progress are built in.  

= Is the plugin compatible with all themes? =  
Lithe Course is designed for **block themes only**. It may not work properly with classic themes.  

= Does this plugin use external services? =  
Yes, but only when you embed videos. If you add YouTube, Vimeo, or Bunny.net videos into your course content, playback requests are sent to those services.  

- **YouTube (Google)** – Embedding and playback of course videos  
  - Data sent: Video URL and standard web request data (IP address, cookies as per Google policies)  
  - When: Only when a visitor plays a YouTube video  
  - [Terms](https://www.youtube.com/t/terms) | [Privacy](https://policies.google.com/privacy)  

- **Vimeo** – Embedding and playback of course videos  
  - Data sent: Video URL and standard web request data  
  - When: Only when a visitor plays a Vimeo video  
  - [Terms](https://vimeo.com/terms) | [Privacy](https://vimeo.com/privacy)  

- **Bunny.net (BunnyCDN)** – Embedding and playback of course videos  
  - Data sent: Video URL and standard web request data  
  - When: Only when a visitor plays a Bunny.net video  
  - [Terms](https://bunny.net/terms/) | [Privacy](https://bunny.net/privacy/)  

📌 **Important:** All course data, student enrollments, and progress tracking remain stored in your WordPress database. No course content or student information is sent to these services.  

== Changelog ==

= 1.0.1 =
* Minor update to utility functions
* Updated screenshots in plugin page

= 1.0.0 =  
* Initial release  
* Course post type and drag-and-drop organizer  
* Student enrollment system  
* Lesson completion tracking  
* Course blocks for Gutenberg  
* Course taxonomy and categorization  

== Upgrade Notice ==

= 1.0.1 =
Minor improvements to utilities and updated screenshots.

= 1.0.0 =  
First release of Lithe Course.
