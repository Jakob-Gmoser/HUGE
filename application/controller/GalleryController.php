<?php

class GalleryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    public function index()
    {
        $this->View->render('gallery/index', array(
            'pictures' => GalleryModel::getAllPictures()
        ));
    }

    public function upload()
    {
        GalleryModel::uploadPicture();
        Redirect::to('gallery');
    }

    public function showImage($picture_id)
    {
        GalleryModel::outputPicture($picture_id, false);
    }

    public function download($picture_id)
    {
        GalleryModel::outputPicture($picture_id, true);
    }

    public function delete($picture_id)
    {
        GalleryModel::deletePicture($picture_id);
        Redirect::to('gallery');
    }
}
