<?php

namespace App\Controllers\Api;

use App\Models\FileModel;
use CodeIgniter\RESTful\ResourceController;

class ApiController extends ResourceController
{

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }

    public function uploadImage()
    {
        $file = $this->request->getFile('image');

        $profile_image = $file->getName();

        // Renaming file before upload
        $temp = explode(".",$profile_image);
        $newFilename = round(microtime(true)) . '.' . end($temp);

        if ($file->move("images", $newFilename)) {

            $fileModel = new FileModel();

            $data = [
                "file_name" => $newFilename,
                "file_path" => "/images/" . $newFilename
            ];

            if ($fileModel->insert($data)) {

                $id = $fileModel->getInsertID();
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'Image uploaded successfully',
                    'data' => $fileModel->find($id)
                ];
            } else {

                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => 'Failed to save image',
                    'data' => []
                ];
            }
        } else {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => 'Failed to upload image',
                'data' => []
            ];
        }

        return $this->respondCreated($response);
    }

    public function listImages()
    {
        $fileModel = new FileModel();

        $response = [
            'status' => 200,
            "error" => false,
            'messages' => 'Files list',
            'data' => $fileModel->findAll()
        ];

        return $this->respondCreated($response);
    }

    public function deleteImage($id)
    {
        $fileModel = new FileModel();
        $image = $fileModel->find($id);

        if ($image) {
            // delete record from db
             $fileModel->delete($id);

            // delete file if exist
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $image['file_path'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $image['file_path']);
            }
            $response = [
                'status' => 200,
                "error" => false,
                'messages' => 'Image deleted.',
                'data' => file_exists($_SERVER['DOCUMENT_ROOT'] . $image['file_path'])
            ];
        } else {
            $response = [
                'status' => 500,
                'error' => true,
                'message' => 'Record not found',
                'data' => []
            ];
        }

        return $this->respondCreated($response);
    }
}
