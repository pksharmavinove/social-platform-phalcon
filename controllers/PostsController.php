<?php

class PostsController extends ControllerBase
{

    /**
     * Index Method
     */
    public function index()
    {   
         
        //Get User Details using Token
        $token_decoded = $this->decodeToken($this->getToken());
        // print_r($token_decoded); die;
        $username_lat = $token_decoded->username_lat;
        $username_lng = $token_decoded->username_lng;
        $username_level = $token_decoded->username_level;

        if($username_level == 'User'){

            $sql         = "SELECT *, (  
                                6371 * acos(cos(radians($username_lat)) * cos(radians(`latitude`) ) *   cos(radians(`longitude`) - radians($username_lng)) + sin(radians($username_lat)) *  sin(radians(`latitude`)))  
                            ) AS distance  
                            FROM posts  
                            WHERE `latitude` != $username_lat AND `longitude` != $username_lng 
                            HAVING distance < 1200
                            ORDER BY distance ";
                            
            $connection = $this->db;
            $data       = $connection->query($sql);
            $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $results    = $data->fetchAll();
            
            
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $results);

        }else{
            $this->initializeGet();
            $options = $this->buildOptions('title asc', $this->request->get('sort'), $this->request->get('order'), $this->request->get('limit'), $this->request->get('offset'));
            $filters = $this->buildFilters($this->request->get('filter')); 
            $posts = $this->findElements('Posts', $filters['conditions'], $filters['parameters'], 'id, title, description', $options['order_by'], $options['offset'], $options['limit']);
            $total = $this->calculateTotalElements('Posts', $filters['conditions'], $filters['parameters']);
            $data = $this->buildListingObject($posts, $options['rows'], $total);
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }

        
    }

    /**
     * Create Method 
     */
    public function create()
    {   
        //Fetch Token Based Data
        $token_decoded = $this->decodeToken($this->getToken());
        $user_id = $token_decoded->username_id;
        $created_at = $this->getUnixTimeStamp();
        
        $this->initializePost();
        $this->checkForEmptyData([
                    $this->request->getPost('title'), 
                    $this->request->getPost('description'),
                    $this->request->getPost('latitude'),
                    $this->request->getPost('longitude')  
            ]); 
        $post = $this->createPost(
                    $this->request->getPost('title'), 
                    $this->request->getPost('description'),
                    $this->request->getPost('latitude'),
                    $this->request->getPost('longitude'),
                    $created_at,
                    $user_id
                );
        $this->registerLog();
        $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $post->toArray());
    }

    /**
     * 
     */
    public function get($id)
    {
        $this->initializeGet();
        $post = $this->findElementById('Posts', $id);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $post->toArray());
    }

    /**
     * 
     */
    public function update($id)
    {
        $this->initializePatch();
        $this->checkForEmptyData([
                    $this->request->getPut('title'), 
                    $this->request->getPut('description')
            ]);  
        $post = $this->updatePost($this->findElementById('Posts', $id), $this->request->getPut('title'), $this->request->getPut('description'));
        $this->registerLog();
        $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $post->toArray());
    }

    /**
     * 
     */
    public function delete($id)
    {
        $this->initializeDelete();
        if ($this->tryToDeleteData($this->findElementById('Posts', $id))) {
            $this->registerLog();
            $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
        }
    }



    /**
     * Private Methods
     */

    private function createPost($title, $description, $latitude, $longitude, $created_at, $user_id)
    {
        $post               = new Posts();
        $post->title        = trim($title);
        $post->description  = trim($description);
        $post->latitude     = trim($latitude);
        $post->longitude    = trim($longitude);
        $post->created_at   = trim($created_at);
        $post->user_id      = trim($user_id);
        $this->tryToSaveData($post, 'common.COULD_NOT_BE_CREATED');
        return $post;
    }

    /**
     * 
     */
    private function updatePost($post, $title, $description)
    {
        $post->title        = trim($title);
        $post->description  = trim($description); 
        $this->tryToSaveData($post, 'common.COULD_NOT_BE_UPDATED');
        return $post;
    }

}

