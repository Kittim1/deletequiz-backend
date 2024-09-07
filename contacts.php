<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Data
{
    private $conn;

    function __construct()
    {
        include "connection.php";
        $this->conn = $conn;
    }

    function getContacts()
    {
        $sql = "SELECT contact_id, contact_name, contact_phone, contact_email, contact_address, tblgroups.grp_name, tblusers.usr_fullname
                FROM tblcontacts
                INNER JOIN tblgroups ON tblcontacts.contact_group = tblgroups.grp_id
                INNER JOIN tblusers ON tblcontacts.contact_userId = tblusers.usr_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($contacts);
    }

    function addContact($data)
    {
        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO tblcontacts (contact_userId, contact_name, contact_phone, contact_email, contact_address, contact_group, contact_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data["contact_userId"], 
                $data["contact_name"], 
                $data["contact_phone"], 
                $data["contact_email"], 
                $data["contact_address"], 
                $data["contact_group"], 
                $data["contact_image"]
            ]);
            $contact_id = $this->conn->lastInsertId();
            $this->conn->commit();
            echo json_encode(["status" => 1, "message" => "Contact added successfully.", "contact_id" => $contact_id]);
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo json_encode(["status" => 0, "message" => $e->getMessage()]);
        }
    }

    function updateContact($data)
    {
        try {
            $sql = "UPDATE tblcontacts SET contact_userId = ?, contact_name = ?, contact_phone = ?, contact_email = ?, contact_address = ?, contact_group = ?, contact_image = ? 
                    WHERE contact_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data["contact_userId"], 
                $data["contact_name"], 
                $data["contact_phone"], 
                $data["contact_email"], 
                $data["contact_address"], 
                $data["contact_group"], 
                $data["contact_image"], 
                $data["contact_id"]
            ]);
            echo json_encode(["status" => 1, "message" => "Contact updated successfully."]);
        } catch (Exception $e) {
            echo json_encode(["status" => 0, "message" => $e->getMessage()]);
        }
    }

    function deleteContact($data)
    {
        try {
            $sql = "DELETE FROM tblcontacts WHERE contact_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$data["contact_id"]]);
            echo json_encode(["status" => 1, "message" => "Contact deleted successfully."]);
        } catch (Exception $e) {
            echo json_encode(["status" => 0, "message" => $e->getMessage()]);
        }
    }

    function getGroups()
    {
        $sql = "SELECT grp_id, grp_name FROM tblgroups";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($groups);
    }

    function getUsers()
    {
        $sql = "SELECT usr_id, usr_fullname FROM tblusers";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    }
}

$data = json_decode(file_get_contents("php://input"), true);
$object = new Data();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($data['operation']) {
    case 'getContacts':
        $object->getContacts();
        break;
    case 'addContact':
        $object->addContact($data);
        break;
    case 'updateContact':
        $object->updateContact($data);
        break;
    case 'deleteContact':
        $object->deleteContact($data);
        break;
    case 'getGroups':
        $object->getGroups();
        break;
    case 'getUsers':
        $object->getUsers();
        break;
    }
}
?>