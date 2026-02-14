![Quasi](https://cms.protoqol.nl/assets/1f26790d-79c2-4aeb-ab2e-4534820c3cb4)

![Packagist](https://img.shields.io/github/actions/workflow/status/Protoqol/quasi/testing)
![Packagist](https://img.shields.io/packagist/v/protoqol/quasi.svg)
![PHP Version](https://img.shields.io/packagist/php-v/protoqol/quasi.svg)

## Features

- **Smarter Resource Generation**: Automatically populate all columns in a Resource from your database table.
- **Table Name Guessing**: Guesses the table name based on the resource name, or you can specify it manually.
- **Model Integration**: Use `--model` to resolve the table name directly from an Eloquent model.
- **Advanced Filtering**: Use `--only` or `--except` to filter columns on the fly.
- **Relationship Discovery**: Automatically detects `_id` columns and adds commented-out `whenLoaded` placeholders.
- **Bulk Generation**: Generate resources for all tables in your database with a single command.
- **Customisable Stubs**: Publish and customise the resource stubs to match your project's style.
- **Fine-grained Control**: Exclude specific fields globally via the configuration file or rely on automatic sensitive
  data hiding.

---

## Installation

You can install the package via Composer:

```bash
composer require protoqol/quasi
```

(Optional) You can publish the configuration file or the stubs with:

```bash
# Publish configuration
php artisan vendor:publish --tag=quasi-config

# Publish stubs for customization
php artisan vendor:publish --tag=quasi-stubs
```

---

## Usage

### Basic Usage

You can create a resource by specifying the name. The table name will be guessed automatically.

```bash
# Table name is guessed based on the resource name (e.g., 'users' for UserResource).
php artisan make:qresource UserResource 

# Explicitly provide the table name.
php artisan make:qresource UserResource --table=users

# Resolve table name from a model.
php artisan make:qresource UserResource --model=User
```

### Advanced Filtering

Filter which columns should be included in the generated resource.

```bash
# Only include specific columns.
php artisan make:qresource UserResource --only=id,name,email

# Exclude specific columns.
php artisan make:qresource UserResource --except=deleted_at,internal_notes
```

Note: Common sensitive fields like `password`, `token`, `secret`, and `remember_token` are automatically excluded.

### Bulk Generation

Generate resources for your entire database at once.

```bash
# Generate resources for all tables.
php artisan make:qresource --all

# Generate resources AND collections for all tables.
php artisan make:qresource --all --collection
```

---

## Feedback, suggestions or issues?

Please open an issue on this repository. We're happy to hear back from you!

---

Developed by [Protoqol](https://protoqol.nl/).
