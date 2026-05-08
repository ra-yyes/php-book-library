# PHP Book Library

A single-file PHP web application for managing a personal book library.

The application allows users to browse books, add new books through a validated form, edit existing books, delete books with confirmation, search by title or author, and sort the book table.

## Project Information

- **Name:** Tareq ElRayyes
- **Instructor:** Mohammed Zoqlam
- **Repository Name:** php-book-library

## Features

- Display books using a multi-dimensional PHP array
- Store each book as an associative array with the following keys:
  - `id`
  - `title`
  - `author`
  - `genre`
  - `year`
  - `pages`
- Add new books using a validated form
- Validate all form fields with field-specific error messages
- Re-populate form data after failed validation
- Generate new book IDs using the maximum existing ID plus one
- Show success messages using PHP sessions
- Prevent duplicate submissions using redirect after successful POST requests
- Edit books using the `edit_id` query parameter
- Delete books using a POST request
- Confirm delete actions with a Bootstrap modal
- Search books by title or author
- Sort the table by clicking column headers
- Escape user output using `htmlspecialchars()`


## Validation Rules

| Field  | Validation Rule                                                       |
| ------ | --------------------------------------------------------------------- |
| Title  | Required, must be between 3 and 120 characters                        |
| Author | Required, must contain at least two words                             |
| Genre  | Required, must exist in the allowed genres array                      |
| Year   | Required, must be a 4-digit integer between 1000 and the current year |
| Pages  | Required, must be a positive integer greater than 0                   |


## Project Structure

```text
php-book-library/
├── index.php
├── repository-link.txt
└── README.md
```
## Technologies Used

- PHP
- HTML5
- Bootstrap 5 CDN
- Git
- GitHub

## How to Run the Project

1. Clone the repository:

```bash
https://github.com/ra-yyes/php-book-library.git
```

2. Open the project folder:

```bash
cd php-book-library
```

3. Start a local PHP development server:

```bash
php -S localhost:8000
```

4. Open the project in your browser:

```text
http://localhost:8000
```

## Notes

- The application is written in a single PHP file named `index.php`.
- PHP sessions are used to keep book date available during the browser session.
- Bootstrap is loaded using CDN links.
