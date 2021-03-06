<?php

class Moment {

    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    //read 20 public moments from the pin point, return moment list
    //return 0 for error
    public function readPublicMoments($load) {
        $momentQuery = 'SELECT moment_id, pet_id, image_name, moment_message 
                        FROM moment WHERE display = 1 ORDER BY moment_id DESC LIMIT :pin, 20';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->bindValue(':pin', $load * 20, PDO::PARAM_INT);
            $momentStmt->execute();
            return $momentStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //read one moment date
    //return 0 for error
    public function readOneMoment($id) {
        $momentQuery = 'SELECT * FROM moment WHERE moment_id = :id AND display = 1';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $momentStmt->execute();
            return $momentStmt->fetch(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //read 20 moments for one pet
    public function readPetMoments($id, $load, $add = 0) {
        $momentQuery = 'SELECT moment_id, pet_id, image_name, moment_message 
                        FROM moment WHERE pet_id = :id AND display = 1 ORDER BY moment_id DESC LIMIT :pin, 20';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $momentStmt->bindValue(':pin', $load * 20 + $add, PDO::PARAM_INT);
            $momentStmt->execute();
            return $momentStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //read 20 moments on the pet list
    public function readPetsList($list, $load) {
        $values = implode(',', $list);
        $pin = $load * 20;
        $momentQuery = 'SELECT moment_id, pet_id, image_name, moment_message FROM moment 
                        WHERE pet_id IN (' . $values . ') AND display = 1 ORDER BY moment_id DESC LIMIT ' . $pin . ', 20';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->execute();
            return $momentStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //get moments data from moments list
    public function readMomentsList($moment) {
        $values = implode(',', $moment);
        $momentQuery = 'SELECT moment_id, pet_id, image_name, moment_message FROM moment 
                        WHERE moment_id IN (' . $values . ') AND display = 1';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->execute();
            return $momentStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //add one new moment
    public function createNewMoment($image, $message, $pet) {
        $time = date('Y-m-d H:i:s');
        $momentQuery = 'INSERT INTO moment (image_name, moment_message, pet_id, moment_date, display) 
                       VALUES (:image, :message, :pet, :time, 1)';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->bindValue(':image', $image, PDO::PARAM_STR);
            $momentStmt->bindValue(':message', $message);
            $momentStmt->bindValue(':pet', $pet);
            $momentStmt->bindValue(':time', $time);
            $this->db->beginTransaction();
            $momentStmt->execute();
            $id = $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }

    }

    //hide one moment as delete from public
    public function hideOneMoment($moment, $pet) {
        $momentQuery = 'UPDATE moment SET display = 0 WHERE moment_id = :moment AND pet_id = :pet';
        try {
            $momentStmt = $this->db->prepare($momentQuery);
            $momentStmt->bindValue(':moment', $moment, PDO::PARAM_INT);
            $momentStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
            $this->db->beginTransaction();
            $momentStmt->execute();
            $this->db->commit();
            return $momentStmt->rowCount();
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

}