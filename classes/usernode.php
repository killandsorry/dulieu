<?

/*
 * This example would probably work best if you're using
 * an MVC framework, but it can be used standalone as well.
 *
 * This example also assumes you are using Predis, the excellent
 * PHP Redis library available here:
 * https://github.com/nrk/predis
 */
class UserNode {
	// The user's ID, probably loaded from MySQL
	private $id;
	
	// The redis server configuration
	private $redis_config = array( 
		array('host' => 'localhost', 'port' => 6379 )
	);
	
	// Stores the redis connection resource so that
	// we only need to connect to Redis once
	private $redis;
	
	public function __construct($userID) {
		$this->id = $userID;	
	}
	
	private function redis() {
		return false;
		if (!$this->redis) {
			$this->redis = new Redis();
			$this->redis->connect('127.0.0.1', 6379);
		}
		return $this->redis;
	}
	
	/*
	 * Makes this user follow the user with the given ID.
	 * In order to stay efficient, we need to make a two-way
	 * directed graph. This means when we follow a user, we also
	 * say that that user is followed by this user, making a forward
	 * and backword directed graph.
	 */
	public function follow($user) {
		$this->redis()->sAdd("graph:user:{$this->id}:following", $user);
		$this->redis()->sAdd("graph:user:$user:followed_by", $this->id);
	}
	
	/*
	 * Makes this user unfollow the user with the given ID.
	 * First we check to make sure that we are actually following
	 * the user we want to unfollow, then we remove both the forward
	 * and backward references.
	 */
	public function unfollow($user) {
		if ($this->is_following($user)) {
			$this->redis()->sRem("graph:user:{$this->id}:following", $user);
			$this->redis()->sRem("graph:user:$user:followed_by", $this->id);
		}
	}
	
	/*
	 * Returns an array of user ID's that this user follows.
	 */
	public function following() {
		return $this->redis()->sMembers("graph:user:{$this->id}:following");
	}
	
	/*
	 * Returns an array of user ID's that this user is followed by.
	 */
	 public function followed_by() {
	 	return $this->redis()->sMembers("graph:user:{$this->id}:followed_by");
	 }
	 
	/* 
	 * Test to see if this user is following the given user or not.
	 * Returns a boolean.
	 */
	public function is_following($user) {
		return $this->redis()->sIsMember("graph:user:{$this->id}:following", $user);
	}
	 
	/*
	 * Test to see if this user is followed by the given user.
	 * Returns a boolean.
	 */
	public function is_followed_by($user) {
		return $this->redis()->sIsMember("graph:user:{$this->id}:followed_by", $user);
	}
	
	/*
	 * Tests to see if the relationship between this user and the given user is mutual.
	 */
	public function is_mutual($user) {
		return ($this->is_following($user) && $this->is_followed_by($user));
	}
	
	/*
	 * Returns the number of users that this user is following.
	 */
	public function follow_count() {
		return $this->redis()->sCard("graph:user:{$this->id}:following");
	}
	
	/*
	 * Retuns the number of users that follow this user.
	 */
	public function follower_count() {
		return $this->redis()->sCard("graph:user:{$this->id}:followed_by");
	}
	
	/*
	 * Finds all users that the given users follow in common.
	 * Returns an array of user IDs
	 */
	public function common_following($users) {
		$redis = $this->redis();
		$users[] = $this->id;
		$keys = array();
		foreach ($users as $user) {
			$keys[] = "graph:user:{$user}:following";
		}
		
		return call_user_func_array(array($redis, "sInter"), $keys);
	}
	
	/*
	 * Finds all users that all of the given users are followed by in common.
	 * Returns an array of user IDs
	 */
	public function common_followed_by($users) {
		$redis = $this->redis();
		$users[] = $this->id;
		foreach ($users as $user) {
			$keys[] = "graph:user:{$user}:followed_by";
		}
		
		return call_user_func_array(array($redis, "sInter"), $keys);
	}
	
}