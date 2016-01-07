<?php
session_start();
//
try{
    /* Instantiate new PDO object called $db
     * When doing query $db-> should be used
     * e.g: $sql = $db->prepare(QUERY); $sql->execute();
     */
    $db = new PDO('mysql:host=localhost;dbname=battlesocial', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO WILL DISPLAY ADDITONAL INFO WHEN THERE IS AN ERROR
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); //PDO WILL DISPLAY ADDITONAL INFO WHEN THERE IS AN ERROR
}
catch (Exception $e)
{
    //In case of error
    die('ERROR : ' . $e->getMessage());
}
?>
