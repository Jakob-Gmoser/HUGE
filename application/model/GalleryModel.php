<?php

class GalleryModel
{
    const MAX_FILE_SIZE = 5242880;

    private static function getBasePath()
    {
        return realpath(dirname(__FILE__) . '/../../') . '/galleryimages/';
    }

    public static function getAllPictures()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, owner_id, name, size, downloads
                FROM gallery
                WHERE owner_id = :owner_id
                ORDER BY id DESC";
        $query = $database->prepare($sql);
        $query->execute(array(':owner_id' => Session::get('user_id')));

        return $query->fetchAll();
    }

    public static function uploadPicture()
    {
        if (!isset($_FILES['picture']) || $_FILES['picture']['error'] !== UPLOAD_ERR_OK) {
            Session::add('feedback_negative', 'Upload failed.');
            return false;
        }

        if ($_FILES['picture']['size'] > self::MAX_FILE_SIZE) {
            Session::add('feedback_negative', 'File is too large. Maximum size is 5 MB.');
            return false;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['picture']['tmp_name']);
        $allowed = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        );

        if (!isset($allowed[$mime])) {
            Session::add('feedback_negative', 'Only JPG, PNG and GIF files are allowed.');
            return false;
        }

        $user_id = (int) Session::get('user_id');
        $target_folder = self::getBasePath();

        if (!is_dir($target_folder) && !mkdir($target_folder, 0755, true)) {
            Session::add('feedback_negative', 'Upload folder could not be created.');
            return false;
        }

        $original_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['picture']['name']));
        $stored_name = time() . '_' . bin2hex(random_bytes(8)) . '_' . $original_name;
        $target_path = $target_folder . $stored_name;

        if (!move_uploaded_file($_FILES['picture']['tmp_name'], $target_path)) {
            Session::add('feedback_negative', 'File could not be saved.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO gallery (name, owner_id, size, downloads)
                VALUES (:name, :owner_id, :size, 0)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':name' => $stored_name,
            ':owner_id' => $user_id,
            ':size' => filesize($target_path)
        ));

        if ($query->rowCount() !== 1) {
            unlink($target_path);
            Session::add('feedback_negative', 'Database entry could not be created.');
            return false;
        }

        Session::add('feedback_positive', 'Picture uploaded.');
        return true;
    }

    public static function outputPicture($picture_id, $download = false)
    {
        $picture = self::getOwnPicture($picture_id);

        if (!$picture) {
            http_response_code(404);
            exit('Picture not found.');
        }

        $path = self::getBasePath() . basename($picture->name);

        if (!file_exists($path)) {
            http_response_code(404);
            exit('File not found.');
        }

        if ($download) {
            self::increaseDownloadCounter($picture->id);
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . basename($picture->name) . '"');
        readfile($path);
        exit;
    }

    public static function deletePicture($picture_id)
    {
        $picture = self::getOwnPicture($picture_id);

        if (!$picture) {
            Session::add('feedback_negative', 'Picture could not be deleted.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM gallery WHERE id = :id AND owner_id = :owner_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':id' => $picture->id,
            ':owner_id' => Session::get('user_id')
        ));

        if ($query->rowCount() !== 1) {
            Session::add('feedback_negative', 'Picture could not be deleted.');
            return false;
        }

        $path = self::getBasePath() . basename($picture->name);
        if (file_exists($path)) {
            unlink($path);
        }

        Session::add('feedback_positive', 'Picture deleted.');
        return true;
    }

    private static function getOwnPicture($picture_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, owner_id, name, size, downloads
                FROM gallery
                WHERE id = :id AND owner_id = :owner_id
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':id' => (int) $picture_id,
            ':owner_id' => Session::get('user_id')
        ));

        return $query->fetch();
    }

    private static function increaseDownloadCounter($picture_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE gallery SET downloads = downloads + 1 WHERE id = :id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':id' => (int) $picture_id));
    }
}
