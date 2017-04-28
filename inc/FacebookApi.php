<?php  
namespace net\kon;

require_once __DIR__ . '/../Facebook/autoload.php';
require_once __DIR__ . '/Exceptions/ApiException.php';

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use net\kon\exceptions\ApiException;



class FacebookApi {

	/**
     * @var Facebook The facebook client.
     */
	private $fbClient;

	/**
     * @var string The app id.
     */
	private $appId;

	/**
     * @var string The app secret.
     */
	private $secret;

	/**
	 * @param $appId
	 * @param $secret
	 */
	public function __construct($appId, $secret) {
        $this -> appId = $appId;
        $this -> secret = $secret;
        $this -> fbClient = new Facebook([
			'app_id' => $appId,
			'app_secret' => $secret,
			'default_graph_version' => 'v2.9'
		]);
    }

    /**
     * 取得facebook login url, 需搭配session_start()
     * 
     * @param $returnUrl
     * @param $permissions
     *
     * @return string
     */
    public function getLoginUrl($returnUrl, $permissions) {
        $redirectLoginHelper = $this -> fbClient -> getRedirectLoginHelper();
        return $redirectLoginHelper -> getLoginUrl($returnUrl, $permissions);
    }

    /**
     * 取得accessToken
     * 
     * @return string
     */
    public function getAccessToken() {
        $helper = $this -> fbClient -> getRedirectLoginHelper();
        $accessToken = $helper -> getAccessToken();
        if($accessToken=='') {
            $helper = $this -> fbClient -> getCanvasHelper();
            $accessToken = $helper -> getAccessToken();
        }

        return $accessToken -> getValue();
    }


    /**
     * 取得自己相關資料, fields: accounts(取得粉絲團), groups(取得社團)
     *
     * @param string $accesstoken
     * @param array $fields
     *
     * @return array
     */
    public function queryMe($accessToken, $fields=array()) {
    	try {
    		$graphApi = '/me';
    		if(sizeof($fields)>0) {
    			$graphApi = $graphApi . '?fields=' . implode(',', $fields);
    		}
    		$response = $this -> fbClient -> get($graphApi, $accessToken);
    		return $response -> getGraphUser() -> asArray();
    	}
    	catch(FacebookResponseException $e) {
    		echo 'Facebook Response returned an error: ' . $e->getMessage();
    	}
    	catch(FacebookSDKException $e) {
    		echo 'Facebook SDK returned an error: ' . $e->getMessage();
    	}
    }

    /**
     * 重新取得長效token
     * 
     * @param string $accessToken
     * 
     * @return string
     */
    public function getLongLiveToken($accessToken) {
    	try {
	    	$oAuth2Client = $this -> fbClient -> getOAuth2Client();
	    	$longLivedAccessToken = $oAuth2Client -> getLongLivedAccessToken($accessToken);
	    	return $longLivedAccessToken -> getValue();
	    }
	    catch(Exception $e) {
	    	echo 'Get long live token returned an error: ' . $e -> getMessage();
	    }
    }


    /**
     * 發佈訊息，可用粉絲團id, 個人id, 社團id 進行發文
     * 
     * @param $accessToken
     * @param $sourceId
     * @param $message
     * @param $link
     *
     * @return array
     */
    public function publishFeed($accessToken, $sourceId, $message, $link='') {
    	try {
    		$graphApi = "/$sourceId/feed";
    		$postData = array('message'=> $message, 'link'=> $link);
    		$response = $this -> fbClient -> post($graphApi, $postData, $accessToken);
    		return $response -> getGraphNode() -> asArray();
    	}
    	catch(Exception $e) {
    		echo "feed error: " . $e -> getMessage();
    	}
    }

    /**
     * 取得feed id的訊息與留言資料, 若沒sinceDate則抓最原先
     *
     * @param $accessToken
     * @param $feedId
     * @param $sinceDate 開始日期(Y-m-d H:i:s)
     * 
     * @return array
     */
    public function queryCommentsByFeedId($accessToken, $feedId, $sinceDate='') {
        try {
            $graphApi = "/$feedId?fields=created_time,";
            $graphApi .= ($sinceDate != '') ? "comments.since($sinceDate)" :  "comments";
            $graphApi .= '{message,comments,created_time,from}';
            $response = $this -> fbClient -> get($graphApi,$accessToken);
            return $response -> getGraphNode() -> asArray();
        }
        catch(Exception $e) {
            echo "query feed comments error: " . $e -> getMessage();
        }
    }

    /**
     * 取得粉絲團(page_id)，社團(group_id)，個人(me)的貼文
     * 
     * @param $accessToken
     * @param $sourceId
     * @param $sinceDate
     *
     * @return array
     */
    public function queryFeeds($accessToken, $sourceId, $sinceDate='') {
        try {
            $graphApi = "/$sourceId?fields=feed";
            $graphApi .= $sinceDate!= "" ? ".since($sinceDate)" : "";
            $response = $this -> fbClient -> get($graphApi, $accessToken);
            return $response -> getGraphNode() -> asArray();
         }
        catch(Exception $e) {

        }

    }


    /**
     * 回覆comment id的訊息, permission: publish_pages
     * 
     * @param $accessToken
     * @param $commentId
     * @param $message
     *
     * @return array
     */
    public function replyComments($accessToken, $commentId, $message) {
        try {
            $graphApi = "/$commentId/comments";
            $postFields = array('message' => $message);
            $response = $this -> fbClient -> post($graphApi, $postFields, $accessToken);
            return $response -> getGraphNode() -> asArray();
        }
        catch(Exception $e) {
            echo "reply comment error: " . $e -> getMessage();
        }
    }

    /**
     * 透過facebook messenger, 回覆comment id訊息, permission: read_page_mailboxes  (目前只有粉絲團有此功能)
     * 
     * @param $accessToken 粉絲團必須是粉絲團的accessToken
     * @param $commentId
     * @param $message
     *
     * @return array
     */
    public function replyCommentsViaMessenger($accessToken, $commentId, $message) {
        try {
            $graphApi = "/$commentId/private_replies";
            $postFields = array('message' => $message);
            $response = $this -> fbClient -> post($graphApi, $postFields, $accessToken);
            return $response -> getGraphNode() -> asArray();
        }
        catch(Exception $e) {
            echo "reply comment via facebook messenger error: " . $e -> getMessage();
        }
    }

    /**
     * 建立相簿,  publish_actions and user_photos permission
     * 
     * @param $accessToken
     * @param $sourceId
     * @param $albumName
     *
     * @return array
     */
    public function createAlbum($accessToken, $sourceId, $albumName) {
        try {
            $graphApi = "/$sourceId/albums";
            $postFields = array('name' => $albumName);
            $response = $this -> fbClient -> post($graphApi, $postFields, $accessToken);
            return $response -> getGraphNode() -> asArray();
        }
        catch(Exception $e) {
            echo "create album error: " . $e -> getMessage();

        }
    }

    /**
     * 上傳圖片到相本
     * 
     * @param $accessToken
     * @param $albumId
     * @param $photoPathOrUrl
     * @param $message
     *
     * @return array
     *
     * @throws ApiException
     */
    public function uploadPhotoToAlbum($accessToken, $albumId, $photoPathOrUrl, $message) {
        $pathData = parse_url($photoPathOrUrl);
        $scheme = $pathData['scheme'];
        $host = $pathData['host'];
        $path = $pathData['path'];
        if(($scheme!='http' && $scheme!='https') || $host=='') {
            if(!file_exists($photoPathOrUrl)) {
                $errCode = ApiException::$FILE_NOT_EXIST;
                $errMsg = ApiException::$ERROR_MESSAGE[$errCode];
                throw new ApiException($errMsg, $errCode);
            }
        }

        try {
            $uploadData = [
                'message' => $message,
                'source' => $this -> fbClient -> fileToUpload($photoPathOrUrl),
            ];

            $response = $this -> fbClient -> post("/$albumId/photos", $uploadData, $accessToken);
            return $response -> getGraphNode() -> asArray();
        }
        catch(Exception $e) {
            echo "upload photo to album error: " . $e -> getMessage();
        }
    }


}
?>