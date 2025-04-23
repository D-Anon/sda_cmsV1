<?php
require_once '../super_admin/db.php'; // Assuming your connection code is here
class TaskManager
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Create a new task
    public function createTask($taskData)
    {
        try {
            $this->conn->beginTransaction();

            // Insert the task
            $stmt = $this->conn->prepare("
                INSERT INTO tasks (task_name, description, deadline, created_at) 
                VALUES (:task_name, :description, :deadline, NOW())
            ");
            $stmt->execute([
                ':task_name' => $taskData['task_name'],
                ':description' => $taskData['description'],
                ':deadline' => $taskData['deadline']
            ]);
            $taskId = $this->conn->lastInsertId();

            // Assign leader
            $this->assignMember($taskId, $taskData['leader_id'], 1);

            // Assign members
            foreach ($taskData['member_ids'] as $memberId) {
                if ($memberId != $taskData['leader_id']) { // Don't add leader twice
                    $this->assignMember($taskId, $memberId, 0);
                }
            }

            $this->conn->commit();
            return $taskId;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Assign a member to a task
    private function assignMember($taskId, $userId, $isLeader)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO task_members (task_id, user_id, is_leader) 
            VALUES (:task_id, :user_id, :is_leader)
        ");
        $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':is_leader' => $isLeader
        ]);
    }

    // Get all tasks with leader and member details
    public function getAllTasks()
    {
        $stmt = $this->conn->query("
            SELECT t.*, 
                   ul.id AS leader_id, ul.fname AS leader_fname, ul.lname AS leader_lname,
                   GROUP_CONCAT(DISTINCT CONCAT(um.fname, ' ', um.lname) SEPARATOR ', ') AS member_names,
                   GROUP_CONCAT(DISTINCT um.id SEPARATOR ', ') AS member_ids
            FROM tasks t
            LEFT JOIN task_members tm_leader ON t.id = tm_leader.task_id AND tm_leader.is_leader = 1
            LEFT JOIN users ul ON tm_leader.user_id = ul.id
            LEFT JOIN task_members tm_member ON t.id = tm_member.task_id
            LEFT JOIN users um ON tm_member.user_id = um.id
            GROUP BY t.id, ul.id, ul.fname, ul.lname
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get a single task by ID with full details
    public function getTaskById($taskId)
    {
        $stmt = $this->conn->prepare("
            SELECT t.*, 
                   ul.id AS leader_id, CONCAT(ul.fname, ' ', ul.lname) AS leader_name,
                   ul.profile_picture AS leader_avatar,
                   GROUP_CONCAT(CONCAT(um.fname, ' ', um.lname)) AS member_names,
                   GROUP_CONCAT(um.id) AS member_ids
            FROM tasks t
            LEFT JOIN task_members tm_leader ON t.id = tm_leader.task_id AND tm_leader.is_leader = 1
            LEFT JOIN users ul ON tm_leader.user_id = ul.id
            LEFT JOIN task_members tm_member ON t.id = tm_member.task_id AND tm_member.is_leader = 0
            LEFT JOIN users um ON tm_member.user_id = um.id
            WHERE t.id = :task_id
            GROUP BY t.id
        ");
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update a task
    public function updateTask($taskId, $taskData)
    {
        try {
            $this->conn->beginTransaction();

            // Update task details
            $stmt = $this->conn->prepare("
                UPDATE tasks 
                SET task_name = :task_name, 
                    description = :description, 
                    deadline = :deadline 
                WHERE id = :task_id
            ");
            $stmt->execute([
                ':task_name' => $taskData['task_name'],
                ':description' => $taskData['description'],
                ':deadline' => $taskData['deadline'],
                ':task_id' => $taskId
            ]);

            // Delete existing members
            $stmt = $this->conn->prepare("DELETE FROM task_members WHERE task_id = :task_id");
            $stmt->execute([':task_id' => $taskId]);

            // Assign new leader
            $this->assignMember($taskId, $taskData['leader_id'], 1);

            // Assign new members
            foreach ($taskData['member_ids'] as $memberId) {
                if ($memberId != $taskData['leader_id']) {
                    $this->assignMember($taskId, $memberId, 0);
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Delete a task
    public function deleteTask($taskId)
    {
        // The ON DELETE CASCADE in task_members will handle the member records
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = :task_id");
        return $stmt->execute([':task_id' => $taskId]);
    }

    // Get all users for dropdown selection
    public function getAllUsers()
    {
        $stmt = $this->conn->query("
            SELECT id, CONCAT(fname, ' ', lname) AS full_name, position 
            FROM users 
            WHERE status = 'active'
            ORDER BY lname, fname
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize TaskManager
$taskManager = new TaskManager($conn);

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'create':
                $taskData = [
                    'task_name' => $_POST['task_name'],
                    'description' => $_POST['description'],
                    'deadline' => $_POST['deadline'],
                    'leader_id' => $_POST['leader_id'],
                    'member_ids' => $_POST['member_ids'] ?? []
                ];
                $taskId = $taskManager->createTask($taskData);
                $response = [
                    'success' => true,
                    'message' => 'Task created successfully',
                    'taskId' => $taskId
                ];
                break;

            case 'update':
                $taskData = [
                    'task_name' => $_POST['task_name'],
                    'description' => $_POST['description'],
                    'deadline' => $_POST['deadline'],
                    'leader_id' => $_POST['leader_id'],
                    'member_ids' => $_POST['member_ids'] ?? []
                ];
                $taskManager->updateTask($_POST['task_id'], $taskData);
                $response = [
                    'success' => true,
                    'message' => 'Task updated successfully'
                ];
                break;

            case 'delete':
                $taskManager->deleteTask($_POST['task_id']);
                $response = [
                    'success' => true,
                    'message' => 'Task deleted successfully'
                ];
                break;

            default:
                $response['message'] = 'Invalid action';
                break;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        $response['success'] = false;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
