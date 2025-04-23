<?php
// Start session
session_start();

// Include database connection
require_once "db.php";

// Function to create a new task
function createTask($title, $description, $assigned_to, $status, $due_date) {
    global $conn;

    try {
        $sql = "INSERT INTO tasks (title, description, assigned_to, status, due_date) 
                VALUES (:title, :description, :assigned_to, :status, :due_date)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':assigned_to' => $assigned_to,
            ':status' => $status,
            ':due_date' => $due_date
        ]);
        return "Task created successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get details of a specific task
function getTask($task_id) {
    global $conn;

    try {
        $sql = "SELECT * FROM tasks WHERE id = :task_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':task_id' => $task_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// Function to update a task
function updateTask($task_id, $title, $description, $status, $due_date) {
    global $conn;

    try {
        $sql = "UPDATE tasks SET title = :title, description = :description, status = :status, due_date = :due_date WHERE id = :task_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':status' => $status,
            ':due_date' => $due_date,
            ':task_id' => $task_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to delete a task
function deleteTask($task_id) {
    global $conn;

    try {
        $sql = "DELETE FROM tasks WHERE id = :task_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':task_id' => $task_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to list all tasks
function listTasks() {
    global $conn;

    try {
        $sql = "SELECT tasks.*, users.full_name AS assigned_user FROM tasks 
                JOIN users ON tasks.assigned_to = users.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
