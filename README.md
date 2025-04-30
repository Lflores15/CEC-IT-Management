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
- Responsive UI built with JavaScript and PHP
- Dockerized for simple setup and deployment

---

## 🐳 Docker Setup

To run this project locally using Docker:

### Prerequisites

- Docker
- Docker Compose

### Steps to Start

1. Make sure Docker is running.
2. Run the following commands:

```bash
docker-compose build
docker-compose up
```

1. Access the app at [http://localhost:8080]
2. Access phpmyadmin : Database at [http://localhost:8081]

### Stopping Containers

To stop all running containers:

```bash
docker-compose down
```

---

## ⚙️ Environment Variables

The environment variables are defined in the `.env.dev` file and used by Docker Compose to configure the database.

Example `.env.dev`:

```
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=cec_it_management
MYSQL_USER=root
MYSQL_PASSWORD=root
MYSQL_SERVERNAME=db
```

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