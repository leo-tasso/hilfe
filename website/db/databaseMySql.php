<?php
require("databaseHelper.php");
class DatabaseHelperMySql implements DatabaseHelper{
    private $db;
    public function __construct($servername,$username,$password,$dbname){
        $this->db = new mysqli($servername,$username,$password,$dbname);
        if($this->db->connect_error){
            die("Connection failed: " . $this->db->connect_error);
        }
    }
    public function getHelpPosts($n){
        $stmt = $this->db->prepare("SELECT 	
        * FROM PostInterventi LIMIT ?");
        $stmt->bind_param('i', $n);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getUserFromId($id){
        $stmt = $this->db->prepare("SELECT * FROM User where idUser =".$id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getProfilePicPathFromId($id){
        $stmt = $this->db->prepare("SELECT FotoProfilo FROM User where idUser =".$id." LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getAuthorFromHelpPost($id){
        $stmt = $this->db->prepare("SELECT * FROM User, PostInterventi  where idUser = Autore_idUser AND idPostIntervento =".$id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getMaterialFromHelpPost($id){
        $stmt = $this->db->prepare("SELECT * FROM Materiale  where idPostIntervento =".$id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function getPicFromId($id){
        $stmt = $this->db->prepare("SELECT FotoProfilo FROM user  where idUser =".$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lines = $result->fetch_all(MYSQLI_ASSOC);
        if(count($lines) == 0 || $lines[0]["FotoProfilo"] == null){
            return DEFAULT_PIC_PATH.DEFAULT_PIC;
        }
        else{
            return DEFAULT_PIC_PATH.$lines[0]["FotoProfilo"];
        }
    }

    public function getProfilePic($id){
        if($id==null){
            return DEFAULT_PIC_PATH.DEFAULT_PIC;
        }
        return $this->getPicFromId($id);
    }
    public function getSelfProfilePic(){
        if(isLogged()){
            return $this->getPicFromId($_SESSION["idUser"]);
        }
        return DEFAULT_PIC_PATH.DEFAULT_PIC;
    }
    public function getNotification() {
        if(isLogged()){
            $stmt = $this->db->prepare("SELECT * FROM notifica  where idUser =".$_SESSION["idUser"]." AND Letta=0");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        else{
            return [];
        }
    }
    public function getSuggestedUsers($n) {
        $suggestedUsers = [];
        if(isLogged()){
            $stmt = $this->db->prepare("SELECT * FROM user,seguiti  where idUser not in (SELECT idSeguito From user,seguiti where idSeguace =".$_SESSION["idUser"]." ) AND idSeguito =".$_SESSION["idUser"]." AND idSeguace=idUser LIMIT ".$n);
            $stmt->execute();
            $result = $stmt->get_result();
            $entries = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($entries as $key =>$entry) {
                $entries[$key]["seguace"]=1;
            }
            $suggestedUsers = array_merge($suggestedUsers, $entries);
            if(count($suggestedUsers)<$n){
                $stmt = $this->db->prepare("
                SELECT u.idUser, u.Name, u.Surname, COUNT(s1.idSeguito) AS NumSeguitiInComune
                FROM User u
                JOIN Seguiti s1 ON u.idUser = s1.idSeguito
                JOIN Seguiti s2 ON s1.idSeguito = s2.idSeguace AND s2.idSeguito IN (SELECT idSeguito FROM Seguiti WHERE idSeguace = ".$_SESSION["idUser"].")
                WHERE u.idUser <> ".$_SESSION["idUser"]."
                AND u.idUser NOT IN (SELECT idSeguito FROM Seguiti WHERE idSeguace = ".$_SESSION["idUser"].") -- Escludi quelli che stai già seguendo
                GROUP BY u.idUser, u.Name, u.Surname
                ORDER BY NumSeguitiInComune DESC
                LIMIT ".$n - count($suggestedUsers));
                $stmt->execute();
                $result = $stmt->get_result();
                $suggestedUsers = array_merge($suggestedUsers, $result->fetch_all(MYSQLI_ASSOC));
            }
            if(count($suggestedUsers)<$n){
                $selectedUserIdsString = implode(',', array_column($suggestedUsers, 'idUser'));
                $stmt = $this->db->prepare("SELECT * FROM user,seguiti  where idUser not in (".$selectedUserIdsString." ) AND idSeguito =".$_SESSION["idUser"]." AND idSeguace=idUser ORDER BY RAND() LIMIT ".$n - count($suggestedUsers));
                $stmt->execute();
                $result = $stmt->get_result();
                $entries = $result->fetch_all(MYSQLI_ASSOC);
                foreach ($entries as $key =>$entry) {
                    $entries[$key]["Motivazione"]=CUTE_PHRASES[array_rand(CUTE_PHRASES)];
                }
                $suggestedUsers = array_merge($suggestedUsers, $entries);
            }
            return $suggestedUsers;
        }
        if(count($suggestedUsers)<$n){
            $stmt = $this->db->prepare("SELECT * FROM user ORDER BY RAND() LIMIT ".$n);
            $stmt->execute();
            $result = $stmt->get_result();
            $entries = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($entries as $key =>$entry) {
                $entries[$key]["Motivazione"]=CUTE_PHRASES[array_rand(CUTE_PHRASES)];
            }
            $suggestedUsers = array_merge($suggestedUsers, $entries);
            return $suggestedUsers;
        }

    }
}


$dbh = new DatabaseHelperMySql("localhost", "root", "", "HilfeDb", 3306);

?>