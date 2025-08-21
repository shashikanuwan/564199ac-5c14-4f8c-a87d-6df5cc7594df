## CLI Assessment Reporting System

This is a Laravel 12 console application built as part of a coding challenge.
It generates assessment reports for students based on provided JSON input data.

### 📋 Requirements
The application supports three types of reports:

1. `Diagnostic Report` – shows areas of weakness by strand.
2. `Progress Report` – shows a student’s improvement over time. 
3. `Feedback Report` – gives details on wrong answers with hints.

### 🛠 Development Setup

This project uses PHP 8.3 + Laravel 12, packaged with Docker Compose to avoid host dependencies.
No database is required. All data is loaded from JSON files in the data/ directory.

### ⚙️ Installation & Setup
1. Clone the Repository
   ```bash
   git clone git@github.com:shashikanuwan/564199ac-5c14-4f8c-a87d-6df5cc7594df.git
    ```
    ```bash
   cd 564199ac-5c14-4f8c-a87d-6df5cc7594df
    ```
   
2. Build the Docker image
   ```bash
   docker-compose build
   ```

### 🏃‍♂️ Running the Application
1. Start the Docker containers in the background
   ```bash
   docker-compose up -d
   ```
2. Run the application in interactive mode (CLI)
   ```bash
   docker-compose run --rm app
   ```
3. Run the PEST tests
   ```bash
   docker-compose run --rm test
   ```
