# DENR-CAR PMS Project Documentation

This directory contains the specification-driven documentation for the DENR-CAR Performance Management System (PMS).

## Documentation Structure

### 📋 Global Specifications

- [Project Overview](#project-overview) - System purpose and primary objectives
- [System Scope](#system-scope) - Features and responsibilities covered by PMS
- [User Roles](#user-roles) - Access levels and their responsibilities
- [Core Modules](#core-modules) - Main performance-monitoring areas
- [Technology Stack](#technology-stack) - Application technologies and tools

### 🏗️ Architecture Specifications

- [Architecture Overview](architecture.md) - System structure, components, and high-level design

### 🖥️ Frontend Specifications

- [Frontend Specification](frontend.md) - User interface structure, views, styling, and browser behavior

### 🔧 Backend Specifications

- [Backend Specification](backend.md) - Laravel controllers, models, routes, services, and data processing
- [Excel Upload Specification](uploadexcel.md) - Workbook format, preview, parsing, hierarchy mapping, and transactional import

## Project Overview

The DENR-CAR Performance Management System is a web application for recording, monitoring, and reporting physical and financial performance across DENR-CAR programs, activities, and projects. It provides role-based dashboards and detailed performance views for administrators, regional personnel, and users.

## System Scope

PMS supports the following core workflows:

- User authentication and role-based access
- Dashboard summaries and performance rankings
- Physical target and accomplishment entry
- Financial performance entry and persistence
- Program and indicator monitoring by sector and year
- Excel-based data upload for supported modules
- Edit-history tracking and audit visibility

## User Roles

### Administrator

Manages users and system-wide records, reviews performance information, and has access to administrative dashboards and history logs.

### Regional Personnel

Reviews and manages regional performance data through regional dashboards and program-specific views.

### User

Records and reviews authorized physical and financial performance data for assigned programs and indicators.

## Core Modules

The application organizes performance records into program areas, including:

- GASS
- STO
- ENF
- PA
- ENGP
- LANDS
- SOILCON
- NRA
- PARIA
- COBB
- Continuing programs

Each program area provides physical target and accomplishment monitoring. Applicable workflows also support financial inputs, concerns, remarks, and supporting performance details.

## Technology Stack

- **Backend:** PHP and Laravel
- **Database access:** Laravel Eloquent ORM and migrations
- **Frontend:** Blade templates, HTML, CSS, and JavaScript
- **Asset build:** Vite
- **Authentication and authorization:** Laravel middleware and role-aware routes
- **Testing:** PHPUnit

---

> Keep these specifications updated whenever application behavior, data structures, user roles, or deployment requirements change.
