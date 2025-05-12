# Dynamic Records System

<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></p>

A flexible, dynamic data entry and management system built with Laravel. This application allows you to create custom data templates with various field types, then collect and manage records based on those templates without writing additional code.

## Core Concepts

### Templates

Templates are the foundation of the system. They define the structure for a specific type of data collection:

- Each template has a name and description
- Templates contain a collection of fields that define what data to collect
- Examples: Employee Information, Customer Feedback, Project Details, Event Registration

### Fields

Fields are the individual data points collected within a template:

- Supports multiple field types: text, select, radio, checkbox, date, number, boolean
- Each field can be marked as required or optional
- Fields have a display order that determines their position in forms
- Select, radio, and checkbox fields can have multiple options

### Records

Records are individual entries created based on a template:

- Each record is associated with a specific template
- Records contain values for the fields defined in the template
- Values for select, radio, and checkbox fields are linked to predefined options

## Key Features

- **Dynamic Form Creation**: Create custom templates with various field types without coding
- **Flexible Field Types**: Supports text, select, radio, checkbox, date, number, and boolean fields
- **Validation**: Automatic validation of required fields and option values
- **Data Organization**: Records are organized by template type for easy management
- **API-First Design**: Complete REST API for all operations
- **User Management**: Admin and regular user roles with appropriate permissions

## API Endpoints

### Templates

- `GET /api/templates` - List all templates
- `POST /api/templates` - Create a new template with fields
- `GET /api/templates/{id}` - Get a specific template
- `PUT /api/templates/{id}` - Update a template
- `DELETE /api/templates/{id}` - Delete a template

### Records

- `GET /api/records` - List all records
- `POST /api/records` - Create a new record
- `GET /api/records/{id}` - Get a specific record
- `PUT /api/records/{id}` - Update a record
- `DELETE /api/records/{id}` - Delete a record

## Database Seeding

The application includes seeders to populate the database with sample data for testing and development:

### Seeding the Database

Run the following command to seed the database with sample templates:

```bash
php artisan db:seed
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
