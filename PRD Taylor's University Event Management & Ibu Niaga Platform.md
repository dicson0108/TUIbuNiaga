# Product Requirements Document: Taylor's University Event Management & Ibu Niaga Platform

## 1. Overview

### 1.1 Purpose

This document defines the requirements for a dynamic, interactive web application for Taylor's University that combines event management, program registration for the Ibu Niaga initiative, skill development modules, an Open Bazaar showcase, and a support system.

### 1.2 Product Summary

The platform lets visitors learn about Ibu Niaga, register for programs and events, upload required documents, track application status, take part in skill development modules, showcase products and services, and get help through tickets and a Help Center. Moderators and Admins manage data, review applications and monitor performance through a dashboard.

### 1.3 Technology Stack

- Front-end: HTML5, CSS3, JavaScript
- Back-end: PHP
- Database: MySQL
- Email: SMTP
- Hosting: Apache/Nginx with PHP and MySQL

### 1.4 Design Direction

Light theme with red accents. Responsive layout for desktop, tablet and mobile. Language switcher available on every page.

## 2. Goals and Success Metrics

### 2.1 Goals

- Provide a single platform for Ibu Niaga program registration and event participation.
- Make application progress transparent to applicants.
- Promote Ibu Niaga members and their businesses through the Open Bazaar.
- Give Moderators and Admins efficient tools to manage data and monitor performance.
- Support English, Malay and Chinese users.

### 2.2 Success Metrics

| Metric | Target |
| --- | --- |
| Registration completion rate | At least 70% of started forms submitted |
| Application review turnaround | Majority reviewed within the set service window |
| Ticket first-response time | Within one working day |
| Bazaar listings published | Growing month over month |
| Page load time | Under 3 seconds on standard broadband |

Targets are initial proposals and should be confirmed with stakeholders.

## 3. Users and Roles

| Role | Description | Key Permissions |
| --- | --- | --- |
| Visitor | Not logged in | View public pages, Help Center, register, log in |
| User | Registered participant (any valid email) | Apply to programs, upload documents, join events, manage own bazaar listings, raise tickets |
| Moderator | Operations staff | Review applications, CRUD on participant records and content, moderate bazaar listings and feedback, handle tickets. Cannot change roles or delete users |
| Admin | System owner | All Moderator permissions plus user and role management, event and module management, analytics, system settings, audit log |

## 4. Scope

### 4.1 In Scope

- Authentication, role-based access and multi-language interface (English, Malay, Chinese)
- Program registration with document upload and status tracking
- Ibu Niaga promotion pages and skill development modules
- Events with categories, capacity limits, waitlist, ratings and comments
- Open Bazaar with showcase listings and enquiry/order requests
- Admin and Moderator CRUD, analytics dashboard
- Email and in-app notifications, automated reminders
- Ticketing system and Help Center

### 4.2 Out of Scope

- Online payment processing
- SMS and WhatsApp notifications
- Native mobile applications
- Third-party social login

## 5. Functional Requirements

### 5.1 User Management and Authentication

| ID | Requirement |
| --- | --- |
| UM-01 | Anyone with a valid email can register. |
| UM-02 | Registration form is available in English, Malay and Chinese. |
| UM-03 | Email verification is required before first login. |
| UM-04 | A confirmation email and an in-app welcome notification are sent after registration. |
| UM-05 | Login redirects users to a role-specific dashboard. |
| UM-06 | Password reset via time-limited email link. |
| UM-07 | Users can edit their profile, language and notification preferences. |
| UM-08 | Admin can create, edit, suspend and delete users and assign roles. |

### 5.2 Program Registration

| ID | Requirement |
| --- | --- |
| PR-01 | Interactive registration form with participant, business and program details. |
| PR-02 | Users can save a draft and submit later. |
| PR-03 | Users upload required documents (for example IC, business proposal). Accepted types: PDF, JPG, PNG, with a file size limit. |
| PR-04 | Application status bar shows Submitted, In Review, Needs Documents, Approved or Rejected. |
| PR-05 | Status history and reviewer remarks are visible to the applicant. |
| PR-06 | Every status change triggers an email and an in-app notification. |
| PR-07 | Applicants can re-upload documents when status is Needs Documents. |
| PR-08 | Moderators and Admins review, approve, reject or request documents. |

### 5.3 Ibu Niaga Promotion and Skill Development Modules

| ID | Requirement |
| --- | --- |
| IN-01 | Home page introduces the Ibu Niaga platform. |
| IN-02 | About page explains what Ibu Niaga is. |
| IN-03 | Past Projects pages list projects with details and galleries. |
| IN-04 | Members Directory with profiles, search and filter. |
| IN-05 | Success Stories pages. |
| IN-06 | Skill modules: Emotional Support, Pastry Skills, Financial Literacy, Table Setting Etiquette. |
| IN-07 | Each module has an overview, lessons, schedule and resources. |
| IN-08 | Moderators and Admins manage all of this content through the content management pages. |

### 5.4 Events

| ID | Requirement |
| --- | --- |
| EV-01 | Admin creates, edits and deletes events with category, type (physical, online or hybrid), venue or link, capacity and poster image. |
| EV-02 | Users browse events with search, category filter and sorting. |
| EV-03 | Users join an event with one click. No approval step. |
| EV-04 | When capacity is reached, users join a waitlist and are promoted automatically when a place opens. |
| EV-05 | Users can cancel participation. |
| EV-06 | Users can rate and comment on events they joined. |
| EV-07 | Moderators can hide or delete inappropriate comments. |

### 5.5 Open Bazaar

| ID | Requirement |
| --- | --- |
| OB-01 | Users create, edit and delete product or service listings with photos, description and category. |
| OB-02 | New or edited listings can require moderator approval before publishing. |
| OB-03 | Public visitors browse and search listings by category. |
| OB-04 | Visitors and users submit enquiry or order requests to the seller. No payment is processed. |
| OB-05 | Sellers receive enquiries by email and in-app notification and can reply and mark them handled. |
| OB-06 | Business promotion tools: featured listing slots, seller profile page, shareable listing links. |
| OB-07 | Moderators can unpublish or delete listings and manage flagged items. |

### 5.6 Database Management and Analytics

| ID | Requirement |
| --- | --- |
| DB-01 | Moderators and Admins can create, read, update and delete participant records, applications, events, modules and content. |
| DB-02 | Records support search, filter, sort and CSV export. |
| DB-03 | Participant data is stored securely, with access limited by role. |
| DB-04 | Admin dashboard shows registration and approval counts, event attendance and ratings, bazaar activity, and ticket volume and resolution time. |
| DB-05 | Dashboard supports date filters and export to CSV and PDF. |
| DB-06 | An audit log records create, update and delete actions with user and timestamp. |

### 5.7 Support and Notifications

| ID | Requirement |
| --- | --- |
| SN-01 | Notifications are delivered by email and in-app. |
| SN-02 | Users can set notification preferences. |
| SN-03 | Automated reminders: event start, application document deadlines, pending application actions and unanswered tickets. |
| SN-04 | Admin manages email templates, reminder schedules and broadcast announcements. |
| SN-05 | Users create support tickets with category, priority and optional attachment. |
| SN-06 | Moderators assign, reply to, prioritise and close tickets. |
| SN-07 | Ticket status: Open, In Progress, Resolved, Closed. |
| SN-08 | Help Center provides searchable FAQs and articles by category and links to ticket creation. |

## 6. Page Inventory

| Area | Pages |
| --- | --- |
| Public | Home, About Ibu Niaga, Past Projects, Members Directory, Success Stories, Skill Modules, Events, Open Bazaar, Help Center, Contact, Register, Login, Reset Password, Email Verification |
| User | Dashboard, My Profile, Program Registration, My Applications, My Events, My Bazaar Listings, Enquiries, Notifications, My Tickets |
| Moderator | Dashboard, Application Review, Participant Records, Bazaar Moderation, Feedback Moderation, Ticket Management, Content Management |
| Admin | Dashboard and Analytics, User and Role Management, Event Management, Module Management, Reminder and Notification Settings, Translation Management, System Settings, Audit Log |

## 7. Data Model (Key Tables)

| Table | Key Fields |
| --- | --- |
| users | id, name, email, password\_hash, role, language, status, email\_verified\_at |
| applications | id, user\_id, program\_id, status, remarks, submitted\_at, reviewed\_by |
| application\_documents | id, application\_id, type, file\_path, uploaded\_at |
| application\_status\_history | id, application\_id, status, changed\_by, note, changed\_at |
| programs | id, name, description, status |
| events | id, title, category\_id, type, venue, link, capacity, poster\_path, start\_at, end\_at |
| event\_registrations | id, event\_id, user\_id, status (joined or waitlisted), joined\_at |
| event\_feedback | id, event\_id, user\_id, rating, comment, status |
| modules | id, title, overview, schedule, resources |
| module\_lessons | id, module\_id, title, content, sort\_order |
| members | id, name, bio, business\_name, photo\_path |
| success\_stories | id, title, body, member\_id, published\_at |
| projects | id, title, summary, outcomes, gallery |
| bazaar\_listings | id, user\_id, title, description, category, photos, status |
| enquiries | id, listing\_id, sender\_name, sender\_email, message, status |
| notifications | id, user\_id, type, message, read\_at, created\_at |
| reminders | id, type, schedule, template\_id, active |
| tickets | id, user\_id, category, priority, status, assigned\_to |
| ticket\_replies | id, ticket\_id, user\_id, message, attachment\_path |
| help\_articles | id, category, title, body, language |
| translations | id, key, language, value |
| audit\_logs | id, user\_id, action, entity, entity\_id, created\_at |

## 8. Key User Flows

### 8.1 Program Application

1. User registers and verifies email.
2. User opens the Program Registration form and completes it.
3. User uploads required documents and submits.
4. Status becomes Submitted and a confirmation is sent.
5. Moderator reviews and sets In Review, Needs Documents, Approved or Rejected.
6. User is notified of every change and tracks progress on the status bar.

### 8.2 Event Participation

1. User browses events and opens an event.
2. User clicks Join. If capacity is full, the user joins the waitlist.
3. Reminder is sent before the event.
4. After the event, the user can rate and comment.

### 8.3 Bazaar Enquiry

1. Visitor opens a listing and submits an enquiry or order request.
2. Seller is notified by email and in-app.
3. Seller replies and marks the enquiry as handled.

### 8.4 Support Ticket

1. User checks the Help Center, then creates a ticket if unresolved.
2. Moderator assigns, replies and resolves the ticket.
3. User is notified and can reopen within a set period.

## 9. Non-Functional Requirements

### 9.1 Security

- Passwords hashed with bcrypt or Argon2.
- Prepared statements (PDO) to prevent SQL injection.
- Output escaping to prevent XSS; CSRF tokens on all forms.
- Server-side role checks on every protected page and action.
- File uploads validated by type and size, stored outside the public web root with randomised names.
- Session hardening: secure, HttpOnly cookies and session expiry.
- HTTPS enforced.
- Personal data access restricted by role and recorded in the audit log.

### 9.2 Performance and Reliability

- Pages load within 3 seconds under normal load.
- Database indexes on frequently searched fields.
- Scheduled database backups.

### 9.3 Usability and Accessibility

- Responsive design.
- Keyboard navigation, sufficient colour contrast and alt text on images.
- Consistent navigation and a language switcher on every page.

### 9.4 Localisation

- All interface text stored as translations in English, Malay and Chinese.
- UTF-8 throughout, with Chinese-compatible fonts.
- Emails sent in the user's chosen language.

### 9.5 Compatibility

- Latest versions of Chrome, Edge, Firefox and Safari, plus mobile browsers.

### 9.6 Compliance

- Handling of personal data, including IC documents, follows applicable data protection requirements (Malaysia PDPA) and the university's policies.

## 10. UI and Visual Guidelines

- Light background with white cards.
- Red as the primary accent for buttons, links, active states and the status bar highlight.
- Neutral greys for text and borders.
- Clear status badges for applications, tickets and listings.
- Consistent components: cards, forms, tables, modals and toasts.

## 11. Assumptions and Dependencies

- An SMTP service is available for emails.
- Hosting supports PHP and MySQL.
- Taylor's University and Ibu Niaga provide content (about text, past projects, member profiles, success stories, module materials).
- Translations for Malay and Chinese are provided or reviewed by native speakers.

## 12. Risks and Mitigations

| Risk | Mitigation |
| --- | --- |
| Sensitive documents (IC) exposed | Private storage, role-based access, audit logging |
| Spam registrations or listings | Email verification, rate limiting, moderator approval of listings |
| Poor translation quality | Native-speaker review and a translation management page |
| Unsuitable bazaar content | Moderation queue and flagging |
| Scope creep | Payment and SMS/WhatsApp kept out of scope for this release |

## 13. Delivery Phases

| Phase | Deliverables |
| --- | --- |
| 1. Foundation | Database schema, authentication, roles, language switcher, base layout |
| 2. Registration | Program registration form, document upload, status tracking, notifications |
| 3. Content | Ibu Niaga pages, members, success stories, skill modules |
| 4. Events and Bazaar | Events with waitlist and feedback, Open Bazaar and enquiries |
| 5. Operations | Moderator and Admin CRUD, analytics dashboard, audit log |
| 6. Support | Ticketing, Help Center, automated reminders |
| 7. Release | Testing, security review, content loading, deployment |

## 14. Open Items

- Final list of required documents per program.
- File size limit for uploads.
- Reminder timing rules.
- Whether bazaar listings require approval before publishing.
- Final list of event categories.
