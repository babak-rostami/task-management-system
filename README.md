# Task Management System (Laravel + PostgreSQL + Redis + Docker + Pest)

Task Management System built with Laravel
featuring role-based permissions, PostgreSQL, Redis caching, and containerized using Docker.
Includes RESTful APIs for managing tasks and users.

## Installation

```bash
git clone https://github.com/babak-rostami/task-management-system.git
cd task-management-system
cp .env.example .env
docker-compose up -d --build
docker exec -it task_management_app bash
composer install
php artisan key:generate
php artisan migrate --seed
```

After these steps, the app is ready.

Some demo tasks and users are already added to the database by **seeder**.

# 📦 Postman Collection

You can use the Postman collection to test all API endpoints easily.  
The collection is included in the repository.

Download it here:

`https://github.com/babak-rostami/task-management-system/blob/main/postman/TaskManagement.postman_collection.json
`

Import it into Postman and start testing the API right away.

# 🔐 Authentication

When you are logged out, you cannot use the task management features.

So first, register a new account:

## Register

```
POST: http://localhost:8000/api/register

{
  "name": "babak",
  "email": "babakrostami76@gmail.com",
  "password": "password",
  "password_confirmation": "password"
}
```

## Login

```
POST: http://localhost:8000/api/login

{
  "email": "babakrostami76@gmail.com",
  "password": "password"
}
```

You will receive a token in the response.

You must add this token to the Authorization header to stay logged in.

## Example response:

```
{
  "status": true,
  "message": "Use this Token...",
  "data": {
    "token": "1|IVU2laYJsMT2P1FaQrelOuPvdroV9PmHorNxJrYEc9de1658"
  }
}
```

## ✔ What a logged-in user can do

-   You can create a new task and manage it.  
    `POST /api/v1/tasks`

### 👑 Task Owner

Only the user who created the task can:

-   Add users to the task  
    `POST /api/v1/tasks/{task}/users`
-   Remove users from the task  
    `DELETE /api/v1/tasks/{task}/users/{user}`

### 👥 Task Member

Users who are added to a task can:

-   Change the **status** of the task  
     `POST /api/v1/tasks/{task}/status`
-   See other users who are members of the same task  
    `GET /api/v1/tasks/{task}/users`

### 🚫 No Access for Non-Members

If a user is **not** a member of a task:

-   They cannot see the task
-   They cannot see the users inside the task

# 🔐 TaskPolicy

All access for tasks are defined in the **TaskPolicy** file.  
This file controls who can create, view, update, or manage users in each task.

# 🔑 Roles and Permissions

This project uses **spatie/laravel-permission** to manage roles and permissions.

Before doing anything, you must run:

```bash
php artisan db:seed
```

This will run the RolePermissionSeeder, which creates all roles and permissions.

We have two roles:

-   Admin
-   User

When someone registers, they automatically get the User role and can only use the actions allowed for normal users.

# 👑 Create Admin

To create an admin user, run:

```bash
php artisan make:admin
```

This command will ask for an email and password.

If the user already exists, the admin role will be added to that user.

If the user does not exist, a new user will be created with the admin role.

Admins have full access to all routes and can see all tasks.

Admins can also get all tasks here:

`GET /api/v1/admin/tasks`

---

# ⚡ Cache Service

When users views their tasks, the tasks are stored in cache.  
Next time, the tasks load from cache instead of the database.

The cache key for user tasks is:
`"user_{userId}_tasks"` that defined in cache service

Whenever a task is deleted, updated, or changed in any way, the cache is cleared for all users who have that task.

Cache handling for tasks is managed in the **TaskCacheService**, where you can also change the cache TTL.

### 👥 Task Users Cache

The list of users inside a task is also cached.  
When a user is added or removed from a task, the cache for that task is cleared.

The cache key for task users is: `"task:{taskId}:users"`

Cache handling for task users is managed in the **TaskUserCacheService**, where you can also change the TTL.

---

# 📝 Logging System

The log system is built in a way that lets us add new log drivers in the future without changing the main code.

Every driver must **implement `LogInterface`**.

Right now, we have two drivers:

-   **FileLogger** – writes logs to a file
-   **DatabaseLogger** – saves logs in the database

In the **LogManager**, we choose which drivers are active.  
We can also enable multiple drivers at the same time, so one log event can be saved in different places.

We use the log service in different parts of the app—when an error happens, when some data changes, or when something is deleted.  
This keeps our logging simple, clear, and easy to expand later.

# 🧪Testing: Pest
This project also includes a test using Pest - testing framework for Laravel.

These tests help make sure everything works correctly when the project grows

-   **when packages are updated**
-   **new features are added**
-   **existing code changes**
-   **prevent unexpected bugs**

They also make it easier for new developers to understand how the system behaves, since the tests clearly show what each part of the application is expected to do.

You can run all tests with:

`php artisan test`

---

# 🙌 Thank You

Thank you for checking out this project.  
If you have ideas or find any issues, feel free to open an issue or send a pull request.
