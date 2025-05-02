# CEC_IT_Management

## Starting Docker
    # Open Docker application
    # docker-compose build 
    # docker-compose run 

# CEC IT Management

CEC IT Management is a PHP- and MySQL-based inventory system designed to track IT assets such as laptops, phones, and other devices across a company. It provides inline editing, user role support, and CSV import/export for efficient device management.

---

## 🚀 Features

- Asset management dashboard with inline editing
- Assign devices to employees using a dropdown interface
- Role-based access and authentication
- CSV import/export functionality
  - Auditing employees to laptops
  - Importing asset inventory
  - Export of laptop inventory
- Responsive UI built with JavaScript and PHP
- Dockerized for simple setup and deployment

---

## 🐳 Docker Setup

To run this project locally using Docker:

### Prerequisites

- Docker
- Database files (automated setup):
  - 01-create-tables.sql
  - 02-seed-from-csv.sql
  - 03-seed-admin.sql
  - SparkList_Inventory

### Steps to Start

1. Make sure Docker is running.
2. Run the following commands:

```bash
docker-compose build
docker-compose up
```

1. Access the app at [http://localhost:8080] (local use)
2. Access phpmyadmin : Database at [http://localhost:8081] (local use)

### Stopping Containers

To stop all running containers:

```bash
docker-compose down
```

### Clearing Volume for Database Files (Just in Case)
```bash
docker-compose down -v
```

---

## ⚙️ Environment Variables

The environment variables are defined in the `.env.dev` file and used by Docker Compose to configure the database.

Example `.env.dev`:

```
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=cec_it_management
MYSQL_USER=test
MYSQL_PASSWORD=test
MYSQL_SERVERNAME=db

USER_LOG_PATH=/var/www/html/logs/user_event_log.txt
DEVICE_LOG_PATH=/var/www/html/logs/device_event_log.txt
```

Make sure to copy `.env.dev` and rename to `.env` so that all environment variables work with application.

---

## 📂 Project Structure

```
/Forms          - PHP forms for user/device handling
/includes       - Navigation and reusable components
/sql            - SQL files for database initialization
dockerfile      - PHP/Apache Docker setup
.env.dev        - Environment variables for development
docker-compose.yml - Docker configuration
```

---

## 📄 License

This project is for educational use. All rights reserved.
