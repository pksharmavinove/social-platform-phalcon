<?php

class PostRemarksController extends ControllerBase
{

    /**
     * Index Method
     */
    public function index()
    {
        $this->initializeGet();
        $options = $this->buildOptions('id desc', $this->request->get('sort'), $this->request->get('order'), $this->request->get('limit'), $this->request->get('offset'));
        $filters = $this->buildFilters($this->request->get('filter'));
        $postremark = $this->findElements('PostRemarks', $filters['conditions'], $filters['parameters'], 'id, post_id, user_id, remark, created_at', $options['order_by'], $options['offset'], $options['limit']);
        $total = $this->calculateTotalElements('PostRemarks', $filters['conditions'], $filters['parameters']);
        $data = $this->buildListingObject($postremark, $options['rows'], $total);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }

    /**
     * Create Method 
     */
    public function create()
    {   
        // print_r($this->request->getPost()); die;
        //Fetch Token Based Data
        $token_decoded = $this->decodeToken($this->getToken());
        $user_id = $token_decoded->username_id;
        $created_at = $this->getUnixTimeStamp();
        
        $this->initializePost();
        $this->checkForEmptyData([
                    $this->request->getPost('post_id'), 
                    $this->request->getPost('remark')  
            ]); 
        $postremark = $this->createPostRemarks(
                    $this->request->getPost('post_id'), 
                    $this->request->getPost('remark'),
                    $created_at,
                    $user_id
                );
        $this->registerLog();
        $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $postremark->toArray());
    }

    /**
     * 
     */
    public function get($id)
    {
        $this->initializeGet();
        $postremarks = $this->findElementById('PostRemarks', $id);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $postremarks->toArray());
    }


    /**
     * 
     */
    public function getwithpost($postid)
    { 
        $this->initializeGet();
        $postremarks = $this->findElementByPostId('PostRemarks', $postid);        
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $postremarks->toArray());
    }

    

    /**
     * Private Methods
     */

    private function createPostRemarks($post_id, $remark, $user_id, $created_at)
    {
        $postremarks               = new PostRemarks();
        $postremarks->post_id      = trim($post_id);
        $postremarks->remark       = trim($remark);
        $postremarks->user_id      = trim($user_id); 
        $postremarks->created_at   = trim($created_at); 
        $this->tryToSaveData($postremarks, 'common.COULD_NOT_BE_CREATED');
        return $postremarks;
    }
 

}

