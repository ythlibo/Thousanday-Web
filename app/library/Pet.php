<?php

class Pet {

    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    //read data for one pet
    public function readOnePet($id) {
        $petQuery = 'SELECT * FROM pet WHERE pet_id = :id';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $petStmt->execute();
            return $petStmt->fetch(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //get owner id and relative id of one pet
    public function readPetFamily($id) {
        $familyQuery = 'SELECT owner_id, relative_id FROM pet WHERE pet_id = :id';
        try {
            $familyStmt = $this->db->prepare($familyQuery);
            $familyStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $familyStmt->execute();
            return $familyStmt->fetch(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //get all pets belong to owner or relative, except current pet
    public function readPetFriends($owner, $relative, $pet) {
        $familyQuery = 'SELECT pet_id, pet_name FROM pet WHERE
                        (owner_id = :owner OR relative_id = :owner OR owner_id = :relative 
                        OR relative_id = :owner) AND pet_id != :pet';
        try {
            $familyStmt = $this->db->prepare($familyQuery);
            $familyStmt->bindValue(':owner', $owner, PDO::PARAM_INT);
            $familyStmt->bindValue(':relative', $relative, PDO::PARAM_INT);
            $familyStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
            $familyStmt->execute();
            return $familyStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //get all pets id belong to one user
    public function readUserBelong($user) {
        $userQuery = 'SELECT pet_id, pet_name, pet_gender, pet_type, owner_id, relative_id FROM pet
                      WHERE owner_id = :user OR relative_id = :user';
        try {
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindValue(':user', $user, PDO::PARAM_INT);
            $userStmt->execute();
            return $userStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //get all pets info belong to one user except current one
    public function readUserPets($owner, $pet) {
        $userQuery = 'SELECT pet_id, pet_name FROM pet WHERE owner_id = :owner AND pet_id != :pet';
        try {
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindValue(':owner', $owner, PDO::PARAM_INT);
            $userStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
            $userStmt->execute();
            return $userStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //find pets list fit in certain type and nature
    public function readFilterPets($type, $nature) {
        $userQuery = 'SELECT pet_id FROM pet WHERE pet_type = :type AND pet_nature = :nature';
        try {
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindValue(':type', $type, PDO::PARAM_INT);
            $userStmt->bindValue(':nature', $nature, PDO::PARAM_INT);
            $userStmt->execute();
            return $userStmt->fetchAll(PDO::FETCH_NUM);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //from pet id list return pet name list
    public function readPetsNames($list) {
        $values = implode(',', $list);
        $petQuery = 'SELECT pet_id, pet_name FROM pet WHERE pet_id IN (' . $values . ')';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->execute();
            return $petStmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            print $e->getMessage();
            return 0;
        }
    }

    //update pet's name
    public function updatePetName($id, $name) {
        $petQuery = 'UPDATE pet SET pet_name = :name WHERE pet_id = :id';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $petStmt->bindValue(':name', $name, PDO::PARAM_STR);
            $this->db->beginTransaction();
            $petStmt->execute();
            $this->db->commit();
            return 1;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

    //end relative of one pet
    public function endPetRelation($id) {
        $petQuery = 'UPDATE pet SET relative_id = NULL WHERE pet_id = :id';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $this->db->beginTransaction();
            $petStmt->execute();
            $this->db->commit();
            return 1;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

    //transfer ownership for one pet
    public function transferPetOwner($pet, $owner, $relative) {
        $petQuery = 'UPDATE pet SET owner_id = :owner, relative_id = :relative WHERE pet_id = :pet';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->bindValue(':owner', $relative, PDO::PARAM_INT);
            $petStmt->bindValue(':relative', $owner, PDO::PARAM_INT);
            $petStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
            $this->db->beginTransaction();
            $petStmt->execute();
            $this->db->commit();
            return 1;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

    //create new pet
    public function createNewPet($name, $gender, $type, $nature, $user) {
        $time = date('Y-m-d H:i:s');
        $addQuery = 'INSERT INTO pet (pet_name, pet_gender, pet_type, pet_nature, pet_reg, owner_id) 
                     VALUES (:name, :gender, :type, :nature, :reg, :user)';
        try {
            $addStmt = $this->db->prepare($addQuery);
            $addStmt->bindValue(':name', $name, PDO::PARAM_STR);
            $addStmt->bindValue(':gender', $gender, PDO::PARAM_INT);
            $addStmt->bindValue(':type', $type, PDO::PARAM_INT);
            $addStmt->bindValue(':nature', $nature, PDO::PARAM_INT);
            $addStmt->bindValue(':reg', $time);
            $addStmt->bindValue(':user', $user, PDO::PARAM_INT);
            $this->db->beginTransaction();
            $addStmt->execute();
            $id = $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

    //add relative for pet
    public function addPetRelative($pet, $user) {
        $petQuery = 'UPDATE pet SET relative_id = :relative WHERE pet_id = :pet AND relative_id is NULL';
        try {
            $petStmt = $this->db->prepare($petQuery);
            $petStmt->bindValue(':relative', $user, PDO::PARAM_INT);
            $petStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
            $this->db->beginTransaction();
            $petStmt->execute();
            $this->db->commit();
            return 1;
        } catch (PDOException $e) {
            print $e->getMessage();
            $this->db->rollback();
            return 0;
        }
    }

}