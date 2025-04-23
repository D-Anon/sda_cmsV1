<?php
// Start session
session_start();

// Include database connection
require_once "db.php";

// Function to create a new event
function createEvent($title, $description, $location, $start_date, $end_date, $created_by) {
    global $conn;

    try {
        $sql = "INSERT INTO events (title, description, location, start_date, end_date, created_by) 
                VALUES (:title, :description, :location, :start_date, :end_date, :created_by)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':location' => $location,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':created_by' => $created_by
        ]);
        return "Event created successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get details of a specific event
function getEvent($event_id) {
    global $conn;

    try {
        $sql = "SELECT events.*, users.full_name AS creator_name FROM events 
                JOIN users ON events.created_by = users.id 
                WHERE events.id = :event_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':event_id' => $event_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// Function to update an event
function updateEvent($event_id, $title, $description, $location, $start_date, $end_date) {
    global $conn;

    try {
        $sql = "UPDATE events SET title = :title, description = :description, location = :location, 
                start_date = :start_date, end_date = :end_date WHERE id = :event_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':location' => $location,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':event_id' => $event_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to delete an event
function deleteEvent($event_id) {
    global $conn;

    try {
        $sql = "DELETE FROM events WHERE id = :event_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':event_id' => $event_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to list all events
function listEvents() {
    global $conn;

    try {
        $sql = "SELECT events.*, users.full_name AS creator_name FROM events 
                JOIN users ON events.created_by = users.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
