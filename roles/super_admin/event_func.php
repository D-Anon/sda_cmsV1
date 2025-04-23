<?php
// Start session
session_start();

// Include database connection
require_once "../super_admin/db.php";

// Function to create a new event
function createEvent($event_name, $event_date, $location, $description, $created_by) {
    global $conn;

    try {
        $sql = "INSERT INTO events (event_name, event_date, location, description, created_by) 
                VALUES (:event_name, :event_date, :location, :description, :created_by)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':event_name' => $event_name,
            ':event_date' => $event_date,
            ':location' => $location,
            ':description' => $description,
            ':created_by' => $created_by
        ]);
        return "Event created successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get all events
function listEvents() {
    global $conn;

    try {
        $sql = "SELECT events.*, 
                       CONCAT_WS(' ', users.fname, users.mname, users.lname, users.suffix) AS creator_name 
                FROM events 
                LEFT JOIN users ON events.created_by = users.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Debugging output
        return [];
    }
}

// Function to get details of a specific event
function getEvent($event_id) {
    global $conn;

    try {
        $sql = "SELECT events.*, 
                       CONCAT_WS(' ', users.fname, users.mname, users.lname, users.suffix) AS creator_name 
                FROM events 
                LEFT JOIN users ON events.created_by = users.id 
                WHERE events.id = :event_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':event_id' => $event_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Debugging output
        return null;
    }
}

// Function to update an event
function updateEvent($event_id, $event_name, $event_date, $location, $description) {
    global $conn;

    try {
        $sql = "UPDATE events 
                SET event_name = :event_name, 
                    event_date = :event_date, 
                    location = :location, 
                    description = :description 
                WHERE id = :event_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':event_name' => $event_name,
            ':event_date' => $event_date,
            ':location' => $location,
            ':description' => $description,
            ':event_id' => $event_id
        ]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Debugging output
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
        echo "Error: " . $e->getMessage(); // Debugging output
        return false;
    }
}
?>